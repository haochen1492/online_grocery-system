<?php
include '../includes/dbconnect.php';
session_start();

// 1. Security Check: Only allow logged-in customers
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=please_login");
    exit();
}

// 2. Fetch added products from the database[cite: 1, 2]
$products = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    // Use the product_id column from your database.sql
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // Note: Assuming you are using PDO based on previous steps
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[$row['product_id']] = $row;
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
    <title>Your Shopping Cart - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css"> <!--[cite: 7] -->
</head>
<body>

<header>
    <?php include 'includes/header.php'; ?> <!--[cite: 6] -->
</header>

<div class="cart-container"> <!--[cite: 7] -->
    <h2>Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-msg">Your cart is empty. <a href="products.php">Go shopping!</a></p>
    <?php else: ?>
        <?php foreach ($_SESSION['cart'] as $id => $quantity): 
            // Check if product exists in the fetched list to avoid errors
            if (isset($products[$id])):
                $item = $products[$id];
                $subtotal = $item['price'] * $quantity;
                $total += $subtotal;
        ?>
            <div class="cart-item"> <!--[cite: 7] -->
                <div class="item-details">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <small>Quantity: <?php echo $quantity; ?> x RM<?php echo number_format($item['price'], 2); ?></small>
                </div>
                <div>
                    <strong>RM<?php echo number_format($subtotal, 2); ?></strong>
                    <br>
                    <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn">Remove</a>
                </div>
            </div>
        <?php 
            endif;
        endforeach; ?>

        <div class="cart-summary">
            <h3>Total: RM<?php echo number_format($total, 2); ?></h3>
            <!-- Link to your checkout.php file[cite: 3] -->
            <form action="checkout.php" method="POST">
                <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                <button type="submit" class="checkout-btn">Proceed to Checkout</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>