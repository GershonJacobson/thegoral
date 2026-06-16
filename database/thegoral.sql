-- The Goral - complete database schema
-- Derived from every query in the codebase (June 2026 launch-fix pass).
-- The old "thegoral (1).sql" dump in the repo root is truncated and must
-- not be used: it is missing most ticket/user columns and entire tables.
--
-- Import: create a database named `thegoral`, then run this file.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email_address` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) NOT NULL DEFAULT '',
  `password` VARCHAR(255) NOT NULL,
  `confirmation_code` VARCHAR(120) NOT NULL DEFAULT '',
  `fp_code` VARCHAR(120) NOT NULL DEFAULT '',
  `fp_code_expires` DATETIME NULL,
  `active` TINYINT NOT NULL DEFAULT 1,
  `admin` TINYINT NOT NULL DEFAULT 0,
  `delegate_date` DATETIME NULL,
  `date_joined` DATETIME NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_email` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS `tbl_campaign` (
  `campaign_id` VARCHAR(100) NOT NULL,
  `campaign_name` VARCHAR(100) NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `date_added` DATETIME NULL,
  `added_by` INT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'open',
  `category` VARCHAR(30) NOT NULL DEFAULT '',
  `first_name` VARCHAR(30) NOT NULL DEFAULT '',
  `last_name` VARCHAR(30) NOT NULL DEFAULT '',
  `email_address` VARCHAR(50) NOT NULL DEFAULT '',
  `phone` VARCHAR(40) NOT NULL DEFAULT '',
  `page_url` VARCHAR(80) NOT NULL DEFAULT '',
  `public` INT NOT NULL DEFAULT 1,
  `keep_show` INT NOT NULL DEFAULT 0,
  `weekly_no` INT NULL,
  PRIMARY KEY (`campaign_id`),
  KEY `ix_campaign_page` (`page_url`),
  KEY `ix_campaign_status` (`status`, `category`, `end_date`),
  KEY `ix_campaign_owner` (`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket` (
  `ticket_id` INT NOT NULL AUTO_INCREMENT,
  `ticket_no` INT NULL,
  `campaignid_fk` VARCHAR(100) NOT NULL,
  `first_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `email` VARCHAR(160) NULL,
  `phone` VARCHAR(40) NULL,
  `card_holder_name` VARCHAR(160) NULL,
  `card_last4` VARCHAR(4) NULL,
  `card_brand` VARCHAR(40) NULL,
  `zip` VARCHAR(20) NULL,
  `payarc_token` VARCHAR(120) NULL,
  `payarc_charge_id` VARCHAR(120) NULL,
  `total_ticket` INT NOT NULL DEFAULT 1,
  `total_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `purchased_by` INT NOT NULL DEFAULT 0,
  `purchased_date` DATETIME NULL,
  `payment_method` VARCHAR(80) NULL,
  `payment_status` TINYINT NOT NULL DEFAULT 1,
  `win` CHAR(1) NOT NULL DEFAULT 'N',
  `win_ticket_id` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`ticket_id`),
  UNIQUE KEY `uq_ticket_campaign_no` (`campaignid_fk`, `ticket_no`),
  UNIQUE KEY `uq_ticket_charge` (`payarc_charge_id`),
  KEY `ix_ticket_campaign_win` (`campaignid_fk`, `win`),
  KEY `ix_ticket_purchaser` (`purchased_by`, `purchased_date`),
  KEY `ix_ticket_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket_price` (
  `ticket_price_id` INT NOT NULL AUTO_INCREMENT,
  `campaignid_fk` VARCHAR(100) NOT NULL,
  `1ticket_price` DECIMAL(12,2) NOT NULL DEFAULT 2.00,
  `2ticket_price` DECIMAL(12,2) NOT NULL DEFAULT 3.00,
  PRIMARY KEY (`ticket_price_id`),
  UNIQUE KEY `uq_price_campaign` (`campaignid_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saved payment methods. We store ONLY PayArc vault references + display info,
-- never the raw card number/CVV.
CREATE TABLE IF NOT EXISTS `tbl_card` (
  `card_id` INT NOT NULL AUTO_INCREMENT,
  `card_name` VARCHAR(160) NOT NULL DEFAULT '',
  `card_last4` VARCHAR(4) NULL,
  `card_brand` VARCHAR(40) NULL,
  `expired` VARCHAR(20) NOT NULL DEFAULT '',
  `zip` VARCHAR(20) NOT NULL DEFAULT '',
  `payarc_customer_id` VARCHAR(120) NULL,
  `payarc_card_id` VARCHAR(120) NULL,
  `email_address` VARCHAR(160) NULL,
  `userid_fk` INT NOT NULL,
  PRIMARY KEY (`card_id`),
  KEY `ix_card_user` (`userid_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Durable record of every charge ATTEMPT, written before the gateway call and
-- updated after. Guarantees a reconcilable record even if fulfillment (ticket
-- insert) fails or the gateway times out — so a customer can never be charged
-- with zero trace. Also used for per-IP rate limiting on the public checkout.
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

CREATE TABLE IF NOT EXISTS `tbl_support` (
  `support_id` INT NOT NULL AUTO_INCREMENT,
  `ticket_no` VARCHAR(40) NULL,
  `full_name` VARCHAR(180) NULL,
  `phone` VARCHAR(40) NULL,
  `email` VARCHAR(160) NULL,
  `message` TEXT NULL,
  `date_added` DATETIME NULL,
  PRIMARY KEY (`support_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_refund` (
  `refund_id` INT NOT NULL AUTO_INCREMENT,
  `ticketid_fk` INT NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `refund_date` DATETIME NULL,
  PRIMARY KEY (`refund_id`),
  KEY `ix_refund_ticket` (`ticketid_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_payment` (
  `payment_id` INT NOT NULL AUTO_INCREMENT,
  `campaignid_fk` VARCHAR(100) NOT NULL,
  `payment_option` VARCHAR(80) NULL,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`payment_id`),
  KEY `ix_payment_campaign` (`campaignid_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
