<?php
include '../includes/dbconnect.php';
session_start();

// check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$total_price = $_SESSION['temp_total'];
$address_id = $_SESSION['temp_address_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // insert into orders
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, address_id, total_price, delivery_status, payment_method) VALUES (?, ?, ?, 'pending', 'TnG E-Wallet')");
        $stmt->bind_param("iid", $customer_id, $address_id, $total_price);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // insert payment record 
        $stmt_pay = $conn->prepare("INSERT INTO payments (order_id, price, payment_status) VALUES (?, ?, 'completed')");
        $stmt_pay->bind_param("id", $order_id, $total_price);
        $stmt_pay->execute();

        // process items, Update Stock, and insert details
        $stmt_items = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, product_price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // Fetch current price to lock it in order_details
            $res = $conn->query("SELECT price FROM products WHERE product_id = $product_id");
            $p_data = $res->fetch_assoc();
            $current_price = $p_data['price'];

            // Insert details
            $stmt_items->bind_param("iiid", $order_id, $product_id, $quantity, $current_price);
            $stmt_items->execute();

            // Update stock
            $stmt_stock->bind_param("ii", $quantity, $product_id);
            $stmt_stock->execute();
        }

        // deactivate cart items
        $stmt_deact = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND active = 1");
        $stmt_deact->bind_param("i", $customer_id);
        $stmt_deact->execute();

        // Clear session cart and redirect to confirmation
        unset($_SESSION['cart']);
        unset($_SESSION['temp_total']);
        unset($_SESSION['temp_address_id']);
        
        header('Location: order_confirmation.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "Payment Error: " . $e->getMessage();
    }
}

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
    <div class="payment-container">
    <h2>Touch 'n Go E-Wallet Payment</h2>
    <p>Complete your purchase using your Touch 'n Go E-Wallet.</p>
    <form method="POST" class="payment-form">
        <div class="tng">
            <table>
                <tr><td>Payment to Infinity Grocer</td></tr>
                <tr><td>RM <?php echo number_format($total_price, 2); ?></td></tr>
            </table>
        </div>
        <div class="tng-login">
            <h2>Login</h2>
            <select name='country_code' required>
                <option value="">-- Select Country Code --</option>
                <option value="+60">+60 (Malaysia)</option>
                <option value="+65">+65 (Singapore)</option>
            </select>
            <input type="tel" name="phone_number" placeholder="Enter Phone Number" required>
            <p>6-digit PIN</p>
            <input type="password" name="tng_pin" placeholder="Enter TNG PIN" required>
        </div>
        <button type="submit" class="pay-btn">Pay Now</button>
    </form>
    </div>
</body>
</html>
<script>
    // Basic client-side validation for TnG E-Wallet payment form
    document.querySelector('.payment-form').addEventListener('submit', function(e) {
        const phoneInput = document.querySelector('input[name="phone_number"]');
        const pinInput = document.querySelector('input[name="tng_pin"]');
        const phonePattern = /^\d{9,10}$/; // Basic pattern for phone numbers
        const pinPattern = /^\d{6}$/; // 6-digit PIN

        if (!phonePattern.test(phoneInput.value)) {
            alert('Please enter a valid phone number (9-10 digits).');
            e.preventDefault();
            return;
        }

        if (!pinPattern.test(pinInput.value)) {
            alert('Please enter a valid 6-digit TNG PIN.');
            e.preventDefault();
            return;
        }
    });