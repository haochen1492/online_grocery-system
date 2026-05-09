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

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="orders.php">Order History</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <?php 
            // Detect if the current page is login.php
            $current_page = basename($_SERVER['PHP_SELF']); 
            if ($current_page == 'login.php'): 
        ?>
            <!-- Show Register button ONLY when on login.php -->
            <a href="register.php">Register</a>
        <?php else: ?>
            <!-- Show Login button on all other pages -->
            <a href="login.php">Login</a>
        <?php endif; ?>
    <?php endif; ?>
</nav>