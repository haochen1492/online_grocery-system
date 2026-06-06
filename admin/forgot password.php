<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/mailer.php'; 

// Already logged in → go to dashboard
if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');

    if (empty($username)) {
        $error = 'Please enter your username.';
    } else {
        $db = getDB();

        // Check if admin exists
        $st = $db->prepare("SELECT admin_id, username FROM admin WHERE username = ? LIMIT 1");
        $st->bind_param("s", $username);
        $st->execute();
        $admin = $st->get_result()->fetch_assoc();

        if ($admin) {
            // Delete any old unused tokens for this user
            $db->query("DELETE FROM password_resets WHERE username = '$username'");

            // Generate a secure random token
            $token      = bin2hex(random_bytes(32)); // 64 character hex string
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // expires in 1 hour

            // Save token to database
            $st2 = $db->prepare("INSERT INTO password_resets (username, token, expires_at)
                                  VALUES (?, ?, ?)");
            $st2->bind_param("sss", $username, $token, $expires_at);
            $st2->execute();

            // Build the reset link
            $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host       = $_SERVER['HTTP_HOST'];
            $reset_link = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/reset password.php?token=' . $token;

            // ── In a real system you would send this by email ──
            // For this project we display it directly (no email server needed)
            $success = $reset_link;

        } else {
            // Don't reveal if username exists or not (security best practice)
            // Show same success message regardless
            $success = 'fake'; // triggers success UI without real token
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — FreshMart Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green:    #1a5c38;
            --green2:   #27764a;
            --green3:   #4caf76;
            --green-bg: #edf7f1;
            --bg:       #f4f6f3;
            --surface:  #ffffff;
            --border:   #e4e8e2;
            --text:     #1a1f1c;
            --text2:    #5a6a5e;
            --text3:    #8a9a8e;
            --red:      #b91c1c;
            --red-bg:   #fef2f2;
            --orange:   #c95f1e;
            --orange-bg:#fef2eb;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Background pattern */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(rgba(26,92,56,.04) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
        }

        .wrap {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        /* Logo */
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 58px; height: 58px;
            background: var(--green);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            margin: 0 auto 12px;
            box-shadow: 0 4px 16px rgba(26,92,56,.3);
        }
        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 800;
            color: var(--text);
        }
        .brand p { font-size: 13px; color: var(--text3); margin-top: 3px; }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }

        .card-icon {
            width: 52px; height: 52px;
            background: var(--orange-bg);
            border: 1.5px solid #f5c9ae;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }

        .card .sub {
            font-size: 13.5px;
            color: var(--text2);
            margin-bottom: 26px;
            line-height: 1.6;
        }

        /* Form */
        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 11.5px; font-weight: 700;
            color: var(--text3);
            text-transform: uppercase; letter-spacing: .7px;
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px; pointer-events: none;
        }
        input {
            width: 100%;
            background: #f8f9f7;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 11px 13px 11px 42px;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 14px; outline: none;
            transition: all .17s;
        }
        input:focus {
            border-color: var(--green2);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(39,118,74,.1);
        }
        input::placeholder { color: #ccc; }

        /* Error */
        .err {
            display: flex; align-items: center; gap: 9px;
            background: var(--red-bg);
            border: 1.5px solid #fca5a5;
            color: var(--red);
            padding: 11px 15px; border-radius: 10px;
            font-size: 13px; margin-bottom: 18px;
        }

        /* Button */
        .btn-submit {
            width: 100%;
            background: var(--green); color: #fff;
            border: none; border-radius: 10px;
            padding: 13px;
            font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 700;
            cursor: pointer; transition: all .17s;
            box-shadow: 0 4px 14px rgba(26,92,56,.26);
        }
        .btn-submit:hover {
            background: var(--green2);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,92,56,.32);
        }

        /* Back link */
        .back-link {
            display: flex; align-items: center; gap: 6px;
            color: var(--text2); font-size: 13px; font-weight: 500;
            text-decoration: none; margin-top: 18px;
            justify-content: center;
            transition: color .15s;
        }
        .back-link:hover { color: var(--green); }

        /* ── SUCCESS STATE ── */
        .success-box {
            text-align: center;
            padding: 8px 0 4px;
        }
        .success-icon {
            width: 64px; height: 64px;
            background: var(--green-bg);
            border: 2px solid var(--green3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin: 0 auto 18px;
        }
        .success-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px; font-weight: 800;
            color: var(--text); margin-bottom: 8px;
        }
        .success-box p {
            font-size: 13.5px; color: var(--text2);
            line-height: 1.6; margin-bottom: 20px;
        }

        /* Token display box */
        .token-box {
            background: var(--green-bg);
            border: 1.5px solid var(--green3);
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 18px;
            text-align: left;
        }
        .token-box .tb-label {
            font-size: 10.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
            color: var(--green); margin-bottom: 8px;
        }
        .token-box .tb-link {
            font-size: 12.5px;
            color: var(--green2);
            word-break: break-all;
            line-height: 1.6;
            font-family: monospace;
        }

        .note-box {
            background: var(--orange-bg);
            border: 1px solid #f5c9ae;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 12.5px;
            color: var(--orange);
            margin-bottom: 18px;
            display: flex; gap: 8px; align-items: flex-start;
        }
        .note-box .ni { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

        .btn-reset {
            display: block; width: 100%;
            background: var(--green); color: #fff;
            border: none; border-radius: 10px;
            padding: 13px; text-align: center;
            font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 700;
            text-decoration: none;
            transition: all .17s;
            box-shadow: 0 4px 14px rgba(26,92,56,.26);
        }
        .btn-reset:hover {
            background: var(--green2);
            transform: translateY(-1px);
        }

        /* Step indicators */
        .steps {
            display: flex; align-items: center; gap: 0;
            margin-bottom: 26px;
        }
        .step {
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; font-weight: 600;
        }
        .step-num {
            width: 24px; height: 24px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800;
        }
        .step.done .step-num { background: var(--green); color: #fff; }
        .step.active .step-num { background: var(--orange); color: #fff; }
        .step.wait .step-num { background: var(--border); color: var(--text3); }
        .step.done .step-label { color: var(--green); }
        .step.active .step-label { color: var(--orange); }
        .step.wait .step-label { color: var(--text3); }
        .step-line {
            flex: 1; height: 2px;
            background: var(--border); margin: 0 8px;
        }
        .step-line.done { background: var(--green); }
    </style>
</head>
<body>

<div class="wrap">
    <!-- Brand -->
    <div class="brand">
        <div class="brand-icon">🛒</div>
        <h1>FreshMart Admin</h1>
        <p>Password Recovery</p>
    </div>

    <div class="card">

        <?php if ($success): ?>
        <!-- ── SUCCESS: Show reset link ── -->
        <div class="success-box">
            <div class="success-icon">✓</div>
            <h3>Reset Link Generated!</h3>
            <p>A password reset link has been created. In production this would be emailed. For now, use the link below:</p>
        </div>

        <?php if ($success !== 'fake'): ?>
        <!-- Show actual reset link (dev/demo mode) -->
        <div class="token-box">
            <div class="tb-label">🔗 Your Password Reset Link</div>
            <div class="tb-link"><?= htmlspecialchars($success) ?></div>
        </div>

        <div class="note-box">
            <span class="ni">⏰</span>
            <div>This link expires in <strong>1 hour</strong>. It can only be used once.</div>
        </div>

        <a href="<?= htmlspecialchars($success) ?>" class="btn-reset">
            → Go to Reset Password Page
        </a>

        <?php else: ?>
        <!-- Username not found but we show generic message (security) -->
        <div class="note-box">
            <span class="ni">ℹ️</span>
            <div>If that username exists, a reset link has been sent. Please check your email or contact your administrator.</div>
        </div>
        <?php endif; ?>

        <a href="index.php" class="back-link">← Back to Login</a>

        <?php else: ?>
        <!-- ── FORM: Enter username ── -->

        <!-- Step indicator -->
        <div class="steps">
            <div class="step active">
                <div class="step-num">1</div>
                <div class="step-label">Enter Username</div>
            </div>
            <div class="step-line"></div>
            <div class="step wait">
                <div class="step-num">2</div>
                <div class="step-label">Reset Password</div>
            </div>
            <div class="step-line"></div>
            <div class="step wait">
                <div class="step-num">3</div>
                <div class="step-label">Done</div>
            </div>
        </div>

        <div class="card-icon">🔑</div>
        <h2>Forgot Password?</h2>
        <p class="sub">Enter your admin username and we'll generate a password reset link for you.</p>

        <?php if ($error): ?>
        <div class="err">⚠ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Admin Username</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username"
                           placeholder="Enter your username"
                           value="<?= sanitize($_POST['username'] ?? '') ?>"
                           required autofocus>
                </div>
            </div>
            <button type="submit" class="btn-submit">Generate Reset Link →</button>
        </form>

        <a href="index.php" class="back-link">← Back to Login</a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
