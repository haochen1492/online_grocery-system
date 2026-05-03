<?php
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32)); 
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); 

    $stmt = $conn->prepare("UPDATE customers SET reset_token = ?, reset_expires = ? WHERE customer_email = ?");
    $stmt->execute([$token, $expires, $email]);

    if ($stmt->rowCount() > 0) {
        $reset_link = "reset_password.php?token=" . $token;
        $success = "Reset link generated: <a href='$reset_link'>Click here to reset your password</a>";
    } else {
        $error = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online Grocery System</title>
    <link rel="stylesheet" href="includes/styles.css"> <!--[cite: 7] -->
</head>
<body>

<?php include 'includes/header.php'; ?> <!--[cite: 6] -->

<div class="auth-container">
    <h2>Reset Password</h2>
    <p>Enter your email and we will provide a link to reset your password.</p>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php elseif (isset($success)): ?>
        <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Registered Email:</label>
        <input type="email" name="email" required placeholder="e.g. user@example.com">

        <button type="submit" class="btn btn-secondary">Send Reset Link</button>
    </form>
    
    <p style="margin-top: 15px;">
        Remember your password? <a href="login.php">Back to Login</a>
    </p>
</div>

</body>
</html>