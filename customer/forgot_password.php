<?php
include '../includes/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32)); // Secure random token
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); // Valid for 1 hour

    $stmt = $conn->prepare("UPDATE customers SET reset_token = ?, reset_expires = ? WHERE customer_email = ?");
    $stmt->execute([$token, $expires, $email]);

    if ($stmt->rowCount() > 0) {
        // In a real project, you'd email this link:
        $reset_link = "http://localhost/grocery/reset_password.php?token=" . $token;
        echo "<div class='success'>Reset link generated (Demo): <a href='$reset_link'>Click here to reset</a></div>";
    } else {
        echo "<div class='error'>Email not found.</div>";
    }
}
?>

<form method="POST">
    <h2>Forgot Password</h2>
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>