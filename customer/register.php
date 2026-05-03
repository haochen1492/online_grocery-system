<?php
// Include your database connection
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from the form[cite: 4]
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // 1. Server-side Validation[cite: 4]
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } 
    // NEW: Check if phone contains only numbers[cite: 1]
    else if (!ctype_digit($phone)) {
        $error = "Phone number must contain only digits (0-9).";
    } 
    else {
        // Hash the password for security[cite: 4]
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // 2. Check if email already exists in the customers table
        $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered!";
        } else {
            // 3. Insert data into the customers table following your schema[cite: 3]
            $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_email, customer_password, customer_phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hashed, $phone);
            
            if ($stmt->execute()) {
                // Redirect to login page upon success[cite: 4]
                header("Location: login.php?registration=success");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <h2>Create Customer Account</h2>

    <!-- Display error message if any[cite: 4] -->
    <?php if (isset($error)): ?>
        <div class="error-msg" style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required placeholder="Enter your full name">

        <label for="email">Email Address:</label>
        <input type="email" name="email" required placeholder="Enter your email">

        <label for="phone">Phone Number:</label>
        <!-- Added id and oninput for real-time validation[cite: 4] -->
        <input type="text" name="phone" id="phoneInput" required placeholder="e.g. 0123456789" oninput="validatePhone()">
        <span id="phoneError" style="color: red; font-size: 0.8em; display: none; margin-top: 5px;">Only numbers are allowed!</span>

        <label for="password">Password:</label>
        <input type="password" name="password" id="regPassword" required minlength="8" placeholder="At least 8 characters">
        
        <div style="margin: 10px 0; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="showPassToggle" onclick="togglePassword()">
            <label for="showPassToggle" style="cursor: pointer; font-weight: normal;">Show Password</label>
        </div>

        <button type="submit" class="btn">Register</button>
    </form>
    
    <p style="margin-top: 15px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<script>
// Logic to only allow numbers in the phone field[cite: 4]
function validatePhone() {
    var phoneInput = document.getElementById("phoneInput");
    var phoneError = document.getElementById("phoneError");
    
    // Replace non-numeric characters with empty string
    var cleanedValue = phoneInput.value.replace(/[^0-9]/g, '');
    
    if (phoneInput.value !== cleanedValue) {
        phoneError.style.display = "block";
        phoneInput.value = cleanedValue;
    } else {
        phoneError.style.display = "none";
    }
}

// Toggle Password Visibility Logic[cite: 4]
function togglePassword() {
    var x = document.getElementById("regPassword");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
</script>

</body>
</html>