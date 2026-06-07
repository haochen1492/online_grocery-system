<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$db    = getDB();
$token = sanitize($_GET['token'] ?? '');
$type  = '';

if (empty($token)) {
    $result = 'invalid';
} else {
    // First check if it's an email change token (stored in pending_email_token)
    $st = $db->prepare("SELECT admin_id, username, pending_email, pending_email_token FROM admin WHERE pending_email_token = ? LIMIT 1");
    $st->bind_param("s", $token);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();

    if ($row) {
        // Apply the pending email change
        $up = $db->prepare("UPDATE admin SET email = ?, email_verified = 1, pending_email = NULL, pending_email_token = NULL WHERE admin_id = ?");
        $up->bind_param("si", $row['pending_email'], $row['admin_id']);
        $up->execute();
        $type   = 'email_changed';
        $result = 'success';
    } else {
        // Check if it's a new account verification token
        $st2 = $db->prepare("SELECT admin_id, username FROM admin WHERE verification_token = ? LIMIT 1");
        $st2->bind_param("s", $token);
        $st2->execute();
        $row2 = $st2->get_result()->fetch_assoc();

        if ($row2) {
            $up2 = $db->prepare("UPDATE admin SET email_verified = 1, verification_token = NULL WHERE admin_id = ?");
            $up2->bind_param("i", $row2['admin_id']);
            $up2->execute();
            $type   = 'verified';
            $result = 'success';
        } else {
            $result = 'invalid';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Email Verification — Infinity Grocer Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
<style>
:root{--green:#1a5c38;--green2:#27764a;--green3:#4caf76;--gbg:#edf7f1;--bg:#f4f6f3;--sur:#fff;--bor:#e4e8e2;--txt:#1a1f1c;--txt2:#5a6a5e;--red:#b91c1c;--rbg:#fef2f2}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:var(--bg);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
body::before{content:'';position:fixed;inset:0;background-image:radial-gradient(rgba(26,92,56,.04) 1px,transparent 1px);background-size:24px 24px;pointer-events:none}
.wrap{width:100%;max-width:420px;position:relative;z-index:1;text-align:center}
.brand-icon{width:60px;height:60px;background:var(--green);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;box-shadow:0 4px 16px rgba(26,92,56,.3)}
.brand h1{font-family:'Playfair Display',serif;font-size:22px;font-weight:800;color:var(--txt)}
.brand p{font-size:13px;color:#8a9a8e;margin-top:3px;margin-bottom:28px}
.card{background:var(--sur);border:1px solid var(--bor);border-radius:16px;padding:36px 32px;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.icon-circle{width:70px;height:70px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 20px;animation:popIn .4s cubic-bezier(.34,1.56,.64,1)}
@keyframes popIn{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
.icon-circle.ok{background:var(--gbg);border:2px solid var(--green3)}
.icon-circle.err{background:var(--rbg);border:2px solid #fca5a5}
h2{font-family:'Playfair Display',serif;font-size:22px;font-weight:800;margin-bottom:10px}
p{font-size:14px;color:var(--txt2);line-height:1.7;margin-bottom:24px}
.btn{display:inline-block;background:var(--green);color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:12px 30px;border-radius:10px;box-shadow:0 4px 14px rgba(26,92,56,.26);transition:all .17s}
.btn:hover{background:var(--green2);transform:translateY(-1px)}
.btn-ghost{background:var(--bg);color:var(--txt);border:1.5px solid var(--bor)}
.btn-ghost:hover{background:var(--gbg);border-color:var(--green3);color:var(--green)}
</style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <div class="brand-icon">🛒</div>
        <h1>Infinity Grocer Admin</h1>
        <p>Email Verification</p>
    </div>
    <div class="card">
        <?php if ($result === 'success'): ?>
            <div class="icon-circle ok">✓</div>
            <?php if ($type === 'email_changed'): ?>
                <h2>Email Updated!</h2>
                <p>Your email address has been successfully updated and verified. You can now log in with your new email.</p>
            <?php else: ?>
                <h2>Email Verified!</h2>
                <p>Your email address has been verified successfully. You can now log in to your admin account.</p>
            <?php endif; ?>
            <a href="index.php" class="btn">→ Go to Login</a>
        <?php else: ?>
            <div class="icon-circle err">✕</div>
            <h2>Invalid Link</h2>
            <p>This verification link is invalid or has already been used. Please contact your superadmin for a new link.</p>
            <a href="index.php" class="btn btn-ghost">← Back to Login</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
