-- Add created_at column to users table
ALTER TABLE `users` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Create komisi_master table for Master Owner commission tracking
CREATE TABLE IF NOT EXISTS `komisi_master` (
  `id_komisi` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_master` INT(10) UNSIGNED NOT NULL,
  `tanggal_komisi` DATETIME NOT NULL,
  `periode` VARCHAR(100) NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `catatan` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `id_master` (`id_master`),
  CONSTRAINT `komisi_master_ibfk_1` FOREIGN KEY (`id_master`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
