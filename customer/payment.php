<?php
include '../includes/dbconnect.php';
session_start();
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
    <form action="process_payment.php" method="POST" class="payment-form">
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