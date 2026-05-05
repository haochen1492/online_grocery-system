<?php
session_start();
require '../includes/dbconnect.php';
/*Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}*/

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <div class="order-confirmation-container">
        <h1>Thank you for your order!</h1>
        <p>Your order has been successfully placed. We will update the status once we started the delivery.</p>
        <a href="index.php" class="btn">Continue Shopping</a>
    </div>
</body>
</html>