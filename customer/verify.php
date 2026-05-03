<?php
include '../includes/dbconnect.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Update the customer to verified status[cite: 1]
    $stmt = $conn->prepare("UPDATE customers SET is_verified = 1 WHERE verification_code = ?");
    $stmt->bind_param("s", $code);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<script>alert('Email verified successfully! You can now login.'); window.location='login.php';</script>";
    } else {
        echo "Invalid or expired verification link.";
    }
}
?>