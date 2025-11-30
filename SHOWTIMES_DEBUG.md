# Debug Showtimes - Hướng dẫn kiểm tra

## Vấn đề
"Không có suất chiếu nào cho ngày này"

## Đã sửa

### 1. Controller (`MovieController@show`)
- ✅ Sửa query: `where('thoi_gian_bat_dau', '>', now())` thay vì `whereDate`
- ✅ Giới hạn 7 ngày tới
- ✅ Tự động chọn ngày đầu tiên nếu selectedDate không có

### 2. Seeder (`SuatChieuSeeder`)
- ✅ Tạo showtimes thông minh:
  - Hôm nay: chỉ tạo suất chiếu sau giờ hiện tại
  - Các ngày khác: tạo đầy đủ (10:00, 14:00, 16:30, 19:00, 21:30)
- ✅ Kiểm tra thời gian trước khi tạo

### 3. View (`movie-detail.blade.php`)
- ✅ Hiển thị thông báo rõ ràng hơn
- ✅ Fallback query nếu controller không truyền dữ liệu

## Cách kiểm tra

### 1. Kiểm tra dữ liệu
```
http://your-domain/debug/showtimes?movie_id=1&date=2025-11-15
```

### 2. Kiểm tra trong database
```sql
SELECT * FROM suat_chieu 
WHERE id_phim = 1 
  AND trang_thai = 1 
  AND thoi_gian_bat_dau > NOW()
ORDER BY thoi_gian_bat_dau;
```

### 3. Chạy lại seeder
```bash
php artisan db:seed --class=SuatChieuSeeder
```

## Nguyên nhân có thể

1. **Không có showtimes trong database**
   - Giải pháp: Chạy `SuatChieuSeeder`

2. **Showtimes đã qua thời gian**
   - Giải pháp: Seeder đã được sửa để chỉ tạo showtimes trong tương lai

3. **Query điều kiện quá strict**
   - Giải pháp: Đã sửa query để linh hoạt hơn

4. **Date format không khớp**
   - Giải pháp: Đã normalize date format

## Test lại

1. Vào `/movies/{id}` - xem chi tiết phim
2. Kiểm tra sidebar "Lịch chiếu"
3. Chọn các ngày khác nhau
4. Xem showtimes có hiển thị không

