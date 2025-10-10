# Hệ thống Quản lý Phim - MovieHub

## Tổng quan
Hệ thống quản lý phim với các chức năng CRUD đầy đủ, phân quyền theo vai trò Admin/Staff.

## Tính năng chính

### Quản lý Phim (CRUD)
- **Thêm phim mới**: Admin có thể thêm phim với đầy đủ thông tin
- **Xem danh sách phim**: Admin và Staff đều có thể xem danh sách phim
- **Chỉnh sửa phim**: Chỉ Admin mới có thể chỉnh sửa thông tin phim
- **Xóa phim**: Chỉ Admin mới có thể xóa phim
- **Thay đổi trạng thái**: Admin có thể kích hoạt/tạm dừng phim

### Phân quyền
- **Admin**: Có đầy đủ quyền CRUD (Create, Read, Update, Delete)
- **Staff**: Chỉ có quyền xem (Read-only)

## Cấu trúc Database

### Bảng `phim`
```sql
- id: Primary key
- ten_phim: Tên phim (VARCHAR 255)
- do_dai: Độ dài phim tính bằng phút (INT)
- poster: Đường dẫn poster phim (VARCHAR 255)
- mo_ta: Mô tả phim (TEXT)
- dao_dien: Tên đạo diễn (VARCHAR 100)
- dien_vien: Danh sách diễn viên (TEXT)
- trailer: Link trailer (VARCHAR 255)
- trang_thai: Trạng thái hoạt động (TINYINT 1)
```

## Routes

### Admin & Staff (Xem)
- `GET /admin/movies` - Danh sách phim
- `GET /admin/movies/{movie}` - Chi tiết phim

### Admin Only (CRUD)
- `GET /admin/movies/create` - Form thêm phim
- `POST /admin/movies` - Lưu phim mới
- `GET /admin/movies/{movie}/edit` - Form chỉnh sửa
- `PUT /admin/movies/{movie}` - Cập nhật phim
- `DELETE /admin/movies/{movie}` - Xóa phim
- `PATCH /admin/movies/{movie}/toggle-status` - Thay đổi trạng thái

## Middleware
- `auth`: Yêu cầu đăng nhập
- `role:admin,staff`: Cho phép Admin và Staff
- `role:admin`: Chỉ cho phép Admin

## Models
- `Phim`: Model chính cho quản lý phim
- `SuatChieu`: Model cho suất chiếu
- `PhongChieu`: Model cho phòng chiếu

## Controllers
- `MovieController`: Xử lý tất cả logic CRUD cho phim

## Views
- `admin/movies/index.blade.php`: Danh sách phim
- `admin/movies/create.blade.php`: Form thêm phim
- `admin/movies/edit.blade.php`: Form chỉnh sửa phim
- `admin/movies/show.blade.php`: Chi tiết phim

## Cài đặt và Sử dụng

### 1. Chạy Migration
```bash
php artisan migrate
```

### 2. Chạy Seeder (Tạo dữ liệu mẫu)
```bash
php artisan db:seed --class=MovieSeeder
```

### 3. Tạo Symbolic Link cho Storage
```bash
php artisan storage:link
```

### 4. Truy cập hệ thống
- Đăng nhập với tài khoản Admin: `admin@example.com`
- Đăng nhập với tài khoản Staff: `staff@example.com`
- Truy cập: `/admin/movies`

## Upload File
- Poster phim được lưu trong `storage/app/public/posters/`
- Hỗ trợ định dạng: JPEG, PNG, JPG, GIF
- Kích thước tối đa: 2MB

## Validation Rules
- Tên phim: Bắt buộc, tối đa 255 ký tự
- Độ dài: Bắt buộc, số nguyên dương
- Mô tả: Bắt buộc
- Đạo diễn: Bắt buộc, tối đa 100 ký tự
- Diễn viên: Bắt buộc
- Trailer: URL hợp lệ (không bắt buộc)
- Poster: File hình ảnh, tối đa 2MB (không bắt buộc)

## Bảo mật
- Tất cả routes đều được bảo vệ bởi middleware `auth`
- Phân quyền nghiêm ngặt theo vai trò
- Validation đầy đủ cho tất cả input
- CSRF protection cho tất cả forms
- XSS protection với Blade templating
