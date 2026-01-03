@extends('admin.layout')

@section('title', 'Thanh toán QR')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Thanh toán QR</h1>
        <a href="{{ route('admin.bookings.index') }}" class="text-gray-400 hover:text-white">
            <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
    </div>

    <div class="bg-[#151822] border border-[#262833] rounded-2xl p-8">
        <!-- Thông tin đơn hàng -->
        <div class="mb-6 p-4 bg-[#1b1e28] rounded-lg border border-[#262833]">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fas fa-receipt text-blue-500 mr-2"></i>
                Thông tin đơn hàng
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-400">Mã đơn hàng:</span>
                    <span class="text-white font-semibold ml-2">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Khách hàng:</span>
                    <span class="text-white font-semibold ml-2">{{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Phim:</span>
                    <span class="text-white font-semibold ml-2">{{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Suất chiếu:</span>
                    <span class="text-white font-semibold ml-2">
                        {{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') ?? 'N/A' }}
                    </span>
                </div>
                <div class="md:col-span-2">
                    <span class="text-gray-400">Ghế đã chọn:</span>
                    <span class="text-white font-semibold ml-2">
                        {{ $booking->chiTietDatVe->map(function($item) { return $item->ghe->so_ghe ?? ''; })->filter()->implode(', ') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Mã QR thanh toán -->
        <div class="flex flex-col items-center justify-center mb-6">
            <h3 class="text-xl font-semibold text-white mb-4">
                <i class="fas fa-qrcode text-green-500 mr-2"></i>
                Quét mã QR để thanh toán
            </h3>
            
            @php
                // Tạo mã QR fake cho thanh toán
                $qrPaymentData = 'PAYMENT_' . $qrCode . '_' . number_format($booking->tong_tien, 0, '', '');
                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrPaymentData);
            @endphp
            
            <div class="bg-white p-6 rounded-xl shadow-2xl mb-4">
                <img src="{{ $qrCodeUrl }}" 
                     alt="QR Code Thanh toán" 
                     class="w-64 h-64"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'300\'%3E%3Crect fill=\'%23f0f0f0\' width=\'300\' height=\'300\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-family=\'Arial\' font-size=\'14\'%3EQR Code%3C/text%3E%3C/svg%3E';">
            </div>
            
            <div class="text-center mb-4">
                <p class="text-2xl font-bold text-green-400 mb-2">
                    {{ number_format($booking->tong_tien, 0, ',', '.') }} VNĐ
                </p>
                <p class="text-sm text-gray-400">
                    Mã thanh toán: <span class="text-white font-mono">{{ $qrCode }}</span>
                </p>
            </div>
            
            <div class="bg-yellow-900/20 border border-yellow-500/50 rounded-lg p-4 max-w-md">
                <p class="text-yellow-400 text-sm text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Lưu ý:</strong> Đây là mô phỏng thanh toán QR. 
                    Vui lòng bấm nút "Đã chuyển khoản" bên dưới để hoàn tất thanh toán.
                </p>
            </div>
        </div>

        <!-- Hướng dẫn thanh toán -->
        <div class="bg-[#1b1e28] rounded-lg p-4 border border-[#262833] mb-6">
            <h4 class="text-white font-semibold mb-3">
                <i class="fas fa-list-ol text-blue-500 mr-2"></i>
                Hướng dẫn thanh toán:
            </h4>
            <ol class="list-decimal list-inside space-y-2 text-gray-300 text-sm">
                <li>Mở ứng dụng ngân hàng trên điện thoại</li>
                <li>Quét mã QR ở trên</li>
                <li>Xác nhận số tiền và thông tin thanh toán</li>
                <li>Hoàn tất giao dịch trên ứng dụng ngân hàng</li>
                <li>Bấm nút "Đã chuyển khoản" bên dưới để xác nhận</li>
            </ol>
        </div>

        <!-- Nút xác nhận -->
        <div class="flex justify-center gap-4">
            <button id="cancel-btn" 
                    onclick="window.location.href='{{ route('admin.bookings.index') }}'"
                    class="px-6 py-3 bg-[#262833] text-gray-300 rounded-lg hover:bg-[#374151] transition">
                <i class="fas fa-times mr-2"></i>
                Hủy
            </button>
            <button id="confirm-payment-btn" 
                    class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                <i class="fas fa-check-circle mr-2"></i>
                Đã chuyển khoản
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('confirm-payment-btn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    // Disable button và hiển thị loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';
    
    // Gọi API xác nhận thanh toán
    fetch('{{ route("admin.bookings.qr-payment.confirm", $booking->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            booking_id: {{ $booking->id }}
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            alert('Thanh toán thành công! Đang chuyển đến trang chi tiết đơn hàng...');
            
            // Redirect đến trang chi tiết đơn hàng
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.href = '{{ route("admin.bookings.show", $booking->id) }}';
            }
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xác nhận thanh toán'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Lỗi khi xác nhận thanh toán. Vui lòng thử lại.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>
@endpush
@endsection
