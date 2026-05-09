<?php
// Ensure session is started only once to prevent errors
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="header-container" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background-color: #fff; border-bottom: 2px solid #329b18;">
    <p style="font-weight: bold; font-size: 1.5em; color: #329b18; margin: 0;">Infinity Grocer</p>
    
    <nav style="display: flex; gap: 20px; align-items: center;">
        <a href="index.php" style="text-decoration: none; color: #333;">Home</a>
        <a href="products.php" style="text-decoration: none; color: #333;">Products</a>
        <a href="Contact.php" style="text-decoration: none; color: #333;">Contact</a>
        <a href="about.php" style="text-decoration: none; color: #333;">About Us</a>
        <a href="cart.php" style="text-decoration: none; color: #333;">Cart</a> 

        <?php if (isset($_SESSION['customer_id'])): ?>
            <div class="user-menu" style="display: flex; gap: 15px; align-items: center; border-left: 1px solid #ddd; padding-left: 15px;">
                <a href="profile.php" style="text-decoration: none; color: #329b18; font-weight: bold;">👤 Profile</a>
                <a href="orders.php" style="text-decoration: none; color: #333;">📦 Orders</a>
                <a href="logout.php" style="text-decoration: none; color: #d9534f; font-weight: bold;">Logout</a>
            </div>
        <?php else: ?>
            <?php 
                $current_page = basename($_SERVER['PHP_SELF']); 
                // Context-aware button: show Register on Login page, otherwise show Login
                if ($current_page == 'login.php'): 
            ?>
                <a href="register.php" style="text-decoration: none; background: #329b18; color: white; padding: 5px 15px; border-radius: 4px;">Register</a>
            <?php else: ?>
                <a href="login.php" style="text-decoration: none; background: #329b18; color: white; padding: 5px 15px; border-radius: 4px;">Login</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</div>