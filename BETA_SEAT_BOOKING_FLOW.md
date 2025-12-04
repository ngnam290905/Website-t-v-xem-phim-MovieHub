# Logic Chọn Ghế - Chuẩn Beta Cinemas

## 📋 Tóm tắt Flow

### Bước 1: User chọn ghế (selectSeats)
**Endpoint:** `POST /api/showtimes/{id}/select-seats`

**Hành động:**
- ✅ Chỉ HOLD ghế trong Redis (TTL 5 phút)
- ✅ KHÔNG tạo booking trong DB
- ✅ KHÔNG update ShowtimeSeat thành "booked"
- ✅ Trả về `booking_hold_id` (tạm thời)

**Trạng thái ghế:**
- Redis: `seat_hold:{showtime_id}:{seat_id}` = HOLD
- DB ShowtimeSeat: Không thay đổi (vẫn available hoặc giữ nguyên)
- DB DatVe: Không tạo

---

### Bước 2: User nhấn "Thanh toán" (store)
**Endpoint:** `POST /booking/store`

**Hành động:**
- ✅ Tạo booking trong DB với `trang_thai = 0` (pending)
- ✅ Tạo ChiTietDatVe (chi tiết ghế) - để lưu thông tin booking
- ✅ Tạo ThanhToan với `trang_thai = 0` (chưa thanh toán)
- ✅ Lưu `booking_hold_id` vào session
- ❌ KHÔNG update ShowtimeSeat thành "booked"
- ❌ Ghế vẫn ở trạng thái HOLD trong Redis

**Trạng thái ghế:**
- Redis: Vẫn HOLD (chưa hết TTL)
- DB ShowtimeSeat: Không thay đổi
- DB DatVe: Tạo mới với `trang_thai = 0`

---

### Bước 3: Payment Success (handlePaymentSuccess)
**Endpoint:** Callback từ VNPay/MoMo

**Hành động:**
- ✅ Update booking `trang_thai = 1` (đã thanh toán)
- ✅ Update ThanhToan `trang_thai = 1`
- ✅ Update ShowtimeSeat `status = 'booked'` (SOLD)
- ✅ Release HOLD từ Redis
- ✅ Generate ticket_code

**Trạng thái ghế:**
- Redis: Đã xóa HOLD
- DB ShowtimeSeat: `status = 'booked'` (SOLD)
- DB DatVe: `trang_thai = 1` (đã thanh toán)

---

### Bước 4: Payment Fail (handlePaymentFailure)
**Hành động:**
- ✅ Release HOLD từ Redis ngay lập tức
- ✅ Xóa booking và ChiTietDatVe, ChiTietCombo, ThanhToan
- ✅ Update ShowtimeSeat về `available` (nếu chưa sold)

**Trạng thái ghế:**
- Redis: Đã xóa HOLD
- DB ShowtimeSeat: `status = 'available'`
- DB DatVe: Đã xóa

---

## 🔍 Kiểm tra Logic Hiện Tại

### ✅ Đúng chuẩn Beta:

1. **selectSeats()** - ✅ Chỉ HOLD trong Redis, không tạo DB
2. **store()** - ✅ Tạo booking pending, KHÔNG update ghế thành booked
3. **handlePaymentSuccess()** - ✅ Chỉ khi success mới update thành sold
4. **handlePaymentFailure()** - ✅ Release hold và xóa booking

### ⚠️ Cần kiểm tra:

1. **Frontend có gửi booking_hold_id không?**
   - Cần đảm bảo khi gọi store(), frontend gửi `booking_hold_id` từ selectSeats()

2. **Validation ghế trong store()**
   - Cần kiểm tra ghế vẫn còn HOLD trước khi tạo booking
   - Tránh trường hợp ghế đã hết TTL nhưng vẫn tạo booking

3. **Cleanup expired holds**
   - Redis TTL tự động cleanup
   - Database fallback cần cron job hoặc middleware cleanup

---

## 🎯 Trạng thái Ghế

| Trạng thái | Redis | DB ShowtimeSeat | Ý nghĩa |
|------------|-------|-----------------|---------|
| **available** | Không có | `status = 'available'` | Ghế trống, có thể chọn |
| **hold** | `seat_hold:{showtime}:{seat}` (TTL 5 phút) | `status = 'holding'` (fallback) | User đang chọn, chưa thanh toán |
| **sold** | Không có | `status = 'booked'` | Đã thanh toán thành công |
| **reserved** | Không có | `status = 'reserved'` | Staff đặt trực tiếp (chờ thanh toán quầy) |

---

## 🔄 Flow Diagram

```
User chọn ghế
    ↓
selectSeats() → HOLD trong Redis (TTL 5 phút)
    ↓
Trả về booking_hold_id
    ↓
User nhấn "Thanh toán"
    ↓
store() → Tạo booking (trang_thai = 0)
    ↓
    ├─→ ChiTietDatVe (lưu thông tin)
    ├─→ ThanhToan (trang_thai = 0)
    └─→ Lưu booking_hold_id vào session
    ↓
Redirect đến VNPay/MoMo
    ↓
    ├─→ Payment Success
    │       ↓
    │   handlePaymentSuccess()
    │       ├─→ Update booking trang_thai = 1
    │       ├─→ Update ShowtimeSeat = 'booked'
    │       └─→ Release HOLD từ Redis
    │
    └─→ Payment Fail
            ↓
        handlePaymentFailure()
            ├─→ Release HOLD từ Redis
            ├─→ Xóa booking
            └─→ Ghế về available
```

---

## ✅ Checklist Chuẩn Beta

- [x] Chọn ghế → chỉ HOLD (không tạo DB)
- [x] Nhấn thanh toán → tạo booking pending (không update ghế thành booked)
- [x] Payment success → mới update ghế thành SOLD
- [x] Payment fail → giải phóng HOLD ngay lập tức
- [x] TTL 5 phút tự động cleanup
- [x] Không có ghế nào bị kẹt khi payment fail
- [x] Frontend hiển thị đúng màu (hold = vàng, sold = đỏ)
- [x] Frontend gửi booking_hold_id khi thanh toán
- [x] Backend validate ghế vẫn còn HOLD trước khi tạo booking
- [x] Lưu booking_hold_id vào session để xử lý payment callback

---

## 🔧 Các Thay Đổi Đã Thực Hiện

### 1. Frontend (booking.blade.php)
- ✅ Thêm biến `currentBookingHoldId` để lưu booking_hold_id từ selectSeats()
- ✅ Lưu `booking_hold_id` khi selectSeats() thành công
- ✅ Gửi `booking_hold_id` khi gọi store() để thanh toán
- ✅ Clear `booking_hold_id` khi deselect ghế

### 2. Backend (BookingController.php)
- ✅ Thêm validation kiểm tra ghế vẫn còn HOLD trước khi tạo booking
- ✅ Kiểm tra booking_hold_id còn hợp lệ (chưa hết TTL)
- ✅ Lưu booking_hold_id vào session để PaymentController sử dụng

### 3. PaymentController.php
- ✅ Lấy booking_hold_id từ session khi xử lý payment callback
- ✅ Release HOLD từ Redis khi payment success
- ✅ Release HOLD từ Redis khi payment fail

