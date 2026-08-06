-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 06 Agu 2026 pada 14.15
-- Versi server: 8.4.3
-- Versi PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_madura`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_authorize`
--

CREATE TABLE `admin_authorize` (
  `admin_id` int UNSIGNED NOT NULL,
  `permission_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data untuk tabel `admin_authorize`
--

INSERT INTO `admin_authorize` (`admin_id`, `permission_id`, `status`, `created_at`, `updated_at`) VALUES
(59, 1, -1, '2026-07-29 13:25:25', NULL),
(59, 2, -1, '2026-07-29 13:25:25', NULL),
(59, 3, -1, '2026-07-29 13:25:25', NULL),
(59, 4, -1, '2026-07-29 13:25:25', NULL),
(59, 5, -1, '2026-07-29 13:25:25', NULL),
(59, 8, -1, '2026-07-29 13:25:25', NULL),
(59, 9, -1, '2026-07-29 13:25:25', NULL),
(59, 10, -1, '2026-07-29 13:25:25', NULL),
(59, 11, -1, '2026-07-29 13:25:25', NULL),
(59, 12, -1, '2026-07-29 13:25:25', NULL),
(59, 13, -1, '2026-07-29 13:25:25', NULL),
(59, 14, -1, '2026-07-29 13:25:25', NULL),
(59, 15, -1, '2026-07-29 13:25:25', NULL),
(59, 16, -1, '2026-07-29 13:25:25', NULL),
(59, 17, -1, '2026-07-29 13:25:25', NULL),
(59, 18, -1, '2026-07-29 13:25:25', NULL),
(59, 19, -1, '2026-07-29 13:25:25', NULL),
(59, 20, -1, '2026-07-29 13:25:25', NULL),
(59, 37, -1, '2026-07-29 13:25:25', NULL),
(59, 38, -1, '2026-07-29 13:25:25', NULL),
(59, 41, -1, '2026-07-29 13:25:25', NULL),
(59, 42, -1, '2026-07-29 13:25:25', NULL),
(59, 53, -1, '2026-07-29 13:25:25', NULL),
(59, 54, -1, '2026-07-29 13:25:26', NULL),
(59, 55, -1, '2026-07-29 13:25:26', NULL),
(59, 56, -1, '2026-07-29 13:25:26', NULL),
(59, 85, -1, '2026-07-29 16:31:32', NULL),
(59, 86, -1, '2026-08-06 13:29:01', NULL),
(59, 87, -1, '2026-08-06 13:29:01', NULL),
(59, 88, -1, '2026-08-06 13:29:01', NULL),
(61, 85, -1, '2026-07-29 16:31:32', NULL),
(107, 1, -1, '2026-08-03 17:28:06', NULL),
(107, 2, -1, '2026-08-03 17:28:06', NULL),
(107, 3, -1, '2026-08-03 17:28:06', NULL),
(107, 4, -1, '2026-08-03 17:28:06', NULL),
(107, 5, -1, '2026-08-03 17:28:06', NULL),
(107, 8, -1, '2026-08-03 17:28:06', NULL),
(107, 9, -1, '2026-08-03 17:28:06', NULL),
(107, 10, -1, '2026-08-03 17:28:06', NULL),
(107, 11, -1, '2026-08-03 17:28:06', NULL),
(107, 12, -1, '2026-08-03 17:28:06', NULL),
(107, 13, -1, '2026-08-03 17:28:06', NULL),
(107, 14, -1, '2026-08-03 17:28:06', NULL),
(107, 15, -1, '2026-08-03 17:28:06', NULL),
(107, 16, -1, '2026-08-03 17:28:06', NULL),
(107, 17, -1, '2026-08-03 17:28:06', NULL),
(107, 18, -1, '2026-08-03 17:28:06', NULL),
(107, 19, -1, '2026-08-03 17:28:06', NULL),
(107, 20, -1, '2026-08-03 17:28:06', NULL),
(107, 37, -1, '2026-08-03 17:28:06', NULL),
(107, 38, -1, '2026-08-03 17:28:06', NULL),
(107, 41, -1, '2026-08-03 17:28:06', NULL),
(107, 42, -1, '2026-08-03 17:28:06', NULL),
(107, 53, -1, '2026-08-03 17:28:06', NULL),
(107, 54, -1, '2026-08-03 17:28:06', NULL),
(107, 55, -1, '2026-08-03 17:28:06', NULL),
(107, 56, -1, '2026-08-03 17:28:06', NULL),
(107, 85, -1, '2026-08-03 17:28:06', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_module`
--

CREATE TABLE `admin_module` (
  `id` int NOT NULL,
  `m_order` int DEFAULT '1',
  `group_id` int NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` int DEFAULT '-1',
  `visible` int DEFAULT '-1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data untuk tabel `admin_module`
--

INSERT INTO `admin_module` (`id`, `m_order`, `group_id`, `module`, `status`, `visible`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Dashboard', -1, -1, '2026-07-23 12:19:52', NULL),
(2, 2, 2, 'Investor', -1, -1, '2026-07-23 12:19:52', NULL),
(4, 1, 3, 'Admin', -1, -1, '2026-07-23 12:19:52', NULL),
(5, 1, 4, 'group', -1, -1, '2026-07-23 12:19:52', NULL),
(6, 2, 4, 'module', -1, -1, '2026-07-23 12:19:52', NULL),
(7, 1, 5, 'Outlet', -1, -1, '2026-07-23 15:47:15', '2026-07-28 01:49:08'),
(19, 1, 12, 'data_master', -1, -1, '2026-07-28 15:00:51', '2026-08-06 06:28:54'),
(32, 2, 19, 'rekening_bank', -1, -1, '2026-07-29 16:31:32', NULL),
(33, 2, 12, 'komisi', -1, -1, '2026-08-06 13:28:54', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_module_group`
--

CREATE TABLE `admin_module_group` (
  `id` int NOT NULL,
  `order` int UNSIGNED NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `type` enum('single','dropdown') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'single',
  `icon` text COLLATE utf8mb4_general_ci,
  `min_level` int NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data untuk tabel `admin_module_group`
--

INSERT INTO `admin_module_group` (`id`, `order`, `group`, `type`, `icon`, `min_level`) VALUES
(1, 1, 'Dashboard', 'single', 'ti-home sidemenu-icon menu-icon', 1),
(2, 3, 'Investor', 'single', 'fa fa-handshake-o sidemenu-icon menu-icon', 1),
(3, 8, 'Admin', 'single', 'fa fa-user-secret sidemenu-icon menu-icon', 1),
(4, 9, 'Developer', 'dropdown', 'ti-panel sidemenu-icon menu-icon', 1),
(5, 4, 'Outlet', 'single', 'fa fa-building sidemenu-icon menu-icon', 1),
(12, 2, 'Master', 'dropdown', 'fa fa-user-circle sidemenu-icon menu-icon', 1),
(19, 6, 'Pengaturan', 'dropdown', 'fa fa-cog sidemenu-icon menu-icon', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int NOT NULL,
  `module_id` int NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `desc` text COLLATE utf8mb4_general_ci,
  `url` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data untuk tabel `admin_permissions`
--

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
(88, 33, 'delete', 'Hapus Komisi Master', '/master/komisi/delete', '2026-08-06 13:29:01', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `investor`
--

CREATE TABLE `investor` (
  `id_investor` int UNSIGNED NOT NULL,
  `id_users` int UNSIGNED NOT NULL,
  `id_master` int UNSIGNED NOT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_investor` text COLLATE utf8mb4_general_ci,
  `persen_bagian_investor` decimal(5,2) NOT NULL DEFAULT '50.00',
  `biaya_langganan_outlet` decimal(15,2) NOT NULL DEFAULT '100000.00',
  `persen_bagian_master` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tanggal_bergabung` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `investor`
--

INSERT INTO `investor` (`id_investor`, `id_users`, `id_master`, `kecamatan`, `alamat_investor`, `persen_bagian_investor`, `biaya_langganan_outlet`, `persen_bagian_master`, `tanggal_bergabung`) VALUES
(19, 62, 61, 'Tulangan', 'GMCF+C87, Kadelesan, Kedondong, Kec. Tulangan, Kabupaten Sidoarjo, Jawa Timur 61273', 50.00, 100000.00, 0.00, '2026-07-29 10:50:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komisi_master`
--

CREATE TABLE `komisi_master` (
  `id_komisi` int UNSIGNED NOT NULL,
  `id_master` int UNSIGNED NOT NULL,
  `tanggal_komisi` datetime NOT NULL,
  `periode` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `catatan` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_omzet`
--

CREATE TABLE `laporan_omzet` (
  `id_laporan` int UNSIGNED NOT NULL,
  `id_outlet` int UNSIGNED NOT NULL,
  `periode_laporan` date NOT NULL,
  `omzet` decimal(15,2) NOT NULL DEFAULT '0.00',
  `presentase_potongan` decimal(5,2) NOT NULL,
  `persen_bagian_investor` decimal(5,2) DEFAULT NULL,
  `nominal_potongan` decimal(15,2) NOT NULL,
  `waktu_input` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan_omzet`
--

INSERT INTO `laporan_omzet` (`id_laporan`, `id_outlet`, `periode_laporan`, `omzet`, `presentase_potongan`, `persen_bagian_investor`, `nominal_potongan`, `waktu_input`) VALUES
(3, 45, '2026-07-30', 300000.00, 8.00, 70.00, 24000.00, '2026-07-30 16:14:50'),
(4, 45, '2026-07-31', 300000.00, 8.00, 70.00, 24000.00, '2026-07-30 16:15:11'),
(5, 45, '2026-07-29', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:15:47'),
(6, 45, '2026-07-28', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:15:55'),
(7, 45, '2026-07-27', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:16:03'),
(8, 45, '2026-07-26', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:16:11'),
(9, 45, '2026-07-24', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:16:24'),
(10, 45, '2026-07-23', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:16:47'),
(11, 45, '2026-07-22', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:17:24'),
(12, 45, '2026-07-21', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:17:35'),
(13, 45, '2026-07-20', 100000.00, 8.00, 70.00, 8000.00, '2026-07-30 16:17:46'),
(14, 53, '2026-07-31', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:42:16'),
(15, 53, '2026-07-30', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:42:23'),
(16, 53, '2026-07-29', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:42:32'),
(17, 53, '2026-07-28', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:42:43'),
(18, 53, '2026-07-27', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:42:53'),
(19, 53, '2026-07-26', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:43:00'),
(20, 53, '2026-07-25', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:43:09'),
(21, 53, '2026-07-24', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:43:16'),
(22, 53, '2026-07-23', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:43:23'),
(23, 53, '2026-07-22', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:43:29'),
(24, 53, '2026-07-21', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 10:43:40'),
(25, 53, '2026-07-17', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 13:16:09'),
(26, 53, '2026-07-14', 100000.00, 4.00, 60.00, 4000.00, '2026-07-31 13:17:42'),
(27, 53, '2026-07-13', 300000.00, 4.00, 60.00, 12000.00, '2026-07-31 13:19:03'),
(28, 52, '2026-08-01', 100000.00, 7.00, 50.00, 7000.00, '2026-08-01 10:52:49'),
(29, 52, '2026-08-02', 100000.00, 7.00, 50.00, 7000.00, '2026-08-01 10:52:56'),
(30, 52, '2026-08-03', 100000.00, 7.00, 50.00, 7000.00, '2026-08-01 10:53:05'),
(31, 52, '2026-08-04', 100000.00, 7.00, 50.00, 7000.00, '2026-08-01 10:53:14'),
(32, 45, '2026-08-03', 100000.00, 5.00, 90.00, 5000.00, '2026-08-03 17:22:02'),
(34, 45, '2026-08-04', 50000.00, 5.00, 90.00, 2500.00, '2026-08-04 11:04:10'),
(35, 45, '2026-08-05', 20000.00, 5.00, 90.00, 1000.00, '2026-08-04 11:19:35'),
(36, 45, '2026-08-06', 20000.00, 5.00, 90.00, 1000.00, '2026-08-04 11:19:48'),
(37, 45, '2026-08-15', 20000.00, 5.00, 90.00, 1000.00, '2026-08-04 11:19:54'),
(38, 45, '2026-08-14', 1500.00, 5.00, 90.00, 75.00, '2026-08-04 11:20:25'),
(39, 45, '2026-08-16', 1000.00, 5.00, 90.00, 50.00, '2026-08-04 11:24:01'),
(40, 48, '2026-08-04', 10000.00, 0.00, 50.00, 0.00, '2026-08-04 15:22:55'),
(41, 48, '2026-08-05', 200000.00, 0.00, 50.00, 0.00, '2026-08-04 15:23:39'),
(42, 48, '2026-08-06', 50000.00, 10.00, 50.00, 5000.00, '2026-08-04 15:23:56'),
(43, 53, '2026-08-01', 5000.00, 0.00, 60.00, 0.00, '2026-08-05 13:13:09'),
(44, 53, '2026-08-02', 5000.00, 0.00, 60.00, 0.00, '2026-08-05 13:13:59'),
(45, 53, '2026-08-03', 20000.00, 0.00, 60.00, 0.00, '2026-08-05 13:14:14'),
(46, 53, '2026-08-04', 50000.00, 0.00, 60.00, 0.00, '2026-08-05 13:14:27'),
(47, 53, '2026-08-05', 40000.00, 0.00, 60.00, 0.00, '2026-08-05 13:14:33'),
(48, 53, '2026-08-06', 70000.00, 0.00, 60.00, 0.00, '2026-08-05 13:14:56'),
(49, 53, '2026-08-07', 30000.00, 0.00, 60.00, 0.00, '2026-08-05 13:15:09'),
(50, 53, '2026-08-08', 60000.00, 0.00, 60.00, 0.00, '2026-08-05 13:18:32'),
(51, 53, '2026-08-09', 10000.00, 0.00, 60.00, 0.00, '2026-08-05 13:20:37'),
(52, 53, '2026-08-10', 2000.00, 4.00, 60.00, 80.00, '2026-08-05 13:22:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `outlet`
--

CREATE TABLE `outlet` (
  `id_outlet` int UNSIGNED NOT NULL,
  `id_users` int UNSIGNED NOT NULL,
  `id_investor` int UNSIGNED NOT NULL,
  `persentase_potongan` decimal(5,2) NOT NULL DEFAULT '10.00',
  `persen_bagian_investor` decimal(5,2) DEFAULT '50.00',
  `nama_outlet` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_outlet` text COLLATE utf8mb4_general_ci,
  `status` enum('pending','active','reject') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `tipe_request` enum('baru','perpanjangan') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'baru',
  `tgl_jatuh_tempo` datetime DEFAULT NULL,
  `tanggal_request` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_disetujui` datetime DEFAULT NULL,
  `tanggal_ditolak` datetime DEFAULT NULL,
  `nominal_biaya` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alasan_penolakan` text COLLATE utf8mb4_general_ci,
  `tanggal_bergabung` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `outlet`
--

INSERT INTO `outlet` (`id_outlet`, `id_users`, `id_investor`, `persentase_potongan`, `persen_bagian_investor`, `nama_outlet`, `kecamatan`, `alamat_outlet`, `status`, `tipe_request`, `tgl_jatuh_tempo`, `tanggal_request`, `tanggal_disetujui`, `tanggal_ditolak`, `nominal_biaya`, `bukti_pembayaran`, `alasan_penolakan`, `tanggal_bergabung`) VALUES
(45, 97, 19, 5.00, 90.00, 'Toko Madura Merdeka', 'Bangkalan', 'Jl. Pemuda No. 12, Bangkalan', 'active', 'baru', '2026-08-30 23:59:59', '2026-05-15 09:00:00', '2026-07-30 10:00:00', NULL, 100000.00, 'uploads/bukti_pembayaran/bukti_sample_merdeka.png', NULL, '2026-05-15 10:00:00'),
(46, 98, 19, 10.00, 60.00, 'Toko Madura Sumber Rejeki', 'Kamal', 'Jl. Trunojoyo No. 45, Kamal', 'active', 'perpanjangan', '2026-08-02 00:00:00', '2026-07-30 16:10:36', '2026-07-30 16:13:18', NULL, 100000.00, 'uploads/bukti_pembayaran/bukti_renew_1785402636_4993.png', NULL, '2026-06-02 11:00:00'),
(47, 99, 19, 10.00, 50.00, 'Toko Madura Melati Indah', 'Socah', 'Jl. Raya Socah No. 88, Socah', 'active', 'baru', '2026-07-20 23:59:59', '2026-05-20 08:00:00', '2026-06-20 09:00:00', NULL, 100000.00, 'uploads/bukti_pembayaran/bukti_sample_melati.png', NULL, '2026-05-20 09:00:00'),
(48, 100, 19, 10.00, 50.00, 'Toko Madura Cahaya Jaya', 'Kamal', 'Jl. Pelabuhan No. 19, Kamal', 'active', 'perpanjangan', '2026-08-30 16:13:26', '2026-07-30 11:00:00', '2026-07-30 16:13:26', NULL, 100000.00, 'uploads/bukti_pembayaran/bukti_sample_cahaya.png', NULL, '2026-04-10 08:00:00'),
(51, 103, 19, 10.00, 50.00, 'asasas', 'sas', 'sasas', 'pending', 'baru', NULL, '2026-07-30 12:18:39', NULL, NULL, 100000.00, 'uploads/bukti_pembayaran/bukti_1785388719_7112.png', NULL, '2026-07-30 12:18:39'),
(52, 104, 19, 7.00, 50.00, 'asas', 'sasa', 'sasa', 'pending', 'perpanjangan', '2026-08-04 10:51:32', '2026-08-03 16:58:58', '2026-08-01 10:51:32', NULL, 100000.00, 'uploads/bukti_pembayaran/bukti_renew_1785751138_1363.png', NULL, '2026-07-30 12:19:07'),
(53, 105, 19, 4.00, 60.00, 'cumik', 'genggex', 'kjbdkjakjbkajbjakbfjakbkajbfkjabfajkbfjkakjfbajkfakjfakjffjajfa', 'active', 'baru', '2026-08-31 10:41:25', '2026-07-31 10:40:52', '2026-07-31 10:41:25', NULL, 100000.00, 'uploads/bukti_pembayaran/bukti_1785469252_9070.png', NULL, '2026-07-31 10:40:52'),
(54, 106, 19, 10.00, 60.00, 'yuhu', 'taub', 'taman', 'reject', 'baru', NULL, '2026-08-03 16:53:41', NULL, '2026-08-03 16:54:43', 100000.00, 'uploads/bukti_pembayaran/bukti_1785750821_5460.png', 'bukti tidak valid', '2026-08-03 16:53:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan_sistem`
--

CREATE TABLE `pengaturan_sistem` (
  `id_pengaturan` int UNSIGNED NOT NULL,
  `nama_pengaturan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nilai` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan_sistem`
--

INSERT INTO `pengaturan_sistem` (`id_pengaturan`, `nama_pengaturan`, `nilai`) VALUES
(1, 'potongan_global', '10.00'),
(2, 'biaya_langganan_outlet', '100000'),
(3, 'bank_nama', 'BRI'),
(4, 'bank_no_rekening', '987-654-321'),
(5, 'bank_atas_nama', 'Anonymous');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rekap_bagi_hasil`
--

CREATE TABLE `rekap_bagi_hasil` (
  `id_rekap` int UNSIGNED NOT NULL,
  `id_investor` int UNSIGNED NOT NULL,
  `periode_rekap` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `akumulasi_omzet` decimal(15,2) NOT NULL DEFAULT '0.00',
  `akumulasi_potongan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `hak_investor` decimal(15,2) NOT NULL DEFAULT '0.00',
  `hak_outlet` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_users` int UNSIGNED NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('programmer','master','investor','outlet') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'outlet',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_users`, `nama_lengkap`, `username`, `no_hp`, `password`, `role`, `created_at`) VALUES
(59, 'Anonymous', 'admin', '0123456789', '$2y$10$bEl1pyxvEEHdLPS917gqlukvAuW0Sjo8dcebpWny4W9.f935YC59C', 'programmer', '2026-07-23 12:19:52'),
(61, 'Riski Ardhika', 'master', '0123456789', '$2y$10$bEl1pyxvEEHdLPS917gqlukvAuW0Sjo8dcebpWny4W9.f935YC59C', 'master', '2026-07-23 12:19:52'),
(62, 'M. Fahrul Alfanani', 'investor', '0987654321', '$2y$10$bEl1pyxvEEHdLPS917gqlukvAuW0Sjo8dcebpWny4W9.f935YC59C', 'investor', '2026-07-23 12:19:52'),
(97, 'Bambang Sugiono', 'toko1', '08123456789', '$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi', 'outlet', '2026-05-15 10:00:00'),
(98, 'Hendra Wijaya', 'toko2', '08123456789', '$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi', 'outlet', '2026-06-02 11:00:00'),
(99, 'Siti Rahmawati', 'toko3', '08123456789', '$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi', 'outlet', '2026-05-20 09:00:00'),
(100, 'Dedi Pratama', 'toko4', '08123456789', '$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi', 'outlet', '2026-04-10 08:00:00'),
(103, 'Agus Setiawan', 'sasas', 'sasa', '$2y$10$fW67fu8J9Q3fGM7kvDfJ1edTIaLsmzQTVm/kNiEs3sKuYIYX53.IW', 'outlet', '2026-07-30 12:18:39'),
(104, 'Fajar Nugroho', 'sasa', 'asas', '$2y$10$Go6bTK.ADikKHY1ezzf/R.aCGkl4BzL6KoZDzu2GkJxS8gFQGYETi', 'outlet', '2026-07-30 12:19:07'),
(105, 'Eko Prasetyo', 'cumik', '9797', '$2y$10$vTMxN4Ed0wpBlPutGXGfrejMaYc11nL.P/6oK1R1L2fesYaCCQUt2', 'outlet', '2026-07-31 10:40:52'),
(106, 'Budi', 'uban', '7875787', '$2y$10$zw6OoVpuivXj1MfWZ6mKjeLPmiiQjp85LDbyE9SwKFvBkIWqRUK8S', 'outlet', '2026-08-03 16:53:41'),
(107, 'oke123', 'oke123', '123', '$2y$10$xBG5kNvHNkYtnHg.xsp1MeuYMKxuWlMTrFVTWzRn5yx2mMFX/WzfK', 'programmer', '2026-08-03 17:28:06');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin_authorize`
--
ALTER TABLE `admin_authorize`
  ADD UNIQUE KEY `uniq_role` (`admin_id`,`permission_id`) USING BTREE,
  ADD KEY `fk_permission_id` (`permission_id`) USING BTREE;

--
-- Indeks untuk tabel `admin_module`
--
ALTER TABLE `admin_module`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `unique_module_name` (`module`) USING BTREE,
  ADD KEY `fk_group_id` (`group_id`) USING BTREE;

--
-- Indeks untuk tabel `admin_module_group`
--
ALTER TABLE `admin_module_group`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indeks untuk tabel `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `fk_module_id_on_permission` (`module_id`) USING BTREE;

--
-- Indeks untuk tabel `investor`
--
ALTER TABLE `investor`
  ADD PRIMARY KEY (`id_investor`),
  ADD UNIQUE KEY `id_users` (`id_users`),
  ADD KEY `id_master` (`id_master`);

--
-- Indeks untuk tabel `komisi_master`
--
ALTER TABLE `komisi_master`
  ADD PRIMARY KEY (`id_komisi`),
  ADD KEY `id_master` (`id_master`);

--
-- Indeks untuk tabel `laporan_omzet`
--
ALTER TABLE `laporan_omzet`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_outlet` (`id_outlet`),
  ADD KEY `idx_laporan_periode` (`periode_laporan`);

--
-- Indeks untuk tabel `outlet`
--
ALTER TABLE `outlet`
  ADD PRIMARY KEY (`id_outlet`),
  ADD UNIQUE KEY `id_users` (`id_users`),
  ADD KEY `id_investor` (`id_investor`);

--
-- Indeks untuk tabel `pengaturan_sistem`
--
ALTER TABLE `pengaturan_sistem`
  ADD PRIMARY KEY (`id_pengaturan`),
  ADD UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`);

--
-- Indeks untuk tabel `rekap_bagi_hasil`
--
ALTER TABLE `rekap_bagi_hasil`
  ADD PRIMARY KEY (`id_rekap`),
  ADD KEY `id_investor` (`id_investor`),
  ADD KEY `idx_rekap_periode` (`periode_rekap`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin_module`
--
ALTER TABLE `admin_module`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `admin_module_group`
--
ALTER TABLE `admin_module_group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT untuk tabel `investor`
--
ALTER TABLE `investor`
  MODIFY `id_investor` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `komisi_master`
--
ALTER TABLE `komisi_master`
  MODIFY `id_komisi` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `laporan_omzet`
--
ALTER TABLE `laporan_omzet`
  MODIFY `id_laporan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `outlet`
--
ALTER TABLE `outlet`
  MODIFY `id_outlet` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `pengaturan_sistem`
--
ALTER TABLE `pengaturan_sistem`
  MODIFY `id_pengaturan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `rekap_bagi_hasil`
--
ALTER TABLE `rekap_bagi_hasil`
  MODIFY `id_rekap` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `admin_authorize`
--
ALTER TABLE `admin_authorize`
  ADD CONSTRAINT `admin_authorize_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id_users`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `admin_authorize_ibfk_3` FOREIGN KEY (`permission_id`) REFERENCES `admin_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `admin_module`
--
ALTER TABLE `admin_module`
  ADD CONSTRAINT `admin_module_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `admin_module_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `fk_module_id_on_permission` FOREIGN KEY (`module_id`) REFERENCES `admin_module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `investor`
--
ALTER TABLE `investor`
  ADD CONSTRAINT `investor_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE,
  ADD CONSTRAINT `investor_ibfk_2` FOREIGN KEY (`id_master`) REFERENCES `users` (`id_users`);

--
-- Ketidakleluasaan untuk tabel `laporan_omzet`
--
ALTER TABLE `laporan_omzet`
  ADD CONSTRAINT `laporan_omzet_ibfk_1` FOREIGN KEY (`id_outlet`) REFERENCES `outlet` (`id_outlet`);

--
-- Ketidakleluasaan untuk tabel `outlet`
--
ALTER TABLE `outlet`
  ADD CONSTRAINT `outlet_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE,
  ADD CONSTRAINT `outlet_ibfk_2` FOREIGN KEY (`id_investor`) REFERENCES `investor` (`id_investor`);

--
-- Ketidakleluasaan untuk tabel `rekap_bagi_hasil`
--
ALTER TABLE `rekap_bagi_hasil`
  ADD CONSTRAINT `rekap_bagi_hasil_ibfk_1` FOREIGN KEY (`id_investor`) REFERENCES `investor` (`id_investor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
