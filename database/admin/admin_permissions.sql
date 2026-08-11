-- Dumping structure for table admin_permissions
CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `desc` text DEFAULT NULL,
  `url` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_module_id_on_permission` (`module_id`) USING BTREE,
  CONSTRAINT `fk_module_id_on_permission` FOREIGN KEY (`module_id`) REFERENCES `admin_module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table admin_permissions
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES
(1, 1, 'view', 'View Dashboard', '/dashboard', '2026-07-23 12:19:52', NULL),
(2, 2, 'view', 'View Investor List', '/investor/view', '2026-07-23 12:19:52', NULL),
(3, 2, 'create', 'Create Investor', '/investor/create', '2026-07-23 12:19:52', NULL),
(4, 2, 'update', 'Update Investor', '/investor/update/*', '2026-07-23 12:19:52', NULL),
(5, 2, 'delete', 'Delete Investor', '/investor/delete', '2026-07-23 12:19:52', NULL),
(8, 4, 'view', 'View Admin List', '/admin/view', '2026-07-23 12:19:52', NULL),
(9, 4, 'create', 'Create Admin', '/admin/create', '2026-07-23 12:19:52', NULL),
(10, 4, 'update', 'Update Admin', '/admin/update/*', '2026-07-23 12:19:52', NULL),
(11, 4, 'update.permission', 'Manage Admin Permission', '/admin/permission/*', '2026-07-23 12:19:52', NULL),
(12, 4, 'delete', 'Delete Admin', '/admin/delete', '2026-07-23 12:19:52', NULL),
(13, 5, 'view', 'View Module Group', '/developer/group/view', '2026-07-23 12:19:52', NULL),
(14, 5, 'create', 'Create Module Group', '/developer/group/create', '2026-07-23 12:19:52', NULL),
(15, 5, 'update', 'Update Module Group', '/developer/group/update', '2026-07-23 12:19:52', NULL),
(16, 5, 'delete', 'Delete Module Group', '/developer/group/delete', '2026-07-23 12:19:52', NULL),
(17, 6, 'view', 'View Module List', '/developer/module/view', '2026-07-23 12:19:52', NULL),
(18, 6, 'create', 'Create Module', '/developer/module/create', '2026-07-23 12:19:52', NULL),
(19, 6, 'update', 'Update Module', '/developer/module/update/*', '2026-07-23 12:19:52', NULL),
(20, 6, 'delete', 'Delete Module', '/developer/module/delete', '2026-07-23 12:19:52', NULL),
(37, 7, 'view', 'View Outlet List', '/outlet/view', '2026-07-23 15:47:15', '2026-07-23 09:07:36'),
(38, 7, 'create', 'Create Outlet', '/outlet/create', '2026-07-23 15:47:15', '2026-07-23 09:07:36'),
(41, 7, 'update', 'Update Outlet', '/outlet/update/*', '2026-07-24 10:34:29', NULL),
(42, 7, 'delete', 'Delete Outlet', '/outlet/delete', '2026-07-24 10:34:29', NULL),
(53, 19, 'view', 'View Master List', '/master/view', '2026-07-28 15:01:00', NULL),
(54, 19, 'create', 'Create Master', '/master/create', '2026-07-28 15:01:00', NULL),
(55, 19, 'update', 'Update Master', '/master/update/*', '2026-07-28 15:01:00', NULL),
(56, 19, 'delete', 'Delete Master', '/master/delete', '2026-07-28 15:01:00', NULL),
(85, 32, 'view', 'Rekening Bank Setting', '/pengaturan/rekening_bank', '2026-07-29 16:31:32', '2026-07-30 01:46:19'),
(86, 33, 'view', 'Daftar Komisi Master', '/master/komisi', '2026-08-06 13:29:01', '2026-08-06 06:31:57'),
(87, 33, 'create', 'Tambah/Edit Komisi Master', '/master/komisi_create', '2026-08-06 13:29:01', NULL),
(88, 33, 'delete', 'Hapus Komisi Master', '/master/komisi/delete', '2026-08-06 13:29:01', NULL)
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `desc` = VALUES(`desc`), `url` = VALUES(`url`);
