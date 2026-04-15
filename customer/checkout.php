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
        <p>Products Ordered:</p>
        <ul>
        </ul>
        <p>Total Amount: RM0.00</p>

        <label for="address">Shipping Address:</label><br>
        <button id="address" >Add New Address</button><br><br>
        <label for="payment_method">Payment Method:</label><br>
        <select id="payment_method" name="payment_method" required>
            <option value="">Select a payment method</option>
            <option value="credit_card">Credit Card</option>
            <option value="cash_on_delivery">Cash on Delivery</option>
        </select><br>
        <p>Note: For Cash on Delivery, please have the exact amount ready at the time of delivery.</p>
        <p>Total amount: RM0.00</p>
        <p>Shipping fee: RM4.00</p>
        <p>Grand Total: RM4.00</p>
        <button type="submit">Place Order</button>
    </div>
</body>
</html>
<script>
    document.getElementById('address').addEventListener('click', function() {
        alert('Address form will appear here.');
    });
</script>