<?php
session_start();
require '../includes/dbconnect.php';

// save new address
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

// check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$error = '';

// fetch cart items and calculate totals
$cart_items = [];
$totalAmount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items'])) {
    $selected_ids = $_POST['selected_items'];
    $_SESSION['checkout_final_items'] = $selected_ids; 
} elseif (isset($_SESSION['checkout_final_items'])) {
    $selected_ids = $_SESSION['checkout_final_items'];
} else {
    header('Location: cart.php');
    exit;
}
    
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $stmt_cart = $conn->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $types = str_repeat('i', count($selected_ids));
    $stmt_cart->bind_param($types, ...$selected_ids);
    $stmt_cart->execute();
    $cart_result = $stmt_cart->get_result();
    
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items[] = $row;
        //calculate total based only on the quantity of selected items
        $totalAmount += ($row['price'] * $_SESSION['cart'][$row['product_id']]);
    }

$shippingFee = 5.00; 
$grandTotal = $totalAmount + $shippingFee;

// handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!isset($_POST['address_id'])) {
        $error = "Please select a shipping address before placing your order.";
    } else {
        $address_id = $_POST['address_id'];
        $payment_method = $_POST['payment_method'];
        $status = 'pending';

        if ($payment_method === 'Credit/Debit Card') {
            $_SESSION['temp_address_id'] = $address_id;
            $_SESSION['temp_total'] = $grandTotal;
            header('Location: payment.php');
            exit;
        } elseif ($payment_method === "Touch 'n Go E-Wallet") {
            $_SESSION['temp_address_id'] = $address_id;
            $_SESSION['temp_total'] = $grandTotal;
            header('Location: tng_payment.php');
            exit;
        } elseif ($payment_method === 'Cash On Delivery') {
                
                // stock validation
                foreach ($cart_items as $item) {
                    $requested_qty = $_SESSION['cart'][$item['product_id']];
                    if ($item['stock_quantity'] < $requested_qty) {
                        $error = "Insufficient stock for " . htmlspecialchars($item['name']) . ". Only " . $item['stock_quantity'] . " left.";
                        break; 
                    }
                }

                if (empty($error)) {
                    try {
                        // create order
                        $stmt = $conn->prepare("INSERT INTO orders (customer_id, address_id, total_price, delivery_status, payment_method) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("iidss", $customer_id, $address_id, $grandTotal, $status, $payment_method);
                        $stmt->execute();
                        $order_id = $conn->insert_id;

                        // insert payment record 
                        $stmt_pay = $conn->prepare("INSERT INTO payments (order_id, price, payment_status) VALUES (?, ?, 'pending')");
                        $stmt_pay->bind_param("id", $order_id, $grandTotal);
                        $stmt_pay->execute();

                        // insert order items and update stock
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

                        // deactivated cart items
                        if (!empty($selected_ids)) {
                            $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
                            $stmt_deact = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND product_id IN ($placeholders)");
                            $types = "i" . str_repeat('i', count($selected_ids));
                            $stmt_deact->bind_param($types, $customer_id, ...$selected_ids);
                            $stmt_deact->execute();
                        }

                        // clear session cart and redirect
                        unset($_SESSION['cart']);
                        unset($_SESSION['checkout_final_items']);
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

    <div class="checkout-container">
        <h1>Checkout</h1>
        <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
        
        <button type="button" class="btn-secondary" onclick="window.location.href='cart.php'">← Back to Cart</button>

        <form method="POST" action="checkout.php">
            <div class="checkout-layout-grid">
                
                <div class="checkout-main-col">
                    <div class="checkout-section-card">
                        <h3>Review Your Items</h3>
                        <div class="cart-preview">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-preview-item">
                                    <img src="../admin/products/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="preview-image">
                                    <div class="preview-item-meta">
                                        <span class="preview-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="preview-item-qty">Quantity: <?php echo $_SESSION['cart'][$item['product_id']]; ?></span>
                                    </div>
                                    <span class="preview-item-price">RM<?php echo number_format($item['price'] * $_SESSION['cart'][$item['product_id']], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="checkout-section-card">
                        <h3>Shipping Address</h3>
                        <div id="saved-addresses" class="address-selection-group">
                            <?php if ($address_result->num_rows > 0): ?>
                                <?php while ($addr = $address_result->fetch_assoc()): ?>
                                    <label class="address-radio-card">
                                        <input type="radio" name="address_id" value="<?php echo $addr['address_id']; ?>" required>
                                        <div class="address-text-details">
                                            <?php echo htmlspecialchars($addr['unit_no'] . ", " . $addr['street'] . ", " . $addr['postal_code'] . " " . $addr['city'] . ", " . $addr['state']); ?>
                                        </div>
                                    </label>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="color: #777; margin-bottom: 15px;">No addresses found in your profile.</p>
                            <?php endif; ?>
                            <button type="button" class="btn-add-address" onclick="showAddressForm()">+ Add New Address</button>
                        </div>
                    </div>
                </div>

                <div class="checkout-side-col">
                    <div class="checkout-section-card summary-sticky-card">
                        <h3>Order Summary</h3>
                        <div class="price-summary-box">
                            <div class="summary-line">
                                <span>Subtotal</span>
                                <span>RM<?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Shipping Fee</span>
                                <span>RM<?php echo number_format($shippingFee, 2); ?></span>
                            </div>
                            <hr class="summary-divider">
                            <div class="summary-line grand-total-line">
                                <span>Grand Total</span>
                                <span class="grand-total-amount">RM<?php echo number_format($grandTotal, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="payment-selection-box" style="margin-top: 20px;">
                            <label for="payment_method" style="margin-top: 0; margin-bottom: 8px;">Payment Method:</label>
                            <select name="payment_method" id="payment_method" required>
                                <option value="">-- Select Method --</option>
                                <option value="Credit/Debit Card">Credit/Debit Card</option>
                                <option value="Cash On Delivery">Cash on Delivery</option>
                                <option value="Touch 'n Go E-Wallet">Touch 'n Go E-Wallet</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="place_order" class="place-order-btn" style="width: 100%; margin-top: 20px;">Place Order</button>
                    </div>
                </div>

            </div>
        </form>

        <div id="address-modal-overlay" class="address-modal-overlay">
            <div class="address-modal-content">
                <h4>Add New Address</h4>
                <div id="addressInputs">
                    <input type="text" id="unit_no" placeholder="House No./Unit No./Block" required>
                    <input type="text" id="street" placeholder="Street Name (e.g., Lorong X/XX)" required>
                    <input type="text" id="city" placeholder="City" required>
                    <select name="state" id="state" required>
                        <option value="">-- Select State --</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Penang">Penang</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                    </select>
                    <input type="text" id="postal_code" placeholder="Postal Code (e.g., 57000)" required>
                    <div style="display:flex; gap:10px; margin-top: 15px;">
                        <button type="button" class="btn-secondary" onclick="saveNewAddress()">Save Address</button>
                        <button type="button" onclick="closeAddressForm()" style="background-color: #95a5a6;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>   
    </div>

    <script>
        function showAddressForm() { 
            document.getElementById('address-modal-overlay').style.display = 'flex'; 
        }

        function closeAddressForm() {
            document.getElementById('address-modal-overlay').style.display = 'none';
        }

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

        // Basic client-side validation for payment method and address selection
        document.querySelector('form').addEventListener('submit', function(e) {
            const paymentMethod = document.getElementById('payment_method').value;
            const addressSelected = document.querySelector('input[name="address_id"]:checked');

            if (!paymentMethod) {
                alert('Please select a payment method.');
                e.preventDefault();
                return;
            }

            if (!addressSelected) {
                alert('Please select a shipping address.');
                e.preventDefault();
                return;
            }
        });

        // basic client-side validation for address form
        document.getElementById('addressInputs').addEventListener('submit', function(e) {
            const unitNo = document.getElementById('unit_no').value.trim();
            const street = document.getElementById('street').value.trim();
            const city = document.getElementById('city').value.trim();
            const state = document.getElementById('state').value;
            const postalCode = document.getElementById('postal_code').value.trim();

            if (!unitNo || !street || !city || !state || !postalCode) {
                alert('Please fill in all address fields.');
                e.preventDefault();
                return;
            }

            if (!/^\d{5}$/.test(postalCode)) {
                alert('Please enter a valid 5-digit postal code.');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>