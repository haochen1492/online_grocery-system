<?php
include '../includes/dbconnect.php';
session_start();

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$orders = [];

// fetch all orders for this customer
$stmt = $conn->prepare("
    SELECT o.*, p.payment_status 
    FROM orders o 
    LEFT JOIN payments p ON o.order_id = p.order_id 
    WHERE o.customer_id = ? 
    ORDER BY o.order_date DESC
");$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($order = $result->fetch_assoc()) {
    $order_id = $order['order_id'];
    
    // for each order, fetch the items by joining order_details and products
    $item_stmt = $conn->prepare("
        SELECT od.*, p.name, p.product_image
        FROM order_details od 
        JOIN products p ON od.product_id = p.product_id 
        WHERE od.order_id = ?
    ");
    $item_stmt->bind_param("i", $order_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();
    
    $items = [];
    while ($item = $item_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    // Add the items array into the order array
    $order['items'] = $items;
    $orders[] = $order;
    $item_stmt->close();
}

// Joining with payments table to get payment status
$stmt = $conn->prepare("
    SELECT o.*, p.payment_status 
    FROM orders o 
    LEFT JOIN payments p ON o.order_id = p.order_id 
    WHERE o.customer_id = ? 
    ORDER BY o.order_date DESC
");


$item_stmt = $conn->prepare("SELECT od.*, p.name, p.product_image FROM order_details od JOIN products p ON od.product_id = p.product_id WHERE od.order_id = ?");
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<?php include 'includes/header.php'; ?>
<body>
<div class="order-history-container">
    <h2>Your Order History</h2>
    <?php if (!empty($orders)): ?>
        <table class="order-history-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Delivery Status</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td>RM<?php echo number_format($order['total_price'], 2); ?></td>
                        <td class="status-<?php echo $order['delivery_status']; ?>">
                            <?php echo ucfirst($order['delivery_status']); ?>
                        </td>
                        <td><?php echo isset($order['payment_method']) ? htmlspecialchars($order['payment_method']) : 'N/A'; ?></td>
                        <td class="status-<?php echo $order['payment_status']; ?>">
                            <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="padding: 10px 20px;">
                            <div class="history-items-wrapper">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="history-item-row">
                                        <img src="../admin/products/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="history-item-image">
                                        <div class="history-item-meta">
                                            <span class="history-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                            <span class="history-item-qty">Quantity: <strong><?php echo $item['quantity']; ?></strong></span>
                                        </div>
                                        <div class="history-item-price">
                                            RM<?php echo number_format($item['product_price'] * $item['quantity'], 2); ?>
                                            <small style="display: block; color: #777; font-weight: normal;">(RM<?php echo number_format($item['product_price'], 2); ?> each)</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
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