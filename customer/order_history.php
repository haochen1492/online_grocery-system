<?php
include '../includes/dbconnect.php';
session_start();

/*check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}*/

// Fetch user orders
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // Fetch orders for the logged-in user
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
}

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
    <?php if (!empty($orders)): ?>
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
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        <td>RM<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no orders yet. <a href="index.php">Start shopping!</a></p>
    <?php endif; ?>
</div>
</body>
</html>