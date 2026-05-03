<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function getAdminName() {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function getAdminRole() {
    return $_SESSION['admin_role'] ?? 'admin';
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url, $msg = '', $type = 'success') {
    if ($msg) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }
    header("Location: $url");
    exit;
}

function flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = $f['type'] === 'success' ? '✓' : ($f['type'] === 'error' ? '✕' : 'ℹ');
        echo "<div class='flash flash-{$f['type']}'><span class='flash-icon'>$icon</span> {$f['msg']}</div>";
    }
}

function formatMYR($amount) {
    return 'RM ' . number_format($amount, 2);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    return floor($time/86400) . 'd ago';
}
?>
