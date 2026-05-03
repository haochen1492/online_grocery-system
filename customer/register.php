<?php
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Double check email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $v_code = bin2hex(random_bytes(16));

        // 2. Check if email exists using MySQLi syntax
        $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
        $check->bind_param("s", $email); // "s" means the parameter is a string
        $check->execute();
        $check->store_result(); // Required to use num_rows

        // FIX: Use num_rows instead of rowCount()
        if ($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // 3. Insert new customer into customers table
            $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_email, customer_password, verification_code) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hashed, $v_code);
            
            if ($stmt->execute()) {
                // 4. Send verification email
                $to = $email;
                $subject = "Verify your Infinity Grocer account";
                $message = "Hi $name,\n\nPlease click the link below to verify your account:\n\nhttp://localhost/online_grocery-system/customer/verify.php?code=$v_code\n\nThank you!";
                $headers = "From: noreply@infinitygrocer.com";
                header("Location: login.php?registration=success");
                exit();
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
    <link rel="stylesheet" href="includes/styles.css"> <!--[cite: 7] -->
</head>
<body>

<?php include 'includes/header.php'; ?> <!--[cite: 6] -->

<div class="auth-container">
    <h2>Create an Account</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Inside your register.php auth-container -->
<form method="POST">
    <label for="name">Full Name:</label>
    <input type="text" name="name" required placeholder="Enter your full name">

    <label for="email">Email Address:</label>
    <input type="email" name="email" required placeholder="Enter your email">

    <label for="password">Password:</label>
    <!-- Added id="regPassword" -->
    <input type="password" name="password" id="regPassword" required minlength="6" placeholder="At least 6 characters">
    
    <!-- Added Show Password Toggle -->
    <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
        <input type="checkbox" id="showPassToggle" onclick="togglePassword()">
        <label for="showPassToggle" style="margin-top: 0; font-weight: normal; cursor: pointer;">Show Password</label>
    </div>

    <button type="submit" class="btn">Register</button>
</form>

<!-- Add this JavaScript right before the closing </body> tag -->
<script>
function togglePassword() {
    var x = document.getElementById("regPassword");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
</script>
    
    <p style="margin-top: 15px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

</body>
</html>