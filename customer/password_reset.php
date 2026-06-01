<?php 
include '../includes/dbconnect.php';
session_start();

if (isset($_SESSION['customer_id'])) {
    header("Location: index.php"); 
    exit();
}

$errors = [];
$success = "";
$hide = false;

// Grab token from URL parameter
if (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    $errors[] = "Token is missing from the reset URL.";
    $hide = true;
}

if (isset($_POST['sub_set']) && !$hide) {
    $password = $_POST['password'];
    $passwordConfirm = $_POST['passwordConfirm'];

    // Validations (Matching your profile.php password criteria requirements)
    if (empty($password) || empty($passwordConfirm)) {
        $errors[] = 'Please fill out both password fields.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 15) {
        $errors[] = 'The password must be at least 15 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least 1 uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least 1 lowercase letter.';
    }
    if (!preg_match('/[^\w]/', $password)) {
        $errors[] = 'Password must contain at least 1 special character.';
    }

    if (empty($errors)) {
        // Find matching email belonging to token
        $stmt = $conn->prepare("SELECT email FROM pass_reset WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user_token = $res->fetch_assoc()) {
            $emailtok = $user_token['email'];

            // Hash the password securely matching your native system parameters
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update user table
            $update_stmt = $conn->prepare("UPDATE customers SET customer_password = ? WHERE customer_email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $emailtok);
            
            if ($update_stmt->execute()) {
                $success = "Your password has been updated successfully!";
                
                // Drop token so it can't be reused maliciously
                $del_stmt = $conn->prepare("DELETE FROM pass_reset WHERE token = ?");
                $del_stmt->bind_param("s", $token);
                $del_stmt->execute();
                
                $hide = true;
            } else {
                $errors[] = "Error changing password in system storage.";
            }
        } else {
            $errors[] = 'This link is invalid or has expired.';
            $hide = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        .requirement { font-size: 0.85em; color: red; display: block; }
        .valid { color: green; }
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .toggle-eye { position: absolute; right: 10px; cursor: pointer; color: #666; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="auth-container" style="max-width: 450px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    <h2>Reset Password</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-box" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #f5c6cb;">
            <?php foreach($errors as $err) echo "<p style='margin:4px 0;'>• $err</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success-box" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #c3e6cb; text-align: center;">
            <span style='font-size: 40px;'>&#9989;</span><br>
            <p><?php echo $success; ?></p>
            <a href="login.php" style="color: #155724; font-weight: bold; text-decoration: underline;">Click here to Login</a>
        </div>
    <?php endif; ?>

    <?php if (!$hide): ?>
    <form method="POST" action="">
        <label>New Password</label>
        <div class="password-wrapper" style="margin-bottom: 10px;">
            <input type="password" name="password" id="new_pass" onkeyup="checkRequirements()" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <span class="material-icons toggle-eye" onclick="toggleSinglePass('new_pass', this)">visibility_off</span>
        </div>
        
        <div id="passwordTips" style="margin-bottom: 15px;">
            <span id="len" class="requirement">• Minimum 15 characters</span>
            <span id="upper" class="requirement">• 1 Uppercase letter</span>
            <span id="lower" class="requirement">• 1 Lowercase letter</span>
            <span id="special" class="requirement">• 1 Special character (@, #, $, etc.)</span>
        </div>

        <label>Confirm New Password</label>
        <div class="password-wrapper" style="margin-bottom: 15px;">
            <input type="password" name="passwordConfirm" id="confirm_pass" onkeyup="checkMatch()" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <span class="material-icons toggle-eye" onclick="toggleSinglePass('confirm_pass', this)">visibility_off</span>
        </div>
        <span id="matchError" style="color:red; display:none; font-size:0.8em; margin-bottom: 10px;">Passwords do not match!</span>

        <button type="submit" name="sub_set" class="btn" style="width: 100%; padding: 12px; background-color: #48327a; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Update Password</button>
    </form>
    <?php endif; ?>
</div>

<script>
function toggleSinglePass(fieldId, iconElement) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
        field.type = "text";
        iconElement.textContent = "visibility";
    } else {
        field.type = "password";
        iconElement.textContent = "visibility_off";
    }
}

function checkRequirements() {
    var val = document.getElementById("new_pass").value;
    document.getElementById("len").className = val.length >= 15 ? "requirement valid" : "requirement";
    document.getElementById("upper").className = /[A-Z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("lower").className = /[a-z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("special").className = /[^\w]/.test(val) ? "requirement valid" : "requirement";
    checkMatch();
}

function checkMatch() {
    const p1 = document.getElementById("new_pass").value;
    const p2 = document.getElementById("confirm_pass").value;
    document.getElementById("matchError").style.display = (p1 !== p2 && p2 !== "") ? "block" : "none";
}
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>