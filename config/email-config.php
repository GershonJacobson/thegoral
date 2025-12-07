<?php
/**
 * Email Configuration
 * SMTP and email settings for The Goral
 */

// SMTP Server Configuration
define('SMTP_HOST', 'relay-hosting.secureserver.net');
define('SMTP_PORT', 25);
define('SMTP_USERNAME', 'noreply@thegoral.com');
define('SMTP_PASSWORD', '***REMOVED***'); // Consider moving to environment variable
define('SMTP_AUTH', false);

// Email Settings
define('EMAIL_FROM_ADDRESS', 'noreply@thegoral.com');
define('EMAIL_FROM_NAME', 'The Goral');
define('EMAIL_REPLY_TO', 'support@thegoral.com');

// Email Templates
define('EMAIL_TEMPLATE_PURCHASE', __DIR__ . '/../purchase-email-template.html');
define('EMAIL_TEMPLATE_WINNER', __DIR__ . '/../winner-email-template.html');

// Email subject lines
define('EMAIL_SUBJECT_PURCHASE', 'Ticket Confirmation - The Goral');
define('EMAIL_SUBJECT_WINNER', 'You Won! - The Goral Raffle');
?>