<?php
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... your existing PHP logic for authentication ...
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Grocery System</title>
    <!-- LINKING CSS FILE HERE -->
    <link rel="stylesheet" href="includes/styles.css"> <!--[cite: 7] -->
</head>
<body>

<!-- LINKING HEADER FILE HERE -->
<?php include 'includes/header.php'; ?> <!--[cite: 6] -->

<div class="auth-container"> <!-- Uses the class we defined in styles.css -->
    <h2>Login your account</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"> <!-- Style handled by styles.css -->
            <?php echo $error; ?>
            <?php if ($error == "Please verify your email first!"): ?>
                <br><a href="resend_verification.php">Resend verification link?</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" required placeholder="Enter your email">

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required placeholder="Enter your password">

        <button type="submit" class="btn">Login</button>
    </form>
    
    <p style="margin-top: 15px;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
    <p>
        <a href="forgot_password.php">Forgot Password?</a>
    </p>
</div>

</body>
</html>