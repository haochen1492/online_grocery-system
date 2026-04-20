<?php
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['customer_password'])) {
        if ($user['is_verified'] == 0) {
            $error = "Please verify your email first!";
        } else {
            $_SESSION['user_id'] = $user['customer_id'];
            $_SESSION['username'] = $user['customer_name'];
            header("Location: index.php");
        }
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<?php if (isset($error)): ?>
    <div class="error-msg" style="color: red; margin-bottom: 10px;">
        <?php echo $error; ?>
        <?php if ($error == "Please verify your email first!"): ?>
            <br><a href="resend_verification.php">Resend verification link?</a>
        <?php endif; ?>
    </div>
<?php endif; ?>