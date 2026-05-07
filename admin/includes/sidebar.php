<?php $current = basename($_SERVER['PHP_SELF']); ?>
<nav id="sidebar" class="sidebar d-flex flex-column">
    <div class="sidebar-brand">
        <i class="bi bi-basket2-fill me-2"></i>FreshCart
    </div>
    <ul class="nav flex-column flex-grow-1 px-2 mt-3">
        <li class="nav-item">
            <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'categories.php' ? 'active' : '' ?>" href="categories.php">
                <i class="bi bi-tags me-2"></i>Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'products.php' ? 'active' : '' ?>" href="products.php">
                <i class="bi bi-box-seam me-2"></i>Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'customers.php' ? 'active' : '' ?>" href="customers.php">
                <i class="bi bi-people me-2"></i>Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current === 'orders.php' ? 'active' : '' ?>" href="orders.php">
                <i class="bi bi-cart3 me-2"></i>Orders
            </a>
        </li>
    </ul>
    <div class="sidebar-footer px-3 py-3">
        <div class="text-muted small mb-2">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['admin_username']) ?>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</nav>
