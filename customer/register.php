<?php
include '../includes/dbconnect.php';
session_start();

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust these paths based on where your PHPMailer files are located
require '../vendor/phpmailer/Exception.php';
require '../vendor/phpmailer/PHPMailer.php';
require '../vendor/phpmailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; 

    // Password Complexity Checks
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    // 1. Server-side Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } 
    else if (!ctype_digit($phone)) {
        $error = "Phone number must contain only digits.";
    } 
    else if (!$uppercase || !$lowercase || !$specialChars || strlen($password) < 15) {
        $error = "Password does not meet security requirements.";
    }
    else if ($password !== $confirm_password) { 
        $error = "Passwords do not match!";
    }
    else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // 2. Check if email exists
        $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered!";
        } else {
            // Start transaction to ensure both DB insert and OTP creation succeed together
            $conn->begin_transaction();

            try {
                // 3. Insert into DB (is_verified defaults to 0)
                $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_email, customer_password, customer_phone, is_verified) VALUES (?, ?, ?, ?, 0)");
                $stmt->bind_param("ssss", $name, $email, $password_hashed, $phone);
                $stmt->execute();

                // 4. Generate 6-Digit OTP
                $otp_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

                // Save or Update OTP in register_verify table
                $otp_stmt = $conn->prepare("INSERT INTO register_verify (email, otp_code) VALUES (?, ?) ON DUPLICATE KEY UPDATE otp_code = ?, created_at = CURRENT_TIMESTAMP");
                $otp_stmt->bind_param("sss", $email, $otp_code, $otp_code);
                $otp_stmt->execute();

                // 5. Send OTP via PHPMailer
                $mail = new PHPMailer(true);

                // Server configurations
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'infinitygrocer7@gmail.com';       
                $mail->Password   = 'lfxd qida epnm wzxl'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('infinitygrocer7@gmail.com', 'Infinity Grocer');
                $mail->addAddress($email, $name);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Infinity Grocer Account';
                $mail->Body    = "<h3>Welcome to Infinity Grocer, $name!</h3>
                                  <p>Your OTP code for verification is: <b>$otp_code</b></p>
                                  <p>This code will expire shortly.</p>";

                $mail->send();
                
                // Commit changes if everything went well
                $conn->commit();

                // Redirect to OTP Verification page
                header("Location: verify_otp.php?email=" . urlencode($email));
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Failed to send verification email. Mailer Error: {$mail->ErrorInfo}";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration failed. Please try again.";
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
    <title>Register - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        body {
            background-image: url('images/login_background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .requirement { color: red; font-size: 0.85em; display: block; margin-top: 2px; }
        .valid { color: green; }
        .error-hint { color: red; font-size: 0.85em; display: none; }
        
        /* Ensures the form containers retain readability over the graphic background */
        .auth-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .footer-container, .footer-container .copy {
            color: #ffffff !important;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <h2>CREATE AN ACCOUNT</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg" style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 10px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Full Name:</label>
        <input type="text" name="name" required>

        <label>Email Address:</label>
        <input type="email" name="email" required>

        <label>Phone Number:</label>
        <input type="text" name="phone" id="phoneInput" oninput="validatePhone()" required>

        <label>Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="regPassword" onkeyup="checkRequirements()" required>
            <span class="material-icons toggle-eye" onclick="toggleSinglePass('regPassword', this)">visibility_off</span>
        </div>  
        <div id="passwordTips" style="margin-bottom: 10px;">
            <span id="len" class="requirement">• Minimum 15 characters</span>
            <span id="upper" class="requirement">• 1 Uppercase letter</span>
            <span id="lower" class="requirement">• 1 Lowercase letter</span>
            <span id="special" class="requirement">• 1 Special character (@, #, $, etc.)</span>
        </div>

        <label>Confirm Password:</label>
        <div class="password-wrapper">
            <input type="password" name="confirm_password" id="confirmPassword" onkeyup="checkMatch()" required>
            <span class="material-icons toggle-eye" onclick="toggleSinglePass('confirmPassword', this)">visibility_off</span>
        </div>
        <span id="matchError" class="error-hint">Passwords must match!</span>
        
        <button type="submit" class="btn">Register</button>
    </form>
    <p style="margin-top: 15px;">
        Already a Infinity Grocer Member? <a href="login.php">Welcome Back, Log in</a>
    </p>
</div>

<script>
function checkRequirements() {
    var val = document.getElementById("regPassword").value;
    
    document.getElementById("len").className = val.length >= 15 ? "requirement valid" : "requirement";
    document.getElementById("upper").className = /[A-Z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("lower").className = /[a-z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("special").className = /[^\w]/.test(val) ? "requirement valid" : "requirement";
    
    checkMatch(); 
}

function checkMatch() {
    var p1 = document.getElementById("regPassword").value;
    var p2 = document.getElementById("confirmPassword").value;
    var hint = document.getElementById("matchError");

    if (p1 !== p2 && p2 !== "") {
        hint.style.display = "block";
    } else {
        hint.style.display = "none";
    }
}

function validatePhone() {
    var input = document.getElementById("phoneInput");
    input.value = input.value.replace(/[^0-9]/g, ''); 
}

function toggleSinglePass(fieldId, iconElement) {
    const passwordField = document.getElementById(fieldId);
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        iconElement.textContent = "visibility"; 
    } else {
        passwordField.type = "password";
        iconElement.textContent = "visibility_off"; 
    }
}
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>