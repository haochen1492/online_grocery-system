<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'GreenBasket Admin' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --bg2: #161b22;
            --bg3: #21262d;
            --border: #30363d;
            --green: #3fb950;
            --green-dim: #238636;
            --green-glow: rgba(63,185,80,0.15);
            --text: #e6edf3;
            --text-muted: #7d8590;
            --text-dim: #484f58;
            --accent: #58a6ff;
            --orange: #f0883e;
            --red: #f85149;
            --purple: #bc8cff;
            --yellow: #e3b341;
            --sidebar-w: 240px;
            --radius: 10px;
            --font-head: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }
        a { text-decoration: none; color: inherit; }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg2);
            border-right: 1px solid var(--border);
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo .brand {
            font-family: var(--font-head);
            font-weight: 800;
            font-size: 20px;
            color: var(--green);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sidebar-logo .brand .icon {
            width: 34px; height: 34px;
            background: var(--green-glow);
            border: 1px solid var(--green-dim);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .sidebar-logo small {
            display: block;
            color: var(--text-muted);
            font-size: 11px;
            margin-top: 2px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .sidebar-nav {
            flex: 1;
            padding: 12px 10px;
            overflow-y: auto;
        }
        .nav-label {
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-dim);
            padding: 10px 10px 6px;
            font-weight: 600;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 7px;
            margin-bottom: 2px;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            cursor: pointer;
        }
        .nav-item:hover { background: var(--bg3); color: var(--text); }
        .nav-item.active {
            background: var(--green-glow);
            color: var(--green);
            border: 1px solid rgba(63,185,80,0.2);
        }
        .nav-item .nav-icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer {
            padding: 14px;
            border-top: 1px solid var(--border);
        }
        .admin-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            background: var(--bg3);
            border: 1px solid var(--border);
        }
        .admin-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green-dim), var(--accent));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .admin-info .name { font-size: 13px; font-weight: 600; }
        .admin-info .role { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .logout-btn {
            margin-left: auto;
            color: var(--text-dim);
            font-size: 18px;
            padding: 4px;
            cursor: pointer;
            transition: color 0.15s;
        }
        .logout-btn:hover { color: var(--red); }

        /* MAIN */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .topbar {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-title {
            font-family: var(--font-head);
            font-size: 18px;
            font-weight: 700;
        }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .topbar-time { font-size: 12px; color: var(--text-muted); }
        .page-content { padding: 28px; flex: 1; }

        /* FLASH */
        .flash {
            padding: 12px 18px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
        }
        .flash-success { background: rgba(63,185,80,0.1); border: 1px solid rgba(63,185,80,0.3); color: var(--green); }
        .flash-error { background: rgba(248,81,73,0.1); border: 1px solid rgba(248,81,73,0.3); color: var(--red); }
        .flash-info { background: rgba(88,166,255,0.1); border: 1px solid rgba(88,166,255,0.3); color: var(--accent); }
        .flash-icon { font-size: 16px; }

        /* CARDS */
        .card { background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title { font-family: var(--font-head); font-size: 15px; font-weight: 700; }
        .card-body { padding: 20px; }

        /* TABLE */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead tr { border-bottom: 1px solid var(--border); }
        th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 600;
            white-space: nowrap;
        }
        td { padding: 12px 14px; border-bottom: 1px solid rgba(48,54,61,0.5); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: rgba(255,255,255,0.02); }

        /* BADGES */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .badge-active, .badge-delivered, .badge-paid { background: rgba(63,185,80,0.12); color: var(--green); }
        .badge-inactive, .badge-cancelled { background: rgba(248,81,73,0.12); color: var(--red); }
        .badge-pending { background: rgba(227,179,65,0.12); color: var(--yellow); }
        .badge-processing, .badge-confirmed { background: rgba(88,166,255,0.12); color: var(--accent); }
        .badge-shipped { background: rgba(188,140,255,0.12); color: var(--purple); }
        .badge-blocked { background: rgba(248,81,73,0.12); color: var(--red); }
        .badge-unpaid { background: rgba(240,136,62,0.12); color: var(--orange); }
        .badge-cod, .badge-online, .badge-card { background: var(--bg3); color: var(--text-muted); }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 600;
            transition: all 0.15s;
        }
        .btn-primary { background: var(--green-dim); color: #fff; border: 1px solid var(--green-dim); }
        .btn-primary:hover { background: var(--green); border-color: var(--green); }
        .btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--bg3); color: var(--text); }
        .btn-danger { background: transparent; color: var(--red); border: 1px solid rgba(248,81,73,0.3); }
        .btn-danger:hover { background: rgba(248,81,73,0.1); }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-icon { padding: 6px 8px; }

        /* FORM */
        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }
        input[type=text], input[type=email], input[type=number], input[type=password],
        textarea, select {
            width: 100%;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 9px 13px;
            color: var(--text);
            font-family: var(--font-body);
            font-size: 14px;
            outline: none;
            transition: border 0.15s;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px var(--green-glow);
        }
        select option { background: var(--bg3); }
        textarea { resize: vertical; min-height: 90px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 60px; height: 60px;
            border-radius: 50%;
            opacity: 0.08;
            transform: translate(15px, -15px);
        }
        .stat-card.green::after { background: var(--green); }
        .stat-card.blue::after { background: var(--accent); }
        .stat-card.orange::after { background: var(--orange); }
        .stat-card.purple::after { background: var(--purple); }
        .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 10px; }
        .stat-value { font-family: var(--font-head); font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 6px; }
        .stat-change { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
        .stat-icon { font-size: 26px; margin-bottom: 12px; }

        /* SEARCH BAR */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 12px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 7px;
            width: 240px;
        }
        .search-bar input { border: none; background: transparent; padding: 8px 0; font-size: 13px; color: var(--text); outline: none; width: 100%; }
        .search-bar input::placeholder { color: var(--text-dim); }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.2s ease;
        }
        .modal-lg { max-width: 720px; }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title { font-family: var(--font-head); font-size: 16px; font-weight: 700; }
        .modal-close {
            width: 28px; height: 28px;
            border-radius: 6px;
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text-muted);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            font-family: var(--font-body);
            transition: all 0.15s;
        }
        .modal-close:hover { color: var(--red); border-color: rgba(248,81,73,0.3); }
        .modal-body { padding: 22px; }
        .modal-footer {
            padding: 14px 22px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* FILTERS ROW */
        .filters-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }
        .filters-row select { width: auto; padding: 7px 10px; font-size: 13px; }

        /* EMPTY STATE */
        .empty-state { text-align: center; padding: 50px 20px; color: var(--text-muted); }
        .empty-state .empty-icon { font-size: 42px; margin-bottom: 12px; }
        .empty-state p { font-size: 14px; }

        /* SCROLLBAR */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="dashboard.php" class="brand">
            <span class="icon">🥦</span>
            GreenBasket
        </a>
        <small>Admin Panel</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="dashboard.php" class="nav-item <?= $current_page==='dashboard'?'active':'' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <div class="nav-label">Store</div>
        <a href="orders.php" class="nav-item <?= $current_page==='orders'?'active':'' ?>">
            <span class="nav-icon">🛒</span> Orders
        </a>
        <a href="products.php" class="nav-item <?= $current_page==='products'?'active':'' ?>">
            <span class="nav-icon">📦</span> Products
        </a>
        <a href="categories.php" class="nav-item <?= $current_page==='categories'?'active':'' ?>">
            <span class="nav-icon">🏷️</span> Categories
        </a>
        <a href="customers.php" class="nav-item <?= $current_page==='customers'?'active':'' ?>">
            <span class="nav-icon">👥</span> Customers
        </a>
        <div class="nav-label">Account</div>
        <a href="logout.php" class="nav-item">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-card">
            <div class="admin-avatar"><?= strtoupper(substr(getAdminName(),0,1)) ?></div>
            <div class="admin-info">
                <div class="name"><?= getAdminName() ?></div>
                <div class="role"><?= getAdminRole() ?></div>
            </div>
            <a href="logout.php" class="logout-btn" title="Logout">⏻</a>
        </div>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-title"><?= $page_title ?? 'Dashboard' ?></div>
        <div class="topbar-actions">
            <div class="topbar-time" id="clock"></div>
        </div>
    </div>
    <div class="page-content">
        <?php flash(); ?>
