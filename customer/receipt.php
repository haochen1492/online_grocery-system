<?php
include '../includes/dbconnect.php';
session_start();

// 1. Security Check: Ensure user is logged in and an order_id is provided
if (!isset($_SESSION['customer_id']) || !isset($_GET['order_id'])) {
    die("Invalid request. Please log in and select an order.");
}

$customer_id = $_SESSION['customer_id'];
$order_id = intval($_GET['order_id']);

// 2. Fetch Order, Customer, and Address Data
$stmt = $conn->prepare("
    SELECT o.*, p.payment_status, 
           c.customer_name, c.customer_email, c.customer_phone,
           a.unit_no, a.street, a.city, a.state, a.postal_code
    FROM orders o
    LEFT JOIN payments p ON o.order_id = p.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN addresses a ON o.address_id = a.address_id
    WHERE o.order_id = ? AND o.customer_id = ?
");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order_result = $stmt->get_result();

// If the order doesn't exist or doesn't belong to this customer
if ($order_result->num_rows === 0) {
    die("Order not found or access denied.");
}
$order = $order_result->fetch_assoc();

// 3. Fetch Purchased Items
$item_stmt = $conn->prepare("
    SELECT od.*, pr.name 
    FROM order_details od
    JOIN products pr ON od.product_id = pr.product_id
    WHERE od.order_id = ?
");
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$items_result = $item_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?> - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Infinity Grocer</h1>
            <p>Official Order Receipt</p>
        </div>

        <div class="receipt-meta">
            <div class="receipt-meta-box">
                <h3>Order Details</h3>
                <p><strong>Order ID:</strong> #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?></p>
            </div>
            <div class="receipt-meta-box text-right">
                <h3>Billed To</h3>
                <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                <p><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <p><?php echo htmlspecialchars($order['unit_no'] . ', ' . $order['street']); ?></p>
                <p><?php echo htmlspecialchars($order['postal_code'] . ' ' . $order['city'] . ', ' . $order['state']); ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $calculated_subtotal = 0;
                while ($item = $items_result->fetch_assoc()): 
                    $line_total = $item['product_price'] * $item['quantity'];
                    $calculated_subtotal += $line_total;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td class="text-right">RM <?php echo number_format($item['product_price'], 2); ?></td>
                        <td class="text-right">RM <?php echo number_format($line_total, 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="totals-box">
            <div class="totals-row">
                <span>Items Subtotal:</span>
                <span>RM <?php echo number_format($calculated_subtotal, 2); ?></span>
            </div>
            <div class="totals-row">
                <span>Shipping Fee:</span>
                <span>RM 5.00</span> 
            </div>
            <div class="totals-row receipt-grand-total">
                <span>Grand Total:</span>
                <span>RM <?php echo number_format($order['total_price'], 2); ?></span>
            </div>
        </div>
        
        <div class="clear"></div>

        <div class="receipt-action-buttons">
            <button onclick="window.print()" class="btn">Print / Save as PDF</button>
            <button onclick="window.close()" class="btn btn-secondary">Close Window</button>
        </div>
    </div>
</body>
</html>