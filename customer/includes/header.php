<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .header-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
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

    .header-search-form {
        display: flex;
        flex-direction: row !important; 
        flex: 1;
        min-width: 200px;
        max-width: 400px;
        margin: 0 30px;
        border: 2px solid #329b18; 
        border-radius: 6px; 
        overflow: hidden; 
        background-color: #fff;
    }
    
    .header-search-input {
        flex: 1; 
        padding: 8px 15px;
        border: none !important; 
        outline: none !important;
        font-size: 15px;
        margin: 0 !important;
        background: transparent;
    }
    
    .header-search-btn {
        background-color: #329b18; 
        color: white; 
        border: none;
        padding: 0 15px; 
        cursor: pointer;
        font-size: 18px;
        margin: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    
    .header-search-btn:hover {
        background-color: #287a13;
    }
</style>

<div class="header-container">
    <p style="font-weight: bold; font-size: 1.5em; color: #329b18; margin: 0;">Infinity Grocer</p>

    <!-- search function on header, send it to products.php -->
    <form method="GET" action="products.php" class="header-search-form">
        <input type="text" name="search" class="header-search-input" placeholder="Search for groceries..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit" class="header-search-btn" title="Search">🔍</button>
    </form>
    
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