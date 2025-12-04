<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn điện tử</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #667eea;
        }
        .invoice-info-left, .invoice-info-right {
            flex: 1;
        }
        .content {
            padding: 30px 20px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #667eea;
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 16px;
        }
        .total-row.final {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #667eea;
            padding-top: 15px;
            margin-top: 10px;
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
            <h1>HÓA ĐƠN ĐIỆN TỬ</h1>
            <p style="margin: 10px 0 0 0;">INVOICE</p>
        </div>
        
        <div class="invoice-info">
            <div class="invoice-info-left">
                <p><strong>Mã hóa đơn:</strong> INV-{{ str_pad($booking->id, 8, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Ngày xuất:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                <p><strong>Khách hàng:</strong> {{ $booking->ten_khach_hang ?? ($booking->nguoiDung->ho_ten ?? 'N/A') }}</p>
                @if($booking->nguoiDung && $booking->nguoiDung->email)
                <p><strong>Email:</strong> {{ $booking->nguoiDung->email }}</p>
                @endif
                @if($booking->so_dien_thoai)
                <p><strong>Số điện thoại:</strong> {{ $booking->so_dien_thoai }}</p>
                @endif
            </div>
            <div class="invoice-info-right" style="text-align: right;">
                <p><strong>{{ config('app.name') }}</strong></p>
                <p>Địa chỉ: [Địa chỉ rạp chiếu phim]</p>
                <p>Hotline: 1900 1234</p>
                <p>Email: support@cinema.com</p>
            </div>
        </div>
        
        <div class="content">
            <div class="section">
                <div class="section-title">Thông tin đặt vé</div>
                <table class="table">
                    <tr>
                        <th>Phim</th>
                        <td>{{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ngày chiếu</th>
                        <td>{{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Giờ chiếu</th>
                        <td>{{ $booking->suatChieu->thoi_gian_bat_dau ? $booking->suatChieu->thoi_gian_bat_dau->format('H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Phòng chiếu</th>
                        <td>{{ $booking->suatChieu->phongChieu->ten_phong ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Chi tiết hóa đơn</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mô tả</th>
                            <th class="text-right">Số lượng</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->chiTietDatVe as $detail)
                        <tr>
                            <td>
                                Ghế {{ $detail->ghe->so_ghe ?? 'N/A' }}
                                @if($detail->ghe && $detail->ghe->loaiGhe)
                                    ({{ $detail->ghe->loaiGhe->ten_loai ?? 'Standard' }})
                                @endif
                            </td>
                            <td class="text-right">1</td>
                            <td class="text-right">{{ number_format($detail->gia ?? 0, 0, ',', '.') }} đ</td>
                            <td class="text-right">{{ number_format($detail->gia ?? 0, 0, ',', '.') }} đ</td>
                        </tr>
                        @endforeach
                        @foreach($booking->chiTietCombo as $comboDetail)
                        <tr>
                            <td>{{ $comboDetail->combo->ten_combo ?? 'N/A' }}</td>
                            <td class="text-right">{{ $comboDetail->so_luong ?? 1 }}</td>
                            <td class="text-right">{{ number_format($comboDetail->gia_ap_dung ?? 0, 0, ',', '.') }} đ</td>
                            <td class="text-right">{{ number_format(($comboDetail->gia_ap_dung ?? 0) * ($comboDetail->so_luong ?? 1), 0, ',', '.') }} đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="total-section">
                @php
                    $seatTotal = $booking->chiTietDatVe->sum('gia');
                    $comboTotal = $booking->chiTietCombo->sum(function($item) {
                        return ($item->gia_ap_dung ?? 0) * ($item->so_luong ?? 1);
                    });
                    $subtotal = $seatTotal + $comboTotal;
                    $discount = 0;
                    if ($booking->khuyenMai) {
                        if ($booking->khuyenMai->loai_giam === 'phantram') {
                            $discount = round($subtotal * ((float)$booking->khuyenMai->gia_tri_giam / 100));
                        } else {
                            $discount = (float)$booking->khuyenMai->gia_tri_giam;
                        }
                    }
                    $total = $subtotal - $discount;
                @endphp
                
                <div class="total-row">
                    <span>Tổng tiền ghế:</span>
                    <span>{{ number_format($seatTotal, 0, ',', '.') }} đ</span>
                </div>
                @if($comboTotal > 0)
                <div class="total-row">
                    <span>Tổng tiền combo:</span>
                    <span>{{ number_format($comboTotal, 0, ',', '.') }} đ</span>
                </div>
                @endif
                <div class="total-row">
                    <span>Tổng cộng:</span>
                    <span>{{ number_format($subtotal, 0, ',', '.') }} đ</span>
                </div>
                @if($discount > 0)
                <div class="total-row">
                    <span>Giảm giá ({{ $booking->khuyenMai->ma_km ?? 'N/A' }}):</span>
                    <span style="color: #28a745;">-{{ number_format($discount, 0, ',', '.') }} đ</span>
                </div>
                @endif
                <div class="total-row final">
                    <span>TỔNG THANH TOÁN:</span>
                    <span>{{ number_format($total, 0, ',', '.') }} đ</span>
                </div>
            </div>

            @if($booking->thanhToan)
            <div class="section">
                <div class="section-title">Thông tin thanh toán</div>
                <table class="table">
                    <tr>
                        <th>Phương thức</th>
                        <td>{{ $booking->thanhToan->phuong_thuc ?? 'N/A' }}</td>
                    </tr>
                    @if($booking->thanhToan->ma_giao_dich)
                    <tr>
                        <th>Mã giao dịch</th>
                        <td>{{ $booking->thanhToan->ma_giao_dich }}</td>
                    </tr>
                    @endif
                    @if($booking->thanhToan->thoi_gian)
                    <tr>
                        <th>Thời gian</th>
                        <td>{{ \Carbon\Carbon::parse($booking->thanhToan->thoi_gian)->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            @endif
        </div>

        <div class="footer">
            <p><strong>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</strong></p>
            <p>Hóa đơn này có giá trị pháp lý và được lưu trữ trong hệ thống.</p>
            <p>Trân trọng,<br><strong>{{ config('app.name') }}</strong></p>
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
        </div>
    </div>
</body>
</html>

