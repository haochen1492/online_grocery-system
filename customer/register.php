<?php
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // New field[cite: 3]

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
    else if ($password !== $confirm_password) { // Match Check
        $error = "Passwords do not match!";
    }
    else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // 2. Check if email exists[cite: 3, 6]
        $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered!";
        } else {
            // 3. Insert into DB[cite: 3]
            $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_email, customer_password, customer_phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hashed, $phone);
            
            if ($stmt->execute()) {
                header("Location: login.php?registration=success");
                exit();
            } else {
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
    <style>
        .requirement { color: red; font-size: 0.85em; display: block; margin-top: 2px; }
        .valid { color: green; }
        .error-hint { color: red; font-size: 0.85em; display: none; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <h2>Create Customer Account</h2>

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
        <input type="password" name="password" id="regPassword" onkeyup="checkRequirements()" required>
        <!-- Requirement Tips[cite: 5] -->
        <div id="passwordTips" style="margin-bottom: 10px;">
            <span id="len" class="requirement">• Minimum 15 characters</span>
            <span id="upper" class="requirement">• 1 Uppercase letter</span>
            <span id="lower" class="requirement">• 1 Lowercase letter</span>
            <span id="special" class="requirement">• 1 Special character (@, #, $, etc.)</span>
        </div>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirmPassword" onkeyup="checkMatch()" required>
        <span id="matchError" class="error-hint">Passwords must match!</span>
        
        <div style="margin: 10px 0;">
            <input type="checkbox" onclick="togglePassword()"> Show Passwords
        </div>

        <button type="submit" class="btn">Register</button>
    </form>
        <p style="margin-top: 15px;">
        Already a Infinity Grocer Member? <a href="login.php">Welcome Back, Log in</a>
    </p>
</div>

<script>
function checkRequirements() {
    var val = document.getElementById("regPassword").value;
    
    // Check and update colors
    document.getElementById("len").className = val.length >= 15 ? "requirement valid" : "requirement";
    document.getElementById("upper").className = /[A-Z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("lower").className = /[a-z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("special").className = /[^\w]/.test(val) ? "requirement valid" : "requirement";
    
    checkMatch(); // Re-verify match if main password changes
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
    input.value = input.value.replace(/[^0-9]/g, ''); // Numeric only[cite: 6]
}

function togglePassword() {
    var p1 = document.getElementById("regPassword");
    var p2 = document.getElementById("confirmPassword");
    p1.type = p1.type === "password" ? "text" : "password";
    p2.type = p2.type === "password" ? "text" : "password";
}
</script>

</body>
</html>