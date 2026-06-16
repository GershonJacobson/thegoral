<?php
/**
 * The Goral - Automatic Raffle Processing
 *
 * Thin wrapper around functions/cron-job.php so hosting crons can hit
 * either URL. Run every minute:
 *   * * * * * curl -s https://thegoral.com/cron/process-raffles.php > /dev/null 2>&1
 * OR
 *   * * * * * php /path/to/cron/process-raffles.php
 */

require(__DIR__ . '/../functions/cron-job.php');

exit(0);
?>
