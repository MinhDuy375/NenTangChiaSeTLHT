-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: chiasetailieudb
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
  `loai` enum('tai_lieu','du_an') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `id_mon_hoc` (`id_mon_hoc`),
  KEY `id_danh_muc` (`id_danh_muc`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `bai_chia_se_ibfk_1` FOREIGN KEY (`id_mon_hoc`) REFERENCES `mon_hoc` (`id`),
  CONSTRAINT `bai_chia_se_ibfk_2` FOREIGN KEY (`id_danh_muc`) REFERENCES `danh_muc` (`id`),
  CONSTRAINT `bai_chia_se_ibfk_3` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bai_chia_se`
--

LOCK TABLES `bai_chia_se` WRITE;
/*!40000 ALTER TABLE `bai_chia_se` DISABLE KEYS */;
INSERT INTO `bai_chia_se` VALUES (1,'tai_lieu','Vở ghi Phân tích và thiết kế các hệ thống thông tin','Vở ghi Phân tích và thiết kế các hệ thống thông tin','uploads/tai_lieu/Vở ghi Phân tích và thiết kế các hệ thống thông tin_1757049216_05852f.docx',NULL,NULL,NULL,1,NULL,1,'2025-09-04 22:13:36',NULL,NULL),(2,'tai_lieu','Vở ghi Phân tích và thiết kế các hệ thống thông tin','Vở ghi Phân tích và thiết kế các hệ thống thông tin','uploads/tai_lieu/Vở ghi Phân tích và thiết kế các hệ thống thông tin_1757049455_341602.docx',NULL,NULL,NULL,1,NULL,1,'2025-09-04 22:17:35',NULL,NULL),(7,'du_an','Hệ thống Quản lý Sinh viên','Mã nguồn PHP + MySQL cho quản lý sinh viên. Hỗ trợ thêm/sửa/xóa sinh viên.',NULL,NULL,'https://github.com/example/qlsv','PHP, MySQL',NULL,5,1,'2025-09-08 02:31:16',NULL,NULL),(8,'du_an','Website Bán Hàng Mini','Frontend cơ bản với HTML, CSS, JS. Demo giao diện bán hàng.',NULL,'https://shop-demo.com','https://github.com/example/shop-fe','HTML, CSS, JS',NULL,4,1,'2025-09-08 02:31:16',NULL,NULL),(9,'du_an','Quản lý Thư viện Sách','Ứng dụng Java Swing quản lý sách, cho phép mượn/trả sách.',NULL,NULL,'https://github.com/example/java-lib','Java, Swing',NULL,7,1,'2025-09-08 02:31:16',NULL,NULL),(10,'du_an','Ứng dụng Chat Realtime','Chat realtime sử dụng NodeJS + Socket.io, có giao diện cơ bản.',NULL,'https://chat-demo.com','https://github.com/example/chat-realtime','NodeJS, Socket.io',NULL,5,1,'2025-09-08 02:31:16',NULL,NULL);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `binh_luan`
--

LOCK TABLES `binh_luan` WRITE;
/*!40000 ALTER TABLE `binh_luan` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `danh_muc`
--

LOCK TABLES `danh_muc` WRITE;
/*!40000 ALTER TABLE `danh_muc` DISABLE KEYS */;
INSERT INTO `danh_muc` VALUES (1,'Giáo trình','Tài liệu giảng dạy chính thức'),(2,'Bài tập','Các bài tập và lời giải'),(3,'Đề thi','Đề thi các kỳ trước'),(4,'Frontend','Mã nguồn giao diện người dùng'),(5,'Backend','Mã nguồn xử lý logic phía server'),(6,'Database','Mã nguồn về cơ sở dữ liệu'),(7,'Java','Mã nguồn Java'),(8,'PHP','Mã nguồn PHP'),(9,'Python','Mã nguồn Python');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mon_hoc`
--

LOCK TABLES `mon_hoc` WRITE;
/*!40000 ALTER TABLE `mon_hoc` DISABLE KEYS */;
INSERT INTO `mon_hoc` VALUES (1,'Cơ sở dữ liệu','Môn học về cơ sở dữ liệu quan hệ'),(2,'Lập trình Web','Môn học lập trình web PHP'),(3,'Mạng máy tính','Môn học về nguyên lý mạng');
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
  `ho_ten` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vai_tro` enum('quan_tri_vien','nguoi_dung','khach') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'khach',
  `ngay_tao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `trang_thai` enum('hoạt_dong','khoa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nguoi_dung`
--

LOCK TABLES `nguoi_dung` WRITE;
/*!40000 ALTER TABLE `nguoi_dung` DISABLE KEYS */;
INSERT INTO `nguoi_dung` VALUES (1,'admin','admin@example.com','21232f297a57a5a743894a0e4a801fc3','Quản trị viên','quan_tri_vien','2025-09-04 22:12:10','2025-09-04 22:12:10','hoạt_dong');
/*!40000 ALTER TABLE `nguoi_dung` ENABLE KEYS */;
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
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  KEY `id_bai_chia_se` (`id_bai_chia_se`),
  CONSTRAINT `thu_vien_ca_nhan_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`),
  CONSTRAINT `thu_vien_ca_nhan_ibfk_2` FOREIGN KEY (`id_bai_chia_se`) REFERENCES `bai_chia_se` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thu_vien_ca_nhan`
--

LOCK TABLES `thu_vien_ca_nhan` WRITE;
/*!40000 ALTER TABLE `thu_vien_ca_nhan` DISABLE KEYS */;
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
  UNIQUE KEY `unique_interaction` (`id_bai_chia_se`,`id_nguoi_dung`,`loai`),
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

-- Dump completed on 2025-09-17  7:20:38
