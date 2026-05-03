<p>Infinity Grocer</p>
<nav>
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="Contact.php">Contact</a>
    <a href="about.php">About Us</a>
    <a href="cart.php">Cart</a> <!-- Changed to .php to match your cart file[cite: 2] -->

    <?php if (isset($_SESSION['user_id'])): ?>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
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