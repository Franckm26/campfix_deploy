-- Create Event Configuration Tables
-- This script creates the tables needed for Event Setup in the Management page

-- Create event_request_types table
CREATE TABLE IF NOT EXISTS `event_request_types` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `requires_department` BOOLEAN DEFAULT FALSE,
    `approval_roles` JSON,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create event_intended_users table
CREATE TABLE IF NOT EXISTS `event_intended_users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `code` VARCHAR(255) NOT NULL UNIQUE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create event_departments table
CREATE TABLE IF NOT EXISTS `event_departments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add approval_route column to event_requests if it doesn't exist
ALTER TABLE `event_requests` 
ADD COLUMN IF NOT EXISTS `approval_route` JSON AFTER `approval_history`;

-- Insert default request types
INSERT IGNORE INTO `event_request_types` (`name`, `requires_department`, `approval_roles`, `is_active`, `created_at`, `updated_at`) VALUES
('Academic', TRUE, '["program_head","academic_head","building_admin","school_admin"]', TRUE, NOW(), NOW()),
('Non-Academic', FALSE, '["building_admin","school_admin"]', TRUE, NOW(), NOW());

-- Insert default intended users
INSERT IGNORE INTO `event_intended_users` (`name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
('Faculty', 'faculty', TRUE, NOW(), NOW()),
('Tertiary', 'tertiary', TRUE, NOW(), NOW()),
('Senior High School', 'shs', TRUE, NOW(), NOW()),
('Staff', 'staff', TRUE, NOW(), NOW()),
('Maintenance', 'maintenance', TRUE, NOW(), NOW());

-- Insert default departments
INSERT IGNORE INTO `event_departments` (`name`, `is_active`, `created_at`, `updated_at`) VALUES
('GE', TRUE, NOW(), NOW()),
('ICT', TRUE, NOW(), NOW()),
('Business Management', TRUE, NOW(), NOW()),
('THM', TRUE, NOW(), NOW());
