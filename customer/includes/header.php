<p>Infinity Grocer</p>
<nav>
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="Contact.php">Contact</a>
    <a href="about.php">About Us</a>
    <a href="cart.html">Cart </a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <a href="logout.php">Logout</a>
        <a href="orders.php">Order History</a>
        <a href="profile.php">Profile</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
</nav>