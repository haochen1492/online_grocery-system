<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$nav = [
    'main' => [
        ['dashboard', '📊', 'Dashboard'],
    ],
    'store' => [
        ['orders',     '🛒', 'Orders'],
        ['products',   '📦', 'Products'],
        ['categories', '🏷️',  'Categories'],
        ['customers',  '👥', 'Customers'],
        ['payments',   '💳', 'Payments'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'FreshMart Admin' ?> — FreshMart</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="dashboard.php" class="brand">
            <span class="logo-box">🛒</span>
            FreshMart
        </a>
        <small>Admin Panel</small>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($nav as $section => $items): ?>
        <div class="nav-section"><?= $section ?></div>
        <?php foreach ($items as [$slug, $icon, $label]): ?>
        <a href="<?= $slug ?>.php" class="nav-item <?= $current===$slug?'active':'' ?>">
            <span class="ni"><?= $icon ?></span> <?= $label ?>
        </a>
        <?php endforeach; ?>
        <?php endforeach; ?>

        <div class="nav-section">account</div>
        <a href="logout.php" class="nav-item">
            <span class="ni">🚪</span> Logout
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-card">
            <div class="admin-avatar"><?= strtoupper(substr(getAdminName(),0,1)) ?></div>
            <div class="admin-info">
                <div class="aname"><?= sanitize(getAdminName()) ?></div>
                <div class="arole">Administrator</div>
            </div>
            <a href="logout.php" class="logout-link" title="Logout">⏻</a>
        </div>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <h1><?= $page_title ?? 'Dashboard' ?></h1>
            <div class="breadcrumb">FreshMart Admin › <?= $page_title ?? 'Dashboard' ?></div>
        </div>
        <div class="topbar-right">
            <div class="topbar-time" id="clock">--:--</div>
        </div>
    </div>
    <div class="page-content">
        <?php flash(); ?>
