<?php
require_once 'includes/auth.php';
require_once '../db.php';

if (isLoggedIn()) { header('Location: pages/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['admin_id']       = $row['admin_id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_role']     = $row['role'] ?? 'admin';
            redirect('pages/dashboard.php', 'Welcome back, ' . $row['username'] . '! 👋');
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login — FreshMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Lora:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green:    #1e6641;
            --green2:   #2d8653;
            --green3:   #52b788;
            --green-bg: #eaf7ef;
            --bg:       #f5f5f0;
            --border:   #e2ddd6;
            --text:     #1c1c1c;
            --text2:    #555;
            --red:      #c0392b;
            --red-bg:   #fdecea;
        }
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--bg);
        }

        /* ── LEFT ── */
        .left {
            width: 480px; flex-shrink: 0;
            background: var(--green);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 60px 50px;
            position: relative; overflow: hidden;
        }
        .left::before {
            content:'';
            position: absolute; inset:0;
            background:
                radial-gradient(circle at 20% 80%, rgba(82,183,136,.25) 0%, transparent 55%),
                radial-gradient(circle at 80% 15%, rgba(255,255,255,.06) 0%, transparent 50%);
        }
        .left-dots {
            position: absolute; inset:0;
            background-image: radial-gradient(rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 26px 26px;
        }
        .left-content { position: relative; z-index:1; text-align: center; }
        .left-logo {
            width: 76px; height: 76px;
            background: rgba(255,255,255,.15);
            border: 2px solid rgba(255,255,255,.2);
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; margin: 0 auto 22px;
        }
        .left-content h1 {
            font-family: 'Lora', serif;
            font-size: 36px; font-weight: 700;
            color: #fff; line-height: 1.1; margin-bottom: 14px;
        }
        .left-content p { color: rgba(255,255,255,.6); font-size: 14px; line-height: 1.7; max-width: 300px; }
        .features { margin-top: 36px; display: flex; flex-direction: column; gap: 12px; width: 100%; }
        .feat {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,.09);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 11px; padding: 11px 16px;
            color: rgba(255,255,255,.8);
            font-size: 13px; font-weight: 500; text-align: left;
        }
        .feat .fi { font-size: 18px; }

        /* ── RIGHT ── */
        .right {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 40px 60px;
        }
        .login-box { width: 100%; max-width: 380px; }
        .login-box h2 { font-family: 'Lora', serif; font-size: 27px; font-weight: 700; margin-bottom: 5px; }
        .login-box .sub { color: var(--text2); font-size: 14px; margin-bottom: 32px; }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 11.5px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .iico { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none; }
        input {
            width: 100%; background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 10px; padding: 12px 14px 12px 44px;
            color: var(--text); font-family: 'Outfit', sans-serif;
            font-size: 14px; outline: none; transition: all .18s;
        }
        input:focus { border-color: var(--green2); box-shadow: 0 0 0 3px rgba(45,134,83,.1); }
        input::placeholder { color: #ccc; }

        .err {
            display: flex; align-items: center; gap: 9px;
            background: var(--red-bg); border: 1.5px solid #f5c2c0;
            color: var(--red); padding: 11px 15px; border-radius: 10px;
            font-size: 13px; margin-bottom: 20px;
        }

        .btn-login {
            width: 100%; background: var(--green); color: #fff;
            border: none; border-radius: 10px; padding: 13px;
            font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 700;
            cursor: pointer; transition: all .18s; margin-top: 6px;
            box-shadow: 0 4px 14px rgba(30,102,65,.28);
        }
        .btn-login:hover { background: var(--green2); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(30,102,65,.35); }

        .hint {
            margin-top: 20px; padding: 13px 16px;
            background: var(--green-bg); border: 1px solid #a7e3be;
            border-radius: 10px; font-size: 12.5px;
            color: var(--green); text-align: center; line-height: 1.6;
        }
        .hint strong { font-weight: 700; }
        .copy { margin-top: 22px; text-align: center; font-size: 12px; color: #bbb; }

        @media (max-width: 860px) { .left { display: none; } .right { padding: 30px 24px; } }
    </style>
</head>
<body>

<div class="left">
    <div class="left-dots"></div>
    <div class="left-content">
        <div class="left-logo">🛒</div>
        <h1>FreshMart<br>Admin</h1>
        <p>Manage your online grocery store efficiently with a powerful admin dashboard.</p>
        <div class="features">
            <div class="feat"><span class="fi">👑</span> Superadmin can add & manage admins</div>
            <div class="feat"><span class="fi">📦</span> Add categories & products</div>
            <div class="feat"><span class="fi">👥</span> View customer list</div>
            <div class="feat"><span class="fi">🛍️</span> View orders & update status</div>
            <div class="feat"><span class="fi">📈</span> Generate sales reports</div>
        </div>
    </div>
</div>

<div class="right">
    <div class="login-box">
        <h2>Welcome back 👋</h2>
        <p class="sub">Sign in to your admin account to continue</p>

        <?php if ($error): ?>
        <div class="err">⚠ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrap">
                    <span class="iico">👤</span>
                    <input type="text" name="username" placeholder="Enter your username"
                           value="<?= sanitize($_POST['username'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <span class="iico">🔒</span>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn-login">Sign In →</button>
        </form>

        <div class="hint">
            <strong>Superadmin:</strong> superadmin / superadmin123<br>
            <strong>Admin:</strong> admin / admin123
        </div>
        <p class="copy">© <?= date('Y') ?> FreshMart. All rights reserved.</p>
    </div>
</div>

</body>
</html>
