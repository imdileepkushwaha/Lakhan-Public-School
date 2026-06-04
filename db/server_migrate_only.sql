-- ============================================================
-- Lakhan Public School — SERVER MIGRATION ONLY (safe)
-- Use when old database already exists — does NOT delete data
-- Import in phpMyAdmin on live database
-- ============================================================

-- New table: hero slider
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_filename` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure enquiries has phone column (skip if error "duplicate column")
ALTER TABLE `enquiries` ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `email`;

-- New site_settings keys (only if missing — run one by one if duplicate errors)
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_logo', ''),
('salient_features', ''),
('general_rules', '');

-- Optional: full fresh install — use db/lps_db.sql instead (WIPES existing data)
