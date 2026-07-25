-- Export table `admin_module`
-- --------------------------------------------------------
-- Table structure for `admin_module`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_module`;
CREATE TABLE `admin_module` (
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for `admin_module`
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'Dashboard', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('2', '2', '2', 'Investor', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('3', '3', '8', 'Pengaturan', '-1', '-1', '2026-07-23 12:19:52', '2026-07-24 13:57:29');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('4', '1', '3', 'Admin', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('5', '1', '4', 'group', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('6', '2', '4', 'module', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('7', '4', '5', 'Outlet', '-1', '-1', '2026-07-23 15:47:15', '2026-07-24 13:46:45');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('8', '5', '6', 'Omzet', '-1', '-1', '2026-07-23 15:47:15', '2026-07-24 13:46:45');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('9', '6', '7', 'Bagi Hasil', '-1', '-1', '2026-07-23 15:47:15', '2026-07-24 13:46:45');

