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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-30  9:49:37
