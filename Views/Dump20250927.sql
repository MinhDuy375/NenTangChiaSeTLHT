-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: chiasetailieudb
-- ------------------------------------------------------
-- Server version	8.0.41

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
  `loai` enum('tai_lieu','do_an','bai_viet') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tieu_de` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_upload` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cong_nghe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_mon_hoc` int DEFAULT NULL,
  `id_danh_muc` int DEFAULT NULL,
  `id_nguoi_dung` int NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL,
  `tom_tat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tong_reaction` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_mon_hoc` (`id_mon_hoc`) /*!80000 INVISIBLE */,
  KEY `id_danh_muc` (`id_danh_muc`),
  KEY `bai_chia_se_ibfk_3` (`id_nguoi_dung`),
  CONSTRAINT `bai_chia_se_ibfk_1` FOREIGN KEY (`id_mon_hoc`) REFERENCES `mon_hoc` (`id`),
  CONSTRAINT `bai_chia_se_ibfk_2` FOREIGN KEY (`id_danh_muc`) REFERENCES `danh_muc` (`id`),
  CONSTRAINT `bai_chia_se_ibfk_3` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bai_chia_se`
--

LOCK TABLES `bai_chia_se` WRITE;
/*!40000 ALTER TABLE `bai_chia_se` DISABLE KEYS */;
INSERT INTO `bai_chia_se` VALUES (11,'tai_lieu','Test Insert','Đây là bản ghi test trực tiếp','uploads/tai_lieu/test.pdf',NULL,NULL,NULL,1,NULL,7,'2025-09-26 12:11:56',NULL,NULL,0),(16,'tai_lieu','bài tap lớn vãi lồn','ưds','uploads/tai_lieu/Thuc-hanh-Quan-tri-Windows-Server-2012-v1-pdf_1758890528_036895.pdf',NULL,NULL,NULL,4,NULL,12,'2025-09-26 12:42:08',NULL,NULL,0),(24,'tai_lieu','123','123','uploads/tai_lieu/BTL-Kĩ-thuật-số_1758896571_f927e5.docx',NULL,NULL,NULL,2,NULL,12,'2025-09-26 14:22:51',NULL,NULL,0),(25,'tai_lieu','12312313','12312313','uploads/tai_lieu/1516-TB_Thu hồ sơ hưởng miễn giảm học phí, trợ cấp_đợt 2 2025_1758898482_1d82ca.pdf',NULL,NULL,NULL,5,NULL,12,'2025-09-26 14:54:42',NULL,NULL,0);
/*!40000 ALTER TABLE `bai_chia_se` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `binh_luan`
--

DROP TABLE IF EXISTS `binh_luan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `binh_luan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_bai_chia_se` int DEFAULT NULL,
  `id_nguoi_dung` int DEFAULT NULL,
  `noi_dung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ngay_tao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_bai_chia_se` (`id_bai_chia_se`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `binh_luan_ibfk_1` FOREIGN KEY (`id_bai_chia_se`) REFERENCES `bai_chia_se` (`id`),
  CONSTRAINT `binh_luan_ibfk_2` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `binh_luan`
--

LOCK TABLES `binh_luan` WRITE;
/*!40000 ALTER TABLE `binh_luan` DISABLE KEYS */;
INSERT INTO `binh_luan` VALUES (1,16,14,'1234','2025-09-27 04:33:16'),(2,16,14,'hay đấy','2025-09-27 04:33:34'),(4,16,14,'như cặc','2025-09-27 04:35:21'),(7,16,14,'như cặc','2025-09-27 04:36:11'),(8,16,14,'yêu em','2025-09-27 04:37:12'),(13,16,12,'m làm sao','2025-09-27 05:47:30');
/*!40000 ALTER TABLE `binh_luan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `danh_muc`
--

DROP TABLE IF EXISTS `danh_muc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `danh_muc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten_danh_muc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `danh_muc`
--

LOCK TABLES `danh_muc` WRITE;
/*!40000 ALTER TABLE `danh_muc` DISABLE KEYS */;
/*!40000 ALTER TABLE `danh_muc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mon_hoc`
--

DROP TABLE IF EXISTS `mon_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mon_hoc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten_mon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mon_hoc`
--

LOCK TABLES `mon_hoc` WRITE;
/*!40000 ALTER TABLE `mon_hoc` DISABLE KEYS */;
INSERT INTO `mon_hoc` VALUES (1,'Lập trình Java','Môn học về lập trình hướng đối tượng với Java'),(2,'Cơ sở dữ liệu','Môn học về SQL và thiết kế CSDL'),(3,'Mạng máy tính','Môn học về cấu trúc mạng, giao thức'),(4,'Hệ điều hành','Môn học về nguyên lý hệ điều hành'),(5,'Trí tuệ nhân tạo','Môn học về AI, Machine Learning');
/*!40000 ALTER TABLE `mon_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nguoi_dung`
--

DROP TABLE IF EXISTS `nguoi_dung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nguoi_dung` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten_dang_nhap` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mat_khau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ho_ten` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vai_tro` enum('quan_tri_vien','nguoi_dung','khach') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'khach',
  `ngay_tao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `trang_thai` enum('hoạt_dong','khoa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ma_xac_minh` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `da_xac_minh` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nguoi_dung`
--

LOCK TABLES `nguoi_dung` WRITE;
/*!40000 ALTER TABLE `nguoi_dung` DISABLE KEYS */;
INSERT INTO `nguoi_dung` VALUES (6,'1','doyousay987@gmail.com','$2y$10$l25YWVIJU2C82izuICUzruPeLL21YXU4BF0eT4h23jubpfV2Wd8WW',NULL,'1','nguoi_dung','2025-09-09 00:33:02','2025-09-15 14:12:21','khoa',NULL,0),(7,'2','doyousay321@gmail.com','$2y$10$W88HXMny0D8rns9R0WcO9uDu3GZCVUdhAYQPe2T06PgBEpDzGn7WG',NULL,'2','quan_tri_vien','2025-09-09 00:36:06','2025-09-09 00:36:09','hoạt_dong',NULL,0),(10,'12','thanh221220051@gmail.com','$2y$10$.dNsEmVb88EquS2WlvO8xe7/g/x94aRnCCgmKU769jvyMyw6PSweO',NULL,'12','nguoi_dung','2025-09-15 10:02:49','2025-09-15 10:04:03','khoa',NULL,0),(11,'123','thanh221220052@gmail.com','$2y$10$qxV8XUHwBbxexC04/LEEE.WJsKut7TEl5/wfpQGcdTX8RIoFFjsbG',NULL,'123','nguoi_dung','2025-09-15 12:52:44','2025-09-15 13:23:10','khoa',NULL,0),(12,'12345','thanh221220053@gmail.com','$2y$10$Oj2rxWzT.yw7WRvOG9jDkeiPgc/IObHpmt4sPuItO8xGKC3U0sKPm',NULL,'12345','nguoi_dung','2025-09-15 14:08:36','2025-09-27 05:47:21','hoạt_dong',NULL,0),(13,'123456789','thanh221220059@gmail.com','$2y$10$UL7XeQ3EV4RS1M/2xd9VY.RAYwhXUUWiw95w8ctOZQsOxKlHIz2DC',NULL,'minh sồu','nguoi_dung','2025-09-16 03:40:40','2025-09-16 03:57:42','hoạt_dong',NULL,0),(14,'yeuem','djkhanhbeo2005@gmail.com','$2y$10$D2GgdqB3dU1eHNcs8Y/fuOaO3ZIe3f.O8ERr4M5fZ/Mzh8O8/o6oS',NULL,'123','nguoi_dung','2025-09-27 04:01:24','2025-09-27 05:47:16','khoa',NULL,0);
/*!40000 ALTER TABLE `nguoi_dung` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reaction`
--

DROP TABLE IF EXISTS `reaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reaction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_bai_chia_se` int NOT NULL,
  `id_nguoi_dung` int NOT NULL,
  `loai_cam_xuc` enum('like','love','care','haha','wow','sad','angry') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_bai_chia_se` (`id_bai_chia_se`,`id_nguoi_dung`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `reaction_ibfk_1` FOREIGN KEY (`id_bai_chia_se`) REFERENCES `bai_chia_se` (`id`),
  CONSTRAINT `reaction_ibfk_2` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reaction`
--

LOCK TABLES `reaction` WRITE;
/*!40000 ALTER TABLE `reaction` DISABLE KEYS */;
INSERT INTO `reaction` VALUES (1,25,12,'haha'),(5,16,12,'sad'),(20,16,14,'haha'),(22,11,14,'love'),(31,11,12,'love');
/*!40000 ALTER TABLE `reaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thu_vien_ca_nhan`
--

DROP TABLE IF EXISTS `thu_vien_ca_nhan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thu_vien_ca_nhan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_nguoi_dung` int DEFAULT NULL,
  `id_bai_chia_se` int DEFAULT NULL,
  `loai` enum('tai_lieu','do_an') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_tao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_post` (`id_nguoi_dung`,`id_bai_chia_se`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  KEY `id_bai_chia_se` (`id_bai_chia_se`),
  CONSTRAINT `thu_vien_ca_nhan_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`),
  CONSTRAINT `thu_vien_ca_nhan_ibfk_2` FOREIGN KEY (`id_bai_chia_se`) REFERENCES `bai_chia_se` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thu_vien_ca_nhan`
--

LOCK TABLES `thu_vien_ca_nhan` WRITE;
/*!40000 ALTER TABLE `thu_vien_ca_nhan` DISABLE KEYS */;
INSERT INTO `thu_vien_ca_nhan` VALUES (10,12,16,'tai_lieu','2025-09-27 07:01:13'),(12,12,24,'tai_lieu','2025-09-27 07:04:17');
/*!40000 ALTER TABLE `thu_vien_ca_nhan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tuong_tac`
--

DROP TABLE IF EXISTS `tuong_tac`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tuong_tac` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_bai_chia_se` int DEFAULT NULL,
  `id_nguoi_dung` int DEFAULT NULL,
  `loai` enum('like','dislike','share') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_tao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_bai_chia_se` (`id_bai_chia_se`,`id_nguoi_dung`,`loai`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `tuong_tac_ibfk_1` FOREIGN KEY (`id_bai_chia_se`) REFERENCES `bai_chia_se` (`id`),
  CONSTRAINT `tuong_tac_ibfk_2` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tuong_tac`
--

LOCK TABLES `tuong_tac` WRITE;
/*!40000 ALTER TABLE `tuong_tac` DISABLE KEYS */;
/*!40000 ALTER TABLE `tuong_tac` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-27 14:15:50
