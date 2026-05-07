<?php
if (session_status() === PHP_SESSION_NONE) session_start();

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
    return $_SESSION['admin_username'] ?? 'Admin';
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input ?? '')));
}

function redirect($url, $msg = '', $type = 'success') {
    if ($msg) $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    header("Location: $url");
    exit;
}

function flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icons = ['success' => '✓', 'error' => '✕', 'info' => 'ℹ'];
        $icon = $icons[$f['type']] ?? 'ℹ';
        echo "<div class='flash flash-{$f['type']}'><span>{$icon}</span>{$f['msg']}</div>";
    }
}

function formatRM($amount) {
    return 'RM ' . number_format((float)$amount, 2);
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    return date('d M Y', strtotime($datetime));
}
?>
