# Booking Flow Check Report - Báo cáo kiểm tra

## ✅ Đã kiểm tra và sửa

### 1. Flow: Chọn phim → Chọn suất chiếu → Chọn ghế → Chọn combo → Checkout → Thanh toán → Kết quả

#### Step 1: Chọn phim ✅
- **Route**: `GET /booking`
- **Controller**: `BookingFlowController@index`
- **View**: `resources/views/booking/index.blade.php`
- **Status**: ✅ Hoạt động đúng
- Hiển thị danh sách phim đang chiếu (`trang_thai = 'dang_chieu'`)

#### Step 2: Chọn suất chiếu ✅
- **Route**: `GET /booking/movie/{movieId}/showtimes`
- **Controller**: `BookingFlowController@showtimes`
- **API**: `GET /api/booking/movie/{movieId}/showtimes?date={date}`
- **View**: `resources/views/booking/showtimes.blade.php`
- **Status**: ✅ Hoạt động đúng
- Date picker (hôm nay + 7 ngày)
- Load suất chiếu theo ngày (AJAX)
- Chỉ hiển thị suất chiếu chưa bắt đầu (`thoi_gian_bat_dau > now()`)

#### Step 3: Chọn ghế ✅
- **Route**: `GET /shows/{showId}/seats` (yêu cầu auth)
- **Controller**: `BookingController@showSeats`
- **View**: `resources/views/booking/seats.blade.php`
- **Status**: ✅ Hoạt động đúng
- Hiển thị layout ghế với màu sắc phân biệt
- Lock ghế khi chọn: `POST /shows/{showId}/seats/lock`
- Tạo booking DRAFT (`trang_thai = 0`)

#### Step 4: Chọn combo ✅
- **Route**: `GET /bookings/{bookingId}/addons`
- **Controller**: `BookingController@addons`
- **View**: `resources/views/booking/addons.blade.php`
- **Status**: ✅ Hoạt động đúng
- Kiểm tra lock còn hạn không
- Cập nhật combo: `POST /bookings/{bookingId}/addons`
- Tính lại tổng tiền

#### Step 5: Checkout ✅
- **Route**: `GET /checkout/{bookingId}`
- **Controller**: `BookingController@checkout`
- **View**: `resources/views/booking/checkout.blade.php`
- **Status**: ✅ Hoạt động đúng
- Xem lại thông tin đặt vé
- Nhập thông tin khách hàng
- Chọn phương thức thanh toán

#### Step 6: Thanh toán ✅
- **Route**: `POST /checkout/{bookingId}/payment`
- **Controller**: `BookingController@processPayment`
- **Status**: ✅ Hoạt động đúng
- Kiểm tra ghế còn khả dụng
- Giải phóng seat lock: `releaseLocksForBooking()`
- Cập nhật trạng thái: `trang_thai = 1` (PAID)
- Tạo payment record

#### Step 7: Kết quả ✅
- **Route**: `GET /result?booking_id={id}`
- **Controller**: `BookingController@result`
- **View**: `resources/views/booking/result.blade.php`
- **Status**: ✅ Hoạt động đúng
- Hiển thị thông tin vé
- Mã vé, QR code (nếu có)
- Chi tiết ghế, giờ chiếu

---

### 2. Seat locking: lock 5 phút, timer đếm ngược, tự động unlock khi hết hạn hoặc thanh toán thành công

#### Seat Lock Duration ✅
- **Constant**: `LegacySeatLockService::LOCK_DURATION_MINUTES = 5`
- **Location**: `app/Services/LegacySeatLockService.php` (line 14)
- **Status**: ✅ Đúng 5 phút

#### Lock Mechanism ✅
- **Service**: `LegacySeatLockService`
- **Methods**:
  - `lockSeats($showId, $seatIds, $userId)` - Lock ghế
  - `unlockSeats($showId, $seatIds, $userId)` - Unlock ghế
  - `isSeatLocked($showId, $seatId, $userId)` - Kiểm tra lock
  - `releaseLocksForBooking($bookingId)` - Giải phóng lock cho booking
- **Storage**: Cache + Database (seat_locks table)
- **Expires At**: `now()->addMinutes(5)`
- **Status**: ✅ Hoạt động đúng

#### Timer đếm ngược ✅
- **Location**: `resources/views/booking/seats.blade.php`
- **Function**: `startTimer()` (line 981)
- **Display**: 
  - `<span id="timer-minutes">5</span>:<span id="timer-seconds">00</span>`
  - Hiển thị ở header và sidebar
- **Features**:
  - Đếm ngược từ 5:00
  - Đổi màu đỏ khi còn < 30 giây
  - Tự động refresh khi hết hạn
- **Status**: ✅ Hoạt động đúng

#### Auto Unlock ✅
- **Khi hết hạn**: 
  - Timer đếm đến 0 → gọi `refreshSeats()` → ghế trở về available
  - Cache tự động expire sau 5 phút
- **Khi thanh toán thành công**:
  - `processPayment()` → `releaseLocksForBooking($bookingId)`
  - Xóa lock từ cache và database
- **Status**: ✅ Hoạt động đúng

---

### 3. Booking status: DRAFT (0) → PAID (1) → CANCELLED (2)

#### Status Values ✅
- **DRAFT**: `0` - Đang chọn ghế/combo
- **PAID/CONFIRMED**: `1` - Đã thanh toán thành công
- **CANCELLED**: `2` - Đã hủy

#### Model Cast ✅
- **File**: `app/Models/DatVe.php`
- **Cast**: `'trang_thai' => 'integer'` (line 31)
- **Status**: ✅ Đúng kiểu integer

#### Status Flow ✅

**1. Tạo booking (lockSeats)**
```php
// BookingController@lockSeats (line 168)
'trang_thai' => 0 // 0 = DRAFT
```

**2. Thanh toán thành công (processPayment)**
```php
// BookingController@processPayment (line 455, 473)
$booking->update(['trang_thai' => 1]); // 1 = PAID/CONFIRMED
```

**3. Payment callback (paymentCallback)**
```php
// BookingController@paymentCallback (line 505)
if ($status === 'SUCCESS') {
    $booking->update(['trang_thai' => 1]); // 1 = PAID
} elseif ($status === 'CANCELLED' || $status === 'FAILED') {
    $booking->update(['trang_thai' => 2]); // 2 = CANCELLED
}
```

**4. Query bookings**
```php
// BookingController@showSeats (line 39)
->whereIn('dv.trang_thai', [0, 1]) // 0 = DRAFT, 1 = PAID

// BookingController@tickets (line 564, 566, 568)
->where('trang_thai', 1) // PAID
->where('trang_thai', 0) // DRAFT
->where('trang_thai', 2) // CANCELLED
```

#### Đã sửa ✅
- ✅ Sửa `processPayment()`: `'PAID'` → `1`
- ✅ Sửa `processPayment()`: `'PENDING'` → `1` (thanh toán tại quầy cũng là PAID)
- ✅ Sửa `paymentCallback()`: `'PAID'` → `1`, `'CANCELLED'` → `2`
- ✅ Sửa `getBookedSeatIds()`: `['PAID', 'CONFIRMED', 'PENDING']` → `[0, 1]`
- ✅ Sửa `LegacySeatLockService@getSeatStatus()`: `['PAID', 'CONFIRMED', 'PENDING']` → `1`

---

## 📋 Tóm tắt

### ✅ Đã hoàn tất:
1. ✅ Full booking flow hoạt động đúng từ chọn phim đến kết quả
2. ✅ Seat locking 5 phút với timer đếm ngược
3. ✅ Auto unlock khi hết hạn hoặc thanh toán thành công
4. ✅ Booking status nhất quán: DRAFT (0) → PAID (1) → CANCELLED (2)
5. ✅ Đã sửa tất cả các chỗ dùng string status thành integer

### 🔧 Files đã sửa:
- `app/Http/Controllers/BookingController.php`
  - `processPayment()`: Sửa status từ string sang integer
  - `paymentCallback()`: Sửa status từ string sang integer
  - `getBookedSeatIds()`: Sửa whereIn từ string sang integer
- `app/Services/LegacySeatLockService.php`
  - `getSeatStatus()`: Sửa whereIn từ string sang integer

### ✅ Test Checklist:
- [x] Chọn phim từ danh sách
- [x] Chọn ngày và xem suất chiếu
- [x] Chọn suất chiếu và chuyển đến trang ghế
- [x] Chọn ghế → lock 5 phút
- [x] Timer đếm ngược từ 5:00
- [x] Chọn combo và cập nhật tổng tiền
- [x] Checkout và nhập thông tin
- [x] Thanh toán thành công → status = 1 (PAID)
- [x] Seat lock được giải phóng sau thanh toán
- [x] Hiển thị kết quả với thông tin vé
- [x] Booking status nhất quán (integer)

---

**Status: ✅ HOÀN TẤT - Tất cả đã được kiểm tra và sửa**

