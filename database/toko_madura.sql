-- ========================================================
-- EXPORT DATABASE TOKO MADURA (REVISED SCHEMA & DATA)
-- Generated: 2026-07-28 15:02:08
-- ========================================================

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id_users` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('programmer','master','investor','outlet') NOT NULL DEFAULT 'outlet',
  PRIMARY KEY (`id_users`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `users`
INSERT INTO `users` (`id_users`, `nama_lengkap`, `username`, `no_hp`, `password`, `role`) VALUES ('1', 'Riski Ardhika', 'riski', NULL, '$2y$10$7ZFtaY7BgXqtmX/l0tpcq.YcEdsRzcPWsgSQox/GjVSq/w7G3/6kG', 'programmer');
INSERT INTO `users` (`id_users`, `nama_lengkap`, `username`, `no_hp`, `password`, `role`) VALUES ('47', 'Riski Ardhika 1', 'master', '0123456789', '$2y$10$XLk1LKK8f9iIg0CAIk5XcuydhM.5GNuaQuIZYvys.MdMZfFog3KfG', 'master');
INSERT INTO `users` (`id_users`, `nama_lengkap`, `username`, `no_hp`, `password`, `role`) VALUES ('48', 'M. Fahrul Alfanani', 'investor', '0987654321', '$2y$10$PJoPaaGGwh6fM7ZR8wsuheJaKUeH1TicK2j1fmzZEMpedOmHQTvce', 'investor');
INSERT INTO `users` (`id_users`, `nama_lengkap`, `username`, `no_hp`, `password`, `role`) VALUES ('49', 'Muhhamad Tegar Kurniawan', 'outlet', '014785236', '$2y$10$ZvCsayL/4w91UIHvYCzC7exJjvS2oa6wuIYfKfcNRF3CMzmEJC7H.', 'outlet');

-- --------------------------------------------------------
-- Table structure for `investor`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `investor`;
CREATE TABLE `investor` (
  `id_investor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned NOT NULL,
  `id_master` int(10) unsigned NOT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `alamat_investor` text DEFAULT NULL,
  `persen_bagian_investor` decimal(5,2) NOT NULL DEFAULT 50.00,
  `persen_bagian_master` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tanggal_bergabung` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_investor`),
  UNIQUE KEY `id_users` (`id_users`),
  KEY `id_master` (`id_master`),
  CONSTRAINT `investor_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE,
  CONSTRAINT `investor_ibfk_2` FOREIGN KEY (`id_master`) REFERENCES `users` (`id_users`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `investor`
INSERT INTO `investor` (`id_investor`, `id_users`, `id_master`, `kecamatan`, `alamat_investor`, `persen_bagian_investor`, `persen_bagian_master`, `tanggal_bergabung`) VALUES ('14', '48', '47', 'Tulangan', 'GMCF+C87, Kadelesan, Kedondong, Kec. Tulangan, Kabupaten Sidoarjo, Jawa Timur 61273', '50.00', '0.00', '2026-07-27 15:49:35');

-- --------------------------------------------------------
-- Table structure for `outlet`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `outlet`;
CREATE TABLE `outlet` (
  `id_outlet` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned NOT NULL,
  `id_investor` int(10) unsigned NOT NULL,
  `persentase_potongan` decimal(5,2) NOT NULL DEFAULT 10.00,
  `nama_outlet` varchar(100) NOT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `alamat_outlet` text DEFAULT NULL,
  `status` enum('pending','active','reject') NOT NULL DEFAULT 'active',
  `tanggal_request` datetime DEFAULT current_timestamp(),
  `tanggal_disetujui` datetime DEFAULT NULL,
  `tanggal_ditolak` datetime DEFAULT NULL,
  `nominal_biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `alasan_penolakan` text DEFAULT NULL,
  `tanggal_bergabung` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_outlet`),
  UNIQUE KEY `id_users` (`id_users`),
  KEY `id_investor` (`id_investor`),
  CONSTRAINT `outlet_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE,
  CONSTRAINT `outlet_ibfk_2` FOREIGN KEY (`id_investor`) REFERENCES `investor` (`id_investor`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `outlet`
INSERT INTO `outlet` (`id_outlet`, `id_users`, `id_investor`, `persentase_potongan`, `nama_outlet`, `kecamatan`, `alamat_outlet`, `status`, `tanggal_request`, `tanggal_disetujui`, `tanggal_ditolak`, `nominal_biaya`, `bukti_pembayaran`, `alasan_penolakan`, `tanggal_bergabung`) VALUES ('10', '49', '14', '10.00', 'Toko Madura Merdeka', 'Candi', 'amdakmdmdmadmkadmak', 'active', '2026-07-27 15:49:36', '2026-07-27 15:49:36', NULL, '0.00', NULL, NULL, '2026-07-27 15:49:36');

-- --------------------------------------------------------
-- Table structure for `laporan_omzet`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `laporan_omzet`;
CREATE TABLE `laporan_omzet` (
  `id_laporan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_outlet` int(10) unsigned NOT NULL,
  `periode_laporan` date NOT NULL,
  `omzet` decimal(15,2) NOT NULL DEFAULT 0.00,
  `presentase_potongan` decimal(5,2) NOT NULL,
  `nominal_potongan` decimal(15,2) NOT NULL,
  `waktu_input` datetime NOT NULL,
  PRIMARY KEY (`id_laporan`),
  KEY `id_outlet` (`id_outlet`),
  KEY `idx_laporan_periode` (`periode_laporan`),
  CONSTRAINT `laporan_omzet_ibfk_1` FOREIGN KEY (`id_outlet`) REFERENCES `outlet` (`id_outlet`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `rekap_bagi_hasil`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `rekap_bagi_hasil`;
CREATE TABLE `rekap_bagi_hasil` (
  `id_rekap` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_investor` int(10) unsigned NOT NULL,
  `periode_rekap` varchar(20) NOT NULL,
  `akumulasi_omzet` decimal(15,2) NOT NULL DEFAULT 0.00,
  `akumulasi_potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hak_investor` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hak_outlet` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_rekap`),
  KEY `id_investor` (`id_investor`),
  KEY `idx_rekap_periode` (`periode_rekap`),
  CONSTRAINT `rekap_bagi_hasil_ibfk_1` FOREIGN KEY (`id_investor`) REFERENCES `investor` (`id_investor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `pengaturan_sistem`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pengaturan_sistem`;
CREATE TABLE `pengaturan_sistem` (
  `id_pengaturan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_pengaturan` varchar(50) NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_pengaturan`),
  UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `pengaturan_sistem`
INSERT INTO `pengaturan_sistem` (`id_pengaturan`, `nama_pengaturan`, `nilai`) VALUES ('1', 'potongan_global', '10.00');
INSERT INTO `pengaturan_sistem` (`id_pengaturan`, `nama_pengaturan`, `nilai`) VALUES ('2', 'biaya_langganan_outlet', '999.99');

-- --------------------------------------------------------
-- Table structure for `admin_module_group`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_module_group`;
CREATE TABLE `admin_module_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order` int(10) unsigned NOT NULL,
  `group` varchar(255) NOT NULL,
  `type` enum('single','dropdown') NOT NULL DEFAULT 'single',
  `icon` text DEFAULT NULL,
  `min_level` int(11) NOT NULL DEFAULT 10,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for `admin_module_group`
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('1', '1', 'Dashboard', 'single', 'ti-home sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('2', '3', 'Investor', 'single', 'fa fa-handshake-o sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('3', '8', 'Admin', 'single', 'ti-user sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('4', '9', 'Developer', 'dropdown', 'ti-panel sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('5', '4', 'Outlet', 'single', 'fa fa-building sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('6', '5', 'Omzet', 'single', 'ti-stats-up sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('7', '6', 'Bagi Hasil', 'single', 'ti-wallet sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('8', '7', 'Pengaturan', 'dropdown', 'fa fa-cog sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('12', '2', 'Master', 'single', 'fa fa-user-circle sidemenu-icon menu-icon', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for `admin_module`
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'Dashboard', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('2', '2', '2', 'Investor', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('3', '1', '8', 'Pengaturan', '-1', '0', '2026-07-23 12:19:52', '2026-07-27 17:16:56');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('4', '1', '3', 'Admin', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('5', '1', '4', 'group', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('6', '2', '4', 'module', '-1', '-1', '2026-07-23 12:19:52', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('7', '1', '5', 'Outlet', '-1', '-1', '2026-07-23 15:47:15', '2026-07-28 08:49:08');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('8', '5', '6', 'Omzet', '-1', '-1', '2026-07-23 15:47:15', '2026-07-24 13:46:45');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('9', '6', '7', 'Bagi Hasil', '-1', '-1', '2026-07-23 15:47:15', '2026-07-24 13:46:45');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('16', '2', '5', 'request-outlet', '-1', '0', '2026-07-27 15:53:00', '2026-07-28 10:43:11');
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('17', '1', '8', 'pengaturan-langganan', '-1', '-1', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('18', '2', '8', 'pengaturan-potongan', '-1', '-1', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES ('19', '1', '12', 'Master', '-1', '-1', '2026-07-28 15:00:51', NULL);

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

-- --------------------------------------------------------
-- Table structure for `admin_authorize`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_authorize`;
CREATE TABLE `admin_authorize` (
  `admin_id` int(10) unsigned NOT NULL,
  `permission_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `uniq_role` (`admin_id`,`permission_id`) USING BTREE,
  KEY `fk_permission_id` (`permission_id`) USING BTREE,
  CONSTRAINT `admin_authorize_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_authorize_ibfk_3` FOREIGN KEY (`permission_id`) REFERENCES `admin_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for `admin_authorize`
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '2', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '3', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '4', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '5', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '6', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '7', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '8', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '9', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '10', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '11', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '12', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '13', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '14', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '15', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '16', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '17', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '18', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '19', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '20', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '37', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '38', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '39', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '40', '0', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '41', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '42', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '43', '-1', '2026-07-24 13:46:50', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '48', '-1', '2026-07-27 15:53:00', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '49', '-1', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '50', '-1', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '51', '-1', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '52', '-1', '2026-07-27 17:15:26', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '53', '-1', '2026-07-28 15:01:08', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '54', '-1', '2026-07-28 15:01:08', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '55', '-1', '2026-07-28 15:01:08', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('1', '56', '-1', '2026-07-28 15:01:08', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '1', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '2', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '3', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '4', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '5', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '6', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '7', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '8', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '9', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '10', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '11', '-1', '2026-07-28 10:20:48', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '12', '-1', '2026-07-28 10:20:48', NULL);

