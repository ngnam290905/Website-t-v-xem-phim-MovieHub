<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xuất vé #{{ $ticket->id }} - MovieHub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #F53003 0%, #ff4d4d 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .ticket-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .ticket-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .ticket-code {
            background: white;
            color: #F53003;
            padding: 15px 30px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 15px;
            font-size: 24px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        .ticket-body {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #F53003;
        }
        
        .info-section h3 {
            color: #F53003;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            color: #333;
            font-weight: 600;
            text-align: right;
        }
        
        .qr-section {
            text-align: center;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .qr-code {
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            display: inline-block;
            border: 3px solid #F53003;
        }
        
        .qr-code img {
            max-width: 250px;
            height: auto;
        }
        
        .seats-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .seat-item {
            background: white;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            border: 2px solid #e0e0e0;
        }
        
        .seat-number {
            font-weight: bold;
            color: #F53003;
            font-size: 16px;
        }
        
        .seat-type {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .seat-price {
            font-size: 14px;
            color: #333;
            margin-top: 5px;
            font-weight: 600;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            color: #666;
            font-size: 12px;
        }
        
        .print-actions {
            text-align: center;
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            background: #F53003;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #ff4d4d;
        }
        
        .btn-secondary {
            background: #666;
        }
        
        .btn-secondary:hover {
            background: #777;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .print-actions {
                display: none;
            }
            
            .ticket-container {
                box-shadow: none;
                border: none;
            }
            
            @page {
                size: A4;
                margin: 10mm;
            }
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-checked {
            background: #10b981;
            color: white;
        }
        
        .status-pending {
            background: #f59e0b;
            color: white;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h1>🎬 VÉ XEM PHIM</h1>
            <p>MovieHub Cinema</p>
            <div class="ticket-code">
                {{ $ticket->ticket_code ?: sprintf('MV%06d', $ticket->id) }}
            </div>
        </div>
        
        <div class="ticket-body">
            <!-- Thông tin vé -->
            <div class="info-section">
                <h3>📋 Thông tin vé</h3>
                <div class="info-row">
                    <span class="info-label">Mã vé:</span>
                    <span class="info-value">{{ $ticket->ticket_code ?: sprintf('MV%06d', $ticket->id) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái quét:</span>
                    <span class="info-value">
                        @if($ticket->checked_in)
                            <span class="status-badge status-checked">✓ Đã quét</span>
                        @else
                            <span class="status-badge status-pending">⏳ Chưa quét</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tổng tiền:</span>
                    <span class="info-value" style="color: #F53003; font-size: 18px;">
                        {{ number_format($ticket->tong_tien ?? 0, 0, ',', '.') }} đ
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày đặt vé:</span>
                    <span class="info-value">
                        {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </span>
                </div>
            </div>
            
            <!-- Thông tin khách hàng -->
            <div class="info-section">
                <h3>👤 Thông tin khách hàng</h3>
                <div class="info-row">
                    <span class="info-label">Tên khách hàng:</span>
                    <span class="info-value">{{ $ticket->ten_khach_hang ?? ($ticket->nguoiDung->ho_ten ?? 'N/A') }}</span>
                </div>
                @if($ticket->email ?? $ticket->nguoiDung->email ?? null)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $ticket->email ?? $ticket->nguoiDung->email }}</span>
                </div>
                @endif
                @if($ticket->so_dien_thoai ?? $ticket->nguoiDung->so_dien_thoai ?? null)
                <div class="info-row">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value">{{ $ticket->so_dien_thoai ?? $ticket->nguoiDung->so_dien_thoai }}</span>
                </div>
                @endif
            </div>
            
            <!-- Thông tin suất chiếu -->
            <div class="info-section">
                <h3>🎥 Thông tin suất chiếu</h3>
                <div class="info-row">
                    <span class="info-label">Tên phim:</span>
                    <span class="info-value" style="font-weight: bold; color: #F53003;">
                        {{ $ticket->suatChieu->phim->ten_phim ?? 'N/A' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phòng chiếu:</span>
                    <span class="info-value">{{ $ticket->suatChieu->phongChieu->ten_phong ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày giờ chiếu:</span>
                    <span class="info-value" style="font-weight: bold;">
                        {{ $ticket->suatChieu->thoi_gian_bat_dau ? $ticket->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A' }}
                    </span>
                </div>
            </div>
            
            <!-- Thông tin ghế -->
            @if($seats->count() > 0)
            <div class="info-section">
                <h3>🪑 Thông tin ghế</h3>
                <div class="seats-list">
                    @foreach($seats as $seat)
                    <div class="seat-item">
                        <div class="seat-number">{{ $seat['seat'] }}</div>
                        <div class="seat-type">{{ $seat['type'] }}</div>
                        <div class="seat-price">{{ number_format($seat['price'], 0, ',', '.') }} đ</div>
                    </div>
                    @endforeach
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #e0e0e0;">
                    <div class="info-row">
                        <span class="info-label">Tổng tiền ghế:</span>
                        <span class="info-value" style="font-weight: bold; color: #F53003;">
                            {{ number_format($seats->sum('price'), 0, ',', '.') }} đ
                        </span>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Thông tin combo -->
            @if($ticket->chiTietCombo && $ticket->chiTietCombo->count() > 0)
            <div class="info-section">
                <h3>🍿 Combo đã đặt</h3>
                @foreach($ticket->chiTietCombo as $combo)
                <div class="info-row">
                    <span class="info-label">
                        {{ $combo->combo->ten ?? 'N/A' }} 
                        @if($combo->so_luong > 1)
                            (x{{ $combo->so_luong }})
                        @endif
                    </span>
                    <span class="info-value">
                        {{ number_format(($combo->gia ?? 0) * ($combo->so_luong ?? 1), 0, ',', '.') }} đ
                    </span>
                </div>
                @endforeach
            </div>
            @endif
            
            <!-- Thông tin thanh toán -->
            @if($ticket->thanhToan)
            <div class="info-section">
                <h3>💳 Thông tin thanh toán</h3>
                <div class="info-row">
                    <span class="info-label">Phương thức:</span>
                    <span class="info-value">{{ $ticket->thanhToan->phuong_thuc ?? 'N/A' }}</span>
                </div>
                @if($ticket->thanhToan->ma_giao_dich)
                <div class="info-row">
                    <span class="info-label">Mã giao dịch:</span>
                    <span class="info-value" style="font-family: monospace;">{{ $ticket->thanhToan->ma_giao_dich }}</span>
                </div>
                @endif
                @if($ticket->thanhToan->thoi_gian)
                <div class="info-row">
                    <span class="info-label">Thời gian thanh toán:</span>
                    <span class="info-value">
                        {{ \Carbon\Carbon::parse($ticket->thanhToan->thoi_gian)->format('d/m/Y H:i') }}
                    </span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        @if($ticket->thanhToan->trang_thai == 1)
                            <span style="color: #10b981; font-weight: bold;">✓ Đã thanh toán</span>
                        @else
                            <span style="color: #f59e0b; font-weight: bold;">⏳ Chờ thanh toán</span>
                        @endif
                    </span>
                </div>
            </div>
            @endif
            
            <!-- QR Code -->
            <div class="qr-section">
                <h3 style="color: #F53003; margin-bottom: 15px;">📱 Mã QR Vé</h3>
                @php
                    $qrData = 'ticket_id=' . $ticket->id;
                    if ($ticket->ticket_code) {
                        $qrData = 'ticket_id=' . $ticket->ticket_code;
                    }
                    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrData);
                @endphp
                <div class="qr-code">
                    <img src="{{ $qrCodeUrl }}" alt="QR Code">
                </div>
                <p style="margin-top: 15px; color: #666; font-size: 14px;">
                    <strong>Xuất trình mã QR này tại rạp để vào phòng chiếu</strong>
                </p>
                <p style="margin-top: 5px; color: #999; font-size: 12px;">
                    Mã vé: {{ $ticket->ticket_code ?: sprintf('MV%06d', $ticket->id) }}
                </p>
            </div>
        </div>
        
        <div class="print-actions">
            <button onclick="window.print()" class="btn">
                🖨️ In vé
            </button>
            <a href="{{ route('admin.scan.show', $ticket->id) }}" class="btn btn-secondary">
                ← Quay lại
            </a>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} MovieHub Cinema. Tất cả quyền được bảo lưu.</p>
            <p style="margin-top: 5px;">Vé này được tạo vào: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>

