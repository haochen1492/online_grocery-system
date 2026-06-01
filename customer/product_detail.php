<?php
include "../includes/dbconnect.php";
session_start();

$success_msg = "";
$error_msg = "";

// Validate and fetch product ID
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
} elseif (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
} else {
    header("Location: products.php");
    exit();
}

// Fetch product details from the database
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    header("Location: products.php");
    exit();
}

// Add to cart process execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);

    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
    } else {
        header('Location: login.php');
        exit;
    }

    if ($quantity > 0 && $quantity <= $product['stock_quantity']) {
        // Check if the product is already sitting in an active cart row
        $check_stmt = $conn->prepare("SELECT * FROM cart WHERE customer_id = ? AND product_id = ? AND active = 1");
        $check_stmt->bind_param("ii", $customer_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $cart_item = $check_result->fetch_assoc();
            $new_qty = $cart_item['quantity'] + $quantity;

            if ($new_qty <= $product['stock_quantity']) {
                // Update quantity and force select status back to 1
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ?, selected = 1 WHERE customer_id = ? AND product_id = ? AND active = 1");
                $update_stmt->bind_param("iii", $new_qty, $customer_id, $product_id);
                $update_stmt->execute();
                $success_msg = "Cart updated successfully!";
            } else {
                $error_msg = "Cannot add more. Exceeds total available store stock.";
            }
        } else {
            // Insert a completely fresh item row defaulting to selected=1
            $insert_stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity, selected, active) VALUES (?, ?, ?, 1, 1)");
            $insert_stmt->bind_param("iii", $customer_id, $product_id, $quantity);
            $insert_stmt->execute();
            $success_msg = "Product successfully added to your cart!";
        }
        
        // Refresh session array reference to match database row state
        $_SESSION['cart'][$product_id] = ($cart_item['quantity'] ?? 0) + $quantity;
        
    } else {
        $error_msg = "Invalid quantity specified or item is currently out of stock.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div style="margin-bottom: 20px;">
            <a href="products.php" class="btn-secondary" style="text-decoration: none; padding: 10px 15px; border-radius: 6px; color: white; display: inline-block;">← Back to Products</a>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="success-msg"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="product-layout-wrapper">
            <div class="product-image-side">
                <img src="<?php echo !empty($product['product_image']) ? '../admin/products/'.$product['product_image'] : 'images/no-image.png'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <div class="product-details-side">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                
                <p class="product-detail-price">RM<?php echo number_format($product['price'], 2); ?></p>
                
                <div class="stock-status-badge">
                    Availability: 
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span style="color: #329b18; font-weight: bold;">In Stock (<?php echo $product['stock_quantity']; ?> units available)</span>
                    <?php else: ?>
                        <span style="color: #e74c3c; font-weight: bold;">Out of Stock</span>
                    <?php endif; ?>
                </div>

                <p class="product-description-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                    <form action="product_detail.php" method="POST" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        
                        <div class="quantity-picker-row">
                            <label>Quantity:</label>
                            <div class="quantity-modifier-box">
                                <button type="button" class="qty-change-btn" onclick="modifyQuantity(-1)">-</button>
                                
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" readonly>
                                
                                <button type="button" class="qty-change-btn" onclick="modifyQuantity(1)">+</button>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="add_to_cart-btn">Add to Shopping Cart</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <script>
    function modifyQuantity(amount) {
        const qtyInput = document.getElementById('quantity');
        let currentVal = parseInt(qtyInput.value) || 1;
        const maxStock = parseInt(qtyInput.getAttribute('max'));

        currentVal += amount;

        // Secure boundary validation to respect current database stock limits
        if (currentVal < 1) {
            currentVal = 1;
        } else if (currentVal > maxStock) {
            currentVal = maxStock;
        }

        qtyInput.value = currentVal;
    }
    </script>
<?php include 'includes/footer.php'; ?>
</body>
</html>