<?php
include '../includes/dbconnect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];

// --- FETCH CART DATA ---
$products = [];
$total = 0;
$stmt = $conn->prepare("
    SELECT p.*, c.quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.customer_id = ? AND c.active = 1
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $products[$row['product_id']] = $row;
    $_SESSION['cart'][$row['product_id']] = $row['quantity'];
}

// --- HANDLE ACTIONS ---
// Remove Item
if (isset($_GET['remove'])) {
    $pid = $_GET['remove'];
    $stmt_rem = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND product_id = ?");
    $stmt_rem->bind_param("ii", $customer_id, $pid);
    $stmt_rem->execute();
    unset($_SESSION['cart'][$pid]);
    header('Location: cart.php');
    exit;
}

// Update Quantity
if (isset($_GET['update_qty']) && isset($_GET['product_id'])) {
    $pid = $_GET['product_id'];
    $action = $_GET['update_qty'];
    if ($action === 'increase') {
        $stmt_up = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE customer_id = ? AND product_id = ? AND active = 1");
    } else {
        $stmt_up = $conn->prepare("UPDATE cart SET quantity = GREATEST(1, quantity - 1) WHERE customer_id = ? AND product_id = ? AND active = 1");
    }
    $stmt_up->bind_param("ii", $customer_id, $pid);
    $stmt_up->execute();
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css"> 
</head>
<body>
<header><?php include 'includes/header.php'; ?></header>

<div class="cart-container"> 
    <h2>Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-msg">Your cart is empty. <a href="products.php">Go shopping!</a></p>
    <?php else: ?>
        <?php foreach ($_SESSION['cart'] as $id => $quantity): 
            if (isset($products[$id])):
                $item = $products[$id];
                $subtotal = $item['price'] * $quantity;
                $total += $subtotal;
        ?>
            <div class="cart-item">
                <div class="item-details">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <div class="qty-controls">
                        <a href="cart.php?product_id=<?php echo $id; ?>&update_qty=decrease" class="qty-btn">-</a>
                        <span class="qty-number"><?php echo $quantity; ?></span>
                        <a href="cart.php?product_id=<?php echo $id; ?>&update_qty=increase" class="qty-btn">+</a>
                        <small> x RM<?php echo number_format($item['price'], 2); ?></small>
                    </div>
                </div>
                <div class="item-actions">
                    <strong>RM<?php echo number_format($subtotal, 2); ?></strong>
                    <br>
                    <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn">Remove</a>
                </div>
            </div>
        <?php endif; endforeach; ?>

        <div class="cart-summary">
            <h3>Total: RM<?php echo number_format($total, 2); ?></h3>
            <form action="checkout.php" method="POST">
                <input type="hidden" name="total_amount" value="<?php echo $total * 100; ?>">
                <button type="submit" class="checkout-btn">Proceed to Payment</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>