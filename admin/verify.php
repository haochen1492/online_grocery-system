<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once DIR . '/PHPMailer/PHPMailer.php';
require_once DIR . '/PHPMailer/SMTP.php';
require_once DIR . '/PHPMailer/Exception.php';

function sendVerificationEmail($email, $username, $token)
{
    $verifyLink =
        "http://localhost/online_grocery-system/admin/verify.php?token=" . $token;

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'infinitygrocer7@gmail.com';
        $mail->Password   = 'lfxd qida epnm wzxl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(
            'infinitygrocer7@gmail.com',
            'Infinity Grocer'
        );

        $mail->addAddress($email, $username);

        $mail->isHTML(true);

        $mail->Subject = 'Verify Your Email Address';

        $mail->Body = "
        <h2>Email Verification</h2>

        <p>Hello {$username},</p>

        <p>Please verify your email address by clicking the button below.</p>

        <p>
            <a href='{$verifyLink}'
               style='background:#1e6641;
                      color:white;
                      padding:10px 20px;
                      text-decoration:none;
                      border-radius:5px;'>
               Verify Email
            </a>
        </p>

        <p>If you did not request this, please ignore this email.</p>
        ";

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}
