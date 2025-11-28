@extends('admin.layout')

@section('title', 'Chi tiết Đặt Vé #' . $booking->id)

@section('content')
    @php
        $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
        $comboTotal = (float) ($booking->chiTietCombo->sum(function($i){ return ($i->gia_ap_dung ?? 0) * max(1, (int)$i->so_luong); }) ?? 0);
        $discount = 0;
        if ($booking->khuyenMai) {
            $type = strtolower($booking->khuyenMai->loai_giam);
            $val  = (float) $booking->khuyenMai->gia_tri_giam;
            $base = $seatTotal + $comboTotal;
            if ($type === 'phantram') $discount = round($base * ($val/100));
            else $discount = ($val >= 1000) ? $val : $val * 1000;
            if ($discount > $base) $discount = $base;
        }
        $total = $booking->tong_tien ?? max(0, $seatTotal + $comboTotal - $discount);
    @endphp
    <div class="space-y-6">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-600/10 border border-green-600/30 text-green-400 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-600/10 border border-red-600/30 text-red-400 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <!-- Header + Status -->
        <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-white">🎟️ Chi tiết Đặt Vé #{{ $booking->id }}</h2>
                    <p class="text-sm text-gray-400 mt-1">{{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }} • {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-400">{{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @switch($booking->trang_thai)
                        @case(0)
                            <span class="px-3 py-1 rounded-full text-xs bg-yellow-500/20 text-yellow-300">Chờ xác nhận</span>
                        @break
                        @case(1)
                            <span class="px-3 py-1 rounded-full text-xs bg-green-500/20 text-green-300">Đã xác nhận</span>
                        @break
                        @case(3)
                            <span class="px-3 py-1 rounded-full text-xs bg-orange-500/20 text-orange-300">Yêu cầu hủy</span>
                        @break
                        @case(2)
                            <span class="px-3 py-1 rounded-full text-xs bg-red-500/20 text-red-300">Đã hủy</span>
                        @break
                        @default
                            <span class="px-3 py-1 rounded-full text-xs bg-gray-500/20 text-gray-300">Không xác định</span>
                    @endswitch

                    @auth
                        @if(in_array(optional(auth()->user()->vaiTro)->ten, ['admin','staff']))
                            @if($booking->trang_thai != 2)
                                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="inline-flex items-center px-3 py-1.5 rounded bg-yellow-600/20 text-yellow-300 text-xs hover:bg-yellow-600/30">
                                    <i class="fas fa-edit mr-2"></i>Chỉnh sửa
                                </a>
                            @endif
                            @if($booking->trang_thai == 0)
                                <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Xác nhận đơn vé này?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-green-600/20 text-green-300 text-xs hover:bg-green-600/30">
                                        <i class="fas fa-check mr-2"></i>Xác nhận
                                    </button>
                                </form>
                                <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hủy đơn vé này?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-red-600/20 text-red-300 text-xs hover:bg-red-600/30">
                                        <i class="fas fa-times mr-2"></i>Hủy
                                    </button>
                                </form>
                            @endif
                            @if($booking->trang_thai == 1 && ($booking->email || $booking->nguoiDung?->email))
                                <form id="sendEmailForm" action="{{ route('admin.bookings.send-ticket', $booking->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" id="sendEmailBtn" class="inline-flex items-center px-3 py-1.5 rounded bg-blue-600/20 text-blue-300 text-xs hover:bg-blue-600/30 transition-all">
                                        <i class="fas fa-envelope mr-2"></i>
                                        <span>Gửi email</span>
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                <div class="text-sm text-gray-400">Khách hàng</div>
                <div class="mt-2 text-white font-semibold">{{ $booking->nguoiDung->ho_ten ?? 'Khách vãng lai' }}</div>
                <div class="text-xs text-gray-500">{{ $booking->nguoiDung->email ?? '—' }}</div>
            </div>
            <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                <div class="text-sm text-gray-400">Thanh toán</div>
                <div class="mt-2 text-white font-semibold">{{ $booking->thanhToan?->phuong_thuc ?? '—' }}</div>
                <div class="text-xs text-gray-500">Mã KM: {{ $booking->khuyenMai?->ma_km ?? '—' }}</div>
            </div>
            <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-400">Tổng tiền</div>
                    <div class="text-xs text-gray-500">(ghế + combo − KM)</div>
                </div>
                <div class="mt-2 text-2xl font-bold text-[#F53003]">{{ number_format($total, 0) }}đ</div>
            </div>
        </div>

        <!-- Seats & Combos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">💺 Ghế đã đặt</h3>
                    @if ($booking->chiTietDatVe->isEmpty())
                        <p class="text-gray-400">Không có ghế nào được đặt.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach ($booking->chiTietDatVe as $detail)
                                <div class="bg-[#1d202a] px-3 py-2 rounded border border-[#262833] text-sm text-center">
                                    <span class="text-white font-medium">{{ optional($detail->ghe)->so_ghe ?? '—' }}</span>
                                    <span class="block text-xs text-gray-400">{{ optional($detail->ghe->loaiGhe)->ten_loai ?? 'Ghế' }}</span>
                                    <span class="block text-xs text-gray-300 mt-1">{{ number_format($detail->gia ?? 0, 0) }}đ</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">🍿 Combo đi kèm</h3>
                    @if ($booking->chiTietCombo->isEmpty())
                        <p class="text-gray-400">Không có combo.</p>
                    @else
                        <ul class="divide-y divide-[#262833]">
                            @foreach ($booking->chiTietCombo as $combo)
                                <li class="py-2 flex items-center justify-between text-sm">
                                    <div class="text-gray-300">{{ $combo->combo->ten ?? 'Combo' }} × {{ max(1,(int)$combo->so_luong) }}</div>
                                    <div class="text-white">{{ number_format($combo->gia_ap_dung ?? 0, 0) }}đ</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Breakdown -->
            <div class="space-y-6">
                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">🧮 Chi tiết thanh toán</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between text-gray-300">
                            <span>Tiền ghế</span>
                            <span>{{ number_format($seatTotal, 0) }}đ</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-300">
                            <span>Combo</span>
                            <span>{{ number_format($comboTotal, 0) }}đ</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-300">
                            <span>Khuyến mãi {{ $booking->khuyenMai?->ma_km ? '(' . $booking->khuyenMai->ma_km . ')' : '' }}</span>
                            <span class="text-red-400">-{{ number_format($discount, 0) }}đ</span>
                        </div>
                        <div class="border-t border-[#262833] my-2"></div>
                        <div class="flex items-center justify-between text-white font-semibold">
                            <span>Tổng cộng</span>
                            <span>{{ number_format($total, 0) }}đ</span>
                        </div>
                    </div>
                </div>

                <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                    <h3 class="font-semibold mb-3 text-white">👤 Thông tin khách</h3>
                    <div class="text-sm text-gray-300 space-y-1">
                        <p>Họ tên: <span class="text-white">{{ $booking->nguoiDung->ho_ten ?? '—' }}</span></p>
                        <p>Email: <span class="text-white">{{ $booking->nguoiDung->email ?? '—' }}</span></p>
                        <p>SĐT: <span class="text-white">{{ $booking->nguoiDung->sdt ?? '—' }}</span></p>
                    </div>
                </div>

                <!-- QR Code for Confirmed Tickets -->
                @if($booking->trang_thai == 1)
                    @php
                        // Generate QR code data
                        $qrData = 'ticket_id=' . $booking->id;
                        if ($booking->ticket_code) {
                            $qrData = 'ticket_id=' . $booking->ticket_code;
                        }
                        // Use QR code API for reliable display
                        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);
                    @endphp
                    <div class="bg-[#151822] p-5 rounded-xl border border-[#262833]">
                        <h3 class="font-semibold mb-3 text-white flex items-center gap-2">
                            <i class="fas fa-qrcode"></i>
                            <span>Mã QR Vé</span>
                        </h3>
                        <div class="flex flex-col items-center justify-center">
                            <div class="bg-white p-3 rounded-lg mb-3" style="min-height: 200px; min-width: 200px; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ $qrCodeUrl }}" alt="QR Code" id="qrcode-img-admin" style="width: 200px; height: 200px; display: block;" onerror="console.error('QR Image failed to load'); this.style.display='none'; document.getElementById('qrcode-fallback-admin').style.display='block'; generateQRCodeFallbackAdmin('{{ $qrData }}');">
                                <div id="qrcode-fallback-admin" style="display: none; width: 200px; height: 200px;"></div>
                            </div>
                            <p class="text-xs text-gray-400 text-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Mã QR để quét tại rạp
                            </p>
                            <p class="text-xs text-gray-500 text-center mt-1">
                                Mã vé: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-between mt-2">
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 bg-[#F53003] px-4 py-2 rounded text-sm hover:bg-[#d92903]">
                ← Quay lại danh sách
            </a>
        </div>
    </div>

    @push('scripts')
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    // Show notification function
    function showNotification(message, type = 'success') {
        const colors = {
            success: 'bg-green-600/10 border-green-600/30 text-green-400',
            error: 'bg-red-600/10 border-red-600/30 text-red-400',
            info: 'bg-blue-600/10 border-blue-600/30 text-blue-400'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 ${colors[type]} border px-4 py-3 rounded-lg flex items-center gap-2 shadow-lg transform translate-x-full transition-transform duration-300`;
        notification.innerHTML = `
            <i class="fas ${icons[type]}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 hover:opacity-70">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // Handle send email form
    document.addEventListener('DOMContentLoaded', function() {
        const sendEmailForm = document.getElementById('sendEmailForm');
        const sendEmailBtn = document.getElementById('sendEmailBtn');
        
        if (sendEmailForm && sendEmailBtn) {
            sendEmailForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!confirm('Gửi email vé cho khách hàng?')) {
                    return;
                }
                
                // Disable button and show loading
                const originalHTML = sendEmailBtn.innerHTML;
                sendEmailBtn.disabled = true;
                sendEmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Đang gửi...</span>';
                sendEmailBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Get form data
                const formData = new FormData(sendEmailForm);
                
                // Send AJAX request
                fetch(sendEmailForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || formData.get('_token'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            // If HTML response, treat as success (redirect would happen)
                            return { success: true, message: 'Email đã được gửi thành công!' };
                        });
                    }
                })
                .then(data => {
                    // Restore button
                    sendEmailBtn.disabled = false;
                    sendEmailBtn.innerHTML = originalHTML;
                    sendEmailBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    
                    if (data.success || data.message) {
                        showNotification(data.message || 'Email đã được gửi thành công!', 'success');
                    } else if (data.error) {
                        showNotification(data.error, 'error');
                    } else {
                        showNotification('Email đã được gửi thành công!', 'success');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Restore button
                    sendEmailBtn.disabled = false;
                    sendEmailBtn.innerHTML = originalHTML;
                    sendEmailBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    
                    showNotification('Có lỗi xảy ra khi gửi email!', 'error');
                });
            });
        }
    });

    // Generate QR Code fallback for admin booking detail
    function generateQRCodeFallbackAdmin(qrData) {
        const fallbackElement = document.getElementById('qrcode-fallback-admin');
        const imgElement = document.getElementById('qrcode-img-admin');
        
        if (fallbackElement && typeof QRCode !== 'undefined') {
            if (imgElement) imgElement.style.display = 'none';
            fallbackElement.style.display = 'block';
            new QRCode(fallbackElement, {
                text: qrData,
                width: 200,
                height: 200,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } else {
            // If QRCode library not loaded, try to load it
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
            script.onload = function() {
                if (fallbackElement) {
                    if (imgElement) imgElement.style.display = 'none';
                    fallbackElement.style.display = 'block';
                    new QRCode(fallbackElement, {
                        text: qrData,
                        width: 200,
                        height: 200,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            };
            document.head.appendChild(script);
        }
    }
    </script>
    @endpush
@endsection
