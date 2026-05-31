<?php $cur = basename($_SERVER['PHP_SELF'],'.php'); $role = getAdminRole(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= sanitize($page_title??'Admin') ?> — Infinity Grocery</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-brand-icon">🛒</div>
    <div><div class="sb-brand-name">Infinity Grocery</div><div class="sb-brand-sub">Admin Panel</div></div>
  </div>
  <nav class="sb-nav">
    <div class="sb-sec">Overview</div>
    <a href="dashboard.php"  class="sb-link <?= $cur==='dashboard' ?'active':'' ?>"><span class="ico">📊</span>Dashboard</a>

    <div class="sb-sec">Store — Task 2</div>
    <a href="categories.php" class="sb-link <?= $cur==='categories'?'active':'' ?>"><span class="ico">🏷️</span>Categories</a>
    <a href="products.php"   class="sb-link <?= $cur==='products'  ?'active':'' ?>"><span class="ico">📦</span>Products</a>

    <div class="sb-sec">Users — Task 3</div>
    <a href="customers.php"  class="sb-link <?= $cur==='customers' ?'active':'' ?>">
      <span class="ico">👥</span>Customer List
      <span class="sb-chip sb-chip-ro">view</span>
    </a>

    <div class="sb-sec">Orders — Tasks 4 &amp; 5</div>
    <a href="orders.php"     class="sb-link <?= $cur==='orders'    ?'active':'' ?>"><span class="ico">🛍️</span>Orders &amp; Products</a>

    <div class="sb-sec">Reports — Task 6</div>
    <a href="reports.php"    class="sb-link <?= $cur==='reports'   ?'active':'' ?>"><span class="ico">📈</span>Generate Report</a>

    <?php if($role==='superadmin'): ?>
    <div class="sb-sec">Superadmin — Task 1</div>
    <a href="admins.php"     class="sb-link <?= $cur==='admins'    ?'active':'' ?>">
      <span class="ico">🔑</span>Manage Admins
      <span class="sb-chip sb-chip-sa">SA</span>
    </a>
    <?php endif; ?>

    <div class="sb-sec">Account</div>
    <a href="logout.php" class="sb-link"><span class="ico">🚪</span>Logout</a>
  </nav>
  <div class="sb-foot">
    <div class="sb-user">
      <div class="sb-av"><?= strtoupper(substr(getAdminName(),0,1)) ?></div>
      <div><div class="sb-uname"><?= sanitize(getAdminName()) ?></div><div class="sb-urole"><?= $role ?></div></div>
      <a href="logout.php" class="sb-out" title="Logout">⏻</a>
    </div>
  </div>
</aside>
<main class="main">
  <div class="topbar">
    <div class="tb-left">
      <h1><?= sanitize($page_title??'Dashboard') ?></h1>
      <div class="crumb">Infinity Grocer Admin › <?= sanitize($page_title??'Dashboard') ?></div>
    </div>
    <div class="tb-right">
      <div class="tb-clock" id="clock">--:--</div>
      <div class="tb-role <?= $role ?>"><?= ucfirst($role) ?></div>
    </div>
  </div>
  <div class="page-content">
    <?php flash(); ?>
