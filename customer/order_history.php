<?php
include '../includes/dbconnect.php';
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<header>
    <?php include 'includes/header.php'; ?>
</header>
<body>
<div class="order-history-container">
    <h2>Your Order History</h2>
    
        <table class="order-history-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
            </tbody>
        </table>
        <p>You have no orders yet. <a href="index.php">Start shopping!</a></p>
</div>
</body>
</html>
