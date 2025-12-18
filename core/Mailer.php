<?php

require_once __DIR__ . '/mail/Exception.php';
require_once __DIR__ . '/mail/PHPMailer.php';
require_once __DIR__ . '/mail/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    /**
     * Send the verification email and return true on success.
     */
    public static function sendVerificationEmail(
        string $toEmail,
        string $toName,
        string $verifyUrl,
        string $expiresText
    ): bool {
        $configPath = __DIR__ . '/config/mail.php';
        if (!file_exists($configPath)) {
            return false;
        }

        $config = require $configPath;
        if (!is_array($config) || empty($config['username'])) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['host'] ?? '';
            $mail->Port = (int) ($config['port'] ?? 0);
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'] ?? '';
            $mail->CharSet = 'UTF-8';

            if (!empty($config['encryption'])) {
                $mail->SMTPSecure = $config['encryption'];
            }

            $mail->setFrom($config['username'], 'STRIMR Web');
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Verify your email address';
            $mail->isHTML(true);

            $escapedName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
            $escapedUrl = htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8');
            $escapedExpires = htmlspecialchars($expiresText, ENT_QUOTES, 'UTF-8');

            $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify your email address</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#111;">
    <table width="100%" cellspacing="0" cellpadding="0" role="presentation">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table width="600" cellspacing="0" cellpadding="0" role="presentation" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:8px;box-shadow:0 2px 6px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:40px;">
                            <p style="font-size:18px;margin:0 0 16px 0;">Hello {$escapedName},</p>
                            <p style="font-size:15px;line-height:1.6;margin:0 0 24px 0;">
                                Please confirm your STRIMR Web account by clicking the button below. The verification link will expire {$escapedExpires}.
                            </p>
                            <p style="text-align:center;margin:0 0 32px 0;">
                                <a href="{$escapedUrl}" style="display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none;border-radius:6px;">
                                    Verify Email
                                </a>
                            </p>
                            <p style="font-size:14px;line-height:1.6;margin:0 0 8px 0;">
                                If the button does not work, copy and paste this link into your browser:
                            </p>
                            <p style="word-break:break-all;font-size:14px;margin:0 0 24px 0;color:#2563eb;">
                                {$escapedUrl}
                            </p>
                            <p style="font-size:13px;color:#555;margin:0;">
                                If you did not request this verification, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

            $plainBody = "Hello {$toName},\n\n"
                . "Please confirm your STRIMR Web account by visiting the link below. "
                . "The verification link will expire {$expiresText}.\n\n"
                . "{$verifyUrl}\n\n"
                . "If you did not request this verification, you can ignore this message.";

            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody;

            return $mail->send();
        } catch (Exception $exception) {
            return false;
        }
    }
}
