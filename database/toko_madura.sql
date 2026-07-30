-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: toko_madura
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_authorize`
--

DROP TABLE IF EXISTS `admin_authorize`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_authorize`
--

LOCK TABLES `admin_authorize` WRITE;
/*!40000 ALTER TABLE `admin_authorize` DISABLE KEYS */;
INSERT INTO `admin_authorize` VALUES (59,1,-1,'2026-07-29 13:25:25',NULL),(59,2,-1,'2026-07-29 13:25:25',NULL),(59,3,-1,'2026-07-29 13:25:25',NULL),(59,4,-1,'2026-07-29 13:25:25',NULL),(59,5,-1,'2026-07-29 13:25:25',NULL),(59,8,-1,'2026-07-29 13:25:25',NULL),(59,9,-1,'2026-07-29 13:25:25',NULL),(59,10,-1,'2026-07-29 13:25:25',NULL),(59,11,-1,'2026-07-29 13:25:25',NULL),(59,12,-1,'2026-07-29 13:25:25',NULL),(59,13,-1,'2026-07-29 13:25:25',NULL),(59,14,-1,'2026-07-29 13:25:25',NULL),(59,15,-1,'2026-07-29 13:25:25',NULL),(59,16,-1,'2026-07-29 13:25:25',NULL),(59,17,-1,'2026-07-29 13:25:25',NULL),(59,18,-1,'2026-07-29 13:25:25',NULL),(59,19,-1,'2026-07-29 13:25:25',NULL),(59,20,-1,'2026-07-29 13:25:25',NULL),(59,37,-1,'2026-07-29 13:25:25',NULL),(59,38,-1,'2026-07-29 13:25:25',NULL),(59,41,-1,'2026-07-29 13:25:25',NULL),(59,42,-1,'2026-07-29 13:25:25',NULL),(59,53,-1,'2026-07-29 13:25:25',NULL),(59,54,-1,'2026-07-29 13:25:26',NULL),(59,55,-1,'2026-07-29 13:25:26',NULL),(59,56,-1,'2026-07-29 13:25:26',NULL),(59,84,-1,'2026-07-29 16:31:32',NULL),(59,85,-1,'2026-07-29 16:31:32',NULL),(61,84,-1,'2026-07-29 16:31:32',NULL),(61,85,-1,'2026-07-29 16:31:32',NULL);
/*!40000 ALTER TABLE `admin_authorize` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_module`
--

DROP TABLE IF EXISTS `admin_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_module`
--

LOCK TABLES `admin_module` WRITE;
/*!40000 ALTER TABLE `admin_module` DISABLE KEYS */;
INSERT INTO `admin_module` VALUES (1,1,1,'Dashboard',-1,-1,'2026-07-23 12:19:52',NULL),(2,2,2,'Investor',-1,-1,'2026-07-23 12:19:52',NULL),(4,1,3,'Admin',-1,-1,'2026-07-23 12:19:52',NULL),(5,1,4,'group',-1,-1,'2026-07-23 12:19:52',NULL),(6,2,4,'module',-1,-1,'2026-07-23 12:19:52',NULL),(7,1,5,'Outlet',-1,-1,'2026-07-23 15:47:15','2026-07-28 01:49:08'),(19,1,12,'Master',-1,-1,'2026-07-28 15:00:51',NULL),(31,1,19,'biaya_langganan',-1,-1,'2026-07-29 16:31:32',NULL),(32,2,19,'rekening_bank',-1,-1,'2026-07-29 16:31:32',NULL);
/*!40000 ALTER TABLE `admin_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_module_group`
--

DROP TABLE IF EXISTS `admin_module_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_module_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order` int(10) unsigned NOT NULL,
  `group` varchar(255) NOT NULL,
  `type` enum('single','dropdown') NOT NULL DEFAULT 'single',
  `icon` text DEFAULT NULL,
  `min_level` int(11) NOT NULL DEFAULT 10,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_module_group`
--

LOCK TABLES `admin_module_group` WRITE;
/*!40000 ALTER TABLE `admin_module_group` DISABLE KEYS */;
INSERT INTO `admin_module_group` VALUES (1,1,'Dashboard','single','ti-home sidemenu-icon menu-icon',1),(2,3,'Investor','single','fa fa-handshake-o sidemenu-icon menu-icon',1),(3,8,'Admin','single','fa fa-user-secret sidemenu-icon menu-icon',1),(4,9,'Developer','dropdown','ti-panel sidemenu-icon menu-icon',1),(5,4,'Outlet','single','fa fa-building sidemenu-icon menu-icon',1),(12,2,'Master','single','fa fa-user-circle sidemenu-icon menu-icon',1),(19,6,'Pengaturan','dropdown','fa fa-cog sidemenu-icon menu-icon',1);
/*!40000 ALTER TABLE `admin_module_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_permissions`
--

DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (1,1,'view','View Dashboard','/dashboard','2026-07-23 12:19:52',NULL),(2,2,'view','View Investor List','/investor/view','2026-07-23 12:19:52',NULL),(3,2,'create','Create Investor','/investor/create','2026-07-23 12:19:52',NULL),(4,2,'update','Update Investor','/investor/update/*','2026-07-23 12:19:52',NULL),(5,2,'delete','Delete Investor','/investor/delete','2026-07-23 12:19:52',NULL),(8,4,'view','View Admin List','/admin/view','2026-07-23 12:19:52',NULL),(9,4,'create','Create Admin','/admin/create','2026-07-23 12:19:52',NULL),(10,4,'update','Update Admin','/admin/update/*','2026-07-23 12:19:52',NULL),(11,4,'update.permission','Manage Admin Permission','/admin/permission/*','2026-07-23 12:19:52',NULL),(12,4,'delete','Delete Admin','/admin/delete','2026-07-23 12:19:52',NULL),(13,5,'view','View Module Group','/developer/group/view','2026-07-23 12:19:52',NULL),(14,5,'create','Create Module Group','/developer/group/create','2026-07-23 12:19:52',NULL),(15,5,'update','Update Module Group','/developer/group/update','2026-07-23 12:19:52',NULL),(16,5,'delete','Delete Module Group','/developer/group/delete','2026-07-23 12:19:52',NULL),(17,6,'view','View Module List','/developer/module/view','2026-07-23 12:19:52',NULL),(18,6,'create','Create Module','/developer/module/create','2026-07-23 12:19:52',NULL),(19,6,'update','Update Module','/developer/module/update/*','2026-07-23 12:19:52',NULL),(20,6,'delete','Delete Module','/developer/module/delete','2026-07-23 12:19:52',NULL),(37,7,'view','View Outlet List','/outlet/view','2026-07-23 15:47:15','2026-07-23 09:07:36'),(38,7,'create','Create Outlet','/outlet/create','2026-07-23 15:47:15','2026-07-23 09:07:36'),(41,7,'update','Update Outlet','/outlet/update/*','2026-07-24 10:34:29',NULL),(42,7,'delete','Delete Outlet','/outlet/delete','2026-07-24 10:34:29',NULL),(53,19,'view','View Master List','/master/view','2026-07-28 15:01:00',NULL),(54,19,'create','Create Master','/master/create','2026-07-28 15:01:00',NULL),(55,19,'update','Update Master','/master/update/*','2026-07-28 15:01:00',NULL),(56,19,'delete','Delete Master','/master/delete','2026-07-28 15:01:00',NULL),(84,31,'view','Biaya Langganan Setting','/pengaturan/biaya_langganan','2026-07-29 16:31:32','2026-07-30 01:46:19'),(85,32,'view','Rekening Bank Setting','/pengaturan/rekening_bank','2026-07-29 16:31:32','2026-07-30 01:46:19');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investor`
--

DROP TABLE IF EXISTS `investor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investor`
--

LOCK TABLES `investor` WRITE;
/*!40000 ALTER TABLE `investor` DISABLE KEYS */;
INSERT INTO `investor` VALUES (19,62,61,'Tulangan','GMCF+C87, Kadelesan, Kedondong, Kec. Tulangan, Kabupaten Sidoarjo, Jawa Timur 61273',50.00,0.00,'2026-07-29 10:50:51');
/*!40000 ALTER TABLE `investor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laporan_omzet`
--

DROP TABLE IF EXISTS `laporan_omzet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laporan_omzet`
--

LOCK TABLES `laporan_omzet` WRITE;
/*!40000 ALTER TABLE `laporan_omzet` DISABLE KEYS */;
/*!40000 ALTER TABLE `laporan_omzet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outlet`
--

DROP TABLE IF EXISTS `outlet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outlet` (
  `id_outlet` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned NOT NULL,
  `id_investor` int(10) unsigned NOT NULL,
  `persentase_potongan` decimal(5,2) NOT NULL DEFAULT 10.00,
  `nama_outlet` varchar(100) NOT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `alamat_outlet` text DEFAULT NULL,
  `status` enum('pending','active','reject') NOT NULL DEFAULT 'active',
  `tipe_request` enum('baru','perpanjangan') NOT NULL DEFAULT 'baru',
  `tgl_jatuh_tempo` datetime DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outlet`
--

LOCK TABLES `outlet` WRITE;
/*!40000 ALTER TABLE `outlet` DISABLE KEYS */;
INSERT INTO `outlet` VALUES (45,97,19,10.00,'Toko Madura Merdeka','Bangkalan','Jl. Pemuda No. 12, Bangkalan','active','baru','2026-08-30 23:59:59','2026-05-15 09:00:00','2026-07-30 10:00:00',NULL,100000.00,'uploads/bukti_pembayaran/bukti_sample_merdeka.png',NULL,'2026-05-15 10:00:00'),(46,98,19,10.00,'Toko Madura Sumber Rejeki','Kamal','Jl. Trunojoyo No. 45, Kamal','active','baru','2026-08-02 23:59:59','2026-06-02 10:00:00','2026-07-02 11:00:00',NULL,100000.00,'uploads/bukti_pembayaran/bukti_sample_rejeki.png',NULL,'2026-06-02 11:00:00'),(47,99,19,10.00,'Toko Madura Melati Indah','Socah','Jl. Raya Socah No. 88, Socah','active','baru','2026-07-20 23:59:59','2026-05-20 08:00:00','2026-06-20 09:00:00',NULL,100000.00,'uploads/bukti_pembayaran/bukti_sample_melati.png',NULL,'2026-05-20 09:00:00'),(48,100,19,10.00,'Toko Madura Cahaya Jaya','Kamal','Jl. Pelabuhan No. 19, Kamal','pending','perpanjangan','2026-07-10 23:59:59','2026-07-30 11:00:00','2026-06-10 08:00:00',NULL,100000.00,'uploads/bukti_pembayaran/bukti_sample_cahaya.png',NULL,'2026-04-10 08:00:00'),(49,101,19,10.00,'Toko Madura Barokah Abadi','Bangkalan','Jl. Halim Perdana No. 7, Bangkalan','reject','baru',NULL,'2026-07-29 14:00:00',NULL,NULL,100000.00,'uploads/bukti_pembayaran/bukti_sample_barokah.png','Bukti transfer pembayaran pendaftaran buram dan tidak terbaca.',NULL),(50,102,19,10.00,'Toko Madura Mawar Asri','Bangkalan','Jl. Veteran No. 34, Bangkalan','pending','baru',NULL,'2026-07-30 11:30:00',NULL,NULL,100000.00,'uploads/bukti_pembayaran/bukti_sample_mawar.png',NULL,NULL);
/*!40000 ALTER TABLE `outlet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan_sistem`
--

DROP TABLE IF EXISTS `pengaturan_sistem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengaturan_sistem` (
  `id_pengaturan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_pengaturan` varchar(50) NOT NULL,
  `nilai` varchar(255) NOT NULL,
  PRIMARY KEY (`id_pengaturan`),
  UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan_sistem`
--

LOCK TABLES `pengaturan_sistem` WRITE;
/*!40000 ALTER TABLE `pengaturan_sistem` DISABLE KEYS */;
INSERT INTO `pengaturan_sistem` VALUES (1,'potongan_global','10.00'),(2,'biaya_langganan_outlet','100000'),(3,'bank_nama','BRI'),(4,'bank_no_rekening','987-654-321'),(5,'bank_atas_nama','Anonymous');
/*!40000 ALTER TABLE `pengaturan_sistem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rekap_bagi_hasil`
--

DROP TABLE IF EXISTS `rekap_bagi_hasil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rekap_bagi_hasil`
--

LOCK TABLES `rekap_bagi_hasil` WRITE;
/*!40000 ALTER TABLE `rekap_bagi_hasil` DISABLE KEYS */;
/*!40000 ALTER TABLE `rekap_bagi_hasil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_users` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('programmer','master','investor','outlet') NOT NULL DEFAULT 'outlet',
  PRIMARY KEY (`id_users`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (59,'Anonymous','admin','0123456789','$2y$10$bEl1pyxvEEHdLPS917gqlukvAuW0Sjo8dcebpWny4W9.f935YC59C','programmer'),(61,'Riski Ardhika','master','0123456789','$2y$10$bEl1pyxvEEHdLPS917gqlukvAuW0Sjo8dcebpWny4W9.f935YC59C','master'),(62,'M. Fahrul Alfanani','investor','0987654321','$2y$10$bEl1pyxvEEHdLPS917gqlukvAuW0Sjo8dcebpWny4W9.f935YC59C','investor'),(97,'Kasir Toko Merdeka','toko1','08123456789','$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi','outlet'),(98,'Kasir Toko Rejeki','toko2','08123456789','$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi','outlet'),(99,'Kasir Toko Melati','toko3','08123456789','$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi','outlet'),(100,'Kasir Toko Cahaya','toko4','08123456789','$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi','outlet'),(101,'Kasir Toko Barokah','toko5','08123456789','$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi','outlet'),(102,'Kasir Toko Mawar','toko6','08123456789','$2y$10$Q8cKpe6oLgNbvd6uKW/KieBficJ..tUBUaQdMobXBUQ/3PoOymQZi','outlet');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-30 13:39:56
