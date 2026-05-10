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
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $customer_id);
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
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                        <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td>RM<?php echo number_format($order['total_price'], 2); ?></td>
                        <td class="status-<?php echo $order['delivery_status']; ?>">
                            <?php echo ucfirst($order['delivery_status']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <ul style="list-style: none; padding-left: 20px; font-size: 14px;">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li>
                                        <img src="../admin/products/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 70px; height: 70px; margin-right: 10px;">
                                        • <?php echo htmlspecialchars($item['name']); ?> 
                                        (x<?php echo $item['quantity']; ?>) - 
                                        RM<?php echo number_format($item['product_price'], 2); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
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