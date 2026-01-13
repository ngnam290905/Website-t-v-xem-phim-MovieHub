<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #F53003 0%, #e02a00 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px 20px;
        }
        .info-section {
            margin-bottom: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-left: 4px solid #F53003;
            border-radius: 5px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #F53003;
            font-size: 18px;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #F53003;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background-color: #e02a00;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning-box strong {
            color: #856404;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            background-color: #f8f9fa;
        }
        .link-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Đặt lại mật khẩu</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px;">Yêu cầu đặt lại mật khẩu từ MovieHub</p>
        </div>
        
        <div class="content">
            <p>Xin chào,</p>
            
            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản email <strong>{{ $email }}</strong>.</p>

            <div class="info-section">
                <h3>📋 Hướng dẫn</h3>
                <p>Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu của bạn. Link này sẽ hết hạn sau <strong>60 phút</strong>.</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="button">Đặt lại mật khẩu</a>
            </div>

            <div class="link-box">
                Nếu nút không hoạt động, bạn có thể sao chép và dán link sau vào trình duyệt:<br>
                {{ $resetUrl }}
            </div>

            <div class="warning-box">
                <strong>⚠️ Lưu ý bảo mật:</strong>
                <ul style="margin: 10px 0 0 20px; padding: 0;">
                    <li>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</li>
                    <li>Không chia sẻ link này với bất kỳ ai.</li>
                    <li>Link chỉ có hiệu lực trong 60 phút.</li>
                </ul>
            </div>

            <div class="info-section">
                <h3>❓ Cần hỗ trợ?</h3>
                <p>Nếu bạn gặp vấn đề, vui lòng liên hệ bộ phận hỗ trợ của chúng tôi.</p>
            </div>
        </div>

        <div class="footer">
            <p>Trân trọng,<br><strong>MovieHub</strong></p>
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
        </div>
    </div>
</body>
</html>
