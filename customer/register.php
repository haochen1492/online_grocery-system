<?php
// These "use" statements must be at the very top of the file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Path fixed to go up one level (../) to find the vendor folder[cite: 2]
require '../vendor/phpmailer/Exception.php';
require '../vendor/phpmailer/PHPMailer.php';
require '../vendor/phpmailer/SMTP.php';

include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $v_code = bin2hex(random_bytes(16)); // Generate random verification code

        // 2. Check if email exists using MySQLi syntax[cite: 1]
        $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result(); // Required for num_rows

        if ($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // 3. Insert new customer into the database[cite: 1]
            $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_email, customer_password, verification_code) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hashed, $v_code);
            
            if ($stmt->execute()) {
                // 4. Send verification email using PHPMailer SMTP
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'YOUR_GMAIL@gmail.com'; // Enter your Gmail here
                    $mail->Password   = 'YOUR_APP_PASSWORD';    // Enter 16-character App Password here
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Recipients
                    $mail->setFrom('YOUR_GMAIL@gmail.com', 'Infinity Grocer');
                    $mail->addAddress($email, $name); // Sends to the address typed by the customer[cite: 1]

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Verify your Infinity Grocer account';
                    
                    // Create the verification link
                    $verification_link = "http://localhost/online_grocery-system/customer/verify.php?code=$v_code";
                    
                    $mail->Body = "
                        <h2>Welcome to Infinity Grocer, $name!</h2>
                        <p>Please click the button below to verify your email address and activate your account.</p>
                        <a href='$verification_link' style='background:#329b18; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block;'>Verify Email</a>
                        <p>If the button doesn't work, copy and paste this link into your browser: <br> $verification_link</p>
                    ";

                    $mail->send();
                    
                    // Redirect to login page with success message parameter
                    header("Location: login.php?registration=success");
                    exit();
                } catch (Exception $e) {
                    $error = "Account created, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Grocery System</title>
    <link rel="stylesheet" href="includes/styles.css"> <!-- -->
</head>
<body>

<?php include 'includes/header.php'; ?> <!-- -->

<div class="auth-container">
    <h2>Create an Account</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div> <!-- -->
    <?php endif; ?>

    <form method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required placeholder="Enter your full name">

        <label for="email">Email Address:</label>
        <input type="email" name="email" required placeholder="Enter your email">

        <label for="password">Password:</label>
        <input type="password" name="password" id="regPassword" required minlength="6" placeholder="At least 6 characters">
        
        <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="showPassToggle" onclick="togglePassword()">
            <label for="showPassToggle" style="margin-top: 0; font-weight: normal; cursor: pointer;">Show Password</label>
        </div>

        <button type="submit" class="btn">Register</button>
    </form>
    
    <p style="margin-top: 15px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<script>
// Function to toggle password visibility
function togglePassword() {
    var x = document.getElementById("regPassword");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
</script>

</body>
</html>