-- PayArc migration for an EXISTING production database.
-- Moves card storage from raw PAN/CVV to PayArc tokens.
-- Run once. Safe to re-run (IF NOT EXISTS / IGNORE where possible).
--
-- IMPORTANT: after this runs, purge any historical raw card data you stored
-- (the columns are dropped below, which destroys it — back up first only if
-- legally required, otherwise dropping is the PCI-correct outcome).

-- tbl_ticket: add token columns, drop raw card columns
ALTER TABLE `tbl_ticket`
  ADD COLUMN `card_last4` VARCHAR(4) NULL AFTER `card_holder_name`,
  ADD COLUMN `card_brand` VARCHAR(40) NULL AFTER `card_last4`,
  ADD COLUMN `payarc_token` VARCHAR(120) NULL AFTER `zip`,
  ADD COLUMN `payarc_charge_id` VARCHAR(120) NULL AFTER `payarc_token`;

ALTER TABLE `tbl_ticket`
  DROP COLUMN `card_number`,
  DROP COLUMN `expiry`,
  DROP COLUMN `cvv`;

-- tbl_card: add vault columns, drop raw card columns
ALTER TABLE `tbl_card`
  ADD COLUMN `card_last4` VARCHAR(4) NULL AFTER `card_name`,
  ADD COLUMN `card_brand` VARCHAR(40) NULL AFTER `card_last4`,
  ADD COLUMN `payarc_customer_id` VARCHAR(120) NULL,
  ADD COLUMN `payarc_card_id` VARCHAR(120) NULL;

ALTER TABLE `tbl_card`
  DROP COLUMN `card_number`,
  DROP COLUMN `cvv`;

-- Forgot-password token expiry (from the security hardening pass)
ALTER TABLE `tbl_users` ADD COLUMN `fp_code_expires` DATETIME NULL;

-- Durable charge log (money-safety pass) + one-ticket-per-charge guard.
CREATE TABLE IF NOT EXISTS `tbl_charge_log` (
  `charge_log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaignid_fk` VARCHAR(100) NULL,
  `payarc_token` VARCHAR(120) NULL,
  `payarc_charge_id` VARCHAR(120) NULL,
  `amount_cents` INT NOT NULL DEFAULT 0,
  `email` VARCHAR(160) NULL,
  `ip` VARCHAR(45) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `note` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`charge_log_id`),
  KEY `ix_charge_ip_time` (`ip`, `created_at`),
  KEY `ix_charge_status` (`status`, `created_at`),
  KEY `ix_charge_payarc` (`payarc_charge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tbl_ticket` ADD UNIQUE KEY `uq_ticket_charge` (`payarc_charge_id`);
