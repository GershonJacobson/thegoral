# The Goral

Weekly "split the pot" raffle site. Visitors buy 1–2 tickets toward the current
weekly pot; when the countdown ends a winner is drawn automatically and takes
home half the pot. Includes a user dashboard, a wallet (saved cards) and an
admin/owner CRM.

## Stack

- PHP 7.4+ / 8.x (no framework), MySQL / MariaDB
- mysqli with prepared statements
- PHPMailer (bundled) for transactional email
- Vanilla JS + jQuery + Bootstrap 5 + SweetAlert2 on the front end

## Local setup

1. Create the database and load the schema:
   ```sh
   mysql -uroot -e "CREATE DATABASE thegoral CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -uroot thegoral < database/thegoral.sql
   ```
2. Copy your local DB credentials into `config/db.local.php` (gitignored). Example:
   ```php
   <?php
   define("GORAL_DB_HOST", "127.0.0.1");
   define("GORAL_DB_USER", "root");
   define("GORAL_DB_PASS", "");
   define("GORAL_DB_NAME", "thegoral");
   define("GORAL_DB_PORT", 3306);
   define("GORAL_SMTP_DISABLED", true); // skip outbound email locally
   ```
3. Run the dev server (the built-in server needs the router because it does not
   read `.htaccess`):
   ```sh
   php -S 127.0.0.1:8090 dev-router.php
   ```
   Open http://127.0.0.1:8090/

## Production deploy (cPanel / Apache)

1. Upload everything to `public_html` (or a subfolder).
2. Create the MySQL database + user, import `database/thegoral.sql`.
3. Edit the production defaults at the top of `config/db.php` (or drop a
   `config/db.local.php` on the server) with the live credentials.
4. Confirm `config/email-config.php` has the correct SMTP settings.
5. Add a cron job to run the raffle engine every minute:
   ```
   * * * * * curl -s https://thegoral.com/functions/cron-job.php > /dev/null 2>&1
   ```
6. Apache uses `.htaccess` for clean URLs and HTTPS redirect — no `dev-router.php`
   needed in production.

## Payments (PayArc)

Cards are tokenized **in the browser** by PayArc's hosted-fields iframe
(`testportal/portal.payarc.net/js/iframeprocess.js`), so raw card numbers never reach
this server (PCI-safe). The server only charges the resulting token.

- `config/payarc.php` — `goral_payarc_charge()` (POST `/v1/charges`, `token_id` at
  TOP LEVEL, amount in **cents**, Bearer auth), `goral_payarc_refund()`, and
  `goral_charge_log_update()`.
- Credentials in `config/db.local.php` (local) or env vars (production):
  `GORAL_PAYARC_ENV` (`sandbox`|`production`), `GORAL_PAYARC_CLIENT_ID` (public, used
  by the iframe), `GORAL_PAYARC_BEARER` (secret, server only). Sandbox keys come from
  `testdashboard.payarc.net → API` (eye icon); production keys from `dashboard.payarc.net → API`.
- Checkout (`index.php`, `campaign.php`) loads the iframe, renders the `data-payarc`
  card fields, tokenizes on Buy, and posts `paymentToken` to `functions/buy-ticket.php`.

### Money-safety guarantees (verified against the PayArc sandbox)
- **Charge → ticket is atomic-or-refunded.** Every attempt writes a `tbl_charge_log`
  row *before* the gateway call. If the charge captures but the ticket can't be
  created (any error, even a fatal), `buy-ticket.php` **auto-refunds** via
  `goral_payarc_refund()` and marks the log `refunded` — a customer is never charged
  for nothing. `ignore_user_abort(true)` keeps the sequence running if the browser closes.
- **Gateway timeout ≠ decline.** A no-response/timeout is logged `unknown` (not
  `declined`) and the customer is told not to retry — so a possible live charge is
  flagged for reconciliation instead of silently lost.
- **No double-charge / double-issue.** PayArc tokens are single-use (verified); plus
  a `UNIQUE(payarc_charge_id)` on `tbl_ticket` makes fulfillment idempotent.
- **Rate-limited:** the public checkout caps charge attempts to 8/IP/minute (429 after).
- **Admin refunds actually move money** — `admin/functions/refund.php` calls the
  gateway (clamped to amount paid) and only records the refund on success.
- Schema stores only the token + `payarc_charge_id` + `card_last4` + `card_brand`
  (raw PAN/CVV columns dropped — see `database/migration-payarc.sql`).

**Deferred (card-first launch):** Apple Pay / Google Pay and the saved-card vault.

## Operations & recovery

- **Cron** (`functions/cron-job.php`) is **not public** — it requires CLI or a secret:
  `* * * * * curl -s "https://thegoral.com/functions/cron-job.php?key=$GORAL_CRON_KEY"`.
  It draws winners, archives ended campaigns, and opens the next weekly. **Add a
  dead-man's-switch** (e.g. Healthchecks.io ping at the end of the run) so a stopped
  cron — which silently halts draws and new weeklies — raises an alert. (Not yet wired.)
- **Secrets live only in `config/db.local.php` (local) / cPanel env vars (prod)** —
  they are gitignored and NOT in the repo. Keep a copy in a password manager:
  `GORAL_DB_*`, `GORAL_PAYARC_BEARER`/`CLIENT_ID`, `GORAL_SMTP_*`/`GORAL_SUPPORT_*`,
  `GORAL_CRON_KEY`, `GORAL_CRYPT_KEY`. **Rotate the SMTP passwords** (they were in git
  history) and the PayArc sandbox Bearer before production.
- **Rebuild from scratch:** clone repo → create DB + run `database/thegoral.sql` →
  drop secrets into `config/db.local.php` (or env) → re-add the cron line. The repo
  alone is NOT enough without the secrets vault above.
- **Reconcile payments:** `tbl_charge_log` is the source of truth. Query for
  `status IN ('unknown','captured','refund_failed')` to find anything needing a human
  (a charge whose fulfillment/refund didn't cleanly resolve).

## Key paths

- `index.php` – homepage / current weekly pot + checkout
- `campaign.php` – drawing page for a campaign (live + archived weeklies)
- `functions/` – AJAX endpoints (login, register, buy-ticket, cron-job, …)
- `admin/` – owner CRM (roles: 1 = owner, 2 = edit delegate, 3 = view-only)
- `config/` – db connection, session, email, helpers
- `database/thegoral.sql` – complete schema

## Upgrading an existing production database

The complete schema is in `database/thegoral.sql`. If you already have a live DB
with data, apply these additions (safe to run once):

```sql
CREATE TABLE IF NOT EXISTS `tbl_remember_token` (
  `remember_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `selector` VARCHAR(60) NOT NULL,
  `validator_hash` CHAR(64) NOT NULL,
  `userid_fk` INT NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`remember_id`),
  UNIQUE KEY `uq_remember_selector` (`selector`),
  KEY `ix_remember_user` (`userid_fk`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tbl_users` ADD COLUMN `fp_code_expires` DATETIME NULL;
```

## Security notes

- Passwords are bcrypt-hashed; "remember me" uses a hashed selector/validator
  token in `tbl_remember_token` (never a raw user id in a cookie). `session_regenerate_id`
  runs on login.
- All user input goes through prepared statements; ticket prices are taken from
  the database server-side, never trusted from the client.
- Admin endpoints enforce role checks; card endpoints enforce ownership.
- Output is HTML-escaped (no stored/reflected XSS into the public pages or the CRM).
- Password-reset tokens are random, single-use, and expire after 1 hour.
- The winner draw is concurrency-safe: an atomic "claim" + a `FOR UPDATE`
  transaction guarantee exactly one winner even if cron runs overlap.
- `.htaccess` blocks `.git/`, `config/`, `database/`, `logs/`, `PHPMailer/`, and
  source/secret files; sets HSTS / X-Frame-Options / nosniff / Referrer-Policy;
  and marks the session cookie HttpOnly + Secure + SameSite=Lax. CSRF is mitigated
  by SameSite cookies + the `X-Requested-With` requirement on every endpoint.

### Before going live — action items
1. **Rotate the SMTP passwords.** The old `noreply@`/`support@` passwords were
   committed to git history; treat them as compromised. Set the new ones via the
   `GORAL_SMTP_*` / `GORAL_SUPPORT_*` environment variables (cPanel → Environment
   Variables), not in code.
2. **On PHP-FPM hosts**, set `session.cookie_secure`, `session.cookie_httponly`,
   `session.cookie_samesite=Lax`, and `display_errors=Off` in the MultiPHP INI
   editor (the `.htaccess` `php_value` lines only apply under mod_php).
3. **Card data / PCI (do this with the PayArc integration):** the app currently
   stores raw card number + CVV in `tbl_card`/`tbl_ticket`. Storing CVV is
   prohibited by PCI-DSS and storing plaintext PANs is high-risk. When wiring
   PayArc, switch the checkout to PayArc's client-side tokenization so card data
   never hits this server, store only the PayArc token + last4 + brand, drop the
   `card_number`/`cvv`/`expiry` columns, and purge any existing rows.
4. Consider per-IP rate limiting on login / forgot-password before launch.
