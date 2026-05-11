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
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, address_id, total_price, delivery_status, payment_method) VALUES (?, ?, ?, 'pending', 'Credit/Debit Card')");
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
    <h2>Credit Card Payment</h2>
    <p>Enter your credit card details to complete your purchase.</p>
    <form method="POST" class="payment-form">
        <div class="form-group">
            <label for="name_on_card">Name on Card: </label>
            <input type="text" id="name_on_card" name="name_on_card" required>
        </div>
        <div class="form-group">
            <label for="card_number">Card Number: </label>
            <input type="text" id="card_number" name="card_number" required>
        </div>
        <div class="form-group">
            <label for="expiry_date">Expiry Date (MM/YY): </label>
            <input type="text" id="expiry_date" name="expiry_date" required>
        </div>
        <div class="form-group">
            <label for="cvv">CVV:  </label>
            <input type="text" id="cvv" name="cvv" required>
        </div>
        <button type="submit" class="pay-btn">Pay Now</button>
    </form>
    </div>
</body>
</html>