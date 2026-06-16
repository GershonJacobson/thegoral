<?php
/**
 * Email Configuration
 * SMTP and email settings for The Goral.
 *
 * SECURITY: credentials are read from environment variables (set them in the
 * cPanel "Environment Variables" / a .env loaded outside the web root). The
 * literal fallbacks below are only for local/dev and the previously-committed
 * passwords MUST be rotated — they were exposed in git history.
 */

// SMTP Server Configuration
define('SMTP_HOST', getenv('GORAL_SMTP_HOST') ?: 'relay-hosting.secureserver.net');
define('SMTP_PORT', (int)(getenv('GORAL_SMTP_PORT') ?: 25));
define('SMTP_USERNAME', getenv('GORAL_SMTP_USER') ?: 'noreply@thegoral.com');
define('SMTP_PASSWORD', getenv('GORAL_SMTP_PASS') ?: '');
define('SMTP_AUTH', filter_var(getenv('GORAL_SMTP_AUTH'), FILTER_VALIDATE_BOOLEAN));

// Support mailbox (used by the contact form)
define('SUPPORT_SMTP_USERNAME', getenv('GORAL_SUPPORT_USER') ?: 'support@thegoral.com');
define('SUPPORT_SMTP_PASSWORD', getenv('GORAL_SUPPORT_PASS') ?: '');

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