# MovieHub Database Setup Guide

## Tổng quan
Dự án MovieHub đã được cập nhật để lấy dữ liệu phim từ database thay vì dữ liệu tĩnh. Tất cả thông tin phim hiện được lưu trữ trong database MySQL và hiển thị động trên giao diện.

## Cấu trúc Database

### Bảng `phim`
- `id`: ID duy nhất của phim
- `ten_phim`: Tên phim
- `do_dai`: Thời lượng phim (phút)
- `poster`: URL poster phim
- `mo_ta`: Mô tả phim
- `dao_dien`: Đạo diễn
- `dien_vien`: Diễn viên
- `trailer`: URL trailer phim
- `trang_thai`: Trạng thái phim (1 = đang chiếu, 0 = ngừng chiếu)
- `created_at`: Ngày tạo
- `updated_at`: Ngày cập nhật

## Cài đặt Database

### Cách 1: Sử dụng script tự động
1. Chạy file `setup_database.bat` trong thư mục gốc của dự án
2. Script sẽ tự động chạy migration và seeder

### Cách 2: Chạy thủ công
```bash
# Chạy migration để tạo bảng
php artisan migrate:fresh

# Chạy seeder để thêm dữ liệu mẫu
php artisan db:seed
```

## Các tính năng đã cập nhật

### 1. MovieController
- **index()**: Lấy danh sách phim nổi bật, phim hot, phim sắp chiếu
- **show($id)**: Hiển thị chi tiết phim và phim liên quan
- **getMovies()**: API endpoint để lấy danh sách phim
- **getFeaturedMovies()**: API endpoint để lấy phim nổi bật
- **search($query)**: API endpoint để tìm kiếm phim

### 2. Routes
- `/`: Trang chủ với danh sách phim từ database
- `/phim/{id}`: Trang chi tiết phim
- `/api/movies`: API lấy danh sách phim
- `/api/featured-movies`: API lấy phim nổi bật
- `/api/search?q=query`: API tìm kiếm phim

### 3. Views
- **home.blade.php**: Hiển thị phim động từ database
- **movie-detail.blade.php**: Hiển thị thông tin chi tiết phim từ database

### 4. Database Seeder
- **MovieSeeder**: Thêm 12 phim mẫu vào database
- Dữ liệu bao gồm: tên phim, poster, mô tả, đạo diễn, diễn viên, trailer

## Cách sử dụng

1. **Khởi động server**:
   ```bash
   php artisan serve
   ```

2. **Truy cập website**:
   - Trang chủ: `http://localhost:8000`
   - Chi tiết phim: `http://localhost:8000/phim/{id}`

3. **API endpoints**:
   - Lấy danh sách phim: `GET /api/movies`
   - Lấy phim nổi bật: `GET /api/featured-movies`
   - Tìm kiếm phim: `GET /api/search?q=query`

## Thêm phim mới

Để thêm phim mới vào database, bạn có thể:

1. **Sử dụng tinker**:
   ```bash
   php artisan tinker
   ```
   ```php
   App\Models\Movie::create([
       'ten_phim' => 'Tên phim mới',
       'do_dai' => 120,
       'poster' => 'URL poster',
       'mo_ta' => 'Mô tả phim',
       'dao_dien' => 'Tên đạo diễn',
       'dien_vien' => 'Tên diễn viên',
       'trailer' => 'URL trailer',
       'trang_thai' => 1
   ]);
   ```

2. **Tạo admin panel** (tùy chọn):
   - Tạo form thêm phim
   - Tạo controller để xử lý CRUD operations

## Lưu ý

- Tất cả dữ liệu phim hiện được lưu trong database
- Giao diện sẽ tự động cập nhật khi có thay đổi trong database
- API endpoints có thể được sử dụng cho các ứng dụng mobile hoặc frontend khác
- Dữ liệu mẫu bao gồm 12 phim với đầy đủ thông tin
