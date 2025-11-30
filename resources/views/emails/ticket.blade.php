<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vé xem phim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-left: 4px solid #4a90e2;
        }
        .info-section h3 {
            margin-top: 0;
            color: #4a90e2;
        }
        .ticket-code {
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
            text-align: center;
            padding: 10px;
            background-color: #fff;
            border: 2px dashed #e74c3c;
            margin: 20px 0;
        }
        .qr-code-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: white;
            border: 2px solid #4a90e2;
            border-radius: 10px;
        }
        .qr-code-section img {
            max-width: 250px;
            height: auto;
            border: 3px solid #4a90e2;
            padding: 10px;
            background-color: white;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Vé xem phim của bạn</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $booking->ten_khach_hang ?? ($booking->nguoiDung->ho_ten ?? 'Quý khách') }}</strong>,</p>
        
        <p>Cảm ơn bạn đã đặt vé tại rạp của chúng tôi! Dưới đây là thông tin chi tiết về vé của bạn.</p>

        <div class="info-section">
            <h3>🎬 Thông tin phim</h3>
            <p><strong>Tên phim:</strong> {{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</p>
            @if($booking->suatChieu->phim->do_dai)
            <p><strong>Thời lượng:</strong> {{ $booking->suatChieu->phim->do_dai }} phút</p>
            @endif
        </div>

        <div class="info-section">
            <h3>🎭 Thông tin suất chiếu</h3>
            <p><strong>Ngày chiếu:</strong> {{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y') : 'N/A' }}</p>
            <p><strong>Giờ chiếu:</strong> {{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('H:i') : 'N/A' }}</p>
            <p><strong>Rạp - Phòng chiếu:</strong> {{ $booking->suatChieu->phongChieu->ten_phong ?? 'N/A' }}</p>
        </div>

        <div class="info-section">
            <h3>🪑 Thông tin đặt ghế</h3>
            <p><strong>Danh sách ghế:</strong></p>
            <ul>
                @foreach($booking->chiTietDatVe as $detail)
                <li>{{ $detail->ghe->so_ghe ?? 'N/A' }} 
                    @if($detail->ghe && $detail->ghe->loaiGhe)
                        ({{ $detail->ghe->loaiGhe->ten_loai ?? 'Standard' }})
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

        <div class="ticket-code">
            Mã vé: {{ $booking->id ?? 'N/A' }}
        </div>

        @php
            // Generate QR code data
            $qrData = 'ticket_id=' . $booking->id;
            if ($booking->ticket_code) {
                $qrData = 'ticket_id=' . $booking->ticket_code;
            }
            // Use QR code API for email
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrData);
        @endphp

        <div class="qr-code-section">
            <h3 style="color: #4a90e2; margin-top: 0;">📱 Mã QR Vé</h3>
            <img src="{{ $qrCodeUrl }}" alt="QR Code Ticket" style="display: block; margin: 0 auto;">
            <p style="margin-top: 15px; color: #666; font-size: 14px;">
                <strong>Xuất trình mã QR này tại rạp để vào phòng chiếu</strong>
            </p>
            <p style="margin-top: 5px; color: #999; font-size: 12px;">
                Mã vé: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        <div class="info-section">
            <h3>💳 Thông tin thanh toán</h3>
            <p><strong>Tổng tiền:</strong> {{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} VNĐ</p>
            @if($booking->thanhToan)
            <p><strong>Phương thức thanh toán:</strong> {{ $booking->thanhToan->phuong_thuc ?? 'N/A' }}</p>
            @if($booking->thanhToan->ma_giao_dich)
            <p><strong>Mã giao dịch:</strong> {{ $booking->thanhToan->ma_giao_dich }}</p>
            @endif
            @if($booking->thanhToan->thoi_gian)
            <p><strong>Thời gian thanh toán:</strong> {{ \Carbon\Carbon::parse($booking->thanhToan->thoi_gian)->format('d/m/Y H:i') }}</p>
            @endif
            @endif
        </div>

        @if($booking->chiTietCombo->count() > 0)
        <div class="info-section">
            <h3>🍿 Combo đã đặt</h3>
            <ul>
                @foreach($booking->chiTietCombo as $comboDetail)
                <li>{{ $comboDetail->combo->ten_combo ?? 'N/A' }} x {{ $comboDetail->so_luong ?? 1 }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="info-section">
            <h3>⚠️ Lưu ý quan trọng</h3>
            <ul>
                <li>Vui lòng đến rạp trước <strong>15 phút</strong> so với giờ chiếu</li>
                <li>Đến quầy chỉ cần đưa <strong>mã vé</strong> để nhân viên kiểm tra</li>
                <li>Vé không được hoàn tiền sau khi thanh toán</li>
                <li>Vui lòng giữ vé cẩn thận cho đến khi vào phòng chiếu</li>
            </ul>
        </div>

        <div class="info-section">
            <h3>📞 Hỗ trợ</h3>
            <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ:</p>
            <p><strong>Hotline:</strong> 1900 1234 (Miễn phí)</p>
            <p><strong>Email hỗ trợ:</strong> support@cinema.com</p>
            <p><strong>Thời gian hỗ trợ:</strong> 8:00 - 22:00 hàng ngày</p>
        </div>

        <p style="text-align: center; margin-top: 30px;">
            <strong>Chúc bạn xem phim vui vẻ! 🎉</strong>
        </p>
    </div>

    <div class="footer">
        <p>Trân trọng,<br>{{ config('app.name') }}</p>
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>
