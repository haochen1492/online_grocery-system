<?php
session_start();
require '../includes/dbconnect.php';

// --- 1. AJAX ADDRESS SAVER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $stmt = $conn->prepare("INSERT INTO addresses (customer_id, unit_no, street, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?, 'Malaysia')");
        $stmt->bind_param("isssss", $customer_id, $data['unit_no'], $data['street'], $data['city'], $data['state'], $data['postal_code']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB Error']);
        }
        $stmt->close();
    }
    exit; 
}

// 1. SECURITY: Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$error = '';

// 2. FETCH DATA FOR UI
$cart_items = [];
$totalAmount = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt_cart = $conn->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $types = str_repeat('i', count($ids));
    $stmt_cart->bind_param($types, ...$ids);
    $stmt_cart->execute();
    $cart_result = $stmt_cart->get_result();
    
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items[] = $row;
        $totalAmount += ($row['price'] * $_SESSION['cart'][$row['product_id']]);
    }
}

$shippingFee = 5.00; 
$grandTotal = $totalAmount + $shippingFee;

// 3. HANDLE ORDER PLACEMENT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!isset($_POST['address_id'])) {
        $error = "Please select a shipping address before placing your order.";
    } else {
        $address_id = $_POST['address_id'];
        $payment_method = $_POST['payment_method'];
        $status = 'pending';

        if ($payment_method === 'credit_card') {
            $_SESSION['temp_address_id'] = $address_id;
            $_SESSION['temp_total'] = $grandTotal;
            header('Location: payment.php');
            exit;
    } elseif ($payment_method === 'cash_on_delivery') {
                
                // 1. STOCK VALIDATION CHECK
                foreach ($cart_items as $item) {
                    $requested_qty = $_SESSION['cart'][$item['product_id']];
                    if ($item['stock_quantity'] < $requested_qty) {
                        $error = "Insufficient stock for " . htmlspecialchars($item['name']) . ". Only " . $item['stock_quantity'] . " left.";
                        break; 
                    }
                }

                if (empty($error)) {
                    try {
                        // 2. CREATE ORDER
                        $stmt = $conn->prepare("INSERT INTO orders (customer_id, address_id, total_price, delivery_status) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("iids", $customer_id, $address_id, $grandTotal, $status);
                        $stmt->execute();
                        $order_id = $conn->insert_id;

                        // 3. PROCESS ITEMS & UPDATE STOCK
                        $stmt_items = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, product_price) VALUES (?, ?, ?, ?)");
                        $stmt_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

                        foreach ($cart_items as $item) {
                            $p_id = $item['product_id'];
                            $qty = $_SESSION['cart'][$p_id];
                            $price = $item['price'];

                            $stmt_items->bind_param("iiid", $order_id, $p_id, $qty, $price);
                            $stmt_items->execute();

                            $stmt_stock->bind_param("ii", $qty, $p_id);
                            $stmt_stock->execute();
                        }

                        // 4. DEACTIVATE CART IN DATABASE (Moved inside the success block)
                        $stmt_deactivate = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND active = 1");
                        $stmt_deactivate->bind_param("i", $customer_id);
                        $stmt_deactivate->execute();
                        $stmt_deactivate->close();

                        // 5. CLEAR SESSION AND REDIRECT
                        unset($_SESSION['cart']);
                        header('Location: order_confirmation.php');
                        exit;

                    } catch (Exception $e) {
                        $error = "System Error: " . $e->getMessage();
                    }
                }
            } else {
                $error = "Invalid payment method selected.";
            }
       }
}

$stmt_addr = $conn->prepare("SELECT * FROM addresses WHERE customer_id = ?");
$stmt_addr->bind_param("i", $customer_id);
$stmt_addr->execute();
$address_result = $stmt_addr->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <header><?php include 'includes/header.php'; ?></header>

    <div class="checkout-container">
        <h1>Checkout</h1>
        <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
        
        <button type="button" class="btn-secondary" onclick="window.location.href='cart.php'">← Back to Cart</button>

        <form method="POST" action="checkout.php">
            <h3>Review Your Items</h3>
            <div class="cart-preview">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-preview-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $_SESSION['cart'][$item['product_id']]; ?>)</span>
                        <span>RM<?php echo number_format($item['price'] * $_SESSION['cart'][$item['product_id']], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>Shipping Address</h3>
            <div id="saved-addresses">
                <?php if ($address_result->num_rows > 0): ?>
                    <?php while ($addr = $address_result->fetch_assoc()): ?>
                        <label class="address-radio">
                            <input type="radio" name="address_id" value="<?php echo $addr['address_id']; ?>" required>
                            <?php echo htmlspecialchars($addr['unit_no'] . ", " . $addr['street'] . ", " . $addr['city']); ?>
                        </label>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No addresses found.</p>
                <?php endif; ?>
                <button type="button" onclick="showAddressForm()">+ Add New Address</button>
            </div>

            <h3>Payment & Finalize</h3>
            <div class="price-summary">
                <p>Subtotal: <span>RM<?php echo number_format($totalAmount, 2); ?></span></p>
                <p>Shipping: <span>RM<?php echo number_format($shippingFee, 2); ?></span></p>
                <p class="grand-total">Grand Total: <span>RM<?php echo number_format($grandTotal, 2); ?></span></p>
            </div>
            
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="">-- Select Method --</option>
                <option value="credit_card">Credit/Debit Card</option>
                <option value="cash_on_delivery">Cash on Delivery</option>
            </select>
            
            <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
        </form>

        <div id="address-form-wrapper" style="display: none;" class="address-entry-box">
            <h4>Add New Address</h4>
            <div id="addressInputs">
                <input type="text" id="unit_no" placeholder="Unit/Block" required>
                <input type="text" id="street" placeholder="Street Name" required>
                <input type="text" id="city" placeholder="City" required>
                <input type="text" id="state" placeholder="State" required>
                <input type="text" id="postal_code" placeholder="Postal Code" required>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn-secondary" onclick="saveNewAddress()">Save Address</button>
                    <button type="button" onclick="document.getElementById('address-form-wrapper').style.display='none'">Cancel</button>
                </div>
            </div>
        </div>           
    </div>

    <script>
        function showAddressForm() { document.getElementById('address-form-wrapper').style.display = 'block'; }

        function saveNewAddress() {
            const data = {
                unit_no: document.getElementById('unit_no').value,
                street: document.getElementById('street').value,
                city: document.getElementById('city').value,
                state: document.getElementById('state').value,
                postal_code: document.getElementById('postal_code').value
            };
            
            fetch('checkout.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    alert('Address saved!');
                    location.reload(); 
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('Could not connect to the server.');
            });
        }
    </script>
</body>
</html>