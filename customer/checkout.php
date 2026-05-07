<?php
session_start();
require '../includes/dbconnect.php';

//payment method handling
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    if($_POST['payment_method'] === 'credit_card') {
        header('Location: payment.php');
        exit;
    } elseif ($_POST['payment_method'] === 'cash_on_delivery') {
        $total_amount = $_POST['total_amount'] ?? 0.00; // Calculate total amount based on cart items
        $payment_method = 'cash_on_delivery';
        $status = 'pending';
        $customer_id = $_SESSION['customer_id']; // Assuming customer ID is stored in session

        // Handle cash on delivery logic here (e.g., save order to database)
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_price, payment_method, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $_SESSION['customer_id'], $total_price, $payment_method, $status);
        $stmt->execute();

        //get the last inserted order ID
        $order_id = $conn->insert_id;

        // Insert order items into order_details table
        $stmt_items = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $stmt_items->bind_param("iii", $order_id, $product_id, $quantity);
            $stmt_items->execute();
        }
        // Direct to a confirmation page
        unset($_SESSION['cart']);
        header('Location: order_confirmation.php');
        
        exit;
    } else {
        echo "Invalid payment method selected.";
        exit;
    }
}

//fetch address from logged in user
$customer_id = $_SESSION['customer_id'];
$stmt = $conn->prepare("SELECT * FROM addresses WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

//handle address form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_no = $_POST['unit_no'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';

    if (!empty($unit_no) && !empty($street) && !empty($city) && !empty($state) && !empty($postal_code)) {
        $newAddress = [
            'unit_no' => $unit_no,
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

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart'])) {
    $cart = $_POST['cart'];
    $_SESSION['cart'] = $cart;
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
    <div class="checkout-container" action="checkout.php" method="POST">
        <h1>Checkout</h1>
        <p>Products Ordered:</p>
        <ul>
        </ul>
        <p>Total Amount: RM<?php echo number_format($totalAmount, 2); ?></p>

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
        <button type="button" onclick="showAddressForm()">Add New Address</button><br>
            <div class="address-form" id="address-form">
                <h2>Enter Shipping Address</h2>
                <form id="addressForm" method="POST">
                    <label for="unit_no">Unit No./Block/Building</label>
                    <input type="text" id="unit_no" name="unit_no" required>
                    <label for="street">Street:</label>
                    <input type="text" id="street" name="street" required>
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" required>
                    <label for="state">State:</label>
                    <input type="text" id="state" name="state" required>
                    <label for="postal_code">Postal Code:</label>
                    <input type="text" id="postal_code" name="postal_code" required>
                    <button type="submit" onclick="saveAddress()">Save Address</button>
                </form>
            </div>
        <form method="POST" action="checkout.php">
            <label for="payment_method">Payment Method:</label><br>
            <select id="payment_method" name="payment_method" required>
                <option value="">Select a payment method</option>
                <option value="credit_card">Credit/Debit Card</option>
                <option value="cash_on_delivery">Cash on Delivery</option>
            </select><br>
            <p>Note: For Cash on Delivery, please have the exact amount ready at the time of delivery.</p>
            <p>Total amount: RM<?php echo number_format($totalAmount, 2); ?></p>
            <p>Shipping fee: RM<?php echo number_format($shippingFee, 2); ?></p>
            <p>Grand Total: RM<?php echo number_format($grandTotal, 2); ?></p>
            <button type="submit" >Place Order</button>
        </form>
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