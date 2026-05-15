<?php
include '../includes/dbconnect.php';
session_start();

if (isset($_POST['product_id']) && isset($_SESSION['customer_id'])) {
    $pid = $_POST['product_id'];
    $cid = $_SESSION['customer_id'];
    $status = $_POST['is_selected'];

    // Update the 'selected' status in the database
    $stmt = $conn->prepare("UPDATE cart SET selected = ? WHERE customer_id = ? AND product_id = ? AND active = 1");
    $stmt->bind_param("iii", $status, $cid, $pid);
    $stmt->execute();
}
?>