<?php
require_once __DIR__ . '/../config/constants.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Composer autoload for PHPMailer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Send email using PHPMailer (SMTP)
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message HTML message body
 * @param string|null $from Optional sender email (overrides default)
 * @return bool
 */
function sendEmail($to, $subject, $message, $from = null)
{
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found. Please run 'composer install'.");
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom($from ?? FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send invitation email to a new user
 */
function sendInvitationEmail($to, $role, $token)
{
    $registerLink = BASE_URL . "/register.php?token=" . $token;
    $roleName = ucwords(str_replace('_', ' ', $role));
    $subject = "You're Invited: Join PharmaFlow as a $roleName";
    
    $message = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
        <h2 style='color: #4f46e5;'>Welcome to PharmaFlow!</h2>
        <p>Hello,</p>
        <p>You have been invited to join our Pharmacy Management System as a <strong>$roleName</strong>.</p>
        <p>To complete your registration and set up your account, please click the button below:</p>
        <div style='text-align: center; margin: 30px 0;'>
            <a href='$registerLink' style='background-color: #4f46e5; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Accept Invitation</a>
        </div>
        <p style='font-size: 14px; color: #666;'>This link will expire in 48 hours.</p>
        <p style='font-size: 12px; color: #999;'>If you cannot click the button, copy and paste this link into your browser:<br>$registerLink</p>
        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
        <p style='font-size: 12px; color: #999;'>PharmaFlow System &copy; " . date('Y') . "</p>
    </div>
    ";
    
    return sendEmail($to, $subject, $message);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($to, $resetLink)
{
    $subject = "Reset Your PharmaFlow Password";
    $message = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
        <h2 style='color: #4f46e5;'>Password Reset Request</h2>
        <p>Click the link below to reset your password:</p>
        <div style='text-align: center; margin: 30px 0;'>
            <a href='$resetLink' style='background-color: #4f46e5; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
        </div>
        <p>This link expires in 1 hour.</p>
        <p>If you didn't request this, you can safely ignore this email.</p>
        <br>
        <p>PharmaFlow Team</p>
    </div>
    ";
    return sendEmail($to, $subject, $message);
}

/**
 * Send expiry alert email
 */
function sendExpiryAlert($to, $drugs)
{
    $subject = "Alert: Expiring Drugs in Inventory";
    $message = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
        <h2 style='color: #e53e3e;'>Expiring Drugs Alert</h2>
        <p>The following drugs are expiring soon:</p>
        <ul>";
    
    foreach ($drugs as $drug) {
        $message .= "<li><strong>{$drug['name']}</strong> (Batch: {$drug['batch']}) - Expires on " . date('M d, Y', strtotime($drug['expiry_date'])) . " at {$drug['branch_name']}</li>";
    }

    $message .= "</ul>
        <p>Please take necessary actions.</p>
        <br>
        <p>PharmaFlow Team</p>
    </div>
    ";
    return sendEmail($to, $subject, $message);
}

/**
 * Send low stock alert email
 */
function sendLowStockAlert($to, $drugs)
{
    $subject = "Alert: Low Stock in Inventory";
    $message = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
        <h2 style='color: #dd6b20;'>Low Stock Alert</h2>
        <p>The following drugs are running low on stock:</p>
        <ul>";
    
    foreach ($drugs as $drug) {
        $message .= "<li><strong>{$drug['name']}</strong> (Batch: {$drug['batch']}) - Only {$drug['stock']} units left at {$drug['branch_name']}</li>";
    }

    $message .= "</ul>
        <p>Please restock these items soon.</p>
        <br>
        <p>PharmaFlow Team</p>
    </div>
    ";
    return sendEmail($to, $subject, $message);
}
