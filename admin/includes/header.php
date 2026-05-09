<?php
$cur = basename($_SERVER['PHP_SELF'], '.php');
$role = getAdminRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($page_title ?? 'Admin') ?> — FreshMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<aside class="sidebar">
    <div class="sb-logo">
        <div class="sb-logo-icon">🛒</div>
        <div class="sb-logo-text">
            <div class="brand">FreshMart</div>
            <div class="tagline">Admin Panel</div>
        </div>
    </div>

    <nav class="sb-nav">
        <div class="sb-section">Overview</div>
        <a href="dashboard.php" class="sb-item <?= $cur==='dashboard'?'active':'' ?>">
            <span class="ico">📊</span> Dashboard
        </a>

        <div class="sb-section">Store Management</div>
        <a href="categories.php" class="sb-item <?= $cur==='categories'?'active':'' ?>">
            <span class="ico">🏷️</span> Categories
        </a>
        <a href="products.php" class="sb-item <?= $cur==='products'?'active':'' ?>">
            <span class="ico">📦</span> Products
        </a>

        <div class="sb-section">Users</div>
        <a href="customers.php" class="sb-item <?= $cur==='customers'?'active':'' ?>">
            <span class="ico">👥</span> Customers
            <span class="sb-role-tag">view only</span>
        </a>

        <div class="sb-section">Orders</div>
        <a href="orders.php" class="sb-item <?= $cur==='orders'?'active':'' ?>">
            <span class="ico">🛍️</span> Orders & Products
        </a>

        <div class="sb-section">Reports</div>
        <a href="reports.php" class="sb-item <?= $cur==='reports'?'active':'' ?>">
            <span class="ico">📈</span> Generate Report
        </a>

        <?php if ($role === 'superadmin'): ?>
        <div class="sb-section">Superadmin</div>
        <a href="admins.php" class="sb-item <?= $cur==='admins'?'active':'' ?>">
            <span class="ico">🔑</span> Manage Admins
            <span class="sb-badge">SA</span>
        </a>
        <?php endif; ?>

        <div class="sb-section">Account</div>
        <a href="logout.php" class="sb-item">
            <span class="ico">🚪</span> Logout
        </a>
    </nav>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar"><?= strtoupper(substr(getAdminName(),0,1)) ?></div>
            <div class="sb-user-info">
                <div class="uname"><?= sanitize(getAdminName()) ?></div>
                <div class="urole"><?= sanitize(getAdminRole()) ?></div>
            </div>
            <a href="logout.php" class="sb-logout" title="Logout">⏻</a>
        </div>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <h1><?= sanitize($page_title ?? 'Dashboard') ?></h1>
            <div class="crumb">FreshMart › <?= sanitize($page_title ?? 'Dashboard') ?></div>
        </div>
        <div class="topbar-right">
            <div class="tb-clock" id="clock">--:--</div>
            <div class="tb-role <?= $role ?>"><?= ucfirst($role) ?></div>
        </div>
    </div>
    <div class="page-content">
        <?php flash(); ?>
        <?php if (isset($_GET['error']) && $_GET['error']==='access_denied'): ?>
        <div class="flash flash-error"><span class="flash-icon">✕</span>Access denied. Superadmin only.</div>
        <?php endif; ?>
