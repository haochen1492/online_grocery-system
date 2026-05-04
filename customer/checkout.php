<?php
session_start();
require '../includes/dbconnect.php';

/*Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}*/

//handle address form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_no = $_POST['unit_no'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';

    if (!empty($unit_no) && !empty($street) && !empty($city) && !empty($state) && !empty($postal_code)) {
        $newAddress = [
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postal_code
        ];

        if (!isset($_SESSION['addresses'])) {
            $_SESSION['addresses'] = [];
        }
        $_SESSION['addresses'][] = $newAddress;

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    }
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
    <div class="checkout-container">
        <h1>Checkout</h1>
        <p>Products Ordered:</p>
        <ul>
        </ul>
        <p>Total Amount: RM0.00</p>

        <label for="address">Shipping Address:</label><br>
        <div id="saved-addresses">
            <?php
            if (isset($_SESSION['addresses']) && !empty($_SESSION['addresses'])) {
                echo '<h2>Saved Addresses:</h2>';
                echo '<ul>';
                foreach ($_SESSION['addresses'] as $index => $address) {
                    echo '<li>';
                    echo 'unit_no: ' . htmlspecialchars($address['unit_no']) . ', ';
                    echo 'Street: ' . htmlspecialchars($address['street']) . ', ';
                    echo 'City: ' . htmlspecialchars($address['city']) . ', ';
                    echo 'State: ' . htmlspecialchars($address['state']) . ', ';
                    echo 'Postal Code: ' . htmlspecialchars($address['postal_code']);
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No saved addresses found.</p>';
            }
            ?>
        <button type="button" onclick="showAddressForm()">Add New Address</button><br><br>
            <div class="address-form" id="address-form">
                <h2>Enter Shipping Address</h2>
                <form id="addressForm" method="POST">
                    <label for="unit_no">Unit No./Block/Building</label><br>
                    <input type="text" id="unit_no" name="unit_no" required><br>
                    <label for="street">Street:</label><br>
                    <input type="text" id="street" name="street" required><br>
                    <label for="city">City:</label><br>
                    <input type="text" id="city" name="city" required><br>
                    <label for="state">State:</label><br>
                    <input type="text" id="state" name="state" required><br>
                    <label for="postal_code">Postal Code:</label><br>
                    <input type="text" id="postal_code" name="postal_code" required><br><br>
                    <button type="submit" onclick="saveAddress()">Save Address</button>
                </form>
            </div>
        <label for="payment_method">Payment Method:</label><br>
        <select id="payment_method" name="payment_method" required>
            <option value="">Select a payment method</option>
            <option value="credit_card">Credit/Debit Card</option>
            <option value="cash_on_delivery">Cash on Delivery</option>
        </select><br>
        <p>Note: For Cash on Delivery, please have the exact amount ready at the time of delivery.</p>
        <p>Total amount: RM0.00</p>
        <p>Shipping fee: RM0.00</p>
        <p>Grand Total: RM0.00</p>
        <button type="submit">Place Order</button>
    </div>
</body>
</html>
<script>
    function showAddressForm() {
        document.getElementById('address-form').style.display = 'block';
    }

        document.getElementById('addressForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const unit_no = document.getElementById('unit_no').value;
            const street = document.getElementById('street').value;
            const city = document.getElementById('city').value;
            const state = document.getElementById('state').value;
            const zip = document.getElementById('postal_code').value;

            fetch('save_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ unit_no, street, city, state, zip })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Address saved successfully!');
                    location.reload();
                } else {
                    alert('Failed to save address. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the address. Please try again.');
            });
        });

</script>