<?php
include '../includes/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- DOUBLE CHECK EMAIL FORMAT HERE ---
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format! Please provide a real email.'); window.history.back();</script>";
        exit(); // Stop the script from running further
    }
    // ---------------------------------------

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    $v_code = bin2hex(random_bytes(16));

    // Check if email exists...
    $check = $conn->prepare("SELECT * FROM customers WHERE customer_email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        echo "<script>alert('Email already registered!'); window.history.back();</script>";
    } else {
        // Proceed with INSERT...
    }
}
?>
<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>