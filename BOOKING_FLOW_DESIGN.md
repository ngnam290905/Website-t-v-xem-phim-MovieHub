# Booking Flow & Database Design

## 1. Flow Booking Analysis

### User Journey
1. **Chọn phim** (`/booking`)
   - User xem danh sách phim đang chiếu
   - Click vào phim để tiếp tục

2. **Chọn ngày và suất chiếu** (`/booking/movie/{id}/showtimes`)
   - User chọn ngày (hôm nay + 7 ngày tới)
   - Hệ thống load suất chiếu theo phim và ngày
   - User chọn suất chiếu cụ thể

3. **Chọn ghế** (`/shows/{showId}/seats`)
   - Hiển thị layout ghế với màu sắc phân biệt
   - User chọn ghế (có thể chọn nhiều)
   - Hệ thống lock ghế trong 5 phút
   - User có thể chọn combo bắp nước

4. **Checkout** (`/checkout/{bookingId}`)
   - Xem lại thông tin đặt vé
   - Nhập thông tin khách hàng (tên, email, SĐT)
   - Chọn phương thức thanh toán

5. **Thanh toán** (`/checkout/{bookingId}/payment`)
   - Xử lý thanh toán
   - Seat lock được giải phóng sau khi thanh toán thành công
   - Ghế chuyển sang trạng thái "ĐÃ ĐẶT"

6. **Kết quả** (`/result`)
   - Hiển thị thông tin vé
   - Mã vé, QR code (nếu có)
   - Chi tiết ghế, giờ chiếu

## 2. Database Design

### Bảng `movies` (phim)
- `id`: ID phim
- `title` / `ten_phim`: Tên phim
- `duration_minutes` / `do_dai`: Thời lượng (phút)
- `poster`: URL poster
- `synopsis` / `mo_ta`: Mô tả
- `rating`: Đánh giá
- `status` / `trang_thai`: Trạng thái (dang_chieu, sap_chieu, ngung_chieu)

### Bảng `rooms` (phong_chieu)
- `id`: ID phòng
- `name` / `ten_phong`: Tên phòng
- `capacity`: Sức chứa
- `type` / `loai_phong`: Loại phòng (normal, vip, 3d, imax)
- `rows`: Số hàng ghế
- `cols`: Số cột ghế
- `status`: Trạng thái (active, inactive)

### Bảng `shows` (suat_chieu)
- `id`: ID suất chiếu
- `movie_id` / `id_phim`: FK → movies
- `room_id` / `id_phong`: FK → rooms
- `start_at` / `thoi_gian_bat_dau`: Giờ bắt đầu
- `end_at` / `thoi_gian_ket_thuc`: Giờ kết thúc
- `base_price`: Giá cơ bản
- `status` / `trang_thai`: Trạng thái

### Bảng `seats` (ghe)
- `id`: ID ghế
- `room_id` / `id_phong`: FK → rooms
- `row` / `so_hang`: Hàng (A, B, C...)
- `number` / `so_ghe`: Số ghế
- `seat_code` / `so_ghe`: Mã ghế (A1, B3...)
- `type`: Loại ghế (STANDARD, VIP, COUPLE)
- `status`: Trạng thái (available, booked, locked, disabled)

### Bảng `combos`
- `id`: ID combo
- `name` / `ten`: Tên combo
- `description` / `mo_ta`: Mô tả
- `items`: JSON danh sách items
- `price` / `gia`: Giá
- `is_active` / `trang_thai`: Trạng thái

### Bảng `bookings` (dat_ve)
- `id`: ID booking
- `user_id` / `id_nguoi_dung`: FK → users (nullable)
- `show_id` / `id_suat_chieu`: FK → shows
- `status`: Trạng thái (PENDING, LOCKED, PAID, CANCELLED, EXPIRED)
- `lock_expires_at`: Thời gian hết hạn lock
- `subtotal`: Tổng tiền ghế
- `discount`: Giảm giá
- `total` / `tong_tien`: Tổng tiền
- `payment_provider`: Cổng thanh toán
- `payment_ref`: Mã tham chiếu thanh toán

### Bảng `booking_seats` (chi_tiet_dat_ve)
- `id`: ID
- `booking_id` / `id_dat_ve`: FK → bookings
- `seat_id` / `id_ghe`: FK → seats
- `price` / `gia`: Giá ghế
- `status`: Trạng thái (LOCKED, SOLD)

### Bảng `booking_combos` (chi_tiet_combo)
- `id`: ID
- `booking_id` / `id_dat_ve`: FK → bookings
- `combo_id` / `id_combo`: FK → combos
- `unit_price` / `gia_ap_dung`: Giá đơn vị
- `qty` / `so_luong`: Số lượng
- `total_price`: Tổng tiền

### Bảng `seat_locks`
- `id`: ID
- `show_id`: FK → shows
- `seat_id`: FK → seats
- `booking_id`: FK → bookings (nullable)
- `expires_at`: Thời gian hết hạn
- Unique constraint: (`show_id`, `seat_id`)

### Bảng `payments` (thanh_toan)
- `id`: ID
- `booking_id` / `id_dat_ve`: FK → bookings
- `amount` / `so_tien`: Số tiền
- `status`: Trạng thái (INIT, SUCCESS, FAIL)
- `provider`: Cổng thanh toán
- `transaction_id`: Mã giao dịch
- `paid_at`: Thời gian thanh toán
- `payload`: JSON dữ liệu bổ sung

## 3. Relationships

```
movies (1) → (N) shows
  - Một phim có nhiều suất chiếu

shows (1) → (1) rooms
  - Một suất chiếu diễn ra trong một phòng

rooms (1) → (N) seats
  - Một phòng có nhiều ghế

shows (1) → (N) bookings
  - Một suất chiếu có nhiều booking

bookings (1) → (N) booking_seats
  - Một booking có nhiều ghế

bookings (1) → (N) booking_combos
  - Một booking có nhiều combo

shows (1) → (N) seat_locks
  - Một suất chiếu có nhiều seat lock

bookings (1) → (1) payments
  - Một booking có một payment
```

## 4. Seat Lock Mechanism

### Flow
1. User chọn ghế → Gọi API `/shows/{showId}/seats/lock`
2. Backend kiểm tra:
   - Ghế có bị đặt chưa?
   - Ghế có đang bị lock bởi người khác không?
3. Nếu hợp lệ:
   - Tạo record trong `seat_locks` với `expires_at = now() + 5 minutes`
   - Tạo/update booking với status = `LOCKED`
   - Trả về `booking_id` và `expires_at`
4. Frontend:
   - Hiển thị timer đếm ngược
   - Auto refresh seat status mỗi 5 giây
   - Nếu hết hạn → Reload page và thông báo
5. Khi thanh toán thành công:
   - Update booking status = `PAID`
   - Update booking_seats status = `SOLD`
   - Xóa seat_locks tương ứng
   - Ghế chuyển sang trạng thái "ĐÃ ĐẶT" vĩnh viễn

### Cleanup
- Cron job chạy mỗi phút để:
  - Xóa seat_locks hết hạn
  - Update bookings hết hạn → status = `EXPIRED`

## 5. Seat Status Colors

- **Trống (Available)**: Xám (#2A2F3A) - Có thể chọn
- **VIP**: Vàng (#92400e) - Giá cao hơn, có thể chọn nếu trống
- **Đã chọn (Selected)**: Cam đỏ (#FF784E) - Ghế user đang chọn
- **Đang chọn (Locked by other)**: Xám mờ - Người khác đang chọn
- **Đã bán (Sold)**: Đỏ với dấu X - Không thể chọn
- **Vô hiệu (Disabled)**: Trong suốt - Không có ghế ở vị trí này

## 6. API Endpoints

### Public
- `GET /booking` - Trang chọn phim
- `GET /booking/movie/{movieId}/showtimes` - Trang chọn suất chiếu
- `GET /api/booking/movie/{movieId}/showtimes?date=YYYY-MM-DD` - API lấy suất chiếu
- `GET /api/booking/movie/{movieId}/dates` - API lấy danh sách ngày có suất chiếu

### Authenticated
- `GET /shows/{showId}/seats` - Trang chọn ghế
- `POST /shows/{showId}/seats/lock` - Lock ghế
- `POST /shows/{showId}/seats/unlock` - Unlock ghế
- `GET /shows/{showId}/seats/refresh` - Refresh trạng thái ghế
- `GET /bookings/{bookingId}/addons` - Trang chọn combo
- `POST /bookings/{bookingId}/addons` - Update combo
- `GET /checkout/{bookingId}` - Trang checkout
- `POST /checkout/{bookingId}/payment` - Xử lý thanh toán
- `GET /result?booking_id={id}` - Kết quả thanh toán
- `GET /tickets` - Danh sách vé của tôi

## 7. Testing Checklist

- [ ] Chọn phim → Hiển thị danh sách phim đang chiếu
- [ ] Chọn ngày → Load suất chiếu đúng theo ngày
- [ ] Chọn suất chiếu → Chuyển đến trang chọn ghế
- [ ] Layout ghế hiển thị đúng với màu sắc phân biệt
- [ ] Chọn ghế trống → Ghế chuyển sang màu "đã chọn"
- [ ] Không thể chọn ghế đã bán
- [ ] Không thể chọn ghế đang bị lock bởi người khác
- [ ] Seat lock hoạt động đúng (5 phút)
- [ ] Timer đếm ngược hiển thị đúng
- [ ] Chọn combo → Cập nhật tổng tiền
- [ ] Checkout → Nhập thông tin khách hàng
- [ ] Thanh toán thành công → Booking status = PAID
- [ ] Sau thanh toán → Ghế chuyển sang "ĐÃ ĐẶT"
- [ ] Không có ghế trùng lặp (seat lock hoạt động đúng)

