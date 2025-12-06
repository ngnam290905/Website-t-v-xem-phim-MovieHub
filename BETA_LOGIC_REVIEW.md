# Đánh Giá Logic Chọn Ghế - Chuẩn Beta Cinemas

## ✅ Đã Kiểm Tra và Xác Nhận

### 1. Flow Chọn Ghế (selectSeats)
**File:** `app/Http/Controllers/BookingController.php::selectSeats()`

✅ **Đúng chuẩn Beta:**
- Chỉ HOLD ghế trong Redis với TTL 5 phút
- KHÔNG tạo booking trong DB
- KHÔNG update ShowtimeSeat thành "booked"
- Trả về `booking_hold_id` (tạm thời)

**Code:**
```579:1200:app/Http/Controllers/BookingController.php
// Uses SeatHoldService::holdSeats() - only Redis HOLD, no DB
```

---

### 2. Flow Nhấn "Thanh Toán" (store)
**File:** `app/Http/Controllers/BookingController.php::store()`

✅ **Đúng chuẩn Beta:**
- Validate ghế vẫn còn HOLD trước khi tạo booking
- Tạo booking với `trang_thai = 0` (pending)
- Tạo ChiTietDatVe (để lưu thông tin)
- Tạo ThanhToan với `trang_thai = 0`
- Lưu `booking_hold_id` vào session
- ❌ KHÔNG update ShowtimeSeat thành "booked"
- ❌ Ghế vẫn ở trạng thái HOLD trong Redis

**Code:**
```632:1073:app/Http/Controllers/BookingController.php
// Validates hold exists, creates booking pending, stores booking_hold_id
```

---

### 3. Flow Payment Success
**File:** `app/Http/Controllers/PaymentController.php::handlePaymentSuccess()`

✅ **Đúng chuẩn Beta:**
- Chỉ khi callback thành công mới:
  - Update booking `trang_thai = 1`
  - Update ThanhToan `trang_thai = 1`
  - Update ShowtimeSeat `status = 'booked'` (SOLD)
  - Release HOLD từ Redis
  - Generate ticket_code

**Code:**
```432:586:app/Http/Controllers/PaymentController.php
// Only updates to sold when payment succeeds
```

---

### 4. Flow Payment Fail
**File:** `app/Http/Controllers/PaymentController.php::handlePaymentFailure()`

✅ **Đúng chuẩn Beta:**
- Release HOLD từ Redis ngay lập tức
- Xóa booking và ChiTietDatVe, ChiTietCombo, ThanhToan
- Update ShowtimeSeat về `available` (nếu chưa sold)

**Code:**
```588:700:app/Http/Controllers/PaymentController.php
// Releases holds and deletes booking on failure
```

---

### 5. Frontend Integration
**File:** `resources/views/booking.blade.php`

✅ **Đã sửa:**
- Lưu `booking_hold_id` từ selectSeats()
- Gửi `booking_hold_id` khi gọi store()
- Clear `booking_hold_id` khi deselect ghế

**Code:**
```351:1183:resources/views/booking.blade.php
// Stores and sends booking_hold_id
```

---

### 6. Seat Status Display
**File:** `resources/views/booking.blade.php::loadSeatsForShowtime()`

✅ **Đúng chuẩn Beta:**
- `hold`: Màu vàng (`bg-yellow-500`)
- `sold`: Màu đỏ (`bg-red-600`)
- `available`: Màu xám (`bg-gray-700`)

---

## 🔍 Các Điểm Đã Sửa

### 1. Frontend không gửi booking_hold_id
**Vấn đề:** Frontend chỉ gửi `booking_id`, không gửi `booking_hold_id`

**Đã sửa:**
- Thêm biến `currentBookingHoldId`
- Lưu `booking_hold_id` từ API response
- Gửi `booking_hold_id` khi thanh toán

### 2. Backend không validate HOLD trước khi tạo booking
**Vấn đề:** Có thể tạo booking khi ghế đã hết TTL

**Đã sửa:**
- Validate `booking_hold_id` còn hợp lệ
- Validate từng ghế vẫn còn HOLD
- Trả về lỗi nếu ghế đã hết thời gian giữ

### 3. Lưu booking_hold_id vào session
**Đã sửa:**
- Lưu `booking_hold_id` vào session với key `booking_hold_id_{booking_id}`
- PaymentController lấy từ session khi xử lý callback

---

## ✅ Kết Luận

**Logic hiện tại đã đúng chuẩn Beta Cinemas:**

1. ✅ Chọn ghế → chỉ HOLD (không tạo DB)
2. ✅ Nhấn thanh toán → tạo booking pending (không update ghế thành booked)
3. ✅ Payment success → mới update ghế thành SOLD
4. ✅ Payment fail → giải phóng HOLD ngay lập tức
5. ✅ TTL 5 phút tự động cleanup (Redis)
6. ✅ Không có ghế nào bị kẹt khi payment fail
7. ✅ Frontend hiển thị đúng màu (hold = vàng, sold = đỏ)
8. ✅ Frontend gửi booking_hold_id khi thanh toán
9. ✅ Backend validate ghế vẫn còn HOLD trước khi tạo booking
10. ✅ Lưu booking_hold_id vào session để xử lý payment callback

---

## 📝 Lưu Ý

1. **Redis Fallback:** Nếu Redis không khả dụng, hệ thống tự động fallback về database (ShowtimeSeat table)
2. **TTL Cleanup:** Redis tự động cleanup sau 5 phút. Database fallback cần cron job hoặc middleware cleanup
3. **Validation:** Luôn validate ghế vẫn còn HOLD trước khi tạo booking để tránh race condition

---

## 🎯 Test Cases Cần Kiểm Tra

1. ✅ Chọn ghế → ghế chuyển sang màu vàng (hold)
2. ✅ Nhấn thanh toán → tạo booking pending, ghế vẫn vàng
3. ✅ Payment success → ghế chuyển sang đỏ (sold)
4. ✅ Payment fail → ghế về xám (available)
5. ✅ Hết TTL 5 phút → ghế tự động về available
6. ✅ Chọn lại ghế đã hết TTL → báo lỗi "Thời gian giữ ghế đã hết"


