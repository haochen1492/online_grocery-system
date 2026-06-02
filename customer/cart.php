<?php
include '../includes/dbconnect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];

// fetch cart data
$products = [];
$total = 0;
$stmt = $conn->prepare("
    SELECT p.*, c.quantity , c.selected
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

// Remove Individual Item via GET
if (isset($_GET['remove'])) {
    $pid = $_GET['remove'];
    $stmt_rem = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND product_id = ?");
    $stmt_rem->bind_param("ii", $customer_id, $pid);
    $stmt_rem->execute();
    unset($_SESSION['cart'][$pid]);
    header('Location: cart.php');
    exit;
}

// Update quantity logic
if (isset($_GET['update_qty']) && isset($_GET['product_id'])) {
    $pid = $_GET['product_id'];
    $action = $_GET['update_qty'];

    if ($action === 'increase') {
        $stmt_check = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
        $stmt_check->bind_param("i", $pid);
        $stmt_check->execute();
        $res = $stmt_check->get_result()->fetch_assoc();

        if ($_SESSION['cart'][$pid] < $res['stock_quantity']) {
            $stmt_up = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE customer_id = ? AND product_id = ? AND active = 1");
            $stmt_up->bind_param("ii", $customer_id, $pid);
            $stmt_up->execute();
        }
    } else {
        $stmt_up = $conn->prepare("UPDATE cart SET quantity = GREATEST(1, quantity - 1) WHERE customer_id = ? AND product_id = ? AND active = 1");
        $stmt_up->bind_param("ii", $customer_id, $pid);
        $stmt_up->execute();
    }
    header('Location: cart.php');
    exit;
}

//check if the stock is out, if so, alert customer and remove from cart
foreach ($products as $pid => $item) {
    if ($item['stock_quantity'] <= 0) {
        $stmt_rem = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND product_id = ?");
        $stmt_rem->bind_param("ii", $customer_id, $pid);
        $stmt_rem->execute();
        unset($_SESSION['cart'][$pid]);
        echo "<script>alert('Sorry, the product \"{$item['name']}\" is out of stock and has been removed from your cart.');</script>";
    }
}

// Handle bulk removal of selected items via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_selected'])) {
    if (!empty($_POST['selected_items'])) {
        $selected_ids = $_POST['selected_items'];
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        
        $stmt_rem = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND product_id IN ($placeholders)");
        $types = "i" . str_repeat('i', count($selected_ids));
        $stmt_rem->bind_param($types, $customer_id, ...$selected_ids);
        $stmt_rem->execute();

        foreach ($selected_ids as $id) {
            unset($_SESSION['cart'][$id]);
        }
    }
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
<?php include 'includes/header.php'; ?>
    <div class="cart-container"> 
        <h2>Shopping Cart</h2>
        <?php if (empty($_SESSION['cart'])): ?>
            <p class="empty-msg">Your cart is empty. <a href="products.php">Go shopping!</a></p>
        <?php else: ?>
            <form action="checkout.php" method="POST" id="cart-form">
                <?php foreach ($_SESSION['cart'] as $id => $quantity): 
                    if (isset($products[$id])):
                        $item = $products[$id];
                        $subtotal = $item['price'] * $quantity;
                        $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <div class="item-selection">
                            <input type="checkbox" class="cart-checkbox" data-id="<?php echo $id; ?>" name="selected_items[]" value="<?php echo $id; ?>" <?php echo ($item['selected'] == 1) ? 'checked' : ''; ?>>
                        </div>

                        <div class="item-details">
                            <img src="../admin/products/<?php echo htmlspecialchars($item['product_image']); ?>" class="item-image">
                            <div>
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <div class="qty-controls">
                                    <a href="cart.php?product_id=<?php echo $id; ?>&update_qty=decrease" class="qty-btn">-</a>
                                    <span class="qty-number"><?php echo $quantity; ?></span>
                                    <?php if ($quantity < $item['stock_quantity']): ?>
                                        <a href="cart.php?product_id=<?php echo $id; ?>&update_qty=increase" class="qty-btn">+</a>
                                    <?php else: ?>
                                        <span class="qty-btn">+</span>
                                        <br><small class="stock-warning">Max stock Reached</small>
                                    <?php endif; ?>
                                    <small> x RM<?php echo number_format($item['price'], 2); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="item-actions">
                            <strong class="item-subtotal" data-price="<?php echo $item['price'] * $quantity; ?>">RM<?php echo number_format($subtotal, 2); ?></strong>
                            <br>
                            <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn">Remove</a>
                        </div>
                    </div>
                <?php endif; endforeach; ?>

                <div class="cart-summary">
                    <h3>Total (All Selected Items): RM<span id="dynamic-total"><?php echo number_format($total, 2); ?></span></h3>
                    
                    <div>
                        <button type="submit" name="remove_selected" class="btn-secondary" onclick="changeAction(event, 'cart.php')">Remove Selected</button>
                        <button type="submit" name="proceed_to_checkout" class="checkout-btn" onclick="changeAction(event, 'checkout.php')">Checkout Selected</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Function to change form action based on which button is clicked
        function changeAction(event, url) {
            // count how many checkboxes are currently checked
            const checkedCount = document.querySelectorAll('.cart-checkbox:checked').length;
            //if zero is checked, prevent form submission and alert customer
            if (checkedCount === 0) {
                event.preventDefault(); // Prevent form submission
                alert('Please select at least one item to proceed.');
                return false;
            } else {
                //proceed if item are selected
                document.getElementById('cart-form').action = url; // Set the form action
            }
        }

        // Function to calculate and display the total based on checked items
        function updateDynamicTotal() {
            let newTotal = 0;
            // Look at every checkbox that is currently checked
            document.querySelectorAll('.cart-checkbox:checked').forEach(checkbox => {
                const row = checkbox.closest('.cart-item');
                const subtotalElement = row.querySelector('.item-subtotal');
                // Get the price directly from the data attribute
                const price = parseFloat(subtotalElement.getAttribute('data-price'));
                newTotal += price;
            });
            // Update the total display
            document.getElementById('dynamic-total').innerText = newTotal.toFixed(2);
        }

        // Attach a single listener to all checkboxes
        document.querySelectorAll('.cart-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateDynamicTotal(); // Update the UI immediately

                const productId = this.getAttribute('data-id');
                const isChecked = this.checked ? 1 : 0;

                // Sync with the database via update_selection.php
                fetch('update_selection.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `product_id=${productId}&is_selected=${isChecked}`
                });
            });
        });

        // Run once on page load to ensure total matches the database state
        window.onload = updateDynamicTotal;
    </script>
</body>
<?php include 'includes/footer.php'; ?>
</html>