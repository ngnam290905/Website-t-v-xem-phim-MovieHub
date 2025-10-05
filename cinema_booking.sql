CREATE DATABASE cinema_booking;
USE cinema_booking;

-- Bảng vai trò
CREATE TABLE vai_tro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL,
    mo_ta TEXT
);

-- Bảng người dùng
CREATE TABLE nguoi_dung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    ngay_sinh DATE,
    gioi_tinh TINYINT(1),
    sdt VARCHAR(20),
    dia_chi TEXT,
    hinh_anh VARCHAR(255),
    id_vai_tro INT,
    trang_thai TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vai_tro) REFERENCES vai_tro(id)
);

-- Bảng hạng thành viên
CREATE TABLE hang_thanh_vien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT,
    ten_hang VARCHAR(50),
    uu_dai TEXT,
    diem_toi_thieu INT,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id)
);

-- Bảng điểm thành viên
CREATE TABLE diem_thanh_vien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT,
    tong_diem INT DEFAULT 0,
    ngay_het_han DATE,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id)
);

-- Bảng lịch sử điểm
CREATE TABLE lich_su_diem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT,
    ly_do VARCHAR(255),
    diem_thay_doi INT,
    ngay DATE,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id)
);

-- Bảng khuyến mãi
CREATE TABLE khuyen_mai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_km VARCHAR(50) UNIQUE,
    mo_ta TEXT,
    ngay_bat_dau DATE,
    ngay_ket_thuc DATE,
    gia_tri_giam DECIMAL(10,2),
    dieu_kien TEXT,
    trang_thai TINYINT(1) DEFAULT 1
);

-- Bảng phim
CREATE TABLE phim (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_phim VARCHAR(255),
    do_dai INT,
    poster VARCHAR(255),
    mo_ta TEXT,
    dao_dien VARCHAR(100),
    dien_vien TEXT,
    trailer VARCHAR(255),
    trang_thai TINYINT(1) DEFAULT 1
);

-- Bảng phòng chiếu
CREATE TABLE phong_chieu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_phong VARCHAR(100),
    so_hang INT,
    so_cot INT,
    suc_chua INT,
    mo_ta TEXT,
    trang_thai TINYINT(1) DEFAULT 1
);

-- Bảng loại ghế
CREATE TABLE loai_ghe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_loai VARCHAR(50),
    he_so_gia DECIMAL(5,2)
);

-- Bảng ghế
CREATE TABLE ghe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_phong INT,
    so_ghe VARCHAR(10),
    so_hang INT,
    id_loai INT,
    trang_thai TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_phong) REFERENCES phong_chieu(id),
    FOREIGN KEY (id_loai) REFERENCES loai_ghe(id)
);

-- Bảng suất chiếu
CREATE TABLE suat_chieu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_phim INT,
    id_phong INT,
    thoi_gian_bat_dau DATETIME,
    thoi_gian_ket_thuc DATETIME,
    trang_thai TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_phim) REFERENCES phim(id),
    FOREIGN KEY (id_phong) REFERENCES phong_chieu(id)
);

-- Bảng đặt vé
CREATE TABLE dat_ve (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT,
    id_suat_chieu INT,
    id_khuyen_mai INT NULL,
    trang_thai TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id),
    FOREIGN KEY (id_suat_chieu) REFERENCES suat_chieu(id),
    FOREIGN KEY (id_khuyen_mai) REFERENCES khuyen_mai(id)
);

-- Bảng chi tiết đặt vé (ghế)
CREATE TABLE chi_tiet_dat_ve (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dat_ve INT,
    id_ghe INT,
    gia DECIMAL(10,2),
    FOREIGN KEY (id_dat_ve) REFERENCES dat_ve(id),
    FOREIGN KEY (id_ghe) REFERENCES ghe(id)
);

-- Bảng combo
CREATE TABLE combo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100),
    mo_ta TEXT,
    gia DECIMAL(10,2),
    gia_khuyen_mai DECIMAL(10,2),
    trang_thai TINYINT(1) DEFAULT 1
);

-- Bảng chi tiết combo
CREATE TABLE chi_tiet_combo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dat_ve INT,
    id_combo INT,
    so_luong INT,
    gia_khuyen_mai DECIMAL(10,2),
    FOREIGN KEY (id_dat_ve) REFERENCES dat_ve(id),
    FOREIGN KEY (id_combo) REFERENCES combo(id)
);

-- Bảng thanh toán
CREATE TABLE thanh_toan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dat_ve INT,
    phuong_thuc VARCHAR(50),
    so_tien DECIMAL(10,2),
    ma_giao_dich VARCHAR(100),
    trang_thai TINYINT(1),
    thoi_gian DATETIME,
    FOREIGN KEY (id_dat_ve) REFERENCES dat_ve(id)
);
