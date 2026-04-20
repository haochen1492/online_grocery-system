<?php
include '../includes/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $v_code = bin2hex(random_bytes(16)); // Generate verification code

    // Check if email exists
    $check = $conn->prepare("SELECT * FROM customers WHERE customer_email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $error = "Email already registered!";
    } else {
        $sql = "INSERT INTO customers (customer_name, customer_email, customer_password, verification_code) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $email, $password, $v_code])) {
            // In a real server, use mail() or PHPMailer here:
            // mail($email, "Verify Email", "Click here: verify.php?code=$v_code");
            echo "<script>alert('Registration successful! Please check your email (simulated code: $v_code)'); window.location='login.php';</script>";
        }
    }
}
?>
<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>