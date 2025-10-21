# MovieHub Cinema Database Integration Guide

## Tổng quan
Dự án MovieHub đã được tích hợp đầy đủ với cấu trúc database từ file `cinema_booking.sql`. Hệ thống hiện có thể quản lý phim, phòng chiếu, suất chiếu, đặt vé và thanh toán.

## Cấu trúc Database

### Bảng chính
1. **phim** - Thông tin phim
2. **phong_chieu** - Thông tin phòng chiếu
3. **suat_chieu** - Lịch chiếu phim
4. **dat_ve** - Thông tin đặt vé
5. **chi_tiet_dat_ve** - Chi tiết ghế đã đặt
6. **combo** - Combo đồ ăn/nước uống
7. **thanh_toan** - Thông tin thanh toán
8. **khuyen_mai** - Mã khuyến mãi

### Bảng phụ trợ
- **loai_ghe** - Loại ghế (VIP, thường, v.v.)
- **ghe** - Thông tin ghế trong phòng chiếu

## Models đã tạo

### 1. PhongChieu Model
```php
// Quan hệ
- hasMany(SuatChieu::class, 'id_phong')
- hasMany(Ghe::class, 'id_phong')
```

### 2. SuatChieu Model
```php
// Quan hệ
- belongsTo(Movie::class, 'id_phim')
- belongsTo(PhongChieu::class, 'id_phong')
- hasMany(DatVe::class, 'id_suat_chieu')
```

### 3. DatVe Model
```php
// Quan hệ
- belongsTo(User::class, 'id_nguoi_dung')
- belongsTo(SuatChieu::class, 'id_suat_chieu')
- hasMany(ChiTietDatVe::class, 'id_dat_ve')
```

## API Endpoints

### Phim
- `GET /` - Trang chủ với danh sách phim
- `GET /phim/{id}` - Chi tiết phim với suất chiếu
- `GET /api/movies` - API lấy danh sách phim
- `GET /api/featured-movies` - API lấy phim nổi bật
- `GET /api/search?q=query` - API tìm kiếm phim

### Suất chiếu
- `GET /api/suat-chieu/{movieId}` - API lấy suất chiếu của phim
- `GET /api/phong-chieu` - API lấy danh sách phòng chiếu

## Dữ liệu mẫu

### Phòng chiếu (5 phòng)
- Phòng 1 - IMAX (240 ghế)
- Phòng 2 - 3D (180 ghế)
- Phòng 3 - 2D (120 ghế)
- Phòng 4 - VIP (72 ghế)
- Phòng 5 - 4DX (128 ghế)

### Suất chiếu
- Mỗi phim có 4 suất chiếu/ngày trong 7 ngày tới
- Thời gian: 14:00, 16:30, 19:00, 21:30
- Phòng chiếu được chọn ngẫu nhiên

## Cách sử dụng

### 1. Khởi động dự án
```bash
# Chạy migration và seeder
php artisan migrate:fresh --seed

# Khởi động server
php artisan serve
```

### 2. Truy cập website
- Trang chủ: `http://localhost:8000`
- Chi tiết phim: `http://localhost:8000/phim/{id}`

### 3. API Usage
```javascript
// Lấy danh sách phim
fetch('/api/movies')
  .then(response => response.json())
  .then(data => console.log(data));

// Lấy suất chiếu của phim
fetch('/api/suat-chieu/1')
  .then(response => response.json())
  .then(data => console.log(data));
```

## Tính năng đã hoàn thành

### ✅ Database Integration
- Tạo đầy đủ migration cho tất cả bảng
- Tạo Models với relationships
- Seeder dữ liệu mẫu

### ✅ API Development
- RESTful API cho phim và suất chiếu
- JSON response format
- Error handling

### ✅ Frontend Integration
- Hiển thị phim từ database
- Hiển thị suất chiếu động
- Responsive design

### ✅ Data Management
- Quan hệ giữa các bảng
- Timestamps tự động
- Soft delete support

## Cấu trúc thư mục

```
app/Models/
├── Movie.php
├── PhongChieu.php
├── SuatChieu.php
└── DatVe.php

database/migrations/
├── create_phim_table.php
├── create_phong_chieu_table.php
├── create_suat_chieu_table.php
└── create_dat_ve_table.php

database/seeders/
├── MovieSeeder.php
├── PhongChieuSeeder.php
└── SuatChieuSeeder.php
```

## Mở rộng trong tương lai

### 1. Đặt vé
- Tạo form đặt vé
- Chọn ghế
- Thanh toán

### 2. Quản lý admin
- CRUD phim
- Quản lý suất chiếu
- Quản lý đặt vé

### 3. Tính năng nâng cao
- Đánh giá phim
- Khuyến mãi
- Thông báo

## Lưu ý

- Tất cả dữ liệu được lưu trong MySQL
- Sử dụng Eloquent ORM của Laravel
- API trả về JSON format
- Frontend sử dụng Blade templates
- Responsive design với Tailwind CSS

## Troubleshooting

### Lỗi migration
```bash
# Reset database
php artisan migrate:fresh --seed
```

### Lỗi seeder
```bash
# Chạy seeder riêng lẻ
php artisan db:seed --class=MovieSeeder
php artisan db:seed --class=PhongChieuSeeder
php artisan db:seed --class=SuatChieuSeeder
```

### Lỗi API
- Kiểm tra routes trong `routes/web.php`
- Kiểm tra controller methods
- Kiểm tra database connection

Hệ thống MovieHub hiện đã hoàn toàn tích hợp với database và sẵn sàng cho việc phát triển thêm các tính năng đặt vé!
