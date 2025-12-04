<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nhắc nhở thanh toán</title>
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
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
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
            border-left: 4px solid #ffc107;
            border-radius: 5px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #ffc107;
            font-size: 18px;
        }
        .urgent-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .urgent-box strong {
            color: #856404;
            font-size: 20px;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #ffc107;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Nhắc nhở thanh toán</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px;">Đơn đặt vé của bạn sắp hết hạn</p>
        </div>
        
        <div class="content">
            <p>Xin chào <strong>{{ $booking->ten_khach_hang ?? ($booking->nguoiDung->ho_ten ?? 'Quý khách') }}</strong>,</p>
            
            <p>Chúng tôi nhắc nhở bạn rằng đơn đặt vé của bạn vẫn chưa được thanh toán và sắp hết hạn.</p>

            <div class="urgent-box">
                <strong>⏰ Còn lại {{ $minutesRemaining }} phút để thanh toán!</strong><br>
                Vui lòng hoàn tất thanh toán ngay để giữ ghế của bạn.
            </div>

            <div class="info-section">
                <h3>🎬 Thông tin đặt vé</h3>
                <p><strong>Mã đơn:</strong> #{{ str_pad($booking->id, 8, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Phim:</strong> {{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</p>
                <p><strong>Ngày chiếu:</strong> {{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y') : 'N/A' }}</p>
                <p><strong>Giờ chiếu:</strong> {{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('H:i') : 'N/A' }}</p>
                <p><strong>Phòng chiếu:</strong> {{ $booking->suatChieu->phongChieu->ten_phong ?? 'N/A' }}</p>
                <p><strong>Ghế đã chọn:</strong>
                    @foreach($booking->chiTietDatVe as $detail)
                        {{ $detail->ghe->so_ghe ?? 'N/A' }}@if(!$loop->last), @endif
                    @endforeach
                </p>
                <p><strong>Tổng tiền:</strong> {{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} VNĐ</p>
            </div>

            @if($booking->expires_at)
            <div class="info-section">
                <h3>⏱️ Thời hạn thanh toán</h3>
                <p>Hạn thanh toán: <strong>{{ \Carbon\Carbon::parse($booking->expires_at)->format('d/m/Y H:i') }}</strong></p>
                <p>Sau thời gian này, đơn đặt vé sẽ tự động hủy và ghế sẽ được giải phóng.</p>
            </div>
            @endif

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('user.bookings') }}" class="button">Thanh toán ngay</a>
            </div>

            <div class="info-section">
                <h3>📞 Hỗ trợ</h3>
                <p>Nếu bạn gặp vấn đề khi thanh toán, vui lòng liên hệ:</p>
                <p><strong>Hotline:</strong> 1900 1234 (Miễn phí)</p>
                <p><strong>Email:</strong> support@cinema.com</p>
            </div>
        </div>

        <div class="footer">
            <p>Trân trọng,<br><strong>{{ config('app.name') }}</strong></p>
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
        </div>
    </div>
</body>
</html>

