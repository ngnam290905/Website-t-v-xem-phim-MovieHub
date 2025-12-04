<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thanh toán thành công</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .success-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .info-section {
            margin-bottom: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            border-radius: 5px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #28a745;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .amount-highlight {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            padding: 20px;
            background-color: #d4edda;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            background-color: #f8f9fa;
        }
        .note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✅</div>
            <h1>Thanh toán thành công!</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px;">Cảm ơn bạn đã thanh toán</p>
        </div>
        
        <div class="content">
            <p>Xin chào <strong>{{ $booking->ten_khach_hang ?? ($booking->nguoiDung->ho_ten ?? 'Quý khách') }}</strong>,</p>
            
            <p>Chúng tôi xác nhận bạn đã thanh toán thành công cho đơn đặt vé của mình. Dưới đây là thông tin chi tiết:</p>

            <div class="info-section">
                <h3>💳 Thông tin thanh toán</h3>
                <div class="info-row">
                    <span class="info-label">Mã đơn hàng:</span>
                    <span class="info-value">#{{ str_pad($booking->id, 8, '0', STR_PAD_LEFT) }}</span>
                </div>
                @if(isset($paymentData['transaction_id']))
                <div class="info-row">
                    <span class="info-label">Mã giao dịch:</span>
                    <span class="info-value">{{ $paymentData['transaction_id'] }}</span>
                </div>
                @endif
                @if($booking->thanhToan)
                <div class="info-row">
                    <span class="info-label">Phương thức:</span>
                    <span class="info-value">{{ $booking->thanhToan->phuong_thuc ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Thời gian:</span>
                    <span class="info-value">{{ $booking->thanhToan->thoi_gian ? \Carbon\Carbon::parse($booking->thanhToan->thoi_gian)->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>

            <div class="amount-highlight">
                Số tiền đã thanh toán: {{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} VNĐ
            </div>

            <div class="info-section">
                <h3>🎬 Thông tin vé</h3>
                <div class="info-row">
                    <span class="info-label">Phim:</span>
                    <span class="info-value">{{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày chiếu:</span>
                    <span class="info-value">{{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Giờ chiếu:</span>
                    <span class="info-value">{{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('H:i') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phòng chiếu:</span>
                    <span class="info-value">{{ $booking->suatChieu->phongChieu->ten_phong ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ghế:</span>
                    <span class="info-value">
                        @foreach($booking->chiTietDatVe as $detail)
                            {{ $detail->ghe->so_ghe ?? 'N/A' }}@if(!$loop->last), @endif
                        @endforeach
                    </span>
                </div>
            </div>

            <div class="note">
                <strong>📧 Email vé đã được gửi!</strong><br>
                Chúng tôi đã gửi email chi tiết về vé của bạn. Vui lòng kiểm tra hộp thư (bao gồm thư mục spam).
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('user.bookings') }}" class="button">Xem chi tiết đơn hàng</a>
            </div>

            <div class="info-section">
                <h3>📞 Hỗ trợ</h3>
                <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ:</p>
                <p><strong>Hotline:</strong> 1900 1234 (Miễn phí)</p>
                <p><strong>Email:</strong> support@cinema.com</p>
                <p><strong>Thời gian:</strong> 8:00 - 22:00 hàng ngày</p>
            </div>
        </div>

        <div class="footer">
            <p>Trân trọng,<br><strong>{{ config('app.name') }}</strong></p>
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
        </div>
    </div>
</body>
</html>

