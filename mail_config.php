<?php
// ------------------------------------------------------------
// Mail configuration (edit these for your hosting/server)
// ------------------------------------------------------------

// Sender shown in user's inbox
define('MAIL_FROM', 'no-reply@farmsystem.local');
define('MAIL_FROM_NAME', 'FarmSystem');

// ------------------------------------------------------------
// SMTP configuration (Recommended for localhost)
// ------------------------------------------------------------
// If SMTP is enabled, OTP emails will be sent via SMTP instead of PHP mail().
// Example for Gmail:
//   SMTP_HOST=smtp.gmail.com, SMTP_PORT=587, SMTP_SECURE=tls
//   SMTP_USERNAME=your_gmail@gmail.com
//   SMTP_PASSWORD=your_16_digit_app_password
// NOTE: Gmail requires 2FA + App Password.

define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // 'tls' (STARTTLS) or 'ssl'
define('SMTP_USERNAME', 'rakibraz202@gmail.com');
define('SMTP_PASSWORD', 'lkoz dvyy dnis plug');

// Where replies should go (optional)
define('MAIL_REPLY_TO', '');

// OTP settings
define('OTP_LENGTH', 6);
define('OTP_EXPIRE_MINUTES', 5);

// Basic mail() uses server mail configuration.
// SMTP_ENABLED=true করলে otp_helper.php built-in SMTP sender use করবে.
