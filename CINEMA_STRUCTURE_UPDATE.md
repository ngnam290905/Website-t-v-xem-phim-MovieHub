# Cập Nhật Cấu Trúc Rạp Chiếu Phim

## Tổng Quan

Đã cập nhật cấu trúc database và code để phù hợp với mô hình rạp chiếu phim chuyên nghiệp.

## Cấu Trúc Database Mới

### 1. Bảng Rooms (phong_chieu)

| Trường | Kiểu dữ liệu | Mô tả |
|--------|-------------|-------|
| id | INT (PK) | ID phòng |
| name | VARCHAR(100) | Tên phòng (VD: Phòng 1, Phòng VIP) |
| description | TEXT | Mô tả phòng |
| rows | INT | Số hàng ghế |
| cols | INT | Số cột ghế |
| type | ENUM('normal','vip','3d','imax') | Loại phòng |
| status | ENUM('active','inactive') | Trạng thái hoạt động |
| created_at | DATETIME | Ngày tạo |
| updated_at | DATETIME | Ngày cập nhật |

### 2. Bảng Seats (ghe)

| Trường | Kiểu dữ liệu | Mô tả |
|--------|-------------|-------|
| id | INT (PK) | ID ghế |
| room_id | INT (FK) | Phòng thuộc về |
| row_label | CHAR(1) | Ký tự hàng (A, B, C…) |
| col_number | INT | Số cột |
| seat_code | VARCHAR(10) | Mã ghế (A1, B3, C8…) |
| type | ENUM('normal','vip','disabled') | Loại ghế |
| status | ENUM('available','booked','locked') | Trạng thái ghế |
| price | DECIMAL(10,2) | Giá ghế (nếu khác nhau) |

### 3. Bảng Showtimes (suat_chieu)

| Trường | Kiểu dữ liệu | Mô tả |
|--------|-------------|-------|
| id | INT (PK) | ID suất chiếu |
| movie_id | INT (FK) | Phim chiếu |
| room_id | INT (FK) | Phòng chiếu |
| start_time | DATETIME | Giờ bắt đầu |
| end_time | DATETIME | Giờ kết thúc |
| status | ENUM('coming','ongoing','finished') | Trạng thái suất |

## Quan Hệ Database

```
movies (1) → showtimes (N)     # Mỗi phim có nhiều suất chiếu
showtimes → rooms             # Mỗi suất chiếu diễn ra trong 1 phòng
rooms (1) → seats (N)         # Mỗi phòng có nhiều ghế
bookings → showtimes + seats  # Mỗi vé gắn với 1 suất chiếu & 1 ghế cụ thể
```

## Models Đã Cập Nhật

### 1. PhongChieu (Room)
- **Relationships**: `showtimes()`, `seats()`
- **Scopes**: `active()`, `ofType($type)`
- **Accessors**: `getCapacityAttribute()`

### 2. Ghe (Seat)
- **Relationships**: `room()`, `bookingDetails()`
- **Scopes**: `available()`, `booked()`, `vip()`, `normal()`
- **Methods**: `isAvailable()`, `isBooked()`, `isLocked()`

### 3. SuatChieu (Showtime)
- **Relationships**: `movie()`, `room()`, `bookings()`, `seats()`
- **Scopes**: `coming()`, `ongoing()`, `finished()`, `today()`, `byDate($date)`
- **Accessors**: `getAvailableSeatsCountAttribute()`, `getOccupancyPercentageAttribute()`
- **Methods**: `updateStatus()`, `isComing()`, `isOngoing()`, `isFinished()`

## Controllers Đã Cập Nhật

### SuatChieuController
- Cập nhật tất cả methods để sử dụng cấu trúc mới
- Hỗ trợ tìm kiếm và lọc nâng cao
- Validation rules mới

## Seeders

### CinemaDataSeeder
- Tạo 4 phòng chiếu mẫu (Normal, VIP, IMAX, 3D)
- Tự động tạo ghế cho từng phòng
- Tính toán giá ghế dựa trên loại phòng và vị trí
- Tạo suất chiếu mẫu cho 7 ngày

## Cách Chạy Migration

```bash
# Chạy migrations
php artisan migrate

# Chạy seeders
php artisan db:seed --class=CinemaDataSeeder

# Hoặc chạy tất cả
php artisan db:seed
```

## Tính Năng Mới

### 1. Quản Lý Phòng Chiếu
- Hỗ trợ nhiều loại phòng (Normal, VIP, 3D, IMAX)
- Tự động tính toán sức chứa
- Quản lý trạng thái phòng

### 2. Quản Lý Ghế
- Hệ thống mã ghế (A1, B3, C8...)
- Nhiều loại ghế (Normal, VIP, Disabled)
- Trạng thái ghế (Available, Booked, Locked)
- Tính giá ghế linh hoạt

### 3. Quản Lý Suất Chiếu
- Trạng thái suất chiếu (Coming, Ongoing, Finished)
- Tự động cập nhật trạng thái
- Thống kê tỷ lệ lấp đầy
- Tìm kiếm và lọc nâng cao

### 4. Tìm Kiếm và Lọc
- Tìm kiếm theo tên phim/phòng
- Lọc theo phim, phòng, trạng thái
- Lọc theo khoảng thời gian
- Sắp xếp linh hoạt
- Tìm kiếm real-time

## Backward Compatibility

Tất cả các Models đều có legacy methods để đảm bảo tương thích ngược:
- `phim()` → `movie()`
- `phongChieu()` → `room()`
- `ghe()` → `seats()`
- `datVe()` → `bookings()`

## Lưu Ý

1. **Migration**: Cần chạy migrations theo thứ tự để tránh lỗi
2. **Data**: Seeder sẽ tạo dữ liệu mẫu phong phú
3. **Performance**: Đã tối ưu queries với eager loading
4. **Security**: Validation rules đã được cập nhật

## Kết Luận

Cấu trúc mới cung cấp:
- ✅ Quản lý rạp chiếu chuyên nghiệp
- ✅ Hệ thống ghế linh hoạt
- ✅ Tìm kiếm và lọc mạnh mẽ
- ✅ Tương thích ngược
- ✅ Dữ liệu mẫu phong phú
