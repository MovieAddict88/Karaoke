-- VPN Management Panel Database Schema

CREATE DATABASE IF NOT EXISTS `vpn_panel` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `vpn_panel`;

-- Users Table (Admin, Reseller, Client)
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100),
  `role` ENUM('admin', 'reseller', 'client') DEFAULT 'client',
  `status` ENUM('active', 'suspended', 'pending') DEFAULT 'pending',
  `reseller_id` INT UNSIGNED NULL,
  `balance` DECIMAL(10,2) DEFAULT 0.00,
  `last_login` DATETIME NULL,
  `login_attempts` INT DEFAULT 0,
  `lockout_until` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`),
  INDEX `idx_reseller_id` (`reseller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server Protocols
CREATE TABLE `protocols` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `description` TEXT,
  `icon` VARCHAR(50),
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Servers Table
CREATE TABLE `servers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `hostname` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `port` INT UNSIGNED NOT NULL,
  `protocol_id` INT UNSIGNED NOT NULL,
  `country` VARCHAR(50),
  `city` VARCHAR(50),
  `flag_icon` VARCHAR(100),
  `max_connections` INT DEFAULT 100,
  `current_connections` INT DEFAULT 0,
  `bandwidth_limit` BIGINT UNSIGNED DEFAULT 0,
  `bandwidth_used` BIGINT UNSIGNED DEFAULT 0,
  `config_file` TEXT,
  `config_file_path` VARCHAR(255),
  `username` VARCHAR(100),
  `password` VARCHAR(255),
  `status` ENUM('online', 'offline', 'maintenance') DEFAULT 'online',
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`protocol_id`) REFERENCES `protocols`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_protocol` (`protocol_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Connections Log
CREATE TABLE `connections` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `server_id` INT UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45),
  `connected_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `disconnected_at` TIMESTAMP NULL,
  `duration` INT UNSIGNED DEFAULT 0,
  `bytes_sent` BIGINT UNSIGNED DEFAULT 0,
  `bytes_received` BIGINT UNSIGNED DEFAULT 0,
  `status` ENUM('connected', 'disconnected') DEFAULT 'connected',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_server` (`server_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices
CREATE TABLE `invoices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) UNIQUE NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `tax` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `description` TEXT,
  `due_date` DATE,
  `status` ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice Items
CREATE TABLE `invoice_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT UNSIGNED NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `quantity` INT DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` VARCHAR(100) UNIQUE NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `invoice_id` INT UNSIGNED NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `payment_method` VARCHAR(50),
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reseller Credits
CREATE TABLE `reseller_credits` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reseller_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `type` ENUM('credit', 'debit') NOT NULL,
  `description` TEXT,
  `balance_after` DECIMAL(10,2) NOT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reseller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_reseller` (`reseller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `setting_type` VARCHAR(20) DEFAULT 'string',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Protocols
INSERT INTO `protocols` (`name`, `code`, `description`, `icon`) VALUES
('OpenVPN', 'ovpn', 'OpenVPN protocol for secure VPN connections', 'fa-shield'),
('SSH Tunnel', 'ssh', 'SSH tunneling for secure connections', 'fa-terminal'),
('WireGuard', 'wireguard', 'Modern, fast, and secure VPN protocol', 'fa-bolt'),
('IKEv2/IPSec', 'ikev2', 'Internet Key Exchange version 2', 'fa-key'),
('SSTP', 'sstp', 'Secure Socket Tunneling Protocol', 'fa-lock');

-- Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'VPN Management Panel', 'string'),
('site_email', 'admin@vpnpanel.com', 'string'),
('reseller_commission', '10', 'number'),
('allow_registration', '1', 'boolean'),
('maintenance_mode', '0', 'boolean'),
('currency', 'USD', 'string'),
('currency_symbol', '$', 'string');
