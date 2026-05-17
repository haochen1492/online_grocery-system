<?php 
// 1. Link to your project's connection file
include '../includes/dbconnect.php';
session_start();

// Redirect away if already logged in (Matches your login.php logic)
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php"); 
    exit();
}

$message = "";
$message_type = "";

if (isset($_POST['subforgot'])) {
    $email = $_POST['login_var'];

    // 2. Object-Oriented Prepared Statement targeting your 'customers' table
    $stmt = $conn->prepare("SELECT customer_email FROM customers WHERE customer_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        // Securely generate a reset token
        $token = bin2hex(random_bytes(50));

        // 3. Insert into token validation tracking table
        $ins_stmt = $conn->prepare("INSERT INTO pass_reset (email, token) VALUES (?, ?)");
        $ins_stmt->bind_param("ss", $email, $token);
        
        if ($ins_stmt->execute()) {
            
            // ==========================================
            // NEW FIX: FIXED LOCAL MAIL VIA PHPMAILER
            // ==========================================
            require 'PHPMailer/Exception.php';
            require 'PHPMailer/PHPMailer.php';
            require 'PHPMailer/SMTP.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                // Server configurations settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';               // Using Gmail standard server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'angqiyang2006@gmail.com';     // CHANGE THIS to your Gmail address
                $mail->Password   = 'nmql tzth fzpl gpey';          // CHANGE THIS to your 16-character Google App Password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients routing metadata
                $mail->setFrom('no_reply@infinitygrocer.com', 'Infinity Grocer');
                $mail->addAddress($email); 

                // Email layout HTML content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request - Infinity Grocer';
                
                $reset_link = "http://localhost/online_grocery-system/customer/password_reset.php?token=" . $token;                
                $mail->Body    = "<h3>You have requested a password reset</h3>
                                  <p>Click the link below to reset your passwor:</p>
                                  <p><a href='".$reset_link."'>".$reset_link."</a></p>
                                  <br><br>
                                  <hr>
                                  <center>All rights reserved | Infinity Grocer</center>";

                $mail->send();
                
                $message = "A reset link has been sent to your registered email address. Please check your inbox.";
                $message_type = "alert-success";
                $hide = true;

            } catch (Exception $e) {
                // Capture details if something structural fails (e.g., wrong credential variables)
                $message = "The server failed to send the email. Mailer Error: {$mail->ErrorInfo}";
                $message_type = "alert-error";
            }
            // ==========================================
            
        } else {
            $message = "Something went wrong database-side. Please try again.";
            $message_type = "alert-error";
        }
    } else {
        $message = "No account found with that email address.";
        $message_type = "alert-error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css"> 
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="auth-container" style="max-width: 450px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    <h2>Forgot Password</h2>
    <p style="font-size: 0.9em; color: #666; margin-bottom: 20px;">Enter your email address to receive a secured password reset link.</p>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $message_type; ?>" style="padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; font-weight: bold; <?php echo ($message_type == 'alert-success') ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!isset($hide)): ?>
    <form method="POST" action="forgot_password.php">
        <label for="email">Email Address:</label>
        <input type="email" name="login_var" required placeholder="Enter your registered email" style="width: 100%; padding: 10px; margin: 8px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <button type="submit" name="subforgot" class="btn" style="width: 100%; padding: 12px; background-color: #329b18; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Send Reset Link</button>
    </form>
    <?php endif; ?>
    
    <p style="margin-top: 20px; text-align: center;">
        Remembered it? <a href="login.php">Back to Login</a>
    </p>
</div>

</body>
</html>