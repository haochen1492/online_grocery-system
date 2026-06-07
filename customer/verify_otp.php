<?php
include '../includes/dbconnect.php';
session_start();

// Get email from URL safely
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp_input = $_POST['otp_code'];

    // Check OTP record from DB
    $stmt = $conn->prepare("SELECT otp_code FROM register_verify WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row['otp_code'] === $otp_input) {
            
            // Start transaction to change email safely
            $conn->begin_transaction();
            
            try {
                if (isset($_SESSION['customer_id'])) {
                    // BUG FIX: The user is updating their existing account email.
                    // We finalize the change here now that the code matches!
                    $user_id = $_SESSION['customer_id'];
                    $update = $conn->prepare("UPDATE customers SET customer_email = ?, is_verified = 1 WHERE customer_id = ?");
                    $update->bind_param("si", $email, $user_id);
                } else {
                    // The user is a guest verifying their new registration signup
                    $update = $conn->prepare("UPDATE customers SET is_verified = 1 WHERE customer_email = ?");
                    $update->bind_param("s", $email);
                }
                
                $update->execute();

                // Delete the used OTP code record
                $delete = $conn->prepare("DELETE FROM register_verify WHERE email = ?");
                $delete->bind_param("s", $email);
                $delete->execute();

                $conn->commit();

                // Redirect based on session status
                if (isset($_SESSION['customer_id'])) {
                    header("Location: profile.php?email_update=success");
                } else {
                    header("Location: login.php?registration=success");
                }
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Verification failed to save. Please try again.";
            }
        } else {
            $error = "Invalid OTP verification code. Please try again.";
        }
    } else {
        $error = "No verification request found for this email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <h2>Email Verification</h2>
    <p style="margin-bottom: 15px; font-size: 0.95em; color: #555;">
        We have sent a 6-digit OTP code to <strong><?php echo htmlspecialchars($email); ?></strong>. Please check your inbox.
    </p>

    <?php if (isset($error)): ?>
        <div class="error-msg" style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 10px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

        <label>Enter 6-Digit OTP Code:</label>
        <input type="text" name="otp_code" maxlength="6" pattern="\d{6}" placeholder="e.g. 123456" style="letter-spacing: 2px; font-size: 1.1em; text-align: center;" required autocomplete="off">
        
        <button type="submit" class="btn" style="margin-top: 15px;">Verify Code</button>
    </form>

    <p style="margin-top: 15px;">
        Wrong email? 
        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="profile.php">Back to Profile</a>
        <?php else: ?>
            <a href="register.php">Back to Register</a>
        <?php endif; ?>
    </p>
</div>

</body>
</html>