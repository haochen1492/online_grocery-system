<?php

include '../includes/dbconnect.php';

session_start();

// Check if user is logged in
/*if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}*/

//fetch added products
$products = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}

// Handle adding to cart
if (isset($_POST['product_id'])) {
    $id = $_POST['product_id'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
}

// Handle removing from cart
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<header>
    <?php include 'includes/header.php'; ?>
</header>
<body>
<div class="cart-container">
    <h2>Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-msg">Your cart is empty. <a href="index.php">Go shopping!</a></p>
    <?php else: ?>
        <?php foreach ($_SESSION['cart'] as $id => $quantity): 
            $item = $products[$id];
            $subtotal = $item['price'] * $quantity;
            $total += $subtotal;
        ?>
            <div class="cart-item">
                <div class="item-details">
                    <h4><?php echo $item['name']; ?></h4>
                    <small>Quantity: <?php echo $quantity; ?> x RM<?php echo number_format($item['price'], 2); ?></small>
                </div>
                <div>
                    <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                    <br>
                    <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn">Remove</a>
                </div>
            </div>
        <?php endforeach; ?>

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