# Tài liệu triển khai logic giữ ghế mới

## Tổng quan

Hệ thống đã được thay đổi hoàn toàn từ logic giữ ghế cũ (Redis-based, 5 phút) sang logic mới (Database-based, 10 phút) theo chuẩn rạp chiếu phim.

## Thay đổi chính

### 1. Database
- **Bảng mới**: `seat_holds` - Lưu trữ thông tin giữ ghế trong database
- **Migration**: `2025_01_20_000000_create_seat_holds_table.php`
- **Cấu trúc**:
  - `showtime_id`: ID suất chiếu
  - `seat_id`: ID ghế
  - `user_id`: ID người dùng (nullable)
  - `session_id`: Session ID cho guest (nullable)
  - `expires_at`: Thời gian hết hạn (10 phút)
  - Unique constraint: `(showtime_id, seat_id)` - Một ghế chỉ có thể được giữ bởi một user tại một thời điểm

### 2. Model mới
- **SeatHold** (`app/Models/SeatHold.php`): Model quản lý seat holds
  - Scopes: `active()`, `expired()`, `forShowtime()`, `forSeat()`, `forUser()`, `forSession()`
  - Methods: `isExpired()`, `isActive()`, `releaseExpired()`

### 3. Service mới
- **SeatHoldService** (`app/Services/SeatHoldService.php`): Service xử lý logic giữ ghế
  - `holdSeat()`: Giữ một ghế (10 phút)
  - `holdSeats()`: Giữ nhiều ghế cùng lúc
  - `releaseSeat()`: Nhả một ghế
  - `releaseSeats()`: Nhả nhiều ghế
  - `getSeatStatus()`: Lấy trạng thái ghế ('available', 'held_by_me', 'held_by_other', 'booked')
  - `confirmBooking()`: Xác nhận đặt vé (chuyển hold → booked)
  - `cleanupExpiredHolds()`: Dọn dẹp ghế hết hạn

### 4. API Endpoints mới
- `POST /shows/{showId}/seats/hold` - Giữ một ghế
- `POST /shows/{showId}/seats/release` - Nhả một ghế
- `POST /shows/{showId}/seats/confirm-booking` - Xác nhận đặt vé
- `POST /shows/{showId}/seats/lock` - Giữ nhiều ghế (giữ lại cho tương thích)
- `POST /shows/{showId}/seats/unlock` - Nhả nhiều ghế (giữ lại cho tương thích)
- `GET /shows/{showId}/seats/refresh` - Làm mới trạng thái ghế

### 5. Cron Job
- **Command**: `seats:release-expired`
- **File**: `app/Console/Commands/ReleaseExpiredSeatHolds.php`
- **Schedule**: Chạy mỗi phút (trong `routes/console.php`)
- **Chức năng**: Tự động giải phóng các ghế hết hạn

### 6. Logic thanh toán
- Khi thanh toán thành công, `handlePaymentSuccess()` trong `PaymentController` sẽ:
  1. Cập nhật booking status = 1 (paid)
  2. Gọi `SeatHoldService::confirmBooking()` để release các seat holds
  3. Ghế được chuyển sang trạng thái BOOKED

## Luồng xử lý mới

### Khi user chọn ghế:
1. User click vào ghế → Frontend gọi `POST /shows/{showId}/seats/lock`
2. Backend kiểm tra:
   - Ghế có tồn tại không?
   - Ghế đã được đặt (booked) chưa?
   - Ghế đang được người khác giữ chưa?
3. Nếu cùng user chọn lại → Auto-extend thời gian giữ (10 phút mới)
4. Nếu hợp lệ → Tạo record trong `seat_holds` với `expires_at = now() + 10 phút`
5. Frontend cập nhật UI, hiển thị timer 10 phút

### Khi ghế hết hạn:
1. Cron job chạy mỗi phút
2. Tìm tất cả records trong `seat_holds` có `expires_at <= now()`
3. Xóa các records này
4. Ghế tự động trở về trạng thái 'available'

### Khi thanh toán thành công:
1. Payment callback nhận được response code = '00'
2. `handlePaymentSuccess()` được gọi
3. Cập nhật booking status = 1 (paid)
4. Gọi `SeatHoldService::confirmBooking()` để release holds
5. Ghế được đánh dấu là BOOKED (thông qua `ChiTietDatVe` với `trang_thai = 1`)

## Files đã thay đổi

### Files mới:
- `database/migrations/2025_01_20_000000_create_seat_holds_table.php`
- `app/Models/SeatHold.php`
- `app/Services/SeatHoldService.php` (viết lại hoàn toàn)
- `app/Console/Commands/ReleaseExpiredSeatHolds.php`
- `SEAT_HOLD_IMPLEMENTATION.md` (file này)

### Files đã cập nhật:
- `app/Http/Controllers/BookingController.php`:
  - Thêm `holdSeat()`, `releaseSeat()`, `confirmBooking()`
  - Cập nhật `lockSeats()`, `unlockSeats()`, `refreshSeats()`
- `app/Http/Controllers/PaymentController.php`:
  - Thêm logic `confirmBooking()` trong `handlePaymentSuccess()`
- `routes/web.php`:
  - Thêm routes mới cho hold/release/confirm
- `routes/console.php`:
  - Thêm schedule cho cron job
- `resources/views/booking/seats.blade.php`:
  - Cập nhật JavaScript để gọi API mới
  - Thay đổi timer từ 5 phút → 10 phút
  - Cập nhật logic refresh seats

### Files đã xóa:
- `app/Services/SeatLockService.php` (không còn dùng)
- `app/Models/SeatLock.php` (không còn dùng)
- `app/Services/LegacySeatLockService.php` (không còn dùng)

### Files đã cập nhật (loại bỏ references):
- `app/Models/Seat.php` - Xóa relationship `seatLocks()`
- `app/Models/Show.php` - Xóa relationship `seatLocks()`
- `app/Services/PaymentService.php` - Xóa dependency `SeatLockService`
- `app/Services/BookingService.php` - Xóa dependency `SeatLockService` (có comment về legacy code)

## Cách test

### 1. Chạy migration:
```bash
php artisan migrate
```

### 2. Test giữ ghế:
1. Đăng nhập vào hệ thống
2. Chọn một suất chiếu
3. Click vào một ghế trống
4. Kiểm tra:
   - Ghế chuyển sang màu selected
   - Timer hiển thị 10:00 và đếm ngược
   - Trong database, có record trong `seat_holds`

### 3. Test auto-extend:
1. Chọn một ghế đã được giữ bởi chính mình
2. Kiểm tra: Thời gian hết hạn được gia hạn thêm 10 phút

### 4. Test ghế đã được giữ:
1. User A chọn ghế X
2. User B (hoặc tab khác) cố chọn ghế X
3. Kiểm tra: User B nhận thông báo "Ghế đang được người khác chọn"

### 5. Test hết hạn:
1. Chọn một ghế
2. Đợi 10 phút (hoặc thay đổi `expires_at` trong DB để test nhanh)
3. Chạy cron job: `php artisan seats:release-expired`
4. Kiểm tra: Record trong `seat_holds` đã bị xóa

### 6. Test thanh toán:
1. Chọn ghế và thanh toán thành công
2. Kiểm tra:
   - Booking status = 1 (paid)
   - Record trong `seat_holds` đã bị xóa
   - Ghế không thể chọn lại (đã booked)

### 7. Test refresh real-time:
1. Mở 2 tab/browser khác nhau
2. Tab 1: Chọn ghế X
3. Tab 2: Kiểm tra sau 5 giây, ghế X hiển thị là "locked_by_other"

## Lưu ý

1. **Cron job**: Đảm bảo Laravel scheduler đang chạy:
   ```bash
   php artisan schedule:work
   ```
   Hoặc thêm vào crontab:
   ```
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

2. **Database**: Bảng `seat_holds` sẽ tự động tăng dần theo thời gian. Cron job sẽ tự động dọn dẹp.

3. **Performance**: Với hệ thống lớn, có thể cần thêm index hoặc optimize query trong `SeatHoldService`.

4. **WebSocket**: Hiện tại chưa có WebSocket cho real-time updates. Frontend dùng polling (refresh mỗi 5 giây). Có thể thêm WebSocket sau nếu cần.

5. **Legacy code**: Một số service như `BookingService`, `PaymentService` vẫn có references đến `SeatLockService` nhưng đã được comment. Nếu các API controllers sử dụng các service này, cần cập nhật thêm.

## Kết luận

Hệ thống giữ ghế mới đã được triển khai hoàn toàn:
- ✅ Database-based (không phụ thuộc Redis)
- ✅ Thời gian giữ: 10 phút
- ✅ Auto-extend khi user chọn lại
- ✅ Tự động giải phóng khi hết hạn
- ✅ Chuyển sang BOOKED khi thanh toán thành công
- ✅ Real-time updates (polling mỗi 5 giây)

Tất cả chức năng khác (tìm kiếm, thanh toán, combo, giỏ hàng) vẫn hoạt động bình thường.

