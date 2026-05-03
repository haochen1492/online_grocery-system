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

<div class="auth-container">
    <h2>Customer Login</h2>

    <!-- CHECK FOR REGISTRATION SUCCESS MSG -->
    <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
        <div class="success-msg">
            Registration successful! Please check your email to verify your account before logging in.
        </div>
    <?php endif; ?>

    <!-- Your existing error message block -->
    <?php if (isset($error)): ?>
        <div class="error-msg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <!-- ... email and password inputs ... -->
        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>