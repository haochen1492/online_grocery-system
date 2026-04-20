<?php
include '../includes/dbconnect.php';
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $stmt = $conn->prepare("UPDATE customers SET is_verified = 1 WHERE verification_code = ?");
    if ($stmt->execute([$code])) {
        echo "Email verified! You can now <a href='login.php'>Login</a>";
    }
}
?>