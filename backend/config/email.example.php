<?php
/**
 * Copy this file to email.local.php and set real SMTP credentials.
 * This file is safe to commit; email.local.php should stay private.
 */
return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls', // tls or ssl
    'username' => 'your-email@gmail.com',
    'password' => 'your-16-digit-app-password',
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'BatiFlow Pharma',
];
