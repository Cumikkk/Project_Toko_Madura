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
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '1', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '2', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '3', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '4', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '5', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '8', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '9', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '10', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '11', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '12', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '13', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '14', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '15', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '16', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '17', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '18', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '19', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '20', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '37', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '38', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '41', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '42', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '53', '-1', '2026-07-29 13:25:25', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '54', '-1', '2026-07-29 13:25:26', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '55', '-1', '2026-07-29 13:25:26', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '56', '-1', '2026-07-29 13:25:26', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '84', '-1', '2026-07-29 16:31:32', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('59', '85', '-1', '2026-07-29 16:31:32', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('61', '84', '-1', '2026-07-29 16:31:32', NULL);
INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES ('61', '85', '-1', '2026-07-29 16:31:32', NULL);

