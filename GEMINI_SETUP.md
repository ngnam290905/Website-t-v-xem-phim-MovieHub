# Hướng dẫn cấu hình Google Gemini AI

## Bước 1: Lấy API Key từ Google AI Studio

1. Truy cập: https://makersuite.google.com/app/apikey
2. Đăng nhập bằng tài khoản Google
3. Tạo API key mới
4. Copy API key

## Bước 2: Cấu hình trong file .env

Thêm các dòng sau vào file `.env`:

```env
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-1.5-flash
```

**Lưu ý:**
- `GEMINI_API_KEY`: API key bạn đã lấy từ Google AI Studio
- `GEMINI_MODEL`: Model Gemini sử dụng (mặc định: `gemini-pro`)
  - Các model có sẵn: `gemini-pro` (khuyến nghị), `gemini-1.5-pro`
  - Lưu ý: `gemini-1.5-flash` không hỗ trợ trong API v1

**Cấu hình đã được thêm vào `config/services.php`:**
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
],
```

Bạn có thể sử dụng `config('services.gemini.api_key')` hoặc `env('GEMINI_API_KEY')` trong code.

## Bước 3: Xóa API key cũ (nếu có)

Nếu bạn đã từng sử dụng OpenAI, có thể xóa dòng sau trong `.env`:
```env
# OPENAI_API_KEY=... (không cần thiết nữa)
```

## Kiểm tra

Sau khi cấu hình, chatbot sẽ tự động sử dụng Google Gemini AI thay vì OpenAI.

## Troubleshooting

- Nếu chatbot không hoạt động, kiểm tra:
  1. API key đã được thêm vào `.env` chưa
  2. API key có hợp lệ không
  3. Kiểm tra log trong `storage/logs/laravel.log` để xem lỗi chi tiết

