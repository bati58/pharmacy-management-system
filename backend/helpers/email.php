<?php

/**
 * Email helper — uses PHPMailer when Composer deps are installed, otherwise PHP mail().
 */

$GLOBALS['_batiflow_composer_autoload'] = __DIR__ . '/../../vendor/autoload.php';
$GLOBALS['_batiflow_phpmailer'] = is_file($GLOBALS['_batiflow_composer_autoload']);

if ($GLOBALS['_batiflow_phpmailer']) {
    require_once $GLOBALS['_batiflow_composer_autoload'];
}

$smtpHost = 'smtp.gmail.com';
$smtpUser = 'batijano58@gmail.com';
$smtpPass = 'your-16-digit-app-password';
$smtpFrom = 'batijano58@gmail.com';
$smtpFromName = 'BatiFlow Pharma';

/**
 * @param string $to
 * @param string $subject
 * @param string $message HTML body
 * @param string|null $from
 * @return bool
 */
function sendEmail($to, $subject, $message, $from = null)
{
    global $smtpHost, $smtpUser, $smtpPass, $smtpFrom, $smtpFromName;

    if (!empty($GLOBALS['_batiflow_phpmailer']) && class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom($from ?? $smtpFrom, $smtpFromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('PHPMailer: ' . $e->getMessage());
            return false;
        }
    }

    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: " . ($from ?? $smtpFrom) . "\r\n";
    return @mail($to, $subject, $message, $headers);
}

function sendPasswordResetEmail($to, $resetLink)
{
    $subject = "Reset Your BatiFlow Password";
    $message = "
    <html><body>
        <h2>Password Reset Request</h2>
        <p><a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "'>Reset password</a></p>
    </body></html>";
    return sendEmail($to, $subject, $message);
}

function sendLowStockAlert($managerEmail, $lowStockDrugs)
{
    $subject = "Low Stock Alert - BatiFlow Pharma";
    $drugsList = "";
    foreach ($lowStockDrugs as $drug) {
        $drugsList .= "<li>" . htmlspecialchars($drug['name']) . " — " . (int)$drug['stock'] . " left</li>";
    }
    $message = "<html><body><h2>Low Stock</h2><ul>$drugsList</ul></body></html>";
    return sendEmail($managerEmail, $subject, $message);
}

function sendExpiryAlert($managerEmail, $expiringDrugs)
{
    $subject = "Expiry Alert - BatiFlow Pharma";
    $drugsList = "";
    foreach ($expiringDrugs as $drug) {
        $drugsList .= "<li>" . htmlspecialchars($drug['name']) . " — " . htmlspecialchars($drug['expiry_date']) . "</li>";
    }
    $message = "<html><body><h2>Expiry</h2><ul>$drugsList</ul></body></html>";
    return sendEmail($managerEmail, $subject, $message);
}
