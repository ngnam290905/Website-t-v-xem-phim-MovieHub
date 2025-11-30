# Hướng Dẫn Cấu Hình Gmail SMTP cho Laravel

## Vấn đề
Gmail không cho phép sử dụng mật khẩu thông thường để đăng nhập SMTP từ ứng dụng bên thứ ba. Bạn cần sử dụng **App Password** (Mật khẩu ứng dụng).

## Giải pháp 1: Sử dụng App Password (Khuyến nghị)

### Bước 1: Bật Xác minh 2 bước (2-Step Verification)
1. Truy cập: https://myaccount.google.com/security
2. Tìm mục "Xác minh 2 bước" (2-Step Verification)
3. Bật tính năng này nếu chưa bật

### Bước 2: Tạo App Password
1. Truy cập: https://myaccount.google.com/apppasswords
   - Hoặc vào: Google Account → Security → 2-Step Verification → App passwords
2. Chọn ứng dụng: "Mail"
3. Chọn thiết bị: "Other (Custom name)" → Nhập tên: "Laravel App"
4. Click "Generate"
5. **Copy mật khẩu 16 ký tự** (không có khoảng trắng)

### Bước 3: Cấu hình .env
Cập nhật file `.env` với thông tin sau:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=duynvph50688@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=duynvph50688@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Lưu ý quan trọng:**
- `MAIL_PASSWORD` phải là **App Password** (16 ký tự), KHÔNG phải mật khẩu Gmail thông thường
- Không có khoảng trắng trong App Password
- Sau khi cập nhật, chạy: `php artisan config:clear`

## Giải pháp 2: Sử dụng Mailtrap (Cho môi trường Development)

Mailtrap là dịch vụ test email miễn phí, không cần cấu hình phức tạp.

### Bước 1: Đăng ký Mailtrap
1. Truy cập: https://mailtrap.io/
2. Đăng ký tài khoản miễn phí
3. Vào "Email Testing" → "Inboxes" → Chọn inbox
4. Copy thông tin SMTP

### Bước 2: Cấu hình .env
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Giải pháp 3: Sử dụng Log Driver (Tạm thời cho Testing)

Nếu chỉ muốn test chức năng gửi email mà không cần gửi thật:

```env
MAIL_MAILER=log
```

Email sẽ được lưu vào file log thay vì gửi đi thật.

## Kiểm tra cấu hình

Sau khi cấu hình, chạy lệnh sau để test:

```bash
php artisan config:clear
php artisan cache:clear
```

Sau đó thử gửi email từ ứng dụng.

## Troubleshooting

### Lỗi "535 Username and Password not accepted"
- ✅ Đảm bảo đã bật 2-Step Verification
- ✅ Đảm bảo đang dùng App Password (16 ký tự), không phải mật khẩu Gmail
- ✅ Không có khoảng trắng trong App Password
- ✅ Đã chạy `php artisan config:clear`

### Lỗi "Connection timeout"
- Kiểm tra firewall/antivirus có chặn port 587 không
- Thử đổi port sang 465 với encryption = ssl

### Lỗi "Could not authenticate"
- Kiểm tra lại App Password đã copy đúng chưa
- Tạo App Password mới nếu cần

## Cấu hình Production

Cho môi trường production, nên sử dụng:
- **SendGrid** (có free tier)
- **Mailgun** (có free tier)
- **Amazon SES** (rất rẻ)
- **Postmark** (có free tier)

Các dịch vụ này ổn định và đáng tin cậy hơn Gmail SMTP.

