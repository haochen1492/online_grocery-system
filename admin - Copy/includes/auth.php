<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        header('Location: dashboard.php?error=access_denied');
        exit;
    }
}

function getAdminName() {
    return $_SESSION['admin_username'] ?? 'Admin';
}

function getAdminRole() {
    return $_SESSION['admin_role'] ?? 'admin';
}

function sanitize($v) {
    return htmlspecialchars(strip_tags(trim($v ?? '')));
}

function redirect($url, $msg = '', $type = 'success') {
    if ($msg) $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    header("Location: $url");
    exit;
}

function flash() {
    if (!isset($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $icons = ['success' => '✓', 'error' => '✕', 'info' => 'ℹ'];
    $icon  = $icons[$f['type']] ?? 'ℹ';
    echo "<div class='flash flash-{$f['type']}'><span class='flash-icon'>{$icon}</span>{$f['msg']}</div>";
}

function formatRM($v) {
    return 'RM ' . number_format((float)$v, 2);
}
?>
