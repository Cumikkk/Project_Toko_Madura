-- Export table `admin_authorize`
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
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '1', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '2', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '3', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '4', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '5', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '6', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '7', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '8', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '9', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '10', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '11', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('47', '12', '-1', '2026-07-25 08:24:35', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('52', '1', '-1', '2026-07-25 08:48:44', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('52', '2', '-1', '2026-07-25 08:48:44', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('52', '3', '-1', '2026-07-25 08:48:44', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('52', '4', '-1', '2026-07-25 08:48:44', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('52', '5', '-1', '2026-07-25 08:48:44', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('52', '6', '-1', '2026-07-25 08:48:44', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('52', '7', '-1', '2026-07-25 08:48:44', NULL);

