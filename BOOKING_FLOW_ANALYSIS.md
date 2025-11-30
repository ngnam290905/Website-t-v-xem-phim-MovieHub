# Booking Flow Analysis & Database Design

## 1. Flow Booking Analysis

### 1.1. User Journey

```
Step 1: Chọn phim
  └─> User vào trang booking (/booking)
  └─> Hiển thị danh sách phim đang chiếu
  └─> User click vào phim muốn xem

Step 2: Chọn ngày & suất chiếu
  └─> Hiển thị Date Picker (hôm nay + 7 ngày tới)
  └─> User chọn ngày
  └─> Load danh sách suất chiếu của phim trong ngày đó
  └─> Hiển thị: giờ chiếu, phòng chiếu, giá
  └─> User click "Chọn ghế"

Step 3: Chọn ghế
  └─> Hiển thị sơ đồ ghế (layout lưới)
  └─> Phân biệt màu: trống/đặt/VIP
  └─> User click ghế trống → ghế chuyển sang "đang chọn"
  └─> Hệ thống lock ghế (5 phút)
  └─> Cập nhật tổng tiền
  └─> User có thể chọn combo bắp nước
  └─> Click "Tiếp tục thanh toán"

Step 4: Thanh toán
  └─> Nhập thông tin: tên, email, SĐT
  └─> Chọn phương thức thanh toán
  └─> Xử lý thanh toán:
      ├─> Online (VNPay, MoMo, Credit Card)
      │   └─> Redirect đến cổng thanh toán
      │   └─> Callback xử lý kết quả
      └─> Tại quầy
          └─> Chuyển trạng thái PENDING

Step 5: Kết quả
  └─> Nếu thành công:
      ├─> Booking status = PAID
      ├─> Seat status = SOLD
      ├─> Release seat locks
      └─> Hiển thị thông tin vé (mã vé, QR, chi tiết)
  └─> Nếu thất bại:
      ├─> Booking status = FAILED/CANCELLED
      └─> Release seat locks
```

### 1.2. Seat Locking Mechanism

**Mục đích**: Tránh trùng ghế khi nhiều user cùng chọn

**Cơ chế**:
1. Khi user chọn ghế → Tạo SeatLock record (expires_at = now + 5 phút)
2. Ghế bị lock không thể chọn bởi user khác
3. Timer đếm ngược 5 phút
4. Nếu hết hạn:
   - Tự động release lock
   - Booking status = EXPIRED
   - Ghế trở về trạng thái AVAILABLE
5. Khi thanh toán thành công:
   - SeatLock → BookingSeat (status = SOLD)
   - Release SeatLock records

**Implementation**:
- Sử dụng bảng `seat_locks` để lưu lock
- Cache hỗ trợ để tăng performance
- Unique constraint: (show_id, seat_id) để tránh duplicate

## 2. Database Design

### 2.1. Entity Relationship Diagram

```
┌─────────┐         ┌──────────┐         ┌─────────┐
│  Phim   │────────>│ SuatChieu│<────────│ PhongChieu│
│(movies) │   1:N   │ (shows)  │   N:1   │  (rooms) │
└─────────┘         └──────────┘         └─────────┘
                           │                    │
                           │                    │
                           │ 1:N                │ 1:N
                           │                    │
                    ┌──────▼──────┐      ┌──────▼──────┐
                    │   DatVe    │      │    Ghe     │
                    │ (bookings) │      │  (seats)   │
                    └──────┬──────┘      └──────┬──────┘
                           │                    │
                           │ 1:N                │ 1:N
                           │                    │
                    ┌──────▼──────────┐  ┌──────▼──────────┐
                    │ ChiTietDatVe   │  │  SeatLock       │
                    │(booking_seats)  │  │ (seat_locks)    │
                    └────────────────┘  └─────────────────┘
                           │
                           │ N:1
                           │
                    ┌──────▼──────────┐
                    │ ChiTietCombo   │
                    │(booking_combos)│
                    └──────┬─────────┘
                           │ N:1
                           │
                    ┌──────▼──────────┐
                    │     Combo       │
                    │   (combos)      │
                    └─────────────────┘
```

### 2.2. Tables Structure

#### 2.2.1. Bảng `phim` (movies)
```sql
- id: INT (PK)
- ten_phim: VARCHAR(255) - Tên phim
- ten_goc: VARCHAR(255) - Tên gốc
- poster: VARCHAR(500) - URL poster
- trailer: VARCHAR(500) - URL trailer
- dao_dien: VARCHAR(255) - Đạo diễn
- dien_vien: TEXT - Diễn viên
- the_loai: VARCHAR(255) - Thể loại
- quoc_gia: VARCHAR(100) - Quốc gia
- ngon_ngu: VARCHAR(100) - Ngôn ngữ
- do_tuoi: VARCHAR(10) - Độ tuổi (T13, T16, T18)
- do_dai: INT - Thời lượng (phút)
- ngay_khoi_chieu: DATE - Ngày khởi chiếu
- ngay_ket_thuc: DATE - Ngày kết thúc
- mo_ta: TEXT - Mô tả
- diem_danh_gia: DECIMAL(3,1) - Điểm đánh giá
- so_luot_danh_gia: INT - Số lượt đánh giá
- trang_thai: ENUM('dang_chieu', 'sap_chieu', 'ngung_chieu')
- created_at, updated_at, deleted_at
```

**Quan hệ**:
- `hasMany(SuatChieu::class, 'id_phim')` - Một phim có nhiều suất chiếu

#### 2.2.2. Bảng `phong_chieu` (rooms)
```sql
- id: INT (PK)
- ten_phong: VARCHAR(100) - Tên phòng (hoặc name)
- so_hang: INT - Số hàng ghế (hoặc rows)
- so_cot: INT - Số cột ghế (hoặc cols)
- suc_chua: INT - Sức chứa (hoặc capacity)
- loai_phong: VARCHAR(50) - Loại phòng (normal, vip, 3d, imax)
- mo_ta: TEXT - Mô tả
- trang_thai: TINYINT(1) - Trạng thái (1=active, 0=inactive)
- layout_json: JSON - Layout ghế (nếu có)
```

**Quan hệ**:
- `hasMany(SuatChieu::class, 'id_phong')` - Một phòng có nhiều suất chiếu
- `hasMany(Ghe::class, 'id_phong')` - Một phòng có nhiều ghế

#### 2.2.3. Bảng `suat_chieu` (shows)
```sql
- id: INT (PK)
- id_phim: INT (FK → phim.id)
- id_phong: INT (FK → phong_chieu.id)
- thoi_gian_bat_dau: DATETIME - Giờ bắt đầu
- thoi_gian_ket_thuc: DATETIME - Giờ kết thúc
- trang_thai: TINYINT(1) - Trạng thái (1=active, 0=inactive)
```

**Quan hệ**:
- `belongsTo(Phim::class, 'id_phim')` - Một suất chiếu thuộc một phim
- `belongsTo(PhongChieu::class, 'id_phong')` - Một suất chiếu diễn ra trong một phòng
- `hasMany(DatVe::class, 'id_suat_chieu')` - Một suất chiếu có nhiều booking
- `hasMany(SeatLock::class, 'show_id')` - Một suất chiếu có nhiều seat lock

**Indexes**:
- `(id_phim, thoi_gian_bat_dau)` - Tìm suất chiếu theo phim và ngày
- `(id_phong, thoi_gian_bat_dau)` - Tìm suất chiếu theo phòng và thời gian

#### 2.2.4. Bảng `ghe` (seats)
```sql
- id: INT (PK)
- id_phong: INT (FK → phong_chieu.id)
- id_loai: INT (FK → loai_ghe.id) - Loại ghế
- so_hang: CHAR(1) - Hàng (A, B, C...)
- so_ghe: VARCHAR(10) - Mã ghế (A1, B3, C8...)
- trang_thai: TINYINT(1) - Trạng thái (1=available, 0=disabled)
- pos_x: INT - Vị trí X (nếu có layout)
- pos_y: INT - Vị trí Y (nếu có layout)
- zone: VARCHAR(50) - Khu vực (nếu có)
- meta: JSON - Metadata
```

**Quan hệ**:
- `belongsTo(PhongChieu::class, 'id_phong')` - Một ghế thuộc một phòng
- `belongsTo(LoaiGhe::class, 'id_loai')` - Một ghế có một loại
- `hasMany(ChiTietDatVe::class, 'id_ghe')` - Một ghế có thể được đặt nhiều lần (khác suất chiếu)

**Unique constraint**:
- `(id_phong, so_ghe)` - Mỗi ghế trong phòng có mã duy nhất

#### 2.2.5. Bảng `loai_ghe` (seat_types)
```sql
- id: INT (PK)
- ten_loai: VARCHAR(50) - Tên loại (Thường, VIP, Couple...)
- he_so_gia: DECIMAL(3,2) - Hệ số giá (1.0 = thường, 1.5 = VIP...)
- mo_ta: TEXT - Mô tả
```

#### 2.2.6. Bảng `combo` (combos)
```sql
- id: INT (PK)
- ten: VARCHAR(255) - Tên combo
- mo_ta: TEXT - Mô tả
- hinh_anh: VARCHAR(500) - URL hình ảnh
- gia: DECIMAL(10,2) - Giá
- gia_goc: DECIMAL(10,2) - Giá gốc (nếu có giảm giá)
- trang_thai: TINYINT(1) - Trạng thái (1=active, 0=inactive)
- ngay_bat_dau: DATE - Ngày bắt đầu (nếu có)
- ngay_ket_thuc: DATE - Ngày kết thúc (nếu có)
- created_at, updated_at
```

#### 2.2.7. Bảng `dat_ve` (bookings)
```sql
- id: INT (PK)
- id_nguoi_dung: INT (FK → nguoi_dung.id, nullable) - User ID
- id_suat_chieu: INT (FK → suat_chieu.id)
- id_khuyen_mai: INT (FK → khuyen_mai.id, nullable) - Mã khuyến mãi
- ten_khach_hang: VARCHAR(255) - Tên khách hàng
- so_dien_thoai: VARCHAR(20) - Số điện thoại
- email: VARCHAR(255) - Email
- tong_tien: DECIMAL(10,2) - Tổng tiền
- trang_thai: ENUM('DRAFT', 'PENDING', 'PAID', 'CANCELLED', 'EXPIRED')
  - DRAFT: Đang chọn ghế/combo
  - PENDING: Đã submit, chờ thanh toán (tại quầy)
  - PAID: Đã thanh toán thành công
  - CANCELLED: Đã hủy
  - EXPIRED: Hết hạn (lock hết hạn)
- created_at, updated_at
```

**Quan hệ**:
- `belongsTo(NguoiDung::class, 'id_nguoi_dung')` - Một booking thuộc một user (nullable)
- `belongsTo(SuatChieu::class, 'id_suat_chieu')` - Một booking cho một suất chiếu
- `hasMany(ChiTietDatVe::class, 'id_dat_ve')` - Một booking có nhiều ghế
- `hasMany(ChiTietCombo::class, 'id_dat_ve')` - Một booking có nhiều combo
- `hasOne(ThanhToan::class, 'id_dat_ve')` - Một booking có một thanh toán

**Indexes**:
- `(id_nguoi_dung, trang_thai)` - Tìm booking của user
- `(id_suat_chieu, trang_thai)` - Tìm booking của suất chiếu

#### 2.2.8. Bảng `chi_tiet_dat_ve` (booking_seats)
```sql
- id: INT (PK)
- id_dat_ve: INT (FK → dat_ve.id)
- id_ghe: INT (FK → ghe.id)
- gia: DECIMAL(10,2) - Giá ghế tại thời điểm đặt
```

**Quan hệ**:
- `belongsTo(DatVe::class, 'id_dat_ve')` - Một chi tiết thuộc một booking
- `belongsTo(Ghe::class, 'id_ghe')` - Một chi tiết cho một ghế

**Unique constraint**:
- `(id_dat_ve, id_ghe)` - Mỗi ghế chỉ được đặt một lần trong một booking

#### 2.2.9. Bảng `chi_tiet_combo` (booking_combos)
```sql
- id: INT (PK)
- id_dat_ve: INT (FK → dat_ve.id)
- id_combo: INT (FK → combo.id)
- so_luong: INT - Số lượng
- gia_ap_dung: DECIMAL(10,2) - Giá áp dụng tại thời điểm đặt
```

**Quan hệ**:
- `belongsTo(DatVe::class, 'id_dat_ve')` - Một chi tiết thuộc một booking
- `belongsTo(Combo::class, 'id_combo')` - Một chi tiết cho một combo

**Unique constraint**:
- `(id_dat_ve, id_combo)` - Mỗi combo chỉ được thêm một lần trong một booking

#### 2.2.10. Bảng `seat_locks` (seat_locks)
```sql
- id: INT (PK)
- show_id: INT (FK → suat_chieu.id)
- seat_id: INT (FK → ghe.id)
- booking_id: INT (FK → dat_ve.id, nullable) - Booking ID nếu đã tạo booking
- expires_at: DATETIME - Thời gian hết hạn
- created_at, updated_at
```

**Quan hệ**:
- `belongsTo(SuatChieu::class, 'show_id')` - Một lock cho một suất chiếu
- `belongsTo(Ghe::class, 'seat_id')` - Một lock cho một ghế
- `belongsTo(DatVe::class, 'booking_id')` - Một lock gắn với một booking (nullable)

**Unique constraint**:
- `(show_id, seat_id)` - Mỗi ghế trong một suất chiếu chỉ có một lock tại một thời điểm

**Indexes**:
- `(expires_at)` - Tìm locks hết hạn để cleanup
- `(booking_id)` - Tìm locks của một booking

#### 2.2.11. Bảng `thanh_toan` (payments)
```sql
- id: INT (PK)
- id_dat_ve: INT (FK → dat_ve.id)
- phuong_thuc: VARCHAR(50) - Phương thức (vnpay, momo, credit_card, cash)
- so_tien: DECIMAL(10,2) - Số tiền
- trang_thai: ENUM('pending', 'success', 'failed', 'cancelled')
- transaction_id: VARCHAR(255) - Mã giao dịch từ cổng thanh toán
- thoi_gian: DATETIME - Thời gian thanh toán
- created_at, updated_at
```

**Quan hệ**:
- `belongsTo(DatVe::class, 'id_dat_ve')` - Một thanh toán cho một booking

**Indexes**:
- `(transaction_id)` - Tìm thanh toán theo mã giao dịch
- `(id_dat_ve, trang_thai)` - Tìm thanh toán của booking

### 2.3. Business Rules

1. **Seat Availability**:
   - Ghế có thể chọn nếu:
     - `ghe.trang_thai = 1` (available)
     - Không có `seat_locks` hợp lệ (expires_at > now)
     - Không có `chi_tiet_dat_ve` với `dat_ve.trang_thai = 'PAID'` cho suất chiếu đó

2. **Seat Locking**:
   - Lock duration: 5 phút
   - Tự động cleanup locks hết hạn
   - Khi thanh toán thành công: chuyển lock → booking_seat (SOLD)

3. **Booking Status Flow**:
   ```
   DRAFT → (chọn ghế/combo)
   DRAFT → PENDING (submit, thanh toán tại quầy)
   DRAFT → PAID (thanh toán online thành công)
   DRAFT → EXPIRED (lock hết hạn)
   DRAFT → CANCELLED (user hủy)
   PENDING → PAID (xác nhận thanh toán tại quầy)
   PENDING → CANCELLED (hủy)
   ```

4. **Price Calculation**:
   - Giá ghế = base_price × he_so_gia (loại ghế)
   - Tổng tiền = tổng giá ghế + tổng giá combo - giảm giá (nếu có)

## 3. API Endpoints

### 3.1. Booking Flow
- `GET /booking` - Trang chọn phim
- `GET /booking/movie/{movieId}/showtimes` - Trang chọn suất chiếu
- `GET /api/booking/movie/{movieId}/dates` - API lấy danh sách ngày có suất chiếu
- `GET /api/booking/movie/{movieId}/showtimes?date={date}` - API lấy suất chiếu theo ngày

### 3.2. Seat Selection
- `GET /shows/{showId}/seats` - Trang chọn ghế
- `POST /shows/{showId}/seats/lock` - Lock ghế
- `POST /shows/{showId}/seats/unlock` - Unlock ghế
- `GET /shows/{showId}/seats/refresh` - Refresh trạng thái ghế

### 3.3. Booking Management
- `GET /bookings/{bookingId}/addons` - Trang chọn combo
- `POST /bookings/{bookingId}/addons` - Cập nhật combo
- `GET /checkout/{bookingId}` - Trang thanh toán
- `POST /checkout/{bookingId}/payment` - Xử lý thanh toán
- `GET /result?booking_id={bookingId}` - Trang kết quả
- `GET /tickets` - Danh sách vé của tôi

## 4. Implementation Notes

### 4.1. Seat Locking Strategy

**Current Implementation** (BookingController):
- Sử dụng Cache để lock: `seat_lock:{showId}:{seatId}`
- Tạo booking DRAFT khi lock
- Cleanup khi unlock hoặc hết hạn

**Recommended Improvement**:
- Sử dụng bảng `seat_locks` làm source of truth
- Cache chỉ để tăng performance
- Background job cleanup locks hết hạn

### 4.2. Concurrency Handling

- Unique constraint trên `seat_locks(show_id, seat_id)` đảm bảo không duplicate
- Transaction khi lock/unlock để đảm bảo atomicity
- Optimistic locking nếu cần

### 4.3. Performance Optimization

- Index trên các cột thường query
- Cache seat status để giảm DB queries
- Lazy loading relationships
- Pagination cho danh sách booking

## 5. Testing Checklist

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

