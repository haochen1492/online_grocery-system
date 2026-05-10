<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 5%;
        background-color: #fff;
        border-bottom: 2px solid #329b18;
        font-family: Arial, sans-serif;
    }
    .nav-links a {
        text-decoration: none;
        color: #333;
        margin-left: 20px;
        font-weight: 500;
    }
    .nav-links a:hover { color: #329b18; }
    .auth-btn {
        background: #329b18;
        color: white !important;
        padding: 8px 15px;
        border-radius: 5px;
    }
</style>

<div class="header-container">
    <p style="font-weight: bold; font-size: 1.5em; color: #329b18; margin: 0;">Infinity Grocer</p>
    
    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="Contact.php">Contact</a>
        <a href="about.php">About Us</a>
        <a href="cart.php">Cart</a> 

        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="order_history.php">📦 Orders</a>
            <a href="profile.php">👤 Profile</a>
            <a href="logout.php" style="color: #d9534f;">Logout</a>
        <?php else: ?>
            <?php if (basename($_SERVER['PHP_SELF']) == 'login.php'): ?>
                <a href="register.php" class="auth-btn">Register</a>
            <?php else: ?>
                <a href="login.php" class="auth-btn">Login</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</div>