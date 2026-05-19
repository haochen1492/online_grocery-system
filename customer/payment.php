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
$selected_ids = $_SESSION['checkout_final_items'] ?? [];

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

        // Get selected product IDs from session (if needed for specific processing)
        $selected_ids = $_SESSION['checkout_final_items'] ?? [];

        foreach ($selected_ids as $product_id) {
            if (isset($_SESSION['cart'][$product_id])) {
                $quantity = $_SESSION['cart'][$product_id];

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
        }

        // deactivate cart items
        if (!empty($selected_ids)) {
            $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
            $stmt_deact = $conn->prepare("UPDATE cart SET active = 0 WHERE customer_id = ? AND product_id IN ($placeholders)");
            $types = "i" . str_repeat('i', count($selected_ids));
            $stmt_deact->bind_param($types, $customer_id, ...$selected_ids);
            $stmt_deact->execute();
        }

        // Clear session cart and redirect to confirmation
        unset($_SESSION['cart']);
        unset($_SESSION['temp_total']);
        unset($_SESSION['temp_address_id']);
        unset($_SESSION['checkout_final_items']);

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
    <div class="payment-page-container">
        <div class="payment-page-wrapper">
            <h2>Credit/Debit Card Payment</h2>
            <p>Enter your credit/debit card details to complete your purchase.</p>
                <div class="payment-form-container">
                    <form method="POST" class="payment-form" onsubmit="return validateCardForm(event)">
                        <div class="form-group">
                            <label for="name_on_card">Name on Card: </label>
                            <input type="text" id="name_on_card" name="name_on_card">
                        </div>
                        <div class="form-group">
                            <label for="card_number">Card Number: </label>
                            <input type="text" id="card_number" name="card_number" maxlength="16">
                        </div>
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date (MM/YY): </label>
                            <input type="text" id="expiry_date" name="expiry_date" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV:  </label>
                            <input type="text" id="cvv" name="cvv" maxlength="3">
                        </div>
                        <button type="submit" class="pay-btn">Pay Now</button>
                    </form>
                </div>
        </div>
    </div>
<script>
    //auto format expiry date input
    document.getElementById('expiry_date').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
        if (value.length > 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4); // Insert slash after month
        }
        e.target.value = value;
    });
    // Basic client-side validation for credit/debit card form
    function validateCardForm(e) {
        // Basic validation for card number, expiry date, and CVV
        const cardNumber = document.getElementById('card_number').value;
        const expiryDate = document.getElementById('expiry_date').value;
        const cvv = document.getElementById('cvv').value;

        if (!/^\d{16}$/.test(cardNumber)) {
            alert('Please enter a valid 16-digit card number.');
            e.preventDefault();
            return false;
        }

        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate)) {
            alert('Please enter a valid expiry date in MM/YY format.');
            e.preventDefault();
            return false;
        }

        if (!/^\d{3}$/.test(cvv)) {
            alert('Please enter a valid 3-digit CVV.');
            e.preventDefault();
            return false;
        }
        return true;
    }
</script>
</body>
</html>