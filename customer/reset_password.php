<?php
include '../includes/dbconnect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM customers WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Invalid or expired token.");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Update password and clear the token
        $update = $conn->prepare("UPDATE customers SET customer_password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        if ($update->execute([$new_password, $token])) {
            echo "<script>alert('Password updated! Please login.'); window.location='login.php';</script>";
        }
    }
}
?>

<form method="POST">
    <h2>Create New Password</h2>
    <input type="password" name="password" placeholder="New Password" required minlength="6">
    <button type="submit">Update Password</button>
</form>