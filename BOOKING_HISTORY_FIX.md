# HƯỚNG DẪN SỬA LỖI LỊCH SỬ ĐẶT VÉ

## Vấn đề đã được sửa

Đã cải thiện phần lịch sử đặt vé để hiển thị đúng thông tin vé mà người dùng đã đặt.

## Các thay đổi đã thực hiện

### 1. Cập nhật Model DatVe
- **File**: `app/Models/DatVe.php`
- **Thay đổi**: Bật timestamps để tự động quản lý `created_at` và `updated_at`
- Thay đổi `public $timestamps = false;` thành `public $timestamps = true;`

### 2. Cập nhật Controller
- **File**: `app/Http/Controllers/BookingController.php`
- **Thay đổi**:
  - Thêm kiểm tra xác thực người dùng
  - Thêm logging để debug
  - Load thêm quan hệ `loaiGhe` để hiển thị loại ghế
  - Sắp xếp theo `created_at` hoặc `id` nếu `created_at` null

### 3. Cải thiện giao diện
- **File**: `resources/views/user/bookings.blade.php`
- **Thay đổi**:
  - Hiển thị đầy đủ thông tin vé (mã đặt vé, trạng thái, ngày đặt)
  - Hiển thị chi tiết giá vé theo từng ghế và loại ghế
  - Hiển thị combo và khuyến mãi đã áp dụng
  - Tính toán và hiển thị tổng tiền chính xác
  - Cải thiện empty state khi chưa có đặt vé
  - Thêm status badge với màu sắc rõ ràng

### 4. Thêm migration cho timestamps
- **File**: `database/migrations/2024_01_01_000001_add_timestamps_to_dat_ve_table.php`
- **Mục đích**: Thêm cột `created_at` và `updated_at` vào bảng `dat_ve` nếu chưa có

### 5. Thêm trang debug
- **File**: `resources/views/debug-bookings.blade.php`
- **URL**: `/debug-bookings`
- **Mục đích**: Kiểm tra tất cả bookings trong hệ thống và xem bookings của user hiện tại

## Cách chạy

### Bước 1: Chạy migration để thêm timestamps
```bash
# Chạy file batch
update_booking_timestamps.bat

# Hoặc chạy trực tiếp trong terminal
php artisan migrate --path=database/migrations/2024_01_01_000001_add_timestamps_to_dat_ve_table.php
```

### Bước 2: Xóa cache (nếu có)
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Bước 3: Kiểm tra
1. Đăng nhập vào tài khoản người dùng
2. Truy cập `/user/bookings` để xem lịch sử đặt vé
3. Kiểm tra xem có hiển thị đúng vé đã đặt không

### Bước 4: Debug (nếu vẫn có vấn đề)
1. Truy cập `/debug-bookings` để xem tất cả bookings trong hệ thống
2. Kiểm tra:
   - User ID có khớp không?
   - Có booking nào được highlight màu xanh (của user hiện tại) không?
   - `id_nguoi_dung` trong bảng `dat_ve` có đúng không?

## Kiểm tra logs
Xem file log tại `storage/logs/laravel.log` để xem thông tin debug:
- User ID khi truy cập bookings
- Số lượng bookings tìm thấy
- Danh sách booking IDs

## Các cải tiến hiển thị

### Thông tin vé được hiển thị:
- ✅ Mã đặt vé (format đẹp: #000001)
- ✅ Trạng thái với badge màu sắc
- ✅ Ngày đặt vé
- ✅ Tên phim, phòng chiếu, suất chiếu
- ✅ Danh sách ghế đã đặt với số lượng
- ✅ Chi tiết giá vé theo từng ghế và loại ghế
- ✅ Combo đã chọn
- ✅ Khuyến mãi đã áp dụng
- ✅ Phương thức thanh toán
- ✅ Tổng tiền cuối cùng

### Trạng thái:
- 🟡 Chờ xác nhận (trang_thai = 0)
- 🟢 Đã xác nhận (trang_thai = 1)
- 🔴 Đã hủy (trang_thai = 2)
- 🟠 Yêu cầu hủy (trang_thai = 3)

## Lưu ý khi deploy production

### Xóa route debug:
Trong file `routes/web.php`, xóa hoặc comment dòng:
```php
// Debug route - XÓA KHI DEPLOY PRODUCTION
Route::middleware('auth')->get('/debug-bookings', function() {
    return view('debug-bookings');
})->name('debug.bookings');
```

### Tắt debug logging:
Trong `app/Http/Controllers/BookingController.php`, có thể xóa hoặc comment các dòng `Log::info()` trong method `index()`.

## Kiểm tra dữ liệu trong database

Chạy query để kiểm tra:
```sql
-- Kiểm tra bookings của một user cụ thể
SELECT id, id_nguoi_dung, tong_tien, trang_thai, created_at 
FROM dat_ve 
WHERE id_nguoi_dung = [USER_ID]
ORDER BY id DESC;

-- Kiểm tra bookings không có user
SELECT COUNT(*) FROM dat_ve WHERE id_nguoi_dung IS NULL;

-- Kiểm tra bookings không có created_at
SELECT COUNT(*) FROM dat_ve WHERE created_at IS NULL;
```

## Các vấn đề có thể gặp và cách giải quyết

### 1. Không thấy booking nào
**Nguyên nhân**: `id_nguoi_dung` trong bảng `dat_ve` không khớp với user hiện tại
**Giải pháp**: 
- Truy cập `/debug-bookings` để kiểm tra
- Kiểm tra query SQL trong database

### 2. Hiển thị "Chưa có thông tin ghế"
**Nguyên nhân**: Không có dữ liệu trong bảng `chi_tiet_dat_ve`
**Giải pháp**: Kiểm tra xem booking có `chi_tiet_dat_ve` không

### 3. Tổng tiền không đúng
**Nguyên nhân**: Logic tính toán khuyến mãi hoặc combo
**Giải pháp**: Kiểm tra lại logic trong view hoặc sử dụng giá trị `tong_tien` đã lưu trong database

## Hỗ trợ

Nếu vẫn gặp vấn đề, kiểm tra:
1. File log: `storage/logs/laravel.log`
2. Trang debug: `/debug-bookings`
3. Database trực tiếp bằng phpMyAdmin hoặc MySQL client

---

**Ngày cập nhật**: 23/11/2024
**Phiên bản**: 1.0
