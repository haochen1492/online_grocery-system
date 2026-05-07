<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) { header('Location: pages/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db  = getDB();
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']       = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('pages/dashboard.php', 'Welcome back, ' . $admin['username'] . '!');
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — FreshMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green:  #2d6a4f;
            --green2: #40916c;
            --green3: #74c69d;
            --bg:     #f0ede6;
            --bg2:    #ffffff;
            --border: #ddd8cf;
            --text:   #1a1a1a;
            --text2:  #666;
            --red:    #c1121f;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--bg);
        }

        /* LEFT PANEL */
        .left-panel {
            flex: 1;
            background: var(--green);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 70%, rgba(116,198,157,0.2) 0%, transparent 60%),
                        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
        }
        .left-panel .dots {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .left-content { position: relative; z-index:1; text-align: center; }
        .left-logo {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            margin: 0 auto 24px;
        }
        .left-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 40px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 14px;
            line-height: 1.1;
        }
        .left-content p {
            color: rgba(255,255,255,0.65);
            font-size: 15px;
            line-height: 1.7;
            max-width: 320px;
        }
        .features { margin-top: 40px; display: flex; flex-direction: column; gap: 14px; }
        .feature-item {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 12px 18px;
            text-align: left;
            color: rgba(255,255,255,0.85);
            font-size: 13px; font-weight: 500;
        }
        .feature-item span { font-size: 20px; }

        /* RIGHT PANEL */
        .right-panel {
            width: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 50px;
            background: var(--bg);
        }
        .login-box { width: 100%; }

        .login-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }
        .login-box .sub {
            color: var(--text2);
            font-size: 14px;
            margin-bottom: 32px;
        }

        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 12px; font-weight: 700;
            color: var(--text2);
            text-transform: uppercase; letter-spacing: 0.8px;
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px; pointer-events: none;
        }
        input {
            width: 100%;
            background: var(--bg2);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px 12px 44px;
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px; outline: none;
            transition: all 0.18s;
        }
        input:focus {
            border-color: var(--green2);
            box-shadow: 0 0 0 3px rgba(64,145,108,0.12);
            background: #fff;
        }
        input::placeholder { color: #bbb; }

        .error-msg {
            background: #fff0f0;
            border: 1.5px solid #ffc5c5;
            color: var(--red);
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        .btn-login {
            width: 100%;
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px; font-weight: 700;
            cursor: pointer;
            transition: all 0.18s;
            letter-spacing: 0.3px;
            margin-top: 6px;
            box-shadow: 0 4px 14px rgba(45,106,79,0.3);
        }
        .btn-login:hover {
            background: var(--green2);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(45,106,79,0.38);
        }

        .hint {
            margin-top: 20px;
            padding: 13px 16px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            font-size: 12px;
            color: var(--green);
            text-align: center;
        }
        .hint strong { font-weight: 700; }

        .footer-note {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
        }

        @media (max-width: 900px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; }
        }
    </style>
</head>
<body>

<!-- LEFT -->
<div class="left-panel">
    <div class="dots"></div>
    <div class="left-content">
        <div class="left-logo">🛒</div>
        <h1>FreshMart<br>Admin</h1>
        <p>Manage your online grocery store with a powerful and easy-to-use dashboard.</p>
        <div class="features">
            <div class="feature-item"><span>📦</span> Products & Categories</div>
            <div class="feature-item"><span>👥</span> Customer Management</div>
            <div class="feature-item"><span>🛍️</span> Orders & Payments</div>
            <div class="feature-item"><span>📊</span> Real-time Dashboard</div>
        </div>
    </div>
</div>

<!-- RIGHT -->
<div class="right-panel">
    <div class="login-box">
        <h2>Welcome back</h2>
        <p class="sub">Sign in to your admin account to continue</p>

        <?php if ($error): ?>
        <div class="error-msg">⚠ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username" placeholder="Enter username"
                           value="<?= sanitize($_POST['username'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn-login">Sign In →</button>
        </form>

        <div class="hint">
            Default credentials: <strong>admin</strong> / <strong>admin123</strong>
        </div>
        <p class="footer-note">© <?= date('Y') ?> FreshMart. All rights reserved.</p>
    </div>
</div>

</body>
</html>
