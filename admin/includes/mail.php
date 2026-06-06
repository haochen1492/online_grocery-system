<?php
// settings 
define('MAIL_HOST',       'smtp.gmail.com');   
define('MAIL_PORT',       587);                
define('MAIL_USERNAME',   'infinitygrocer7@gmail.com');   
define('MAIL_PASSWORD',   'lfxd qida epnm wzxl');    
define('MAIL_FROM_EMAIL', 'infinitygrocer7@gmail.com');   
define('MAIL_FROM_NAME',  'Infinity Grocer Admin');        
define('MAIL_ENCRYPTION', 'tls');             // 

/**
 * Send an email using PHPMailer via SMTP
 *
 * @param string $to_email   recipient email address
 * @param string $to_name    recipient display name
 * @param string $subject    email subject
 * @param string $body_html  HTML content of the email
 * @return array ['success' => bool, 'error' => string]
 */
function sendMail($to_email, $to_name, $subject, $body_html) {

    $phpmailer_path = __DIR__ . '/PHPMailer/';

    if (!file_exists($phpmailer_path . 'PHPMailer.php')) {
        return sendMailFallback($to_email, $to_name, $subject, $body_html);
    }

    require_once $phpmailer_path . 'PHPMailer.php';
    require_once $phpmailer_path . 'SMTP.php';
    require_once $phpmailer_path . 'Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;

        // Sender
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

        // Recipient
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags($body_html); // plain text fallback

        $mail->send();
        return ['success' => true, 'error' => ''];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

function sendMailFallback($to_email, $to_name, $subject, $body_html) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $result = mail($to_email, $subject, $body_html, $headers);
    return [
        'success' => $result,
        'error'   => $result ? '' : 'mail() function failed. Check your server mail config.'
    ];
}

/**
 * Build the HTML email template for password reset
 *
 * @param string $username   admin username
 * @param string $reset_link full URL of the reset page
 * @return string            HTML email body
 */
function buildResetEmailHTML($username, $reset_link) {
    return '<!DOCTYPE html>
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f4f6f3;font-family:Arial,sans-serif">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f3;padding:40px 20px">
    <tr><td align="center">
      <table width="100%" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">

        <!-- HEADER -->
        <tr>
          <td style="background:#1a5c38;padding:32px 40px;text-align:center">
            <div style="font-size:36px;margin-bottom:10px">🛒</div>
            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;font-family:Georgia,serif">
              Infinity Grocer Admin
            </h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,.6);font-size:13px">
              Password Reset Request
            </p>
          </td>
        </tr>

        <!-- BODY -->
        <tr>
          <td style="padding:36px 40px">
            <p style="margin:0 0 8px;font-size:15px;color:#1a1f1c;font-weight:700">
              Hello, ' . htmlspecialchars($username) . ' 👋
            </p>
            <p style="margin:0 0 24px;font-size:14px;color:#5a6a5e;line-height:1.7">
              We received a request to reset the password for your Infinity Grocer Admin account.
              Click the button below to set a new password.
            </p>

            <!-- BUTTON -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td align="center" style="padding:8px 0 28px">
                  <a href="' . $reset_link . '"
                     style="display:inline-block;background:#1a5c38;color:#ffffff;
                            text-decoration:none;font-size:15px;font-weight:700;
                            padding:14px 36px;border-radius:10px;
                            box-shadow:0 4px 14px rgba(26,92,56,.3)">
                    Reset My Password →
                  </a>
                </td>
              </tr>
            </table>

            <!-- LINK TEXT -->
            <div style="background:#f4f6f3;border:1px solid #e4e8e2;border-radius:10px;padding:14px 16px;margin-bottom:24px">
              <p style="margin:0 0 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#8a9a8e">
                Or copy this link into your browser
              </p>
              <p style="margin:0;font-size:12px;color:#27764a;word-break:break-all;font-family:monospace">
                ' . htmlspecialchars($reset_link) . '
              </p>
            </div>

            <!-- WARNING -->
            <div style="background:#fef2eb;border:1px solid #f5c9ae;border-radius:10px;padding:14px 16px;margin-bottom:24px">
              <p style="margin:0;font-size:13px;color:#c95f1e;line-height:1.6">
                ⏰ <strong>This link expires in 1 hour</strong> and can only be used once.<br>
                If you did not request a password reset, please ignore this email.
                Your password will not change.
              </p>
            </div>

            <p style="margin:0;font-size:13px;color:#8a9a8e;line-height:1.6">
              For security, never share this link with anyone.<br>
              — The Infinity Grocer Admin Team
            </p>
          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="background:#f4f6f3;padding:18px 40px;text-align:center;border-top:1px solid #e4e8e2">
            <p style="margin:0;font-size:11px;color:#aaa">
              © ' . date('Y') . ' Infinity Grocer · Student C Admin Module<br>
              This is an automated email, please do not reply.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>

</body>
</html>';
}