<?php
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Search for the user in the database[cite: 3, 4]
    $stmt = $conn->prepare("SELECT customer_id, customer_password FROM customers WHERE customer_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the hashed password[cite: 4, 5]
        if (password_verify($password, $user['customer_password'])) {
            $_SESSION['customer_id'] = $user['customer_id'];
            header("Location: index.php"); // Redirect to home on success
            exit();
        } else {
            $error = "Invalid email address or password.";
        }
    } else {
        $error = "Invalid email address or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Grocery System</title>
    <link rel="stylesheet" href="includes/styles.css"> 
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <h2>Login to your account</h2>

    <!-- Displays error for wrong password or unregistered email[cite: 4] -->
    <?php if (isset($error)): ?>
        <div class="error-msg" style="color: red; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Registration success message[cite: 4] -->
    <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
        <div class="success-msg" style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center;">
            Registration successful! You can now log in.
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email Address:</label>
        <input type="email" name="email" required placeholder="Enter your email">

        <label for="password">Password:</label>
        <input type="password" name="password" required placeholder="Enter your password">

        <button type="submit" class="btn">Login</button>
    </form>
    
    <p style="margin-top: 15px;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

</body>
</html>