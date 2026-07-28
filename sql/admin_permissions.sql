-- Export table `admin_permissions`
-- --------------------------------------------------------
-- Table structure for `admin_permissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions` (
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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for `admin_permissions`
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('1', '1', 'view', 'View Dashboard', '/dashboard', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('2', '2', 'view', 'View Investor List', '/investor/view', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('3', '2', 'create', 'Create Investor', '/investor/create', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('4', '2', 'update', 'Update Investor', '/investor/update/*', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('5', '2', 'delete', 'Delete Investor', '/investor/delete', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('6', '3', 'view', 'View System Settings', '/pengaturan/view', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('7', '3', 'update', 'Update System Settings', '/pengaturan/update', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('8', '4', 'view', 'View Admin List', '/admin/view', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('9', '4', 'create', 'Create Admin', '/admin/create', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('10', '4', 'update', 'Update Admin', '/admin/update/*', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('11', '4', 'update.permission', 'Manage Admin Permission', '/admin/permission/*', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('12', '4', 'delete', 'Delete Admin', '/admin/delete', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('13', '5', 'view', 'View Module Group', '/developer/group/view', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('14', '5', 'create', 'Create Module Group', '/developer/group/create', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('15', '5', 'update', 'Update Module Group', '/developer/group/update', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('16', '5', 'delete', 'Delete Module Group', '/developer/group/delete', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('17', '6', 'view', 'View Module List', '/developer/module/view', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('18', '6', 'create', 'Create Module', '/developer/module/create', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('19', '6', 'update', 'Update Module', '/developer/module/update/*', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('20', '6', 'delete', 'Delete Module', '/developer/module/delete', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('37', '7', 'view', 'View Outlet List', '/outlet/view', '2026-07-23 15:47:15', '2026-07-23 16:07:36');
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('38', '7', 'create', 'Create Outlet', '/outlet/create', '2026-07-23 15:47:15', '2026-07-23 16:07:36');
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('39', '8', 'view', 'View Omzet List', '/omzet/view', '2026-07-23 15:47:15', '2026-07-23 16:07:36');
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('40', '9', 'view', 'View Bagi Hasil List', '/bagi-hasil/view', '2026-07-23 15:47:15', '2026-07-23 16:07:36');
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('41', '7', 'update', 'Update Outlet', '/outlet/update/*', '2026-07-24 10:34:29', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('42', '7', 'delete', 'Delete Outlet', '/outlet/delete', '2026-07-24 10:34:29', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('43', '8', 'verify', 'Verify Omzet Nota', '/omzet/verify', '2026-07-24 10:34:29', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('48', '16', 'view', 'View Request Outlet List', '/request-outlet/view', '2026-07-27 15:53:00', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('49', '17', 'view', 'View Biaya Langganan', '/pengaturan-langganan/view', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('50', '17', 'update', 'Update Biaya Langganan', '/pengaturan-langganan/update', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('51', '18', 'view', 'View Potongan Global', '/pengaturan-potongan/view', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('52', '18', 'update', 'Update Potongan Global', '/pengaturan-potongan/update', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('53', '19', 'view', 'View Master List', '/master/view', '2026-07-28 15:01:00', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('54', '19', 'create', 'Create Master', '/master/create', '2026-07-28 15:01:00', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('55', '19', 'update', 'Update Master', '/master/update/*', '2026-07-28 15:01:00', NULL);
INSERT INTO `admin_permissions` (`id`, `module_id`, `code`, `desc`, `url`, `created_at`, `updated_at`) VALUES ('56', '19', 'delete', 'Delete Master', '/master/delete', '2026-07-28 15:01:00', NULL);

