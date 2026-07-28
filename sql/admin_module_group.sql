-- Export table `admin_module_group`
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
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('3', '8', 'Admin', 'single', 'fa fa-user-secret sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('4', '9', 'Developer', 'dropdown', 'ti-panel sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('5', '4', 'Outlet', 'single', 'fa fa-building sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('6', '5', 'Omzet', 'single', 'ti-stats-up sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('7', '6', 'Bagi Hasil', 'single', 'ti-wallet sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('8', '7', 'Pengaturan', 'dropdown', 'fa fa-cog sidemenu-icon menu-icon', '1');
INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES ('12', '2', 'Master', 'single', 'fa fa-user-circle sidemenu-icon menu-icon', '1');

