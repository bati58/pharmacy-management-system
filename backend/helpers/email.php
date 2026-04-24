<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Optional: Composer autoload for PHPMailer
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

// ========== SMTP CONFIGURATION – CHANGE THESE ==========
$smtpHost = 'smtp.gmail.com';               // Gmail SMTP server
$smtpUser = 'batijano58@gmail.com';          // Your full Gmail address
$smtpPass = 'your-16-digit-app-password';    // App Password (no spaces)
$smtpFrom = 'batijano58@gmail.com';          // From email (same as user)
$smtpFromName = 'BatiFlow Pharma';           // Sender name
// ======================================================

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
    global $smtpHost, $smtpUser, $smtpPass, $smtpFrom, $smtpFromName;

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found. Please run 'composer install' or use a different email method.");
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        // Enable verbose debug output (logs to PHP error log)
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;    // Set to 2 for detailed output
        $mail->Debugoutput = function ($str, $level) {
            error_log("PHPMailer: $str");
        };

        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or ENCRYPTION_SMTPS for port 465
        $mail->Port       = 587;

        $mail->setFrom($from ?? $smtpFrom, $smtpFromName);
        $mail->addAddress($to);

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
 * Send password reset email
 */
function sendPasswordResetEmail($to, $resetLink)
{
    $subject = "Reset Your BatiFlow Password";
    $message = "
    <html>
    <head><title>Password Reset</title></head>
    <body>
        <h2>Password Reset Request</h2>
        <p>Click the link below to reset your password:</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p>This link expires in 1 hour.</p>
        <p>If you didn't request this, ignore this email.</p>
        <br>
        <p>BatiFlow Pharma Team</p>
    </body>
    </html>
    ";
    return sendEmail($to, $subject, $message);
}

/**
 * Send low stock alert email to manager
 */
function sendLowStockAlert($managerEmail, $lowStockDrugs)
{
    $subject = "Low Stock Alert - BatiFlow Pharma";
    $drugsList = "";
    foreach ($lowStockDrugs as $drug) {
        $drugsList .= "<li>{$drug['name']} - Only {$drug['stock']} units left (Batch: {$drug['batch']})</li>";
    }
    $message = "
    <html>
    <head><title>Low Stock Alert</title></head>
    <body>
        <h2>Low Stock Alert</h2>
        <p>The following drugs are running low on stock:</p>
        <ul>$drugsList</ul>
        <p>Please restock soon.</p>
        <br>
        <p>BatiFlow Pharma System</p>
    </body>
    </html>
    ";
    return sendEmail($managerEmail, $subject, $message);
}

/**
 * Send expiry alert email to manager
 */
function sendExpiryAlert($managerEmail, $expiringDrugs)
{
    $subject = "Expiry Alert - BatiFlow Pharma";
    $drugsList = "";
    foreach ($expiringDrugs as $drug) {
        $drugsList .= "<li>{$drug['name']} expires on " . date('M d, Y', strtotime($drug['expiry_date'])) . " (Batch: {$drug['batch']})</li>";
    }
    $message = "
    <html>
    <head><title>Drug Expiry Alert</title></head>
    <body>
        <h2>Drug Expiry Alert</h2>
        <p>The following drugs are expiring soon or already expired:</p>
        <ul>$drugsList</ul>
        <p>Please take necessary action.</p>
        <br>
        <p>BatiFlow Pharma System</p>
    </body>
    </html>
    ";
    return sendEmail($managerEmail, $subject, $message);
}
