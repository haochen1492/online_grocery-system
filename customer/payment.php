<?php
include '../includes/dbconnect.php';
session_start();
// check if user is logged in
/*if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}*/



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $total_amount = $_POST['total_amount'] ?? 0.00; // Calculate total amount based on cart items
    $payment_method = 'credit_card';
    $status = 'paid';
    $customer_id = $_SESSION['customer_id']; // Assuming customer ID is stored in session

    // Handle credit card payment logic here (e.g., save order to database)
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, payment_method, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $_SESSION['customer_id'], $total_amount, $payment_method, $status);
    $stmt->execute();

    //get the last inserted order ID
    $order_id = $conn->insert_id;

    // Insert order items into order_details table
    $stmt_items = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity) VALUES (?, ?, ?)");
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt_items->bind_param("iii", $order_id, $product_id, $quantity);
        $stmt_items->execute();
    }
    // Clear the cart after payment
    unset($_SESSION['cart']);
    
    // Redirect to a confirmation page or display a success message
    header('Location: order_confirmation.php');
    exit;
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