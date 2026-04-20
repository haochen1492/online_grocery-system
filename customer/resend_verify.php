<?php
include '../includes/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the user exists and is NOT yet verified
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_email = ? AND is_verified = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $v_code = $user['verification_code'];
        // Logic to send email goes here
        // mail($email, "Resend: Verify Your Email", "Click here: verify.php?code=$v_code");
        echo "<script>alert('Verification link resent! Please check your inbox.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Email not found or already verified.');</script>";
    }
}
?>

<div class="resend-container" style="padding: 50px; text-align: center;">
    <h2>Resend Verification Email</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your registered email" required style="padding: 10px; width: 250px;">
        <br><br>
        <button type="submit" style="padding: 10px 20px; cursor: pointer;">Send Link</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
</div>