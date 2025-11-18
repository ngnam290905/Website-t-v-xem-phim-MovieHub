-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th10 17, 2025 lúc 10:22 PM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `cinema_booking`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_combo_mon`
--

CREATE TABLE `chi_tiet_combo_mon` (
  `id` int NOT NULL,
  `id_combo` int NOT NULL,
  `id_mon_an` int NOT NULL,
  `so_luong` int NOT NULL DEFAULT '1',
  `kich_co` varchar(50) DEFAULT NULL COMMENT 'Lớn, Nhỏ, 32oz, VIP...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_combo_mon`
--

INSERT INTO `chi_tiet_combo_mon` (`id`, `id_combo`, `id_mon_an`, `so_luong`, `kich_co`) VALUES
(1, 1, 1, 1, 'Lớn'),
(2, 1, 2, 2, '32oz'),
(3, 1, 3, 1, 'Vừa');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_dat_ve`
--

CREATE TABLE `chi_tiet_dat_ve` (
  `id` int NOT NULL,
  `id_dat_ve` int DEFAULT NULL,
  `id_ghe` int DEFAULT NULL,
  `gia` decimal(10,2) DEFAULT NULL,
  `gia_ve` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_dat_ve`
--

INSERT INTO `chi_tiet_dat_ve` (`id`, `id_dat_ve`, `id_ghe`, `gia`, `gia_ve`) VALUES
(17, 4, 2999, 120000.00, NULL),
(18, 4, 3000, 120000.00, NULL),
(19, 5, 3028, 120000.00, NULL),
(20, 5, 3029, 120000.00, NULL),
(21, 6, 3179, 120000.00, NULL),
(22, 6, 3180, 120000.00, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_dat_ve_combo`
--

CREATE TABLE `chi_tiet_dat_ve_combo` (
  `id` int NOT NULL,
  `id_dat_ve` int DEFAULT NULL,
  `id_combo` int DEFAULT NULL,
  `so_luong` int DEFAULT NULL,
  `gia_ap_dung` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `combo`
--

CREATE TABLE `combo` (
  `id` int NOT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `mo_ta` text,
  `gia` decimal(10,2) DEFAULT NULL,
  `gia_goc` decimal(10,2) DEFAULT NULL,
  `anh` varchar(500) DEFAULT NULL,
  `combo_noi_bat` tinyint(1) DEFAULT '0',
  `so_luong_toi_da` int DEFAULT NULL,
  `yeu_cau_it_nhat_ve` int DEFAULT NULL,
  `ngay_bat_dau` datetime DEFAULT NULL,
  `ngay_ket_thuc` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `combo`
--

INSERT INTO `combo` (`id`, `ten`, `mo_ta`, `gia`, `gia_goc`, `anh`, `combo_noi_bat`, `so_luong_toi_da`, `yeu_cau_it_nhat_ve`, `ngay_bat_dau`, `ngay_ket_thuc`, `created_at`, `updated_at`, `created_by`, `updated_by`, `trang_thai`) VALUES
(1, 'Combo Cặp Đôi', '1 Bắp lớn + 2 Nước + 1 Snack', 199000.00, 250000.00, '/images/combo-couple.jpg', 1, 3, 2, '2025-10-01 00:00:00', '2025-12-31 23:59:59', '2025-10-30 08:37:01', '2025-10-30 08:37:01', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dat_ve`
--

CREATE TABLE `dat_ve` (
  `id` int NOT NULL,
  `id_nguoi_dung` int DEFAULT NULL,
  `id_suat_chieu` int DEFAULT NULL,
  `id_khuyen_mai` int DEFAULT NULL,
  `tong_tien_goc` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tổng tiền trước giảm giá',
  `tien_giam_khuyen_mai` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tiền giảm từ mã khuyến mãi',
  `tien_giam_thanh_vien` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tiền giảm từ hạng thành viên',
  `diem_su_dung` int NOT NULL DEFAULT '0' COMMENT 'Điểm đã sử dụng',
  `tien_giam_diem` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tiền giảm từ điểm (100 điểm = 10.000đ)',
  `diem_tich_luy` int NOT NULL DEFAULT '0' COMMENT 'Điểm tích lũy được từ đơn',
  `tong_tien` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tổng tiền phải thanh toán',
  `trang_thai` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `dat_ve`
--

INSERT INTO `dat_ve` (`id`, `id_nguoi_dung`, `id_suat_chieu`, `id_khuyen_mai`, `tong_tien_goc`, `tien_giam_khuyen_mai`, `tien_giam_thanh_vien`, `diem_su_dung`, `tien_giam_diem`, `diem_tich_luy`, `tong_tien`, `trang_thai`, `created_at`) VALUES
(3, 6, 3978, 1, 0.00, 0.00, 0.00, 0, 0.00, 0, 0.00, 3, '2025-11-17 21:01:03'),
(4, 6, 3996, NULL, 0.00, 0.00, 0.00, 0, 0.00, 0, 240000.00, 1, '2025-11-17 21:57:33'),
(5, 6, 3985, NULL, 0.00, 0.00, 0.00, 0, 0.00, 0, 240000.00, 1, '2025-11-17 21:58:58'),
(6, 6, 4004, NULL, 0.00, 0.00, 0.00, 0, 0.00, 0, 240000.00, 1, '2025-11-17 22:03:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `diem_thanh_vien`
--

CREATE TABLE `diem_thanh_vien` (
  `id` int NOT NULL,
  `id_nguoi_dung` int DEFAULT NULL,
  `tong_diem` int DEFAULT '0',
  `ngay_het_han` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ghe`
--

CREATE TABLE `ghe` (
  `id` int NOT NULL,
  `id_phong` int DEFAULT NULL,
  `so_ghe` varchar(10) DEFAULT NULL,
  `so_hang` int DEFAULT NULL,
  `id_loai` int DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `ghe`
--

INSERT INTO `ghe` (`id`, `id_phong`, `so_ghe`, `so_hang`, `id_loai`, `trang_thai`) VALUES
(2961, 28, 'A1', 1, 1, 1),
(2962, 28, 'A2', 1, 1, 1),
(2963, 28, 'A3', 1, 1, 1),
(2964, 28, 'A4', 1, 1, 1),
(2965, 28, 'A5', 1, 1, 1),
(2966, 28, 'A6', 1, 1, 1),
(2967, 28, 'A7', 1, 1, 1),
(2968, 28, 'A8', 1, 1, 1),
(2969, 28, 'A9', 1, 1, 1),
(2970, 28, 'A10', 1, 1, 1),
(2971, 28, 'A11', 1, 1, 1),
(2972, 28, 'A12', 1, 1, 1),
(2973, 28, 'A13', 1, 1, 1),
(2974, 28, 'A14', 1, 1, 1),
(2975, 28, 'A15', 1, 1, 1),
(2976, 28, 'B1', 2, 1, 1),
(2977, 28, 'B2', 2, 1, 1),
(2978, 28, 'B3', 2, 1, 1),
(2979, 28, 'B4', 2, 1, 1),
(2980, 28, 'B5', 2, 1, 1),
(2981, 28, 'B6', 2, 1, 1),
(2982, 28, 'B7', 2, 1, 1),
(2983, 28, 'B8', 2, 1, 1),
(2984, 28, 'B9', 2, 1, 1),
(2985, 28, 'B10', 2, 1, 1),
(2986, 28, 'B11', 2, 1, 1),
(2987, 28, 'B12', 2, 1, 1),
(2988, 28, 'B13', 2, 1, 1),
(2989, 28, 'B14', 2, 1, 1),
(2990, 28, 'B15', 2, 1, 1),
(2991, 28, 'C1', 3, 1, 1),
(2992, 28, 'C2', 3, 1, 1),
(2993, 28, 'C3', 3, 2, 1),
(2994, 28, 'C4', 3, 2, 1),
(2995, 28, 'C5', 3, 2, 1),
(2996, 28, 'C6', 3, 2, 1),
(2997, 28, 'C7', 3, 2, 1),
(2998, 28, 'C8', 3, 2, 1),
(2999, 28, 'C9', 3, 2, 1),
(3000, 28, 'C10', 3, 2, 1),
(3001, 28, 'C11', 3, 2, 1),
(3002, 28, 'C12', 3, 2, 1),
(3003, 28, 'C13', 3, 2, 1),
(3004, 28, 'C14', 3, 1, 1),
(3005, 28, 'C15', 3, 1, 1),
(3006, 28, 'D1', 4, 1, 1),
(3007, 28, 'D2', 4, 1, 1),
(3008, 28, 'D3', 4, 2, 1),
(3009, 28, 'D4', 4, 2, 1),
(3010, 28, 'D5', 4, 2, 1),
(3011, 28, 'D6', 4, 2, 1),
(3012, 28, 'D7', 4, 2, 1),
(3013, 28, 'D8', 4, 2, 1),
(3014, 28, 'D9', 4, 2, 1),
(3015, 28, 'D10', 4, 2, 1),
(3016, 28, 'D11', 4, 2, 1),
(3017, 28, 'D12', 4, 2, 1),
(3018, 28, 'D13', 4, 2, 1),
(3019, 28, 'D14', 4, 1, 1),
(3020, 28, 'D15', 4, 1, 1),
(3021, 28, 'E1', 5, 1, 1),
(3022, 28, 'E2', 5, 1, 1),
(3023, 28, 'E3', 5, 2, 1),
(3024, 28, 'E4', 5, 2, 1),
(3025, 28, 'E5', 5, 2, 1),
(3026, 28, 'E6', 5, 2, 1),
(3027, 28, 'E7', 5, 2, 1),
(3028, 28, 'E8', 5, 2, 1),
(3029, 28, 'E9', 5, 2, 1),
(3030, 28, 'E10', 5, 2, 1),
(3031, 28, 'E11', 5, 2, 1),
(3032, 28, 'E12', 5, 2, 1),
(3033, 28, 'E13', 5, 2, 1),
(3034, 28, 'E14', 5, 1, 1),
(3035, 28, 'E15', 5, 1, 1),
(3036, 28, 'F1', 6, 1, 1),
(3037, 28, 'F2', 6, 1, 1),
(3038, 28, 'F3', 6, 2, 1),
(3039, 28, 'F4', 6, 2, 1),
(3040, 28, 'F5', 6, 2, 1),
(3041, 28, 'F6', 6, 2, 1),
(3042, 28, 'F7', 6, 2, 1),
(3043, 28, 'F8', 6, 2, 1),
(3044, 28, 'F9', 6, 2, 1),
(3045, 28, 'F10', 6, 2, 1),
(3046, 28, 'F11', 6, 2, 1),
(3047, 28, 'F12', 6, 2, 1),
(3048, 28, 'F13', 6, 2, 1),
(3049, 28, 'F14', 6, 1, 1),
(3050, 28, 'F15', 6, 1, 1),
(3051, 28, 'G1', 7, 1, 1),
(3052, 28, 'G2', 7, 1, 1),
(3053, 28, 'G3', 7, 3, 1),
(3054, 28, 'G4', 7, 3, 1),
(3055, 28, 'G5', 7, 3, 1),
(3056, 28, 'G6', 7, 3, 1),
(3057, 28, 'G7', 7, 3, 1),
(3058, 28, 'G8', 7, 3, 1),
(3059, 28, 'G9', 7, 3, 1),
(3060, 28, 'G10', 7, 3, 1),
(3061, 28, 'G11', 7, 3, 1),
(3062, 28, 'G12', 7, 3, 1),
(3063, 28, 'G13', 7, 3, 1),
(3064, 28, 'G14', 7, 1, 1),
(3065, 28, 'G15', 7, 1, 1),
(3066, 28, 'H1', 8, 1, 1),
(3067, 28, 'H2', 8, 1, 1),
(3068, 28, 'H3', 8, 3, 1),
(3069, 28, 'H4', 8, 3, 1),
(3070, 28, 'H5', 8, 3, 1),
(3071, 28, 'H6', 8, 3, 1),
(3072, 28, 'H7', 8, 3, 1),
(3073, 28, 'H8', 8, 3, 1),
(3074, 28, 'H9', 8, 3, 1),
(3075, 28, 'H10', 8, 3, 1),
(3076, 28, 'H11', 8, 3, 1),
(3077, 28, 'H12', 8, 3, 1),
(3078, 28, 'H13', 8, 3, 1),
(3079, 28, 'H14', 8, 1, 1),
(3080, 28, 'H15', 8, 1, 1),
(3081, 28, 'I1', 9, 1, 1),
(3082, 28, 'I2', 9, 1, 1),
(3083, 28, 'I3', 9, 1, 1),
(3084, 28, 'I4', 9, 1, 1),
(3085, 28, 'I5', 9, 1, 1),
(3086, 28, 'I6', 9, 1, 1),
(3087, 28, 'I7', 9, 1, 1),
(3088, 28, 'I8', 9, 1, 1),
(3089, 28, 'I9', 9, 1, 1),
(3090, 28, 'I10', 9, 1, 1),
(3091, 28, 'I11', 9, 1, 1),
(3092, 28, 'I12', 9, 1, 1),
(3093, 28, 'I13', 9, 1, 1),
(3094, 28, 'I14', 9, 1, 1),
(3095, 28, 'I15', 9, 1, 1),
(3096, 28, 'J1', 10, 1, 1),
(3097, 28, 'J2', 10, 1, 1),
(3098, 28, 'J3', 10, 1, 1),
(3099, 28, 'J4', 10, 1, 1),
(3100, 28, 'J5', 10, 1, 1),
(3101, 28, 'J6', 10, 1, 1),
(3102, 28, 'J7', 10, 1, 1),
(3103, 28, 'J8', 10, 1, 1),
(3104, 28, 'J9', 10, 1, 1),
(3105, 28, 'J10', 10, 1, 1),
(3106, 28, 'J11', 10, 1, 1),
(3107, 28, 'J12', 10, 1, 1),
(3108, 28, 'J13', 10, 1, 1),
(3109, 28, 'J14', 10, 1, 1),
(3110, 28, 'J15', 10, 1, 1),
(3111, 29, 'A1', 1, 1, 1),
(3112, 29, 'A2', 1, 1, 1),
(3113, 29, 'A3', 1, 1, 1),
(3114, 29, 'A4', 1, 1, 1),
(3115, 29, 'A5', 1, 1, 1),
(3116, 29, 'A6', 1, 1, 1),
(3117, 29, 'A7', 1, 1, 1),
(3118, 29, 'A8', 1, 1, 1),
(3119, 29, 'A9', 1, 1, 1),
(3120, 29, 'A10', 1, 1, 1),
(3121, 29, 'A11', 1, 1, 1),
(3122, 29, 'A12', 1, 1, 1),
(3123, 29, 'A13', 1, 1, 1),
(3124, 29, 'A14', 1, 1, 1),
(3125, 29, 'A15', 1, 1, 1),
(3126, 29, 'B1', 2, 1, 1),
(3127, 29, 'B2', 2, 1, 1),
(3128, 29, 'B3', 2, 1, 1),
(3129, 29, 'B4', 2, 1, 1),
(3130, 29, 'B5', 2, 1, 1),
(3131, 29, 'B6', 2, 1, 1),
(3132, 29, 'B7', 2, 1, 1),
(3133, 29, 'B8', 2, 1, 1),
(3134, 29, 'B9', 2, 1, 1),
(3135, 29, 'B10', 2, 1, 1),
(3136, 29, 'B11', 2, 1, 1),
(3137, 29, 'B12', 2, 1, 1),
(3138, 29, 'B13', 2, 1, 1),
(3139, 29, 'B14', 2, 1, 1),
(3140, 29, 'B15', 2, 1, 1),
(3141, 29, 'C1', 3, 2, 1),
(3142, 29, 'C2', 3, 2, 1),
(3143, 29, 'C3', 3, 2, 1),
(3144, 29, 'C4', 3, 2, 1),
(3145, 29, 'C5', 3, 2, 1),
(3146, 29, 'C6', 3, 2, 1),
(3147, 29, 'C7', 3, 2, 1),
(3148, 29, 'C8', 3, 2, 1),
(3149, 29, 'C9', 3, 2, 1),
(3150, 29, 'C10', 3, 2, 1),
(3151, 29, 'C11', 3, 2, 1),
(3152, 29, 'C12', 3, 2, 1),
(3153, 29, 'C13', 3, 2, 1),
(3154, 29, 'C14', 3, 2, 1),
(3155, 29, 'C15', 3, 2, 1),
(3156, 29, 'D1', 4, 2, 1),
(3157, 29, 'D2', 4, 2, 1),
(3158, 29, 'D3', 4, 2, 1),
(3159, 29, 'D4', 4, 2, 1),
(3160, 29, 'D5', 4, 2, 1),
(3161, 29, 'D6', 4, 2, 1),
(3162, 29, 'D7', 4, 2, 1),
(3163, 29, 'D8', 4, 2, 1),
(3164, 29, 'D9', 4, 2, 1),
(3165, 29, 'D10', 4, 2, 1),
(3166, 29, 'D11', 4, 2, 1),
(3167, 29, 'D12', 4, 2, 1),
(3168, 29, 'D13', 4, 2, 1),
(3169, 29, 'D14', 4, 2, 1),
(3170, 29, 'D15', 4, 2, 1),
(3171, 29, 'E1', 5, 2, 1),
(3172, 29, 'E2', 5, 2, 1),
(3173, 29, 'E3', 5, 2, 1),
(3174, 29, 'E4', 5, 2, 1),
(3175, 29, 'E5', 5, 2, 1),
(3176, 29, 'E6', 5, 2, 1),
(3177, 29, 'E7', 5, 2, 1),
(3178, 29, 'E8', 5, 2, 1),
(3179, 29, 'E9', 5, 2, 1),
(3180, 29, 'E10', 5, 2, 1),
(3181, 29, 'E11', 5, 2, 1),
(3182, 29, 'E12', 5, 2, 1),
(3183, 29, 'E13', 5, 2, 1),
(3184, 29, 'E14', 5, 2, 1),
(3185, 29, 'E15', 5, 2, 1),
(3186, 29, 'F1', 6, 3, 1),
(3187, 29, 'F2', 6, 3, 1),
(3188, 29, 'F3', 6, 3, 1),
(3189, 29, 'F4', 6, 3, 1),
(3190, 29, 'F5', 6, 3, 1),
(3191, 29, 'F6', 6, 3, 1),
(3192, 29, 'F7', 6, 3, 1),
(3193, 29, 'F8', 6, 3, 1),
(3194, 29, 'F9', 6, 3, 1),
(3195, 29, 'F10', 6, 3, 1),
(3196, 29, 'F11', 6, 3, 1),
(3197, 29, 'F12', 6, 3, 1),
(3198, 29, 'F13', 6, 3, 1),
(3199, 29, 'F14', 6, 3, 1),
(3200, 29, 'F15', 6, 3, 1),
(3201, 29, 'G1', 7, 3, 1),
(3202, 29, 'G2', 7, 3, 1),
(3203, 29, 'G3', 7, 3, 1),
(3204, 29, 'G4', 7, 3, 1),
(3205, 29, 'G5', 7, 3, 1),
(3206, 29, 'G6', 7, 3, 1),
(3207, 29, 'G7', 7, 3, 1),
(3208, 29, 'G8', 7, 3, 1),
(3209, 29, 'G9', 7, 3, 1),
(3210, 29, 'G10', 7, 3, 1),
(3211, 29, 'G11', 7, 3, 1),
(3212, 29, 'G12', 7, 3, 1),
(3213, 29, 'G13', 7, 3, 1),
(3214, 29, 'G14', 7, 3, 1),
(3215, 29, 'G15', 7, 3, 1),
(3216, 29, 'H1', 8, 3, 1),
(3217, 29, 'H2', 8, 3, 1),
(3218, 29, 'H3', 8, 3, 1),
(3219, 29, 'H4', 8, 3, 1),
(3220, 29, 'H5', 8, 3, 1),
(3221, 29, 'H6', 8, 3, 1),
(3222, 29, 'H7', 8, 3, 1),
(3223, 29, 'H8', 8, 3, 1),
(3224, 29, 'H9', 8, 3, 1),
(3225, 29, 'H10', 8, 3, 1),
(3226, 29, 'H11', 8, 3, 1),
(3227, 29, 'H12', 8, 3, 1),
(3228, 29, 'H13', 8, 3, 1),
(3229, 29, 'H14', 8, 3, 1),
(3230, 29, 'H15', 8, 3, 1),
(3231, 29, 'I1', 9, 1, 1),
(3232, 29, 'I2', 9, 1, 1),
(3233, 29, 'I3', 9, 1, 1),
(3234, 29, 'I4', 9, 1, 1),
(3235, 29, 'I5', 9, 1, 1),
(3236, 29, 'I6', 9, 1, 1),
(3237, 29, 'I7', 9, 1, 1),
(3238, 29, 'I8', 9, 1, 1),
(3239, 29, 'I9', 9, 1, 1),
(3240, 29, 'I10', 9, 1, 1),
(3241, 29, 'I11', 9, 1, 1),
(3242, 29, 'I12', 9, 1, 1),
(3243, 29, 'I13', 9, 1, 1),
(3244, 29, 'I14', 9, 1, 1),
(3245, 29, 'I15', 9, 1, 1),
(3246, 29, 'J1', 10, 1, 1),
(3247, 29, 'J2', 10, 1, 1),
(3248, 29, 'J3', 10, 1, 1),
(3249, 29, 'J4', 10, 1, 1),
(3250, 29, 'J5', 10, 1, 1),
(3251, 29, 'J6', 10, 1, 1),
(3252, 29, 'J7', 10, 1, 1),
(3253, 29, 'J8', 10, 1, 1),
(3254, 29, 'J9', 10, 1, 1),
(3255, 29, 'J10', 10, 1, 1),
(3256, 29, 'J11', 10, 1, 1),
(3257, 29, 'J12', 10, 1, 1),
(3258, 29, 'J13', 10, 1, 1),
(3259, 29, 'J14', 10, 1, 1),
(3260, 29, 'J15', 10, 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hang_thanh_vien`
--

CREATE TABLE `hang_thanh_vien` (
  `id` int NOT NULL,
  `id_nguoi_dung` int DEFAULT NULL,
  `id_tier` bigint UNSIGNED DEFAULT NULL,
  `uu_dai` text,
  `diem_toi_thieu` int DEFAULT NULL,
  `ngay_cap_nhat_hang` timestamp NULL DEFAULT NULL COMMENT 'Ngày cập nhật hạng gần nhất'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `id` int NOT NULL,
  `ma_km` varchar(50) DEFAULT NULL,
  `mo_ta` text,
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `gia_tri_giam` decimal(10,2) DEFAULT NULL,
  `loai_giam` enum('phantram','codinh') NOT NULL DEFAULT 'phantram',
  `dieu_kien` varchar(255) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `khuyen_mai`
--

INSERT INTO `khuyen_mai` (`id`, `ma_km`, `mo_ta`, `ngay_bat_dau`, `ngay_ket_thuc`, `gia_tri_giam`, `loai_giam`, `dieu_kien`, `trang_thai`, `created_at`, `updated_at`) VALUES
(1, 'KM02', 'Giảm 10% cho đơn hàng', '2025-10-01', '2025-10-31', 10.00, 'phantram', 'Áp dụng cho đơn hàng từ 500000', 1, '2025-10-30 00:36:10', '2025-11-17 22:16:43'),
(2, 'KM01', 'Giảm nhay 20000 vào bill', '2025-10-31', '2025-12-05', 20.00, 'codinh', NULL, 1, '2025-10-30 00:37:09', '2025-11-17 22:14:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_diem`
--

CREATE TABLE `lich_su_diem` (
  `id` int NOT NULL,
  `id_nguoi_dung` int DEFAULT NULL,
  `ly_do` varchar(255) DEFAULT NULL,
  `diem_thay_doi` int DEFAULT NULL,
  `ngay` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loai_ghe`
--

CREATE TABLE `loai_ghe` (
  `id` int NOT NULL,
  `ten_loai` varchar(50) DEFAULT NULL,
  `he_so_gia` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `loai_ghe`
--

INSERT INTO `loai_ghe` (`id`, `ten_loai`, `he_so_gia`) VALUES
(1, 'Ghế thường', 1.00),
(2, 'Ghế VIP', 1.50),
(3, 'Ghế đôi', 2.00),
(4, 'Ghế Thường', 1.00),
(5, 'Ghế VIP', 1.50),
(6, 'Ghế Đôi', 2.00),
(7, 'Ghế Premium', 1.80),
(8, 'Ghế Thường', 1.00),
(9, 'Ghế VIP', 1.50),
(10, 'Ghế Đôi', 2.00),
(11, 'Ghế Premium', 1.80),
(12, 'Thường', 1.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_09_100000_add_loai_giam_to_khuyen_mai_table', 2),
(5, '2025_10_23_100000_add_id_phong_to_phim_table', 3),
(6, '2025_10_11_031039_create_phim_table', 4),
(7, '2025_10_21_120000_alter_phim_table_to_new_schema', 4),
(8, '2025_10_29_093507_add_start_time_to_suat_chieu_table', 5),
(9, '2025_10_15_144130_add_deleted_at_to_nguoi_dung_table', 6),
(10, '2025_10_29_173211_add_gia_ve_to_chi_tiet_dat_ve_table', 7),
(11, '2025_10_29_173325_add_status_to_phong_chieu_table', 8),
(12, '2025_10_30_000000_create_loai_ghe_table', 9),
(13, '2025_11_02_085044_add_revenue_fields_to_phim_table', 10),
(14, '2025_11_08_050821_add_is_member_to_nguoi_dung_table', 11),
(15, '2025_11_08_052209_create_tier_table', 12),
(16, '2025_11_08_052233_add_id_tier_to_hang_thanh_vien_table', 12),
(17, '2025_11_08_053331_add_member_discount_columns_to_dat_ve_table', 13),
(18, '2025_11_09_034027_add_hot_to_phim_table', 14),
(20, '2025_11_17_181352_create_tam_giu_ghe_table_fixed', 15),
(21, '2025_11_09_035000_add_hot_column_to_phim_table', 16);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `mon_an`
--

CREATE TABLE `mon_an` (
  `id` int NOT NULL,
  `ten` varchar(255) NOT NULL,
  `loai` varchar(100) DEFAULT NULL COMMENT 'Bắp, Nước, Snack, Hotdog...',
  `anh` varchar(500) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `mon_an`
--

INSERT INTO `mon_an` (`id`, `ten`, `loai`, `anh`, `trang_thai`, `created_at`, `updated_at`) VALUES
(1, 'Bắp rang bơ', 'Bắp', '/images/bap-lon.jpg', 1, '2025-10-30 08:37:01', '2025-10-30 08:37:01'),
(2, 'Pepsi', 'Nước', '/images/pepsi.jpg', 1, '2025-10-30 08:37:01', '2025-10-30 08:37:01'),
(3, 'Khoai tây chiên', 'Snack', '/images/khoai.jpg', 1, '2025-10-30 08:37:01', '2025-10-30 08:37:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` tinyint(1) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `dia_chi` text,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `id_vai_tro` int DEFAULT NULL,
  `la_thanh_vien` tinyint NOT NULL DEFAULT '0' COMMENT '0: Không phải thành viên, 1: Là thành viên',
  `ngay_dang_ky_thanh_vien` date DEFAULT NULL COMMENT 'Ngày đăng ký thành viên',
  `trang_thai` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ho_ten`, `email`, `mat_khau`, `ngay_sinh`, `gioi_tinh`, `sdt`, `dia_chi`, `hinh_anh`, `id_vai_tro`, `la_thanh_vien`, `ngay_dang_ky_thanh_vien`, `trang_thai`, `created_at`, `deleted_at`) VALUES
(4, 'Admin', 'admin@example.com', '$2y$12$zm6vR96/WrTwZD4gesKW5O7TBUzyPsNY9kAfcNkqG6hn7bSX8jXiu', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 1, '2025-11-08 05:51:46', NULL),
(5, 'Staff', 'staff@example.com', '$2y$12$J3NPwAqEqaP3yZN8C3mEEuDgDehX/uQJ9KRKPcpgDUclKf1k/Tgzi', NULL, NULL, NULL, NULL, NULL, 2, 0, NULL, 1, '2025-11-08 05:51:46', NULL),
(6, 'Nguyễn Quang A', 'user@example.com', '$2y$12$5Ju8ksObKZFBk/KMrWIWke8jAS2Ek0JuybHez0uxU5gJJ7EARgjiG', NULL, NULL, '09876543324', 'ádftyukiolikujhtrsb', NULL, 3, 0, NULL, 1, '2025-11-17 15:57:45', NULL),
(7, 'Admin User', 'admin@moviehub.com', '$2y$12$y/SXOuBw.J1H5u8sXvz.r.dRhKNbfCI.Sl78xGEpLUStc19YKPL5O', NULL, NULL, '0123456789', NULL, NULL, 1, 0, NULL, 1, '2025-11-17 18:24:57', NULL),
(8, 'Customer 1', 'customer1@example.com', '$2y$12$82fX0Hf.4ir4/ZfhmXSiouLBh6kSqr7evsxIWj3T6.aoQTKM3I.BW', NULL, NULL, '0123456781', NULL, NULL, 4, 0, NULL, 1, '2025-11-17 18:24:57', NULL),
(9, 'Customer 2', 'customer2@example.com', '$2y$12$FCNsRpu9eIsy.DkJsOiw5uZ4YZNP575sPsEuauUzrcrcmUonR15dO', NULL, NULL, '0123456782', NULL, NULL, 4, 0, NULL, 1, '2025-11-17 18:24:57', NULL),
(10, 'Customer 3', 'customer3@example.com', '$2y$12$5nLs.m7QZ/ak4ug51Lu2Ner0qGh1VwIjOkwXogllenGE4D/1w2eYi', NULL, NULL, '0123456783', NULL, NULL, 4, 0, NULL, 1, '2025-11-17 18:24:57', '2025-11-17 14:02:10'),
(11, 'Customer 4', 'customer4@example.com', '$2y$12$i82a7YMVAT7t/txONugVwuYRdk6EdZYIHodtnhvyxTOLVO5c2dZ8m', NULL, NULL, '0123456784', NULL, NULL, 4, 0, NULL, 1, '2025-11-17 18:24:57', '2025-11-17 14:02:08'),
(12, 'Customer 5', 'customer5@example.com', '$2y$12$mYj/R2ieHIZ8upnvhomDmueLZ7I8ss0EuMzb4.U/uY9r2MQFBqbcS', NULL, NULL, '0123456785', NULL, NULL, 4, 0, NULL, 1, '2025-11-17 18:24:58', '2025-11-17 14:02:05');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phim`
--

CREATE TABLE `phim` (
  `id` int NOT NULL,
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
  `mo_ta_ngan` text COMMENT 'Mô tả ngắn gọn về phim',
  `diem_danh_gia` decimal(3,1) DEFAULT NULL,
  `so_luot_danh_gia` int DEFAULT '0',
  `trang_thai` enum('sap_chieu','dang_chieu','ngung_chieu') DEFAULT 'sap_chieu',
  `hot` tinyint(1) NOT NULL DEFAULT '0',
  `doanh_thu` decimal(15,2) DEFAULT NULL COMMENT 'Tổng doanh thu từ phim (VNĐ)',
  `loi_nhuan` decimal(15,2) DEFAULT NULL COMMENT 'Lợi nhuận ròng (VNĐ)',
  `id_phong` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `phim`
--

INSERT INTO `phim` (`id`, `ten_phim`, `ten_goc`, `poster`, `trailer`, `dao_dien`, `dien_vien`, `the_loai`, `quoc_gia`, `ngon_ngu`, `do_tuoi`, `do_dai`, `ngay_khoi_chieu`, `ngay_ket_thuc`, `mo_ta`, `mo_ta_ngan`, `diem_danh_gia`, `so_luot_danh_gia`, `trang_thai`, `hot`, `doanh_thu`, `loi_nhuan`, `id_phong`, `created_at`, `updated_at`, `deleted_at`) VALUES
(34, 'Cuộc Chiến Vũ Trụ', NULL, 'https://image.tmdb.org/t/p/w342/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg', 'https://www.youtube.com/watch?v=example4', 'Joss Whedon', 'Robert Downey Jr., Chris Evans, Mark Ruffalo, Chris Hemsworth', NULL, NULL, NULL, NULL, 142, NULL, NULL, 'Trận chiến vũ trụ hoành tráng giữa các thế lực thiện và ác. Những siêu anh hùng đoàn kết để bảo vệ Trái Đất khỏi mối đe dọa từ vũ trụ.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(35, 'Bí Mật Thời Gian', NULL, 'https://image.tmdb.org/t/p/w342/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg', 'https://www.youtube.com/watch?v=example5', 'Christopher Nolan', 'Leonardo DiCaprio, Marion Cotillard, Tom Hardy, Cillian Murphy', NULL, NULL, NULL, NULL, 118, NULL, NULL, 'Câu chuyện về du hành thời gian và những hậu quả không lường trước. Một nhà khoa học phát minh ra cỗ máy thời gian và khám phá những bí mật của vũ trụ.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(36, 'Hành Trình Tình Yêu', NULL, 'https://image.tmdb.org/t/p/w342/6XYLiMxHAaCsoyrVo38LBWMw2p8.jpg', 'https://www.youtube.com/watch?v=example6', 'Richard Linklater', 'Ethan Hawke, Julie Delpy, Andrea Eckert, Hanno Pöschl', NULL, NULL, NULL, NULL, 95, NULL, NULL, 'Câu chuyện tình yêu cảm động vượt qua mọi khó khăn của cuộc sống. Một cặp đôi trẻ gặp nhau trên chuyến tàu và trải qua 24 giờ đáng nhớ ở Vienna.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(37, 'Ma Trận Hành Động', NULL, 'https://image.tmdb.org/t/p/w342/f89q3lefM2kSVVBDowNwQxIC7D9.jpg', 'https://www.youtube.com/watch?v=example7', 'Lana Wachowski, Lilly Wachowski', 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss, Hugo Weaving', NULL, NULL, NULL, NULL, 136, NULL, NULL, 'Cuộc chiến giữa con người và máy móc trong thế giới ảo. Neo phải học cách sử dụng sức mạnh của mình để cứu nhân loại.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(38, 'Cuộc Sống Tuyệt Vời', NULL, 'https://image.tmdb.org/t/p/w342/4u1vptE8aXuzEIAgH8Y8O7v6NLD.jpg', 'https://www.youtube.com/watch?v=example8', 'Frank Capra', 'James Stewart, Donna Reed, Lionel Barrymore, Thomas Mitchell', NULL, NULL, NULL, NULL, 130, NULL, NULL, 'Câu chuyện về một người đàn ông muốn tự tử nhưng được một thiên thần cứu giúp. Anh ta được cho thấy cuộc sống sẽ như thế nào nếu anh ta chưa từng tồn tại.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(39, 'Vua Sư Tử', NULL, 'https://image.tmdb.org/t/p/w342/sKCr78MXSLixwmZ8DyJLrpMsd15.jpg', 'https://www.youtube.com/watch?v=example9', 'Roger Allers, Rob Minkoff', 'Matthew Broderick, James Earl Jones, Jeremy Irons, Nathan Lane', NULL, NULL, NULL, NULL, 88, NULL, NULL, 'Câu chuyện về chú sư tử con Simba học cách trở thành vua. Một bộ phim hoạt hình kinh điển với âm nhạc tuyệt vời.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(40, 'Titanic', NULL, 'https://image.tmdb.org/t/p/w342/9xjZS2rlVxm8SFx8kPC3aIGCOYQ.jpg', 'https://www.youtube.com/watch?v=example10', 'James Cameron', 'Leonardo DiCaprio, Kate Winslet, Billy Zane, Kathy Bates', NULL, NULL, NULL, NULL, 194, NULL, NULL, 'Câu chuyện tình yêu cảm động giữa Jack và Rose trên con tàu Titanic huyền thoại. Một tác phẩm điện ảnh kinh điển với hiệu ứng đặc biệt tuyệt vời.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(41, 'Forrest Gump', NULL, 'https://image.tmdb.org/t/p/w342/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg', 'https://www.youtube.com/watch?v=example11', 'Robert Zemeckis', 'Tom Hanks, Robin Wright, Gary Sinise, Sally Field', NULL, NULL, NULL, NULL, 142, NULL, NULL, 'Câu chuyện về cuộc đời của Forrest Gump, một người đàn ông có IQ thấp nhưng có trái tim vàng. Anh ta đã chứng kiến và tham gia vào nhiều sự kiện lịch sử quan trọng của nước Mỹ.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(42, 'Pulp Fiction', NULL, 'https://image.tmdb.org/t/p/w342/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg', 'https://www.youtube.com/watch?v=example12', 'Quentin Tarantino', 'John Travolta, Samuel L. Jackson, Uma Thurman, Bruce Willis', NULL, NULL, NULL, NULL, 154, NULL, NULL, 'Một bộ phim tội phạm với cốt truyện phi tuyến tính, kể về cuộc sống của những tên tội phạm ở Los Angeles. Một kiệt tác của điện ảnh thế giới.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:20:36', '2025-11-02 02:02:35', NULL),
(52, 'Titanic', NULL, 'https://image.tmdb.org/t/p/w342/9xjZS2rlVxm8SFx8kPC3aIGCOYQ.jpg', 'https://www.youtube.com/watch?v=example10', 'James Cameron', 'Leonardo DiCaprio, Kate Winslet, Billy Zane, Kathy Bates', NULL, NULL, NULL, NULL, 194, NULL, NULL, 'Câu chuyện tình yêu cảm động giữa Jack và Rose trên con tàu Titanic huyền thoại. Một tác phẩm điện ảnh kinh điển với hiệu ứng đặc biệt tuyệt vời.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:21:38', '2025-11-02 02:02:35', NULL),
(53, 'Forrest Gump', NULL, 'https://image.tmdb.org/t/p/w342/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg', 'https://www.youtube.com/watch?v=example11', 'Robert Zemeckis', 'Tom Hanks, Robin Wright, Gary Sinise, Sally Field', NULL, NULL, NULL, NULL, 142, NULL, NULL, 'Câu chuyện về cuộc đời của Forrest Gump, một người đàn ông có IQ thấp nhưng có trái tim vàng. Anh ta đã chứng kiến và tham gia vào nhiều sự kiện lịch sử quan trọng của nước Mỹ.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:21:38', '2025-11-02 02:02:35', NULL),
(54, 'Pulp Fiction', NULL, 'https://image.tmdb.org/t/p/w342/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg', 'https://www.youtube.com/watch?v=example12', 'Quentin Tarantino', 'John Travolta, Samuel L. Jackson, Uma Thurman, Bruce Willis', NULL, NULL, NULL, NULL, 154, NULL, NULL, 'Một bộ phim tội phạm với cốt truyện phi tuyến tính, kể về cuộc sống của những tên tội phạm ở Los Angeles. Một kiệt tác của điện ảnh thế giới.', NULL, NULL, 0, 'sap_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:21:38', '2025-11-02 02:02:35', NULL),
(55, 'Hành Tinh Bí Ẩn', NULL, 'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg', 'https://www.youtube.com/watch?v=example1', 'Christopher Nolan', 'Matthew McConaughey, Anne Hathaway, Jessica Chastain, Michael Caine', NULL, NULL, NULL, NULL, 128, NULL, NULL, 'Cuộc phiêu lưu vũ trụ đầy kịch tính với những hiệu ứng hình ảnh tuyệt đẹp và cốt truyện hấp dẫn. Một nhóm phi hành gia dũng cảm khám phá những bí mật của vũ trụ và tìm kiếm hành tinh mới cho nhân loại.', NULL, NULL, 0, 'dang_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:22:50', '2025-11-17 12:47:01', NULL),
(56, 'Săn Lùng Siêu Trộm', NULL, 'https://image.tmdb.org/t/p/w342/62HCnUTziyWcpDaBO2i1DX17ljH.jpg', 'https://www.youtube.com/watch?v=example2', 'Michael Mann', 'Tom Cruise, Jamie Foxx, Jada Pinkett Smith, Mark Ruffalo', NULL, NULL, NULL, NULL, 115, NULL, NULL, 'Cuộc truy đuổi gay cấn giữa cảnh sát và tên trộm thông minh nhất thế giới. Một tài xế taxi bình thường bị cuốn vào cuộc phiêu lưu đầy nguy hiểm.', NULL, NULL, 0, 'dang_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:22:50', '2025-11-17 09:37:32', NULL),
(57, 'Vùng Đất Linh Hồn', NULL, 'https://image.tmdb.org/t/p/w342/e1mjopzAS2KNsvpbpahQ1a6SkSn.jpg', 'https://www.youtube.com/watch?v=example3', 'Guillermo del Toro', 'Sally Hawkins, Michael Shannon, Richard Jenkins, Octavia Spencer', NULL, NULL, NULL, NULL, 102, NULL, NULL, 'Hành trình khám phá thế giới tâm linh đầy bí ẩn và kỳ diệu. Một câu chuyện về tình yêu vượt qua ranh giới giữa hai thế giới.', NULL, NULL, 0, 'dang_chieu', 0, 0.00, 0.00, NULL, '2025-10-29 16:22:50', '2025-11-17 12:47:05', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phong_chieu`
--

CREATE TABLE `phong_chieu` (
  `id` int NOT NULL,
  `ten_phong` varchar(100) DEFAULT NULL,
  `so_hang` int DEFAULT NULL,
  `so_cot` int DEFAULT NULL,
  `suc_chua` int DEFAULT NULL,
  `mo_ta` text,
  `trang_thai` tinyint(1) DEFAULT '1',
  `status` varchar(255) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `phong_chieu`
--

INSERT INTO `phong_chieu` (`id`, `ten_phong`, `so_hang`, `so_cot`, `suc_chua`, `mo_ta`, `trang_thai`, `status`) VALUES
(28, 'Phòng1', 10, 15, NULL, NULL, 1, 'active'),
(29, 'Phòng2', 10, 15, NULL, NULL, 1, 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('lhLbqalJP12pZAGgOPxPH5ruO0xyNR2352mVwaJ6', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSzNtMXNCaVd4TDVGbVZVRVZRUmsyeFZUQmh4SU9QWWFKaEtweVo3TiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9jb21ib3MiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo0O30=', 1763417849),
('SkkvUU6wdvXe1bHY5GvRSFbN28Ajpz7WZR3xDf7l', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS3ZjM0d3VmZQTTBmTUVrUVRNTmlWbFJuZmFNc21vUDRpRTVlRVFjciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvYm9va2VkLXNlYXRzLzM5OTYiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo2O30=', 1763418044);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suat_chieu`
--

CREATE TABLE `suat_chieu` (
  `id` int NOT NULL,
  `id_phim` int DEFAULT NULL,
  `id_phong` int DEFAULT NULL,
  `thoi_gian_bat_dau` datetime DEFAULT NULL,
  `thoi_gian_ket_thuc` datetime DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `suat_chieu`
--

INSERT INTO `suat_chieu` (`id`, `id_phim`, `id_phong`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `trang_thai`) VALUES
(3978, 55, 28, '2025-11-18 02:00:00', '2025-11-18 04:08:00', 1),
(3979, 55, 28, '2025-11-18 04:38:00', '2025-11-18 06:46:00', 1),
(3980, 55, 28, '2025-11-18 07:16:00', '2025-11-18 09:24:00', 1),
(3981, 55, 28, '2025-11-18 09:54:00', '2025-11-18 12:02:00', 1),
(3982, 55, 28, '2025-11-18 12:32:00', '2025-11-18 14:40:00', 1),
(3983, 55, 28, '2025-11-19 02:00:00', '2025-11-19 04:08:00', 1),
(3984, 55, 28, '2025-11-19 04:38:00', '2025-11-19 06:46:00', 1),
(3985, 55, 28, '2025-11-19 07:16:00', '2025-11-19 09:24:00', 1),
(3986, 55, 28, '2025-11-19 09:54:00', '2025-11-19 12:02:00', 1),
(3987, 55, 28, '2025-11-19 12:32:00', '2025-11-19 14:40:00', 1),
(3988, 55, 28, '2025-11-20 02:00:00', '2025-11-20 04:08:00', 1),
(3989, 55, 28, '2025-11-20 04:38:00', '2025-11-20 06:46:00', 1),
(3990, 55, 28, '2025-11-20 07:16:00', '2025-11-20 09:24:00', 1),
(3991, 55, 28, '2025-11-20 09:54:00', '2025-11-20 12:02:00', 1),
(3992, 55, 28, '2025-11-20 12:32:00', '2025-11-20 14:40:00', 1),
(3993, 55, 28, '2025-11-21 02:00:00', '2025-11-21 04:08:00', 1),
(3994, 55, 28, '2025-11-21 04:38:00', '2025-11-21 06:46:00', 1),
(3995, 55, 28, '2025-11-21 07:16:00', '2025-11-21 09:24:00', 1),
(3996, 55, 28, '2025-11-21 09:54:00', '2025-11-21 12:02:00', 1),
(3997, 55, 28, '2025-11-21 12:32:00', '2025-11-21 14:40:00', 1),
(3998, 56, 29, '2025-11-19 02:00:00', '2025-11-19 03:55:00', 1),
(3999, 56, 29, '2025-11-19 04:25:00', '2025-11-19 06:20:00', 1),
(4000, 56, 29, '2025-11-19 06:50:00', '2025-11-19 08:45:00', 1),
(4001, 56, 29, '2025-11-19 09:15:00', '2025-11-19 11:10:00', 1),
(4002, 56, 29, '2025-11-19 11:40:00', '2025-11-19 13:35:00', 1),
(4003, 56, 29, '2025-11-20 02:00:00', '2025-11-20 03:55:00', 1),
(4004, 56, 29, '2025-11-20 04:25:00', '2025-11-20 06:20:00', 1),
(4005, 56, 29, '2025-11-20 06:50:00', '2025-11-20 08:45:00', 1),
(4006, 56, 29, '2025-11-20 09:15:00', '2025-11-20 11:10:00', 1),
(4007, 56, 29, '2025-11-20 11:40:00', '2025-11-20 13:35:00', 1),
(4008, 56, 29, '2025-11-21 02:00:00', '2025-11-21 03:55:00', 1),
(4009, 56, 29, '2025-11-21 04:25:00', '2025-11-21 06:20:00', 1),
(4010, 56, 29, '2025-11-21 06:50:00', '2025-11-21 08:45:00', 1),
(4011, 56, 29, '2025-11-21 09:15:00', '2025-11-21 11:10:00', 1),
(4012, 56, 29, '2025-11-21 11:40:00', '2025-11-21 13:35:00', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tam_giu_ghe`
--

CREATE TABLE `tam_giu_ghe` (
  `id` bigint UNSIGNED NOT NULL,
  `id_ghe` bigint UNSIGNED NOT NULL,
  `id_suat_chieu` bigint UNSIGNED NOT NULL,
  `id_nguoi_dung` bigint UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gia_giu` decimal(10,2) NOT NULL DEFAULT '0.00',
  `thoi_gian_giu` timestamp NOT NULL,
  `thoi_gian_het_han` timestamp NOT NULL,
  `trang_thai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dang_giu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `id` int NOT NULL,
  `id_dat_ve` int DEFAULT NULL,
  `phuong_thuc` varchar(50) DEFAULT NULL,
  `so_tien` decimal(10,2) DEFAULT NULL,
  `ma_giao_dich` varchar(100) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT NULL,
  `thoi_gian` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tier`
--

CREATE TABLE `tier` (
  `id` bigint UNSIGNED NOT NULL,
  `ten_hang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên hạng: Bronze, Silver, Gold, Platinum',
  `mo_ta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả chi tiết hạng',
  `uu_dai` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Các ưu đãi của hạng',
  `diem_toi_thieu` int NOT NULL DEFAULT '0' COMMENT 'Điểm tối thiểu để đạt hạng',
  `diem_toi_da` int DEFAULT NULL COMMENT 'Điểm tối đa của hạng (NULL = không giới hạn)',
  `giam_gia_ve` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '% giảm giá vé phim',
  `giam_gia_combo` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '% giảm giá combo',
  `ty_le_tich_diem` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT 'Tỷ lệ tích điểm (1.0 = 100%)',
  `so_thu_tu` int NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `mau_sac` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã màu đại diện (#hex)',
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icon/hình ảnh đại diện',
  `trang_thai` tinyint NOT NULL DEFAULT '1' COMMENT '1: Hoạt động, 0: Không hoạt động',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tier`
--

INSERT INTO `tier` (`id`, `ten_hang`, `mo_ta`, `uu_dai`, `diem_toi_thieu`, `diem_toi_da`, `giam_gia_ve`, `giam_gia_combo`, `ty_le_tich_diem`, `so_thu_tu`, `mau_sac`, `icon`, `trang_thai`, `created_at`, `updated_at`) VALUES
(1, 'Đồng', 'Hạng thành viên cơ bản dành cho người mới', 'Tích điểm khi mua vé và combo', 0, 999, 0.00, 0.00, 1.00, 1, '#CD7F32', 'bronze-medal.png', 1, NULL, NULL),
(2, 'Bạc', 'Hạng thành viên bạc với nhiều ưu đãi hơn', 'Giảm 5% vé và combo, tích điểm nhanh hơn 20%', 1000, 4999, 5.00, 5.00, 1.20, 2, '#C0C0C0', 'silver-medal.png', 1, NULL, NULL),
(3, 'Vàng', 'Hạng thành viên vàng với quyền lợi cao cấp', 'Giảm 10% vé và combo, tích điểm x1.5, ưu tiên đặt vé, nâng cấp ghế VIP', 5000, 9999, 10.00, 10.00, 1.50, 3, '#FFD700', 'gold-medal.png', 1, NULL, NULL),
(4, 'Bạch Kim', 'Hạng thành viên cao nhất với đặc quyền VIP', 'Giảm 15% vé và combo, tích điểm x2, ưu tiên tối đa, VIP lounge, hỗ trợ 24/7', 10000, NULL, 15.00, 15.00, 2.00, 4, '#E5E4E2', 'platinum-medal.png', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '2025-10-29 16:16:29', '$2y$12$FyMUonucDkHe7n8hux8Y8.H2u.tE56s.uF2jnbaaMUvI2sfizAAnS', '5YfU1IyaZi', '2025-10-29 16:16:29', '2025-10-29 16:16:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vai_tro`
--

CREATE TABLE `vai_tro` (
  `id` int NOT NULL,
  `ten` varchar(100) NOT NULL,
  `mo_ta` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `vai_tro`
--

INSERT INTO `vai_tro` (`id`, `ten`, `mo_ta`) VALUES
(1, 'admin', 'Quản trị'),
(2, 'staff', 'Nhân viên'),
(3, 'user', 'Người dùng'),
(4, 'Customer', 'Khách hàng');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `chi_tiet_combo_mon`
--
ALTER TABLE `chi_tiet_combo_mon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chi_tiet_combo_mon_ibfk_1` (`id_combo`),
  ADD KEY `chi_tiet_combo_mon_ibfk_2` (`id_mon_an`);

--
-- Chỉ mục cho bảng `chi_tiet_dat_ve`
--
ALTER TABLE `chi_tiet_dat_ve`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dat_ve` (`id_dat_ve`),
  ADD KEY `id_ghe` (`id_ghe`);

--
-- Chỉ mục cho bảng `chi_tiet_dat_ve_combo`
--
ALTER TABLE `chi_tiet_dat_ve_combo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dat_ve` (`id_dat_ve`),
  ADD KEY `id_combo` (`id_combo`);

--
-- Chỉ mục cho bảng `combo`
--
ALTER TABLE `combo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `combo_ibfk_created_by` (`created_by`),
  ADD KEY `combo_ibfk_updated_by` (`updated_by`);

--
-- Chỉ mục cho bảng `dat_ve`
--
ALTER TABLE `dat_ve`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nguoi_dung` (`id_nguoi_dung`),
  ADD KEY `id_suat_chieu` (`id_suat_chieu`),
  ADD KEY `id_khuyen_mai` (`id_khuyen_mai`);

--
-- Chỉ mục cho bảng `diem_thanh_vien`
--
ALTER TABLE `diem_thanh_vien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nguoi_dung` (`id_nguoi_dung`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `ghe`
--
ALTER TABLE `ghe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_phong` (`id_phong`),
  ADD KEY `id_loai` (`id_loai`);

--
-- Chỉ mục cho bảng `hang_thanh_vien`
--
ALTER TABLE `hang_thanh_vien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nguoi_dung` (`id_nguoi_dung`),
  ADD KEY `hang_thanh_vien_id_tier_foreign` (`id_tier`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_km` (`ma_km`);

--
-- Chỉ mục cho bảng `lich_su_diem`
--
ALTER TABLE `lich_su_diem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nguoi_dung` (`id_nguoi_dung`);

--
-- Chỉ mục cho bảng `loai_ghe`
--
ALTER TABLE `loai_ghe`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `mon_an`
--
ALTER TABLE `mon_an`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_vai_tro` (`id_vai_tro`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `phim`
--
ALTER TABLE `phim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_phong` (`id_phong`);

--
-- Chỉ mục cho bảng `phong_chieu`
--
ALTER TABLE `phong_chieu`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `suat_chieu`
--
ALTER TABLE `suat_chieu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_phim` (`id_phim`),
  ADD KEY `id_phong` (`id_phong`);

--
-- Chỉ mục cho bảng `tam_giu_ghe`
--
ALTER TABLE `tam_giu_ghe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_ghe_suat_chieu` (`id_ghe`,`id_suat_chieu`),
  ADD KEY `tam_giu_ghe_id_ghe_id_suat_chieu_index` (`id_ghe`,`id_suat_chieu`),
  ADD KEY `tam_giu_ghe_session_id_index` (`session_id`),
  ADD KEY `tam_giu_ghe_thoi_gian_het_han_index` (`thoi_gian_het_han`);

--
-- Chỉ mục cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dat_ve` (`id_dat_ve`);

--
-- Chỉ mục cho bảng `tier`
--
ALTER TABLE `tier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tier_so_thu_tu_index` (`so_thu_tu`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Chỉ mục cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chi_tiet_combo_mon`
--
ALTER TABLE `chi_tiet_combo_mon`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_dat_ve`
--
ALTER TABLE `chi_tiet_dat_ve`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_dat_ve_combo`
--
ALTER TABLE `chi_tiet_dat_ve_combo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `combo`
--
ALTER TABLE `combo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `dat_ve`
--
ALTER TABLE `dat_ve`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `diem_thanh_vien`
--
ALTER TABLE `diem_thanh_vien`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `ghe`
--
ALTER TABLE `ghe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3261;

--
-- AUTO_INCREMENT cho bảng `hang_thanh_vien`
--
ALTER TABLE `hang_thanh_vien`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `lich_su_diem`
--
ALTER TABLE `lich_su_diem`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `loai_ghe`
--
ALTER TABLE `loai_ghe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `mon_an`
--
ALTER TABLE `mon_an`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `phim`
--
ALTER TABLE `phim`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT cho bảng `phong_chieu`
--
ALTER TABLE `phong_chieu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `suat_chieu`
--
ALTER TABLE `suat_chieu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4013;

--
-- AUTO_INCREMENT cho bảng `tam_giu_ghe`
--
ALTER TABLE `tam_giu_ghe`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `tier`
--
ALTER TABLE `tier`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chi_tiet_combo_mon`
--
ALTER TABLE `chi_tiet_combo_mon`
  ADD CONSTRAINT `chi_tiet_combo_mon_ibfk_1` FOREIGN KEY (`id_combo`) REFERENCES `combo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_combo_mon_ibfk_2` FOREIGN KEY (`id_mon_an`) REFERENCES `mon_an` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chi_tiet_dat_ve`
--
ALTER TABLE `chi_tiet_dat_ve`
  ADD CONSTRAINT `chi_tiet_dat_ve_ibfk_1` FOREIGN KEY (`id_dat_ve`) REFERENCES `dat_ve` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_dat_ve_ibfk_2` FOREIGN KEY (`id_ghe`) REFERENCES `ghe` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chi_tiet_dat_ve_combo`
--
ALTER TABLE `chi_tiet_dat_ve_combo`
  ADD CONSTRAINT `chi_tiet_dat_ve_combo_ibfk_1` FOREIGN KEY (`id_dat_ve`) REFERENCES `dat_ve` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_dat_ve_combo_ibfk_2` FOREIGN KEY (`id_combo`) REFERENCES `combo` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `combo`
--
ALTER TABLE `combo`
  ADD CONSTRAINT `combo_ibfk_created_by` FOREIGN KEY (`created_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `combo_ibfk_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `dat_ve`
--
ALTER TABLE `dat_ve`
  ADD CONSTRAINT `dat_ve_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dat_ve_ibfk_2` FOREIGN KEY (`id_suat_chieu`) REFERENCES `suat_chieu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dat_ve_ibfk_3` FOREIGN KEY (`id_khuyen_mai`) REFERENCES `khuyen_mai` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `diem_thanh_vien`
--
ALTER TABLE `diem_thanh_vien`
  ADD CONSTRAINT `diem_thanh_vien_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `ghe`
--
ALTER TABLE `ghe`
  ADD CONSTRAINT `ghe_ibfk_1` FOREIGN KEY (`id_phong`) REFERENCES `phong_chieu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ghe_ibfk_2` FOREIGN KEY (`id_loai`) REFERENCES `loai_ghe` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `hang_thanh_vien`
--
ALTER TABLE `hang_thanh_vien`
  ADD CONSTRAINT `hang_thanh_vien_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hang_thanh_vien_id_tier_foreign` FOREIGN KEY (`id_tier`) REFERENCES `tier` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `lich_su_diem`
--
ALTER TABLE `lich_su_diem`
  ADD CONSTRAINT `lich_su_diem_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD CONSTRAINT `nguoi_dung_ibfk_1` FOREIGN KEY (`id_vai_tro`) REFERENCES `vai_tro` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `phim`
--
ALTER TABLE `phim`
  ADD CONSTRAINT `phim_ibfk_phong` FOREIGN KEY (`id_phong`) REFERENCES `phong_chieu` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `suat_chieu`
--
ALTER TABLE `suat_chieu`
  ADD CONSTRAINT `suat_chieu_ibfk_1` FOREIGN KEY (`id_phim`) REFERENCES `phim` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suat_chieu_ibfk_2` FOREIGN KEY (`id_phong`) REFERENCES `phong_chieu` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD CONSTRAINT `thanh_toan_ibfk_1` FOREIGN KEY (`id_dat_ve`) REFERENCES `dat_ve` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
