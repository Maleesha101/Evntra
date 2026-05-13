<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

if (is_file(app_root_path('vendor/autoload.php'))) {
    require_once app_root_path('vendor/autoload.php');
}

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function send_app_mail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $config = app_config();

    try {
        if (class_exists(PHPMailer::class)) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'] ?: 'localhost';
            $mail->Port = $config['smtp_port'];
            $mail->SMTPAuth = $config['smtp_user'] !== '';
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_pass'];
            if ($config['smtp_encryption'] !== '') {
                $mail->SMTPSecure = $config['smtp_encryption'];
            }
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($config['mail_from'], $config['mail_from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);
            return $mail->send();
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $config['mail_from_name'] . ' <' . $config['mail_from'] . '>',
        ];
        return mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    } catch (Exception $exception) {
        error_log('Mail error: ' . $exception->getMessage());
        return false;
    } catch (Throwable $throwable) {
        error_log('Mail error: ' . $throwable->getMessage());
        return false;
    }
}

function send_password_reset_mail(array $user, string $token): bool
{
    $resetLink = app_config()['app_url'] . '/auth/reset-password.php?token=' . urlencode($token);
    $subject = 'Reset your Evntra password';
    $html = '<h2>Password reset request</h2><p>Hi ' . e($user['full_name']) . ',</p><p>Use the link below to reset your password. This link expires in 1 hour.</p><p><a href="' . e($resetLink) . '">' . e($resetLink) . '</a></p>';
    return send_app_mail($user['email'], $subject, $html);
}

function send_registration_confirmation_mail(array $user, array $competition, string $status = 'registered'): bool
{
    $subject = 'Registration confirmed: ' . $competition['title'];
    $html = '<h2>Registration update</h2><p>Hi ' . e($user['full_name']) . ',</p><p>Your registration for <strong>' . e($competition['title']) . '</strong> is now <strong>' . e($status) . '</strong>.</p><p>Venue: ' . e($competition['venue']) . '</p>';
    return send_app_mail($user['email'], $subject, $html);
}

function send_approval_mail(array $organizer, array $competition, bool $approved): bool
{
    $subject = $approved ? 'Competition approved: ' . $competition['title'] : 'Competition rejected: ' . $competition['title'];
    $message = $approved ? 'Your competition has been approved and is now public.' : 'Your competition was not approved. Please review the details and resubmit.';
    $html = '<h2>Competition review</h2><p>Hi ' . e($organizer['full_name']) . ',</p><p>' . e($message) . '</p><p><strong>' . e($competition['title']) . '</strong></p>';
    return send_app_mail($organizer['email'], $subject, $html);
}
