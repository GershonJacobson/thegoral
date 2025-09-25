<?php
// config/session.php (Corrected)
declare(strict_types=1);

// Start the session.
// session_start() must be the very first thing in your PHP document.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>