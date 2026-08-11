-- Dumping structure for table admin_module
CREATE TABLE IF NOT EXISTS `admin_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `m_order` int(11) DEFAULT 1,
  `group_id` int(11) NOT NULL,
  `module` varchar(255) NOT NULL,
  `status` int(11) DEFAULT -1,
  `visible` int(11) DEFAULT -1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_module_name` (`module`) USING BTREE,
  KEY `fk_group_id` (`group_id`) USING BTREE,
  CONSTRAINT `admin_module_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `admin_module_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table admin_module
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Dashboard', -1, -1, '2026-07-23 12:19:52', NULL),
(2, 2, 2, 'Investor', -1, -1, '2026-07-23 12:19:52', NULL),
(4, 1, 3, 'Admin', -1, -1, '2026-07-23 12:19:52', NULL),
(5, 1, 4, 'group', -1, -1, '2026-07-23 12:19:52', NULL),
(6, 2, 4, 'module', -1, -1, '2026-07-23 12:19:52', NULL),
(7, 1, 5, 'Outlet', -1, -1, '2026-07-23 15:47:15', '2026-07-28 01:49:08'),
(19, 1, 12, 'data_master', -1, -1, '2026-07-28 15:00:51', '2026-08-06 06:28:54'),
(32, 2, 19, 'rekening_bank', -1, -1, '2026-07-29 16:31:32', NULL),
(33, 2, 12, 'komisi', -1, -1, '2026-08-06 13:28:54', NULL)
ON DUPLICATE KEY UPDATE `module` = VALUES(`module`), `group_id` = VALUES(`group_id`), `m_order` = VALUES(`m_order`);
