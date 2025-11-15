# Booking Implementation Summary

## Đã hoàn thành

### 1. Phân tích Flow Booking & Database Design ✅

**Tài liệu**: `BOOKING_FLOW_ANALYSIS.md`

- Phân tích chi tiết user journey từ chọn phim đến thanh toán
- Thiết kế database với đầy đủ các bảng và quan hệ
- Mô tả seat locking mechanism
- Business rules và validation logic

**Các bảng chính**:
- `phim` (movies) - Thông tin phim
- `phong_chieu` (rooms) - Phòng chiếu
- `suat_chieu` (shows) - Lịch chiếu
- `ghe` (seats) - Ghế trong phòng
- `loai_ghe` (seat_types) - Loại ghế (Thường, VIP)
- `combo` (combos) - Combo bắp nước
- `dat_ve` (bookings) - Đặt vé
- `chi_tiet_dat_ve` (booking_seats) - Chi tiết ghế đã đặt
- `chi_tiet_combo` (booking_combos) - Chi tiết combo
- `seat_locks` - Lock ghế (hỗ trợ cả hệ thống mới và cũ)
- `thanh_toan` (payments) - Thanh toán

### 2. Trang chọn suất chiếu ✅

**Routes**:
- `GET /booking` - Chọn phim
- `GET /booking/movie/{movieId}/showtimes` - Chọn suất chiếu
- `GET /api/booking/movie/{movieId}/dates` - API lấy danh sách ngày
- `GET /api/booking/movie/{movieId}/showtimes?date={date}` - API lấy suất chiếu theo ngày

**Tính năng**:
- Hiển thị danh sách phim đang chiếu
- Date picker (hôm nay + 7 ngày tới)
- Load suất chiếu theo phim và ngày
- Hiển thị: giờ chiếu, phòng, giá

**Files**:
- `app/Http/Controllers/BookingFlowController.php`
- `resources/views/booking/index.blade.php`
- `resources/views/booking/showtimes.blade.php`

### 3. Layout ghế với phân biệt màu ✅

**Tính năng**:
- Sơ đồ ghế dạng lưới (hàng A, B, C... cột 1, 2, 3...)
- Phân biệt màu:
  - **Trống**: Màu xám - có thể chọn
  - **Đã đặt**: Màu đỏ - không cho chọn
  - **VIP**: Màu vàng - giá cao hơn
  - **Đang chọn**: Màu cam - ghế đang được user chọn
  - **Locked**: Màu xám đậm - đang được người khác chọn
  - **Disabled**: Màu xám nhạt - vô hiệu
- Click ghế trống → chuyển sang "đang chọn"
- Cập nhật tổng tiền real-time
- Timer đếm ngược 5 phút
- Auto-refresh trạng thái ghế mỗi 5 giây

**Files**:
- `resources/views/booking/seats.blade.php`
- `app/Http/Controllers/BookingController.php` - `showSeats()`

### 4. Seat Locking Mechanism ✅

**Implementation**:
- Tạo `LegacySeatLockService` tương thích với hệ thống hiện tại
- Sử dụng Cache làm primary storage (performance)
- Hỗ trợ Database (seat_locks table) làm backup
- Lock duration: 5 phút
- Unique constraint đảm bảo không trùng ghế
- Auto cleanup locks hết hạn

**Tính năng**:
- `lockSeats()` - Lock nhiều ghế cùng lúc
- `unlockSeats()` - Unlock ghế
- `isSeatLocked()` - Kiểm tra ghế có bị lock không
- `getSeatStatus()` - Lấy trạng thái ghế (AVAILABLE, LOCKED, SOLD)
- `releaseLocksForBooking()` - Release locks khi thanh toán thành công
- `cleanupExpiredLocks()` - Cleanup locks hết hạn

**Files**:
- `app/Services/LegacySeatLockService.php`
- `app/Http/Controllers/BookingController.php` - Updated to use service
- `app/Console/Commands/CleanupExpiredSeatLocks.php` - Command cleanup

### 5. Full Booking Flow ✅

**Flow hoàn chỉnh**:

1. **Chọn phim** (`/booking`)
   - Hiển thị danh sách phim đang chiếu
   - Click vào phim → chuyển sang chọn suất chiếu

2. **Chọn ngày & suất chiếu** (`/booking/movie/{id}/showtimes`)
   - Chọn ngày từ date picker
   - Load suất chiếu theo ngày
   - Click "Chọn ghế" → chuyển sang chọn ghế

3. **Chọn ghế** (`/shows/{showId}/seats`)
   - Hiển thị sơ đồ ghế
   - Click ghế → lock ghế (5 phút)
   - Chọn combo (optional)
   - Click "Tiếp tục thanh toán"

4. **Chọn combo** (`/bookings/{bookingId}/addons`)
   - Chọn combo bắp nước
   - Cập nhật số lượng
   - Click "Tiếp tục"

5. **Thanh toán** (`/checkout/{bookingId}`)
   - Nhập thông tin: tên, email, SĐT
   - Chọn phương thức thanh toán
   - Submit → xử lý thanh toán

6. **Kết quả** (`/result?booking_id={id}`)
   - Nếu thành công:
     - Booking status = PAID
     - Release seat locks
     - Hiển thị thông tin vé
   - Nếu thất bại:
     - Booking status = FAILED/CANCELLED
     - Release seat locks

## Cải thiện đã thực hiện

### Seat Locking
- **Trước**: Chỉ sử dụng Cache
- **Sau**: Sử dụng Cache + Database (hybrid)
- **Lợi ích**: 
  - Performance tốt (Cache)
  - Reliability cao (Database backup)
  - Dễ dàng cleanup và monitoring

### Code Organization
- Tách logic seat locking vào Service
- Dễ dàng test và maintain
- Tái sử dụng được

### Error Handling
- Validate ghế trước khi lock
- Xử lý conflict khi nhiều user cùng chọn
- Auto cleanup locks hết hạn

## Testing Checklist

### Manual Testing
- [ ] Chọn phim → hiển thị danh sách phim đang chiếu
- [ ] Chọn ngày → load suất chiếu đúng
- [ ] Chọn ghế → lock ghế thành công
- [ ] Ghế đã lock không thể chọn bởi user khác
- [ ] Timer đếm ngược đúng
- [ ] Lock hết hạn → ghế trở về available
- [ ] Chọn combo → cập nhật tổng tiền
- [ ] Thanh toán thành công → booking status = PAID, seat status = SOLD
- [ ] Thanh toán thất bại → release locks
- [ ] Không có trùng ghế khi nhiều user cùng đặt

### Automated Testing (Recommended)
- Unit tests cho `LegacySeatLockService`
- Integration tests cho booking flow
- Test concurrency (nhiều user cùng chọn ghế)

## Commands

### Cleanup Expired Locks
```bash
php artisan booking:cleanup-locks
```

**Recommended**: Chạy mỗi phút qua cron job:
```cron
* * * * * cd /path-to-project && php artisan booking:cleanup-locks >> /dev/null 2>&1
```

## API Endpoints

### Booking Flow
- `GET /booking` - Trang chọn phim
- `GET /booking/movie/{movieId}/showtimes` - Trang chọn suất chiếu
- `GET /api/booking/movie/{movieId}/dates` - API lấy danh sách ngày
- `GET /api/booking/movie/{movieId}/showtimes?date={date}` - API lấy suất chiếu

### Seat Selection
- `GET /shows/{showId}/seats` - Trang chọn ghế
- `POST /shows/{showId}/seats/lock` - Lock ghế
- `POST /shows/{showId}/seats/unlock` - Unlock ghế
- `GET /shows/{showId}/seats/refresh` - Refresh trạng thái ghế

### Booking Management
- `GET /bookings/{bookingId}/addons` - Trang chọn combo
- `POST /bookings/{bookingId}/addons` - Cập nhật combo
- `GET /checkout/{bookingId}` - Trang thanh toán
- `POST /checkout/{bookingId}/payment` - Xử lý thanh toán
- `GET /result?booking_id={bookingId}` - Trang kết quả
- `GET /tickets` - Danh sách vé của tôi

## Next Steps (Optional)

1. **Payment Gateway Integration**
   - VNPay
   - MoMo
   - Credit Card

2. **Email Notifications**
   - Gửi email khi đặt vé thành công
   - Gửi QR code vé

3. **Real-time Updates**
   - WebSocket để update trạng thái ghế real-time
   - Không cần refresh page

4. **Analytics**
   - Thống kê booking
   - Doanh thu theo phim/phòng

5. **Mobile App**
   - API cho mobile app
   - Push notifications

## Notes

- Hệ thống hiện tại sử dụng models tiếng Việt (Phim, SuatChieu, Ghe, DatVe)
- Seat locking sử dụng Cache làm primary, Database làm backup
- Lock duration: 5 phút (có thể config trong `LegacySeatLockService`)
- Unique constraint trên `seat_locks(show_id, seat_id)` đảm bảo không trùng ghế

