-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 23, 2025 at 09:47 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `cinema_booking`
--
CREATE DATABASE IF NOT EXISTS `cinema_booking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `cinema_booking`;

--
-- Table structure for table `cache`
--
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `cache_locks`
--
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `combo`
--
CREATE TABLE IF NOT EXISTS `combo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten` varchar(100) DEFAULT NULL,
  `mo_ta` text,
  `gia` decimal(10,2) DEFAULT NULL,
  `gia_khuyen_mai` decimal(10,2) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `khuyen_mai`
--
CREATE TABLE IF NOT EXISTS `khuyen_mai` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ma_km` varchar(50) DEFAULT NULL,
  `mo_ta` text,
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `gia_tri_giam` decimal(10,2) DEFAULT NULL,
  `loai_giam` enum('phantram','codinh') NOT NULL DEFAULT 'phantram',
  `dieu_kien` varchar(255) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_km` (`ma_km`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `loai_ghe`
--
CREATE TABLE IF NOT EXISTS `loai_ghe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten_loai` varchar(50) DEFAULT NULL,
  `he_so_gia` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `phong_chieu`
--
CREATE TABLE IF NOT EXISTS `phong_chieu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten_phong` varchar(100) DEFAULT NULL,
  `so_hang` int DEFAULT NULL,
  `so_cot` int DEFAULT NULL,
  `suc_chua` int DEFAULT NULL,
  `mo_ta` text,
  `trang_thai` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `phim`
--
CREATE TABLE IF NOT EXISTS `phim` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten_phim` varchar(255) NOT NULL,
  `ten_goc` varchar(255) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `trailer` varchar(255) DEFAULT NULL,
  `dao_dien` varchar(100) DEFAULT NULL,
  `dien_vien` text,
  `the_loai` varchar(255) DEFAULT NULL,
  `quoc_gia` varchar(100) DEFAULT NULL,
  `ngon_ngu` varchar(100) DEFAULT NULL,
  `do_tuoi` varchar(10) DEFAULT NULL,
  `do_dai` int DEFAULT NULL,
  `ngay_khoi_chieu` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `mo_ta` text,
  `diem_danh_gia` decimal(3,1) DEFAULT NULL,
  `so_luot_danh_gia` int DEFAULT 0,
  `trang_thai` enum('sap_chieu','dang_chieu','ngung_chieu') DEFAULT 'sap_chieu',
  `id_phong` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_phong` (`id_phong`),
  CONSTRAINT `phim_ibfk_phong` FOREIGN KEY (`id_phong`) REFERENCES `phong_chieu` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `vai_tro`
--
CREATE TABLE IF NOT EXISTS `vai_tro` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ten` varchar(100) NOT NULL,
  `mo_ta` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vai_tro`
--
INSERT INTO `vai_tro` (`id`, `ten`, `mo_ta`) VALUES
(1, 'admin', 'Quản trị'),
(2, 'staff', 'Nhân viên'),
(3, 'user', 'Người dùng');

--
-- Table structure for table `nguoi_dung`
--
CREATE TABLE IF NOT EXISTS `nguoi_dung` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` tinyint(1) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `dia_chi` text,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `id_vai_tro` int DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_vai_tro` (`id_vai_tro`),
  CONSTRAINT `nguoi_dung_ibfk_1` FOREIGN KEY (`id_vai_tro`) REFERENCES `vai_tro` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nguoi_dung`
--
INSERT INTO `nguoi_dung` (`id`, `ho_ten`, `email`, `mat_khau`, `ngay_sinh`, `gioi_tinh`, `sdt`, `dia_chi`, `hinh_anh`, `id_vai_tro`, `trang_thai`, `created_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$12$CuiECosQUFsjJiALG/crsuWDptAwuCET9oK0r0fsoVZKVyDoqKuQK', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-10-09 06:05:41'),
(2, 'Staff', 'staff@example.com', '$2y$12$1yt6slsE6i32Cq5215Pvp.V3QKoPBVtNpu9tbvpHt813xLE0HSJ6q', NULL, NULL, NULL, NULL, NULL, 2, 1, '2025-10-09 06:05:41');

--
-- Table structure for table `diem_thanh_vien`
--
CREATE TABLE IF NOT EXISTS `diem_thanh_vien` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_nguoi_dung` int DEFAULT NULL,
  `tong_diem` int DEFAULT '0',
  `ngay_het_han` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `diem_thanh_vien_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `hang_thanh_vien`
--
CREATE TABLE IF NOT EXISTS `hang_thanh_vien` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_nguoi_dung` int DEFAULT NULL,
  `ten_hang` varchar(50) DEFAULT NULL,
  `uu_dai` text,
  `diem_toi_thieu` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `hang_thanh_vien_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `lich_su_diem`
--
CREATE TABLE IF NOT EXISTS `lich_su_diem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_nguoi_dung` int DEFAULT NULL,
  `ly_do` varchar(255) DEFAULT NULL,
  `diem_thay_doi` int DEFAULT NULL,
  `ngay` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  CONSTRAINT `lich_su_diem_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `suat_chieu`
--
CREATE TABLE IF NOT EXISTS `suat_chieu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_phim` int DEFAULT NULL,
  `id_phong` int DEFAULT NULL,
  `thoi_gian_bat_dau` datetime DEFAULT NULL,
  `thoi_gian_ket_thuc` datetime DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_phim` (`id_phim`),
  KEY `id_phong` (`id_phong`),
  CONSTRAINT `suat_chieu_ibfk_1` FOREIGN KEY (`id_phim`) REFERENCES `phim` (`id`) ON DELETE CASCADE,
  CONSTRAINT `suat_chieu_ibfk_2` FOREIGN KEY (`id_phong`) REFERENCES `phong_chieu` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `dat_ve`
--
CREATE TABLE IF NOT EXISTS `dat_ve` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_nguoi_dung` int DEFAULT NULL,
  `id_suat_chieu` int DEFAULT NULL,
  `id_khuyen_mai` int DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_nguoi_dung` (`id_nguoi_dung`),
  KEY `id_suat_chieu` (`id_suat_chieu`),
  KEY `id_khuyen_mai` (`id_khuyen_mai`),
  CONSTRAINT `dat_ve_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dat_ve_ibfk_2` FOREIGN KEY (`id_suat_chieu`) REFERENCES `suat_chieu` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dat_ve_ibfk_3` FOREIGN KEY (`id_khuyen_mai`) REFERENCES `khuyen_mai` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `chi_tiet_combo`
--
CREATE TABLE IF NOT EXISTS `chi_tiet_combo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_dat_ve` int DEFAULT NULL,
  `id_combo` int DEFAULT NULL,
  `so_luong` int DEFAULT NULL,
  `gia_khuyen_mai` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_dat_ve` (`id_dat_ve`),
  KEY `id_combo` (`id_combo`),
  CONSTRAINT `chi_tiet_combo_ibfk_1` FOREIGN KEY (`id_dat_ve`) REFERENCES `dat_ve` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chi_tiet_combo_ibfk_2` FOREIGN KEY (`id_combo`) REFERENCES `combo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `ghe`
--
CREATE TABLE IF NOT EXISTS `ghe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_phong` int DEFAULT NULL,
  `so_ghe` varchar(10) DEFAULT NULL,
  `so_hang` int DEFAULT NULL,
  `id_loai` int DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_phong` (`id_phong`),
  KEY `id_loai` (`id_loai`),
  CONSTRAINT `ghe_ibfk_1` FOREIGN KEY (`id_phong`) REFERENCES `phong_chieu` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ghe_ibfk_2` FOREIGN KEY (`id_loai`) REFERENCES `loai_ghe` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `chi_tiet_dat_ve`
--
CREATE TABLE IF NOT EXISTS `chi_tiet_dat_ve` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_dat_ve` int DEFAULT NULL,
  `id_ghe` int DEFAULT NULL,
  `gia` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_dat_ve` (`id_dat_ve`),
  KEY `id_ghe` (`id_ghe`),
  CONSTRAINT `chi_tiet_dat_ve_ibfk_1` FOREIGN KEY (`id_dat_ve`) REFERENCES `dat_ve` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chi_tiet_dat_ve_ibfk_2` FOREIGN KEY (`id_ghe`) REFERENCES `ghe` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `thanh_toan`
--
CREATE TABLE IF NOT EXISTS `thanh_toan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_dat_ve` int DEFAULT NULL,
  `phuong_thuc` varchar(50) DEFAULT NULL,
  `so_tien` decimal(10,2) DEFAULT NULL,
  `ma_giao_dich` varchar(100) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT NULL,
  `thoi_gian` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_dat_ve` (`id_dat_ve`),
  CONSTRAINT `thanh_toan_ibfk_1` FOREIGN KEY (`id_dat_ve`) REFERENCES `dat_ve` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `failed_jobs`
--
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `connection` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `queue` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `jobs`
--
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `job_batches`
--
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_0900_ai_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `migrations`
--
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migrations`
--
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_09_100000_add_loai_giam_to_khuyen_mai_table', 2),
(5, '2025_10_23_100000_add_id_phong_to_phim_table', 3);

--
-- Table structure for table `password_reset_tokens`
--
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `sessions`
--
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_0900_ai_ci,
  `payload` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessions`
--
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('gsrlFSLI3jbinpiwl6i2EpRzyj7O7Vn6QpGcJ1le', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNVZTZEdaSXNKZFhqOFp5d1JPRFcxZzM4Y3k1aDBSeHJLcjhNOVlqcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759761019),
('QfTxVL3eM9ZMSfFsguM6YhCHgX5D8cYf53ixTiyo', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUlRmRjZxd0lvSzdXQUVmRWJKcEZFVEUza3piWk5LSzZRbEx1N1M2TiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9raHV5ZW5tYWkiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1759994184);

--
-- Table structure for table `users`
--
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;