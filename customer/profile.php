<?php
include '../includes/dbconnect.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. HANDLE PROFILE INFORMATION UPDATE
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    $stmt = $conn->prepare("UPDATE customers SET customer_name = ?, customer_phone = ? WHERE customer_id = ?");
    if ($stmt->execute([$name, $phone, $user_id])) {
        $_SESSION['username'] = $name; // Update session name for header display
        $message = "Profile updated successfully!";
    }
}

// 2. HANDLE SECURE PASSWORD CHANGE
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT customer_password FROM customers WHERE customer_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (password_verify($old_pass, $user['customer_password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_new = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE customers SET customer_password = ? WHERE customer_id = ?");
            $update->execute([$hashed_new, $user_id]);
            $message = "Password changed successfully!";
        } else {
            $message = "New passwords do not match!";
        }
    } else {
        $message = "Current password incorrect!";
    }
}

// 3. FETCH CURRENT DATA FOR THE FORM
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Online Grocery System</title>
    <link rel="stylesheet" href="includes/styles.css">
    <style>
        .profile-wrapper { max-width: 600px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-section { border-bottom: 1px solid #ddd; padding-bottom: 20px; margin-bottom: 20px; }
        .form-section:last-child { border-bottom: none; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-save { background-color: #329b18; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-save:hover { background-color: #287a13; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?> <div class="profile-wrapper">
    <h2>User Profile Management</h2>

    <?php if ($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h3>Basic Information</h3>
        <form method="POST">
            <label>Email Address (Cannot change)</label>
            <input type="email" value="<?php echo htmlspecialchars($current_user['customer_email']); ?>" disabled>
            
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($current_user['customer_name']); ?>" required>
            
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($current_user['customer_phone']); ?>" placeholder="e.g. 012-3456789">
            
            <button type="submit" name="update_profile" class="btn-save">Update Info</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Change Password</h3>
        <form method="POST">
            <input type="password" name="old_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required minlength="6">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required minlength="6">
            
            <button type="submit" name="change_password" class="btn-save" style="background-color: #48327a;">Update Password</button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="index.php">Return to Home</a> | <a href="logout.php" style="color: red;">Logout</a>
    </div>
</div>

</body>
</html>