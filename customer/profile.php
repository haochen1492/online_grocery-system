<?php
include '../includes/dbconnect.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['customer_id'];
$message = "";
$message_type = "alert-success"; 

// Handle profile info update
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    // Server-side check to ensure only numbers are saved
    if (!ctype_digit($phone)) {
        $message = "Error: This column only accepts numbers.";
        $message_type = "alert-error";
    } else {
        $stmt = $conn->prepare("UPDATE customers SET customer_name = ?, customer_phone = ? WHERE customer_id = ?");
        $stmt->bind_param("ssi", $name, $phone, $user_id);
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $_SESSION['customer_name'] = $name; 
        } else {
            $message = "Error updating profile.";
            $message_type = "alert-error";
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT customer_password FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($old_pass, $user['customer_password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_new = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE customers SET customer_password = ? WHERE customer_id = ?");
            $update->bind_param("si", $hashed_new, $user_id);
            $update->execute();
            $message = "Password changed successfully!";
        } else {
            $message = "New passwords do not match!";
            $message_type = "alert-error";
        }
    } else {
        $message = "Current password incorrect!";
        $message_type = "alert-error";
    }
}

// Handle address update
if (isset($_POST['update_address'])) {
    $unit = $_POST['unit_no'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $postcode = $_POST['postal_code'];
    $state = $_POST['state'];
    $country = $_POST['country'];

    $check_addr = $conn->prepare("SELECT address_id FROM addresses WHERE customer_id = ?");
    $check_addr->bind_param("i", $user_id);
    $check_addr->execute();
    $addr_exists = $check_addr->get_result()->num_rows > 0;

    if ($addr_exists) {
        $sql = "UPDATE addresses SET unit_no=?, street=?, city=?, state=?, postal_code=?, country=? WHERE customer_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $unit, $street, $city, $state, $postcode, $country, $user_id);
    } else {
        $sql = "INSERT INTO addresses (customer_id, unit_no, street, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $user_id, $unit, $street, $city, $state, $postcode, $country);
    }

    if ($stmt->execute()) {
        $message = "Delivery address updated successfully!";
    }
}

// Fetch latest data to pre-fill the form
$query = "SELECT c.*, a.unit_no, a.street, a.city, a.state, a.postal_code, a.country 
          FROM customers c 
          LEFT JOIN addresses a ON c.customer_id = a.customer_id 
          WHERE c.customer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        .profile-wrapper { max-width: 700px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-family: Arial, sans-serif; }
        .form-section { border-bottom: 1px solid #ddd; padding-bottom: 25px; margin-bottom: 25px; }
        .form-section:last-child { border-bottom: none; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        label { font-weight: bold; display: block; margin-top: 10px; color: #444; }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-save { background-color: #329b18; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%; font-weight: bold; }
        .btn-save:hover { background-color: #287a13; }
        .row { display: flex; gap: 15px; }
        .row div { flex: 1; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="profile-wrapper">
    <h2>My Account Management</h2>

    <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h3>Personal Details</h3>
        <form method="POST">
            <label>Email Address</label>
            <input type="email" value="<?php echo htmlspecialchars($current_data['customer_email']); ?>" disabled style="background: #f9f9f9; cursor: not-allowed;">
            
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($current_data['customer_name']); ?>" required>
            
            <label>Phone Number</label>
            <input type="text" name="phone" id="phone_input" value="<?php echo htmlspecialchars($current_data['customer_phone']); ?>" oninput="validatePhoneOnly()" placeholder="Example: 0123456789">
            
            <button type="submit" name="update_profile" class="btn-save">Update Personal Info</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Delivery Address</h3>
        <form method="POST">
            <div class="row">
                <div><label>Unit/House No</label><input type="text" name="unit_no" value="<?php echo htmlspecialchars($current_data['unit_no'] ?? ''); ?>" required></div>
                <div><label>Street</label><input type="text" name="street" value="<?php echo htmlspecialchars($current_data['street'] ?? ''); ?>" required></div>
            </div>
            <div class="row">
                <div><label>City</label><input type="text" name="city" value="<?php echo htmlspecialchars($current_data['city'] ?? ''); ?>" required></div>
                <div><label>Postcode</label><input type="text" name="postal_code" value="<?php echo htmlspecialchars($current_data['postal_code'] ?? ''); ?>" required></div>
            </div>
            <div class="row">
        <div>
            <label>State</label>
            <select name="state" required style="width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="" disabled>Select your state</option>
                <?php
                $states = [
                    "Johor", "Kedah", "Kelantan", "Melaka", "Negeri Sembilan", 
                    "Pahang", "Penang", "Perak", "Perlis", "Sabah", 
                    "Sarawak", "Selangor", "Terengganu", "Kuala Lumpur", 
                    "Labuan", "Putrajaya"
                ];
                foreach ($states as $state) {
                    $selected = ($current_data['state'] == $state) ? "selected" : "";
                    echo "<option value=\"$state\" $selected>$state</option>";
                }
                ?>
            </select>
        </div>
        <div>
        <label>Country</label>
        <input type="text" name="country" value="Malaysia" readonly style="background-color: #eeeeee; cursor: not-allowed;">
        </div>
</div>
            <button type="submit" name="update_address" class="btn-save" style="background-color: #007bff;">Update Delivery Address</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Change Password</h3>
        <form method="POST">
    </div>
        <label>Current Password</label>
        <div class="password-wrapper">
        <input type="password" name="old_password" id="old_pass" required>
        <span class="material-icons toggle-eye" onclick="toggleSinglePass('old_pass', this)">visibility_off</span>
        </div>

        <label>New Password</label>
        <div class="password-wrapper">
        <input type="password" name="new_password" id="new_pass" onkeyup="checkRequirements()" required>
        <span class="material-icons toggle-eye" onclick="toggleSinglePass('new_pass', this)">visibility_off</span>
        </div>

        <label>Confirm New Password</label>
        <div class="password-wrapper">
        <input type="password" name="confirm_password" id="confirm_pass" onkeyup="checkMatch()" required>
        <span class="material-icons toggle-eye" onclick="toggleSinglePass('confirm_pass', this)">visibility_off</span>
    </div>
        <button type="submit" name="change_password" class="btn-save" style="background-color: #48327a;">Change Account Password</button>
    </form>
    </div>

<script>

function validatePhoneOnly() {
    const phoneInput = document.getElementById('phone_input');
    const regex = /[^0-9]/g;

    if (regex.test(phoneInput.value)) {
        alert("This column only accepts numbers!");
        // Remove alphabets/symbols immediately
        phoneInput.value = phoneInput.value.replace(regex, '');
    }
}

function toggleSinglePass(fieldId, iconElement) {
    const passwordField = document.getElementById(fieldId);
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        iconElement.textContent = "visibility"; // Changes icon to open eye
    } else {
        passwordField.type = "password";
        iconElement.textContent = "visibility_off"; // Changes icon to slashed eye
    }
}
</script>

</body>
</html>