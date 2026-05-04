<?php

/**
 * Email helper — uses PHPMailer when Composer deps are installed, otherwise PHP mail().
 */

$GLOBALS['_batiflow_email_error'] = '';
$GLOBALS['_batiflow_composer_autoload'] = __DIR__ . '/../../vendor/autoload.php';
$GLOBALS['_batiflow_phpmailer'] = is_file($GLOBALS['_batiflow_composer_autoload']);

if ($GLOBALS['_batiflow_phpmailer']) {
    require_once $GLOBALS['_batiflow_composer_autoload'];
}

$localEmailConfigFile = __DIR__ . '/../config/email.local.php';
$localEmailConfig = [];
if (is_file($localEmailConfigFile)) {
    $loaded = require $localEmailConfigFile;
    if (is_array($loaded)) {
        $localEmailConfig = $loaded;
    }
}

$smtpHost = getenv('BATIFLOW_SMTP_HOST') ?: ($localEmailConfig['host'] ?? 'smtp.gmail.com');
$smtpPort = (int)(getenv('BATIFLOW_SMTP_PORT') ?: ($localEmailConfig['port'] ?? 587));
$smtpEncryption = strtolower((string)(getenv('BATIFLOW_SMTP_ENCRYPTION') ?: ($localEmailConfig['encryption'] ?? 'tls')));
$smtpUser = getenv('BATIFLOW_SMTP_USER') ?: ($localEmailConfig['username'] ?? '');
$smtpPass = getenv('BATIFLOW_SMTP_PASS') ?: ($localEmailConfig['password'] ?? '');
$smtpFrom = getenv('BATIFLOW_SMTP_FROM') ?: ($localEmailConfig['from_email'] ?? ($smtpUser ?: 'noreply@batiflow.com'));
$smtpFromName = getenv('BATIFLOW_SMTP_FROM_NAME') ?: ($localEmailConfig['from_name'] ?? 'BatiFlow Pharma');

$smtpUser = trim((string)$smtpUser);
$smtpFrom = trim((string)$smtpFrom);
$smtpFromName = trim((string)$smtpFromName);
$smtpPassRaw = trim((string)$smtpPass);
// Gmail app passwords are often copied with spaces (e.g., "abcd efgh ..."); normalize them.
$smtpPass = str_replace(' ', '', $smtpPassRaw);

function logEmailError($message)
{
    $logFile = __DIR__ . '/../logs/email.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

/**
 * @param string $to
 * @param string $subject
 * @param string $message HTML body
 * @param string|null $from
 * @return bool
 */
function sendEmail($to, $subject, $message, $from = null)
{
    global $smtpHost, $smtpPort, $smtpEncryption, $smtpUser, $smtpPass, $smtpFrom, $smtpFromName;
    $GLOBALS['_batiflow_email_error'] = '';

    if (
        !empty($GLOBALS['_batiflow_phpmailer']) &&
        class_exists(\PHPMailer\PHPMailer\PHPMailer::class) &&
        !empty($smtpUser) &&
        !empty($smtpPass)
    ) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = $smtpEncryption === 'ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort > 0 ? $smtpPort : 587;
            $mail->setFrom($from ?? $smtpFrom, $smtpFromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            $GLOBALS['_batiflow_email_error'] = 'SMTP send failed: ' . $e->getMessage();
            error_log('PHPMailer: ' . $e->getMessage());
            logEmailError($GLOBALS['_batiflow_email_error']);
            return false;
        }
    }

    if (empty($GLOBALS['_batiflow_phpmailer'])) {
        $GLOBALS['_batiflow_email_error'] = 'PHPMailer is not installed. Run: composer require phpmailer/phpmailer';
        logEmailError($GLOBALS['_batiflow_email_error']);
        return false;
    }
    if (empty($smtpUser) || empty($smtpPass)) {
        $GLOBALS['_batiflow_email_error'] = 'SMTP credentials are missing. Configure backend/config/email.local.php';
        logEmailError($GLOBALS['_batiflow_email_error']);
        return false;
    }

    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: " . ($from ?? $smtpFrom) . "\r\n";
    $ok = @mail($to, $subject, $message, $headers);
    if (!$ok) {
        $GLOBALS['_batiflow_email_error'] = 'PHP mail() failed.';
        logEmailError($GLOBALS['_batiflow_email_error']);
    }
    return $ok;
}

function getLastEmailError()
{
    return (string)($GLOBALS['_batiflow_email_error'] ?? '');
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
