<?php
session_start();
include('../config/db.php');

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = $conn->query("SELECT * FROM admins WHERE username='$username' AND password='$password'");

    if ($query->num_rows > 0) {
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
    } else {
        echo "Invalid login!";
    }
}
?>

<form method="POST">
    <h2>Admin Login</h2>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button name="login">Login</button>
</form>
