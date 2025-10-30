# Hướng Dẫn Quản Lý Suất Chiếu và Ghế - MovieHub

## Tổng Quan

Hệ thống quản lý suất chiếu và ghế cho phép admin quản lý toàn bộ các suất chiếu phim và ghế ngồi trong rạp chiếu phim.

## Chức Năng Chính

### 1. Quản Lý Suất Chiếu

#### Các chức năng CRUD:
- **Tạo suất chiếu mới**: Liên kết phim với phòng chiếu và thời gian
- **Xem danh sách suất chiếu**: Hiển thị tất cả suất chiếu với thông tin chi tiết
- **Chỉnh sửa suất chiếu**: Cập nhật thông tin phim, phòng, thời gian
- **Xóa suất chiếu**: Xóa suất chiếu (chỉ khi chưa có vé đặt)
- **Cập nhật trạng thái**: Bật/tắt suất chiếu (dành cho staff)

#### Tính năng đặc biệt:
- **Kiểm tra xung đột thời gian**: Tự động kiểm tra xung đột khi tạo/sửa suất chiếu
- **Liên kết phim - phòng - ghế**: Tự động liên kết các thành phần
- **API hỗ trợ**: Cung cấp API để lấy suất chiếu theo phim và ngày

### 2. Quản Lý Ghế

#### Các chức năng CRUD:
- **Tạo ghế mới**: Thêm ghế vào phòng chiếu với loại ghế cụ thể
- **Xem danh sách ghế**: Hiển thị tất cả ghế theo phòng
- **Chỉnh sửa ghế**: Cập nhật thông tin ghế
- **Xóa ghế**: Xóa ghế (chỉ khi chưa có vé đặt)
- **Cập nhật trạng thái**: Bật/tắt ghế (dành cho staff)

#### Tính năng đặc biệt:
- **Tạo ghế tự động**: Tự động tạo ghế cho toàn bộ phòng theo cấu hình
- **Quản lý loại ghế**: Hỗ trợ nhiều loại ghế với hệ số giá khác nhau
- **Kiểm tra trùng lặp**: Tự động kiểm tra ghế trùng lặp trong cùng phòng

## Cấu Trúc Dữ Liệu

### Bảng Suất Chiếu (suat_chieu)
- `id`: ID suất chiếu
- `id_phim`: ID phim
- `id_phong`: ID phòng chiếu
- `thoi_gian_bat_dau`: Thời gian bắt đầu
- `thoi_gian_ket_thuc`: Thời gian kết thúc
- `trang_thai`: Trạng thái (1: hoạt động, 0: tạm dừng)

### Bảng Ghế (ghe)
- `id`: ID ghế
- `id_phong`: ID phòng chiếu
- `so_ghe`: Số ghế (ví dụ: 1A, 2B)
- `so_hang`: Số hàng
- `id_loai`: ID loại ghế
- `trang_thai`: Trạng thái (1: hoạt động, 0: tạm dừng)

### Bảng Loại Ghế (loai_ghe)
- `id`: ID loại ghế
- `ten_loai`: Tên loại ghế
- `he_so_gia`: Hệ số giá (ví dụ: 1.0, 1.5, 2.0)

## Hướng Dẫn Sử Dụng

### 1. Truy Cập Quản Lý

1. Đăng nhập vào hệ thống admin
2. Truy cập menu "Suất chiếu" hoặc "Ghế" trong sidebar
3. Sử dụng các nút CRUD để quản lý

### 2. Tạo Suất Chiếu Mới

1. Click "Tạo Suất Chiếu Mới"
2. Chọn phim từ dropdown
3. Chọn phòng chiếu từ dropdown
4. Nhập thời gian bắt đầu và kết thúc
5. Chọn trạng thái
6. Click "Tạo Suất Chiếu"

### 3. Tạo Ghế Mới

1. Click "Tạo Ghế Mới"
2. Chọn phòng chiếu từ dropdown
3. Chọn loại ghế từ dropdown
4. Nhập số ghế (ví dụ: 1A, 2B)
5. Nhập số hàng
6. Chọn trạng thái
7. Click "Tạo Ghế"

### 4. Tạo Ghế Tự Động

1. Click "Tạo Ghế Tự Động"
2. Chọn phòng chiếu từ dropdown
3. Chọn loại ghế từ dropdown
4. Click "Tạo Ghế"
5. Hệ thống sẽ tự động tạo ghế theo cấu hình phòng

## API Endpoints

### Suất Chiếu
- `GET /admin/suat-chieu` - Danh sách suất chiếu
- `POST /admin/suat-chieu` - Tạo suất chiếu mới
- `GET /admin/suat-chieu/{id}` - Chi tiết suất chiếu
- `PUT /admin/suat-chieu/{id}` - Cập nhật suất chiếu
- `DELETE /admin/suat-chieu/{id}` - Xóa suất chiếu
- `PATCH /admin/suat-chieu/{id}/status` - Cập nhật trạng thái
- `GET /admin/suat-chieu-by-movie-date` - Lấy suất chiếu theo phim và ngày

### Ghế
- `GET /admin/ghe` - Danh sách ghế
- `POST /admin/ghe` - Tạo ghế mới
- `GET /admin/ghe/{id}` - Chi tiết ghế
- `PUT /admin/ghe/{id}` - Cập nhật ghế
- `DELETE /admin/ghe/{id}` - Xóa ghế
- `PATCH /admin/ghe/{id}/status` - Cập nhật trạng thái
- `GET /admin/ghe-by-room` - Lấy ghế theo phòng
- `POST /admin/ghe/generate` - Tạo ghế tự động

## Lưu Ý Quan Trọng

1. **Kiểm tra xung đột thời gian**: Hệ thống tự động kiểm tra xung đột khi tạo/sửa suất chiếu
2. **Ràng buộc dữ liệu**: Không thể xóa suất chiếu/ghế đã có vé đặt
3. **Quyền hạn staff**: Staff chỉ có thể cập nhật trạng thái, không thể tạo/sửa/xóa
4. **Tự động liên kết**: Hệ thống tự động liên kết phim - phòng - ghế
5. **Validation**: Tất cả dữ liệu đầu vào đều được validate nghiêm ngặt

## Troubleshooting

### Lỗi thường gặp:
1. **"Thời gian này đã bị trùng"**: Kiểm tra lại thời gian suất chiếu
2. **"Ghế này đã tồn tại"**: Kiểm tra lại số ghế trong phòng
3. **"Không thể xóa suất chiếu đã có vé đặt"**: Cần xóa vé đặt trước khi xóa suất chiếu

### Giải pháp:
1. Kiểm tra dữ liệu đầu vào
2. Xem log lỗi trong Laravel
3. Kiểm tra cấu trúc database
4. Liên hệ admin để được hỗ trợ
