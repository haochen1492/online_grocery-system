<?php
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Double check email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format! Please provide a real email.";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $v_code = bin2hex(random_bytes(16));

        // Check if email exists in customers table[cite: 1]
        $check = $conn->prepare("SELECT * FROM customers WHERE customer_email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            $sql = "INSERT INTO customers (customer_name, customer_email, customer_password, verification_code) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$name, $email, $password_hashed, $v_code])) {
                echo "<script>alert('Registration successful! Please check your email. (Simulated Code: $v_code)'); window.location='login.php';</script>";
                exit();
            }
        }
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

    <form method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required placeholder="Enter your full name">

        <label for="email">Email Address:</label>
        <input type="email" name="email" required placeholder="Enter your email">

        <label for="password">Password:</label>
        <input type="password" name="password" required minlength="6" placeholder="At least 6 characters">

        <button type="submit" class="btn">Register</button>
    </form>
    
    <p style="margin-top: 15px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

</body>
</html>