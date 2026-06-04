<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Already logged in → go to dashboard
if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit;
}

$token   = sanitize($_GET['token'] ?? '');
$error   = '';
$success = false;
$admin   = null;

// ── Validate token ──
if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    $db = getDB();

    // Look up token in database
    $st = $db->prepare("
        SELECT pr.*, a.admin_id
        FROM password_resets pr
        JOIN admin a ON pr.username = a.username
        WHERE pr.token = ?
          AND pr.used = 0
          AND pr.expires_at > NOW()
        LIMIT 1
    ");
    $st->bind_param("s", $token);
    $st->execute();
    $reset = $st->get_result()->fetch_assoc();

    if (!$reset) {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    } else {
        $admin = $reset;
    }
}

// ── Handle password reset form submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin) {
    $new_password     = $_POST['new_password']     ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($new_password)) {
        $error = 'Please enter a new password.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match. Please try again.';
    } else {
        // Hash the new password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update admin password
        $st2 = $db->prepare("UPDATE admin SET password = ? WHERE username = ?");
        $st2->bind_param("ss", $hashed, $admin['username']);
        $st2->execute();

        // Mark token as used so it cannot be reused
        $db->query("UPDATE password_resets SET used = 1 WHERE token = '$token'");

        $success = true;
    }
}

// Calculate time remaining on token
$time_remaining = '';
if ($admin && !$success) {
    $expires = strtotime($admin['expires_at']);
    $diff    = $expires - time();
    if ($diff > 0) {
        $mins = floor($diff / 60);
        $time_remaining = $mins > 0 ? "$mins minutes" : "less than a minute";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — FreshMart Admin</title>
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
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(rgba(26,92,56,.04) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
        }
        .wrap {
            width: 100%; max-width: 440px;
            position: relative; z-index: 1;
        }
        .brand {
            text-align: center; margin-bottom: 28px;
        }
        .brand-icon {
            width: 58px; height: 58px;
            background: var(--green);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; margin: 0 auto 12px;
            box-shadow: 0 4px 16px rgba(26,92,56,.3);
        }
        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 800; color: var(--text);
        }
        .brand p { font-size: 13px; color: var(--text3); margin-top: 3px; }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px; padding: 32px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }
        .card-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 16px;
        }
        .card-icon.green { background: var(--green-bg); border: 1.5px solid var(--green3); }
        .card-icon.orange { background: var(--orange-bg); border: 1.5px solid #f5c9ae; }
        .card-icon.red { background: var(--red-bg); border: 1.5px solid #fca5a5; }

        .card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 800; margin-bottom: 6px;
        }
        .card .sub {
            font-size: 13.5px; color: var(--text2);
            margin-bottom: 26px; line-height: 1.6;
        }

        /* Steps */
        .steps {
            display: flex; align-items: center;
            margin-bottom: 26px;
        }
        .step { display: flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 600; }
        .step-num { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; }
        .step.done .step-num  { background: var(--green); color: #fff; }
        .step.active .step-num{ background: var(--orange); color: #fff; }
        .step.wait .step-num  { background: var(--border); color: var(--text3); }
        .step.done .step-label { color: var(--green); }
        .step.active .step-label { color: var(--orange); }
        .step.wait .step-label { color: var(--text3); }
        .step-line { flex:1; height:2px; background:var(--border); margin:0 8px; }
        .step-line.done { background: var(--green); }

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
            font-size: 16px; pointer-events: none; z-index: 1;
        }
        .eye-btn {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; font-size: 16px;
            color: var(--text3); padding: 4px;
            transition: color .15s;
        }
        .eye-btn:hover { color: var(--green); }
        input {
            width: 100%;
            background: #f8f9f7;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 11px 42px 11px 42px;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 14px; outline: none; transition: all .17s;
        }
        input:focus {
            border-color: var(--green2); background: #fff;
            box-shadow: 0 0 0 3px rgba(39,118,74,.1);
        }
        input::placeholder { color: #ccc; }

        /* Password strength */
        .strength-bar {
            height: 4px; background: var(--border);
            border-radius: 10px; margin-top: 8px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%; border-radius: 10px;
            transition: width .3s, background .3s;
            width: 0;
        }
        .strength-text {
            font-size: 11px; margin-top: 4px;
            font-weight: 600;
        }

        /* Match indicator */
        .match-msg {
            font-size: 12px; font-weight: 600;
            margin-top: 6px;
            display: none;
        }
        .match-msg.show { display: block; }
        .match-msg.ok  { color: var(--green); }
        .match-msg.bad { color: var(--red); }

        /* Messages */
        .err {
            display: flex; align-items: center; gap: 9px;
            background: var(--red-bg); border: 1.5px solid #fca5a5;
            color: var(--red); padding: 11px 15px;
            border-radius: 10px; font-size: 13px; margin-bottom: 18px;
        }
        .info-box {
            background: var(--orange-bg); border: 1px solid #f5c9ae;
            border-radius: 10px; padding: 12px 15px;
            font-size: 12.5px; color: var(--orange);
            margin-bottom: 20px;
            display: flex; gap: 8px; align-items: center;
        }

        /* User badge */
        .user-badge {
            display: flex; align-items: center; gap: 11px;
            background: var(--green-bg); border: 1px solid var(--green3);
            border-radius: 10px; padding: 11px 14px;
            margin-bottom: 20px;
        }
        .user-av {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--green); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 15px; flex-shrink: 0;
        }
        .user-name { font-weight: 700; font-size: 14px; color: var(--green); }
        .user-sub  { font-size: 11.5px; color: var(--green2); margin-top: 1px; }

        /* Buttons */
        .btn-submit {
            width: 100%; background: var(--green); color: #fff;
            border: none; border-radius: 10px; padding: 13px;
            font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 700;
            cursor: pointer; transition: all .17s;
            box-shadow: 0 4px 14px rgba(26,92,56,.26);
        }
        .btn-submit:hover { background: var(--green2); transform: translateY(-1px); }
        .btn-submit:disabled {
            background: #ccc; box-shadow: none;
            cursor: not-allowed; transform: none;
        }
        .back-link {
            display: flex; align-items: center; gap: 6px;
            color: var(--text2); font-size: 13px; font-weight: 500;
            text-decoration: none; margin-top: 18px;
            justify-content: center; transition: color .15s;
        }
        .back-link:hover { color: var(--green); }

        /* Success */
        .success-box { text-align: center; padding: 8px 0 4px; }
        .success-icon {
            width: 72px; height: 72px;
            background: var(--green-bg); border: 2px solid var(--green3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; margin: 0 auto 20px;
            animation: popIn .4s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .success-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 800; margin-bottom: 8px;
        }
        .success-box p {
            font-size: 13.5px; color: var(--text2);
            line-height: 1.7; margin-bottom: 22px;
        }
        .btn-login {
            display: block; width: 100%;
            background: var(--green); color: #fff;
            border-radius: 10px; padding: 13px;
            text-align: center; font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 700; text-decoration: none;
            transition: all .17s;
            box-shadow: 0 4px 14px rgba(26,92,56,.26);
        }
        .btn-login:hover { background: var(--green2); transform: translateY(-1px); }

        /* Invalid token */
        .invalid-box { text-align: center; padding: 8px 0 4px; }
        .invalid-icon {
            width: 64px; height: 64px;
            background: var(--red-bg); border: 2px solid #fca5a5;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; margin: 0 auto 18px;
        }
        .invalid-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px; font-weight: 800; margin-bottom: 8px; color: var(--red);
        }
        .invalid-box p { font-size: 13.5px; color: var(--text2); line-height: 1.6; margin-bottom: 22px; }
        .btn-try {
            display: block; width: 100%;
            background: var(--surface); color: var(--text);
            border: 1.5px solid var(--border);
            border-radius: 10px; padding: 13px;
            text-align: center; font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 700; text-decoration: none;
            transition: all .17s; margin-bottom: 10px;
        }
        .btn-try:hover { background: var(--green-bg); border-color: var(--green3); color: var(--green); }
    </style>
</head>
<body>

<div class="wrap">
    <div class="brand">
        <div class="brand-icon">🛒</div>
        <h1>FreshMart Admin</h1>
        <p>Password Recovery</p>
    </div>

    <div class="card">

        <?php if ($success): ?>
        <!-- ── PASSWORD CHANGED SUCCESSFULLY ── -->
        <div class="success-box">
            <div class="success-icon">✓</div>
            <h3>Password Changed!</h3>
            <p>Your password has been updated successfully. You can now log in with your new password.</p>
            <a href="index.php" class="btn-login">→ Go to Login</a>
        </div>

        <?php elseif ($error && !$admin): ?>
        <!-- ── INVALID / EXPIRED TOKEN ── -->
        <div class="invalid-box">
            <div class="invalid-icon">✕</div>
            <h3>Link Invalid or Expired</h3>
            <p><?= $error ?></p>
            <a href="forgot_password.php" class="btn-try">← Request New Reset Link</a>
            <a href="index.php" class="back-link">Back to Login</a>
        </div>

        <?php else: ?>
        <!-- ── RESET PASSWORD FORM ── -->

        <!-- Step indicator -->
        <div class="steps">
            <div class="step done">
                <div class="step-num">✓</div>
                <div class="step-label">Username</div>
            </div>
            <div class="step-line done"></div>
            <div class="step active">
                <div class="step-num">2</div>
                <div class="step-label">New Password</div>
            </div>
            <div class="step-line"></div>
            <div class="step wait">
                <div class="step-num">3</div>
                <div class="step-label">Done</div>
            </div>
        </div>

        <div class="card-icon green">🔒</div>
        <h2>Set New Password</h2>
        <p class="sub">Choose a strong new password for your admin account.</p>

        <!-- Show who is resetting -->
        <div class="user-badge">
            <div class="user-av"><?= strtoupper(substr($admin['username'], 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= sanitize($admin['username']) ?></div>
                <div class="user-sub">Admin Account</div>
            </div>
        </div>

        <?php if ($time_remaining): ?>
        <div class="info-box">
            <span>⏰</span>
            Link expires in <strong><?= $time_remaining ?></strong>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="err">⚠ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" id="resetForm">
            <div class="form-group">
                <label>New Password *</label>
                <div class="input-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="new_password" id="newPass"
                           placeholder="At least 6 characters"
                           oninput="checkStrength(this.value); checkMatch()"
                           required>
                    <button type="button" class="eye-btn" onclick="togglePass('newPass', this)">👁</button>
                </div>
                <!-- Password strength bar -->
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <div class="strength-text" id="strengthText" style="color:var(--text3)"></div>
            </div>

            <div class="form-group">
                <label>Confirm New Password *</label>
                <div class="input-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="confirm_password" id="confirmPass"
                           placeholder="Re-enter new password"
                           oninput="checkMatch()"
                           required>
                    <button type="button" class="eye-btn" onclick="togglePass('confirmPass', this)">👁</button>
                </div>
                <div class="match-msg" id="matchMsg"></div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                Change Password →
            </button>
        </form>

        <a href="index.php" class="back-link">← Back to Login</a>
        <?php endif; ?>

    </div>
</div>

<script>
// ── Toggle password visibility ──
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}

// ── Password strength checker ──
function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    if (!val) { fill.style.width = '0'; text.textContent = ''; return; }

    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;    // has uppercase
    if (/[0-9]/.test(val)) score++;    // has number
    if (/[^A-Za-z0-9]/.test(val)) score++; // has special char

    const levels = [
        { pct: '20%', color: '#ef4444', label: '🔴 Very Weak' },
        { pct: '40%', color: '#f97316', label: '🟠 Weak' },
        { pct: '60%', color: '#eab308', label: '🟡 Fair' },
        { pct: '80%', color: '#22c55e', label: '🟢 Strong' },
        { pct: '100%',color: '#16a34a', label: '💪 Very Strong' },
    ];
    const lv = levels[score - 1] || levels[0];
    fill.style.width    = lv.pct;
    fill.style.background = lv.color;
    text.textContent    = lv.label;
    text.style.color    = lv.color;
}

// ── Password match checker ──
function checkMatch() {
    const p1  = document.getElementById('newPass').value;
    const p2  = document.getElementById('confirmPass').value;
    const msg = document.getElementById('matchMsg');
    const btn = document.getElementById('submitBtn');

    if (!p2) { msg.className = 'match-msg'; btn.disabled = false; return; }

    if (p1 === p2) {
        msg.textContent = '✓ Passwords match';
        msg.className   = 'match-msg show ok';
        btn.disabled    = false;
    } else {
        msg.textContent = '✕ Passwords do not match';
        msg.className   = 'match-msg show bad';
        btn.disabled    = true;
    }
}
</script>

</body>
</html>
