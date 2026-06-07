<?php
define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',   'infinitygrocer7@gmail.com');
define('MAIL_PASSWORD',   'lfxd qida epnm wzxl');
define('MAIL_FROM_EMAIL', 'infinitygrocer7@gmail.com');
define('MAIL_FROM_NAME',  'Infinity Grocer Admin');
define('MAIL_ENCRYPTION', 'tls');

/**
 * Send email via PHPMailer
 */
function sendMail($to_email, $to_name, $subject, $body_html) {
    $path = __DIR__ . '/PHPMailer/';
    if (!file_exists($path . 'PHPMailer.php')) {
        return ['success' => false, 'error' => 'PHPMailer not found at ' . $path];
    }
    require_once $path . 'PHPMailer.php';
    require_once $path . 'SMTP.php';
    require_once $path . 'Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags($body_html);
        $mail->send();
        return ['success' => true, 'error' => ''];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

/**
 * Build password reset email HTML
 */
function buildResetEmailHTML($username, $reset_link) {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f3;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f3;padding:40px 20px">
<tr><td align="center">
<table width="100%" style="max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
<tr><td style="background:#1a5c38;padding:32px 40px;text-align:center">
  <div style="font-size:36px;margin-bottom:10px">🛒</div>
  <h1 style="margin:0;color:#fff;font-size:22px;font-weight:800;font-family:Georgia,serif">Infinity Grocer Admin</h1>
  <p style="margin:6px 0 0;color:rgba(255,255,255,.6);font-size:13px">Password Reset Request</p>
</td></tr>
<tr><td style="padding:36px 40px">
  <p style="margin:0 0 8px;font-size:15px;color:#1a1f1c;font-weight:700">Hello, ' . htmlspecialchars($username) . ' 👋</p>
  <p style="margin:0 0 24px;font-size:14px;color:#5a6a5e;line-height:1.7">
    We received a request to reset your Infinity Grocer Admin password. Click the button below to set a new password.
  </p>
  <table width="100%" cellpadding="0" cellspacing="0"><tr>
    <td align="center" style="padding:8px 0 28px">
      <a href="' . $reset_link . '" style="display:inline-block;background:#1a5c38;color:#fff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:10px;box-shadow:0 4px 14px rgba(26,92,56,.3)">
        Reset My Password →
      </a>
    </td>
  </tr></table>
  <div style="background:#f4f6f3;border:1px solid #e4e8e2;border-radius:10px;padding:14px 16px;margin-bottom:24px">
    <p style="margin:0 0 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#8a9a8e">Or copy this link</p>
    <p style="margin:0;font-size:12px;color:#27764a;word-break:break-all;font-family:monospace">' . htmlspecialchars($reset_link) . '</p>
  </div>
  <div style="background:#fef2eb;border:1px solid #f5c9ae;border-radius:10px;padding:14px 16px">
    <p style="margin:0;font-size:13px;color:#c95f1e;line-height:1.6">
      ⏰ <strong>This link expires in 1 hour</strong> and can only be used once.<br>
      If you did not request this, ignore this email.
    </p>
  </div>
</td></tr>
<tr><td style="background:#f4f6f3;padding:18px 40px;text-align:center;border-top:1px solid #e4e8e2">
  <p style="margin:0;font-size:11px;color:#aaa">© ' . date('Y') . ' Infinity Grocer · Automated email, do not reply.</p>
</td></tr>
</table></td></tr></table>
</body></html>';
}

/**
 * Build verification email HTML (new account or email change)
 */
function buildVerificationEmailHTML($username, $verify_link, $is_email_change = false) {
    $subject_line = $is_email_change ? 'Email Change Verification' : 'Account Email Verification';
    $msg = $is_email_change
        ? 'A request was made to update your email address. Click below to confirm the change.'
        : 'Your Infinity Grocer Admin account has been created. Please verify your email to activate it.';
    $btn_text = $is_email_change ? '✓ Confirm Email Change →' : '✓ Verify My Email →';

    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f3;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f3;padding:40px 20px">
<tr><td align="center">
<table width="100%" style="max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
<tr><td style="background:#1a5c38;padding:32px 40px;text-align:center">
  <div style="font-size:36px;margin-bottom:10px">🛒</div>
  <h1 style="margin:0;color:#fff;font-size:22px;font-weight:800;font-family:Georgia,serif">Infinity Grocer Admin</h1>
  <p style="margin:6px 0 0;color:rgba(255,255,255,.6);font-size:13px">' . $subject_line . '</p>
</td></tr>
<tr><td style="padding:36px 40px">
  <p style="margin:0 0 8px;font-size:15px;color:#1a1f1c;font-weight:700">Hello, ' . htmlspecialchars($username) . ' 👋</p>
  <p style="margin:0 0 24px;font-size:14px;color:#5a6a5e;line-height:1.7">' . $msg . '</p>
  <table width="100%" cellpadding="0" cellspacing="0"><tr>
    <td align="center" style="padding:8px 0 28px">
      <a href="' . $verify_link . '" style="display:inline-block;background:#1a5c38;color:#fff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:10px;box-shadow:0 4px 14px rgba(26,92,56,.3)">
        ' . $btn_text . '
      </a>
    </td>
  </tr></table>
  <div style="background:#f4f6f3;border:1px solid #e4e8e2;border-radius:10px;padding:14px 16px;margin-bottom:24px">
    <p style="margin:0 0 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#8a9a8e">Or copy this link</p>
    <p style="margin:0;font-size:12px;color:#27764a;word-break:break-all;font-family:monospace">' . htmlspecialchars($verify_link) . '</p>
  </div>
  <div style="background:#fef2eb;border:1px solid #f5c9ae;border-radius:10px;padding:14px 16px">
    <p style="margin:0;font-size:13px;color:#c95f1e;line-height:1.6">
      ⏰ <strong>This link expires in 24 hours.</strong><br>
      If you did not expect this, ignore this email.
    </p>
  </div>
</td></tr>
<tr><td style="background:#f4f6f3;padding:18px 40px;text-align:center;border-top:1px solid #e4e8e2">
  <p style="margin:0;font-size:11px;color:#aaa">© ' . date('Y') . ' Infinity Grocer · Automated email, do not reply.</p>
</td></tr>
</table></td></tr></table>
</body></html>';
}
