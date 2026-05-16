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

// 1. HANDLE PROFILE INFORMATION UPDATE
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    if (!ctype_digit($phone)) {
        $message = "Error: Phone column only accepts numbers.";
        $message_type = "alert-error";
    } else {
        $stmt = $conn->prepare("UPDATE customers SET customer_name = ?, customer_phone = ? WHERE customer_id = ?");
        $stmt->bind_param("ssi", $name, $phone, $user_id);
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile.";
            $message_type = "alert-error";
        }
    }
}

// 2. HANDLE ADDING NEW ADDRESS
if (isset($_POST['add_address'])) {
    $unit = $_POST['unit_no'];
    $line1 = $_POST['address_line1'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postcode = $_POST['postal_code'];

    $stmt = $conn->prepare("INSERT INTO addresses (customer_id, unit_no, street, city, state, postal_code) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $unit, $line1, $city, $state, $postcode);
    if ($stmt->execute()) {
        $message = "New address added!";
    } else {
        $message = "Error adding address.";
        $message_type = "alert-error";
    }
}

// 3. HANDLE DELETING ADDRESS
if (isset($_POST['delete_address'])) {
    $addr_id = $_POST['address_id'];
    $stmt = $conn->prepare("DELETE FROM addresses WHERE address_id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $addr_id, $user_id);
    $stmt->execute();
    $message = "Address removed.";
}

// 4. HANDLE SECURE PASSWORD CHANGE
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT customer_password FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($new_pass !== $confirm_pass) {
        $message = "New passwords do not match!";
        $message_type = "alert-error";
    } elseif (!password_verify($old_pass, $user['customer_password'])) {
        $message = "Current password is incorrect.";
        $message_type = "alert-error";
    } else {
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE customers SET customer_password = ? WHERE customer_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        $update_stmt->execute();
        $message = "Password changed successfully!";
    }
}

// Fetch user data for display
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Infinity Grocer</title>
    <link rel="stylesheet" href="includes/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; }
        .container { max-width: 800px; margin: 30px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-section { margin-bottom: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
        h3 { color: #329b18; border-bottom: 2px solid #329b18; padding-bottom: 10px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;
        }
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .toggle-eye { position: absolute; right: 10px; cursor: pointer; color: #666; }
        .btn-save { padding: 12px 25px; border: none; border-radius: 6px; color: white; cursor: pointer; font-weight: bold; margin-top: 15px; width: 100%; }
        .address-card { background: #f9f9f9; padding: 15px; border-radius: 6px; margin-bottom: 10px; border-left: 5px solid #329b18; position: relative; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; text-align: center; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
        .requirement { font-size: 0.85em; color: red; display: block; }
        .valid { color: green; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h2>User Profile</h2>

    <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h3>Personal Information</h3>
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['customer_name']); ?>" required>
            
            <label>Phone Number</label>
            <input type="text" name="phone" id="phone_input" oninput="validatePhoneOnly()" value="<?php echo htmlspecialchars($user_data['customer_phone']); ?>" required>
            
            <label>Email (Cannot be changed)</label>
            <input type="email" value="<?php echo htmlspecialchars($user_data['customer_email']); ?>" disabled style="background:#eee;">
            
            <button type="submit" name="update_profile" class="btn-save" style="background-color: #329b18;">Update Info</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Delivery Addresses</h3>
        <?php
        $addr_query = $conn->prepare("SELECT * FROM addresses WHERE customer_id = ?");
        $addr_query->bind_param("i", $user_id);
        $addr_query->execute();
        $res = $addr_query->get_result();
        
        while ($addr = $res->fetch_assoc()): ?>
            <div class="address-card">
                <strong><?php echo htmlspecialchars($addr['unit_no']); ?></strong>, <?php echo htmlspecialchars($addr['street']); ?><br>
                <?php echo htmlspecialchars($addr['postal_code'] . " " . $addr['city'] . ", " . $addr['state']); ?>
                <form method="POST" style="position:absolute; right:10px; top:10px;">
                    <input type="hidden" name="address_id" value="<?php echo $addr['address_id']; ?>">
                    <button type="submit" name="delete_address" style="background: none; border: none; color: #d9534f; cursor: pointer; padding: 5px; display: flex; align-items: center;">
                        <span class="material-icons" style="font-size: 24px;">delete</span>
                    </button>
                </form>
            </div>
        <?php endwhile; ?>

        <h4 style="margin-top:20px;">Add New Address</h4>
        <form method="POST">
            <input type="text" name="unit_no" placeholder="House No./Unit No./Block (e.g., Block A, 02-03 or No. 123)" required style="margin-bottom:5px; width:100%;">
            <input type="text" name="address_line1" placeholder="Street Name (e.g., Lorong X/XX, Bandar Sunway)" required style="margin-bottom:5px; width:100%;">
            <div style="display:flex; gap:5px; margin-bottom:5px;">
                <input type="text" name="postal_code" placeholder="Postcode (e.g., 57000)" required style="width:30%;">
                <input type="text" name="city" placeholder="City (e.g., Petaling Jaya)" required style="width:70%;">
            </div>
    <div class="row">
        <div>
            <select name="state" required style="width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="" <?php echo empty($current_data['state']) ? 'selected' : ''; ?> disabled>Choose your state...</option>
                
                <?php
                // The 13 States + 3 Federal Territories
                $states = [
                    "Johor", "Kedah", "Kelantan", "Melaka", "Negeri Sembilan", 
                    "Pahang", "Penang", "Perak", "Perlis", "Sabah", 
                    "Sarawak", "Selangor", "Terengganu", "Kuala Lumpur", 
                    "Labuan", "Putrajaya"
                ];
                foreach ($states as $state) {
                    // If the customer has a matching state saved, select it automatically
                    $selected = (isset($current_data['state']) && $current_data['state'] == $state) ? "selected" : "";
                    echo "<option value=\"$state\" $selected>$state</option>";
                }
                ?>
            </select>
        </div>
            <button type="submit" name="add_address" class="btn-save" style="background:#007bff;">Save Address</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Security Settings</h3>
        <form method="POST">
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
            <div id="passwordTips" style="margin-bottom: 10px;">
                <span id="len" class="requirement">• Minimum 15 characters</span>
                <span id="upper" class="requirement">• 1 Uppercase letter</span>
                <span id="lower" class="requirement">• 1 Lowercase letter</span>
                <span id="special" class="requirement">• 1 Special character (@, #, $, etc.)</span>
            </div>

            <label>Confirm New Password</label>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirm_pass" onkeyup="checkMatch()" required>
                <span class="material-icons toggle-eye" onclick="toggleSinglePass('confirm_pass', this)">visibility_off</span>
            </div>
            <span id="matchError" style="color:red; display:none; font-size:0.8em;">Passwords do not match!</span>

            <button type="submit" name="change_password" class="btn-save" style="background-color: #48327a;">Update Password</button>
        </form>
    </div>

    <div style="text-align: center;">
        <a href="index.php">Back to Home</a>
    </div>
</div>

<script>
function validatePhoneOnly() {
    const phoneInput = document.getElementById('phone_input');
    phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '');
}

function toggleSinglePass(fieldId, iconElement) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
        field.type = "text";
        iconElement.textContent = "visibility";
    } else {
        field.type = "password";
        iconElement.textContent = "visibility_off";
    }
}

function checkRequirements() {
    var val = document.getElementById("regPassword").value;
    
    // Check and update colors
    document.getElementById("len").className = val.length >= 15 ? "requirement valid" : "requirement";
    document.getElementById("upper").className = /[A-Z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("lower").className = /[a-z]/.test(val) ? "requirement valid" : "requirement";
    document.getElementById("special").className = /[^\w]/.test(val) ? "requirement valid" : "requirement";
    
    checkMatch(); // Re-verify match if main password changes
}

function checkMatch() {
    const p1 = document.getElementById("new_pass").value;
    const p2 = document.getElementById("confirm_pass").value;
    document.getElementById("matchError").style.display = (p1 !== p2 && p2 !== "") ? "block" : "none";
}
</script>
</body>
</html>