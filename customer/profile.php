<?php
include '../includes/dbconnect.php';
session_start();

if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    $update = $conn->prepare("UPDATE customers SET customer_name = ?, customer_phone = ? WHERE customer_id = ?");
    if ($update->execute([$name, $phone, $user_id])) {
        $_SESSION['username'] = $name; // Update session name
        echo "<script>alert('Profile Updated!');</script>";
    }
}
?>
<div class="profile-container" style="padding: 50px; text-align: center;">
    <h2>Edit Profile</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $user['customer_name']; ?>"><br><br>
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo $user['customer_phone']; ?>"><br><br>
        <button type="submit">Save Changes</button>
    </form>
</div>