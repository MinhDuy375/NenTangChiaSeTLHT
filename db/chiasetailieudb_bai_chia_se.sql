-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: chiasetailieudb
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bai_chia_se`
--

DROP TABLE IF EXISTS `bai_chia_se`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bai_chia_se` (
  `id` int NOT NULL AUTO_INCREMENT,
  `loai` enum('tai_lieu','do_an','bai_viet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tieu_de` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` text COLLATE utf8mb4_unicode_ci,
  `file_upload` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cong_nghe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_mon_hoc` int DEFAULT NULL,
  `id_danh_muc` int DEFAULT NULL,
  `id_nguoi_dung` int NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL,
  `tom_tat` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `id_mon_hoc` (`id_mon_hoc`) /*!80000 INVISIBLE */,
  KEY `id_danh_muc` (`id_danh_muc`),
  KEY `bai_chia_se_ibfk_3` (`id_nguoi_dung`),
  CONSTRAINT `bai_chia_se_ibfk_1` FOREIGN KEY (`id_mon_hoc`) REFERENCES `mon_hoc` (`id`),
  CONSTRAINT `bai_chia_se_ibfk_2` FOREIGN KEY (`id_danh_muc`) REFERENCES `danh_muc` (`id`),
  CONSTRAINT `bai_chia_se_ibfk_3` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-28 11:01:43
