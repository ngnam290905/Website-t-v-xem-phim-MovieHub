# Triển Khai Logic Đặt Ghế Cho Ma Trận Đặc Biệt

## 📋 Tổng Quan

Hệ thống đã được cập nhật để hỗ trợ các dạng ma trận ghế đặc biệt:
- **Hình tam giác** (triangle)
- **Hình thoi** (diamond)
- **4 góc khuyết** (corners_cut)
- **Hình chữ nhật** (rectangle) - mặc định

## 🏗️ Kiến Trúc

### 1. SeatLayoutService
**File:** `app/Services/SeatLayoutService.php`

**Chức năng:**
- `getSeatMatrix(PhongChieu $room)` - Tạo ma trận ghế với null positions
- `buildTriangleMatrix()` - Xây dựng ma trận tam giác
- `buildDiamondMatrix()` - Xây dựng ma trận hình thoi
- `buildCornersCutMatrix()` - Xây dựng ma trận 4 góc khuyết
- `buildRectangleMatrix()` - Xây dựng ma trận chữ nhật (mặc định)
- `isValidSeat()` - Kiểm tra vị trí có phải ghế thật không

### 2. Database Schema

**Bảng `phong_chieu`:**
- `layout_type` - Loại layout: `rectangle`, `triangle`, `diamond`, `corners_cut`
- `layout_config` - JSON config cho layout (tùy chọn)
- `layout_json` - JSON layout chi tiết (tùy chọn)

**Bảng `ghe`:**
- `pos_x`, `pos_y` - Vị trí trong ma trận (đã có sẵn)
- `zone` - Khu vực ghế (đã có sẵn)
- `meta` - Metadata JSON (đã có sẵn)

### 3. API Response

**Endpoint:** `GET /showtime-seats/{showtimeId}`

**Response format:**
```json
{
  "seats": {
    "A1": { "id": 1, "code": "A1", "status": "available", ... },
    "A2": null,  // Empty position
    "B1": { "id": 2, "code": "B1", "status": "sold", ... },
    ...
  },
  "layout": {
    "layout_type": "triangle",
    "max_rows": 10,
    "max_cols": 15
  }
}
```

## 🎨 Frontend Rendering

### Logic Render

1. **Xử lý null positions:**
   - Null positions được render thành invisible placeholder
   - Giữ spacing để tạo hình dạng đúng

2. **Render theo layout:**
   - Sắp xếp ghế theo row và column
   - Fill gaps với empty divs để maintain alignment

3. **Click handling:**
   - Chỉ cho phép click vào ghế thật (không phải null)
   - Validation ở frontend và backend

## 🔒 Validation

### Backend Validation

**File:** `app/Http/Controllers/BookingController.php::store()`

1. **Kiểm tra null positions:**
   - Sử dụng `SeatLayoutService::isValidSeat()` để kiểm tra
   - Reject nếu user cố đặt ghế ở vị trí null

2. **Kiểm tra ghế tồn tại:**
   - Verify ghế có trong database
   - Reject nếu ghế không tồn tại

### Frontend Validation

**File:** `resources/views/booking/seats.blade.php`

1. **Click handling:**
   - Chỉ render button cho ghế thật
   - Null positions render thành invisible div

2. **API validation:**
   - Backend sẽ reject nếu có null positions trong request

## 📐 Các Dạng Layout

### 1. Hình Tam Giác (Triangle)

**Cấu trúc:**
```
      A1
    B1 B2
  C1 C2 C3
D1 D2 D3 D4
```

**Logic:**
- Row 1: 1 ghế, padding trái/phải
- Row 2: 2 ghế, padding trái/phải
- Row 3: 3 ghế, padding trái/phải
- Row N: N ghế, padding trái/phải

### 2. Hình Thoi (Diamond)

**Cấu trúc:**
```
      A1
    B1 B2
  C1 C2 C3
D1 D2 D3 D4
  C4 C5 C6
    B3 B4
      A2
```

**Logic:**
- Tăng dần đến giữa, sau đó giảm dần
- Padding hai bên để tạo hình thoi

### 3. 4 Góc Khuyết (Corners Cut)

**Cấu trúc:**
```
X A1 A2 A3 X
B1 B2 B3 B4
C1 C2 C3 C4
X D1 D2 D3 X
```

**Logic:**
- Row đầu và cuối: cột đầu và cuối = null
- Các row giữa: đầy đủ ghế

### 4. Hình Chữ Nhật (Rectangle)

**Cấu trúc:**
```
A1 A2 A3 A4 A5
B1 B2 B3 B4 B5
C1 C2 C3 C4 C5
```

**Logic:**
- Tất cả vị trí đều có ghế (không có null)

## 🔄 Flow Đặt Ghế

```
1. User vào trang chọn ghế
   ↓
2. Frontend gọi API: GET /showtime-seats/{id}
   ↓
3. Backend:
   - Lấy room layout_type
   - SeatLayoutService::getSeatMatrix() → tạo matrix với null
   - Kiểm tra status từng ghế (available/hold/sold)
   - Trả về flat array với null positions
   ↓
4. Frontend render:
   - Null positions → invisible div (giữ spacing)
   - Real seats → buttons với status colors
   ↓
5. User click ghế:
   - Check: button có dataset.seat không?
   - Check: ghế có disabled không?
   - Nếu OK → holdSeats() → booking_hold_id
   ↓
6. User nhấn "Thanh toán":
   - Gửi booking_hold_id + seat codes
   ↓
7. Backend validation:
   - Check: ghế có trong booking_hold không?
   - Check: ghế có phải null position không? (isValidSeat)
   - Check: ghế đã sold/reserved chưa?
   - Nếu OK → tạo booking
```

## ✅ Checklist Implementation

- [x] Tạo SeatLayoutService với các method build matrix
- [x] Cập nhật PhongChieu model để hỗ trợ layout_type
- [x] Cập nhật getShowtimeSeats() để sử dụng SeatLayoutService
- [x] Cập nhật frontend renderSeatMap() để xử lý null positions
- [x] Thêm validation backend để reject null positions
- [x] Thêm validation frontend để không cho click null positions

## 🎯 Cách Sử Dụng

### 1. Cấu hình Layout cho Phòng

**Trong database hoặc admin panel:**
```php
$room = PhongChieu::find($roomId);
$room->layout_type = 'triangle'; // hoặc 'diamond', 'corners_cut', 'rectangle'
$room->save();
```

### 2. Tạo Ghế cho Layout Đặc Biệt

**Ví dụ cho hình tam giác:**
- Row A: Tạo 1 ghế (A1)
- Row B: Tạo 2 ghế (B1, B2)
- Row C: Tạo 3 ghế (C1, C2, C3)
- Row D: Tạo 4 ghế (D1, D2, D3, D4)

**Lưu ý:** Chỉ tạo ghế ở vị trí thật, không tạo ở vị trí null.

### 3. Frontend Tự Động Render

Frontend sẽ tự động:
- Phát hiện layout_type từ API response
- Render đúng hình dạng với null positions
- Chỉ cho phép click vào ghế thật

## 🔍 Testing

### Test Case 1: Triangle Layout
1. Set room layout_type = 'triangle'
2. Tạo ghế: A1, B1, B2, C1, C2, C3
3. Load seat map → Kiểm tra hiển thị đúng hình tam giác
4. Click vào null position → Không có phản ứng
5. Click vào ghế thật → Hoạt động bình thường

### Test Case 2: Diamond Layout
1. Set room layout_type = 'diamond'
2. Tạo ghế theo pattern hình thoi
3. Load seat map → Kiểm tra hiển thị đúng hình thoi
4. Click vào null position → Không có phản ứng

### Test Case 3: Corners Cut Layout
1. Set room layout_type = 'corners_cut'
2. Tạo ghế (không tạo ở 4 góc)
3. Load seat map → Kiểm tra 4 góc là null
4. Click vào góc → Không có phản ứng

## 📝 Notes

1. **Null positions không được lưu vào database** - Chỉ là logic render
2. **Chỉ tạo ghế ở vị trí thật** - Không tạo ghế ở vị trí null
3. **Layout type mặc định là 'rectangle'** - Nếu không set
4. **Backward compatible** - Phòng cũ không có layout_type vẫn hoạt động

