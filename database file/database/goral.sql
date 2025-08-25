-- Database Creation
CREATE DATABASE IF NOT EXISTS `thegoral_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `thegoral_db`;

-- Users Table
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL UNIQUE,
    `phone` varchar(20) DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `role` tinyint(1) DEFAULT 3 COMMENT '0=Super Admin, 1=Admin, 2=Manager, 3=User',
    `status` tinyint(1) DEFAULT 1 COMMENT '0=Inactive, 1=Active',
    `profile_image` varchar(255) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Sessions Table
CREATE TABLE `admin_sessions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `session_token` varchar(255) NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `expires_at` timestamp NOT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    KEY `idx_session_token` (`session_token`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Cards Table
CREATE TABLE `payment_cards` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `card_number` varchar(20) NOT NULL,
    `card_holder_name` varchar(255) NOT NULL,
    `expiry_month` varchar(2) NOT NULL,
    `expiry_year` varchar(4) NOT NULL,
    `cvv` varchar(4) NOT NULL,
    `card_type` varchar(50) DEFAULT NULL COMMENT 'Visa, MasterCard, etc.',
    `is_default` tinyint(1) DEFAULT 0,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    KEY `idx_user_id` (`user_id`),
    KEY `idx_card_number` (`card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaigns Table
CREATE TABLE `campaigns` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `goal_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
    `current_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `status` enum('draft','active','completed','cancelled') DEFAULT 'draft',
    `category` varchar(100) DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    KEY `idx_status` (`status`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments/Transactions Table
CREATE TABLE `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `campaign_id` int(11) DEFAULT NULL,
    `payment_card_id` int(11) DEFAULT NULL,
    `transaction_id` varchar(255) UNIQUE DEFAULT NULL,
    `amount` decimal(15,2) NOT NULL,
    `currency` varchar(3) DEFAULT 'USD',
    `payment_method` varchar(50) DEFAULT 'card',
    `payment_status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending',
    `payment_gateway` varchar(50) DEFAULT NULL COMMENT 'Stripe, PayPal, etc.',
    `gateway_transaction_id` varchar(255) DEFAULT NULL,
    `payment_date` timestamp DEFAULT CURRENT_TIMESTAMP,
    `refund_amount` decimal(15,2) DEFAULT 0.00,
    `refund_date` timestamp NULL DEFAULT NULL,
    `refund_reason` text DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`payment_card_id`) REFERENCES `payment_cards`(`id`) ON DELETE SET NULL,
    KEY `idx_user_id` (`user_id`),
    KEY `idx_campaign_id` (`campaign_id`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_transaction_id` (`transaction_id`),
    KEY `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delegate Access Table
CREATE TABLE `delegate_access` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) NOT NULL COMMENT 'Admin who grants access',
    `delegate_id` int(11) NOT NULL COMMENT 'User who receives access',
    `permissions` json DEFAULT NULL COMMENT 'JSON array of permissions',
    `access_level` tinyint(1) DEFAULT 2 COMMENT '1=Full Access, 2=Limited Access',
    `granted_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `expires_at` timestamp NULL DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`delegate_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_delegate_id` (`delegate_id`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs Table
CREATE TABLE `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `table_name` varchar(50) DEFAULT NULL,
    `record_id` int(11) DEFAULT NULL,
    `old_values` json DEFAULT NULL,
    `new_values` json DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings Table
CREATE TABLE `system_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL UNIQUE,
    `setting_value` text DEFAULT NULL,
    `setting_type` varchar(20) DEFAULT 'string' COMMENT 'string, number, boolean, json',
    `description` text DEFAULT NULL,
    `is_public` tinyint(1) DEFAULT 0 COMMENT '0=Admin only, 1=Public',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Admin User
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `role`, `status`) VALUES
('Super', 'Admin', 'admin@thegoral.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 1),
('Admin', 'User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

-- Insert Default System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `is_public`) VALUES
('site_name', 'TheGoral', 'string', 'Website name', 1),
('site_url', 'http://thegoral.com', 'string', 'Website URL', 1),
('admin_email', 'admin@thegoral.com', 'string', 'Admin email address', 0),
('currency', 'USD', 'string', 'Default currency', 1),
('timezone', 'UTC', 'string', 'Default timezone', 1);

-- Create Views for easier data access
CREATE VIEW `user_payment_summary` AS
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    u.email,
    u.phone,
    COUNT(p.id) as total_payments,
    SUM(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE 0 END) as total_paid,
    SUM(CASE WHEN p.payment_status = 'refunded' THEN p.refund_amount ELSE 0 END) as total_refunded,
    MAX(p.payment_date) as last_payment_date
FROM users u
LEFT JOIN payments p ON u.id = p.user_id
WHERE u.role = 3
GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone;

-- Create indexes for better performance
CREATE INDEX idx_payments_user_status ON payments(user_id, payment_status);
CREATE INDEX idx_payments_date_status ON payments(payment_date, payment_status);
CREATE INDEX idx_users_role_status ON users(role, status);