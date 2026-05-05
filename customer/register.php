<?php
// Include your database connection
include '../includes/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from the form[cite: 6]
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // 1. Password Complexity Validation Logic
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    // 2. Server-side Validation[cite: 6]
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } 
    else if (!ctype_digit($phone)) {
        $error = "Phone number must contain only digits (0-9).";
    } 
    // NEW: Strict Password Requirement Check
    else if (!$uppercase || !$lowercase || !$specialChars || strlen($password) < 15) {
        $error = "Password must be at least 15 characters long and include at least one uppercase letter, one lowercase letter, and one special character.";
    }
    else {
        // Hash the password for security[cite: 6]
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // 3. Check if email already exists in the customers table[cite: 6]
        $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered!";
        } else {
            // 4. Insert data into the customers table[cite: 3, 6]
            $stmt = $conn->prepare("INSERT INTO customers (customer_name, customer_email, customer_password, customer_phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hashed, $phone);
            
            if ($stmt->execute()) {
                // Redirect to login page upon success[cite: 6]
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

    <!-- Display error message if any[cite: 6] -->
    <?php if (isset($error)): ?>
        <div class="error-msg" style="color: red; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required placeholder="Enter your full name">

        <label for="email">Email Address:</label>
        <input type="email" name="email" required placeholder="Enter your email">

        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" id="phoneInput" required placeholder="e.g. 0123456789" oninput="validatePhone()">
        <span id="phoneError" style="color: red; font-size: 0.8em; display: none; margin-top: 5px;">Only numbers are allowed!</span>

        <label for="password">Password:</label>
        <!-- Updated minlength and placeholder for new security rules[cite: 5] -->
        <input type="password" name="password" id="regPassword" required minlength="15" 
               placeholder="Min 15 chars (A, a, # required)">
        
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
// Numeric only validation for phone field[cite: 6]
function validatePhone() {
    var phoneInput = document.getElementById("phoneInput");
    var phoneError = document.getElementById("phoneError");
    var cleanedValue = phoneInput.value.replace(/[^0-9]/g, '');
    
    if (phoneInput.value !== cleanedValue) {
        phoneError.style.display = "block";
        phoneInput.value = cleanedValue;
    } else {
        phoneError.style.display = "none";
    }
}

// Toggle Password Visibility Logic[cite: 6]
function togglePassword() {
    var x = document.getElementById("regPassword");
    x.type = (x.type === "password") ? "text" : "password";
}
</script>

</body>
</html>