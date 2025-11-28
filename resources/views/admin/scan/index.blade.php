@extends('admin.layout')

@section('title', 'Quản lý Scan vé - Admin')

@section('content')
<div class="space-y-6">
    <!-- QR Scanner Section -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <i class="fas fa-qrcode text-[#F53003]"></i>
            <span>Quét mã QR vé</span>
        </h2>
        
        <!-- Camera Section -->
        <div class="mb-4">
            <div id="camera-container" class="bg-black rounded-lg overflow-hidden mb-4" style="display: none;">
                <video id="video" width="100%" height="400" autoplay playsinline></video>
                <canvas id="canvas" style="display: none;"></canvas>
            </div>
            
            <div class="flex gap-3 mb-4 flex-wrap">
                <button id="start-camera" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    <i class="fas fa-camera mr-2"></i>Bật Camera
                </button>
                <button id="stop-camera" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition" style="display: none;">
                    <i class="fas fa-stop mr-2"></i>Tắt Camera
                </button>
                <label for="upload-image" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition cursor-pointer">
                    <i class="fas fa-image mr-2"></i>Upload ảnh QR
                    <input type="file" id="upload-image" accept="image/*" style="display: none;" />
                </label>
            </div>
            
            <!-- Image Preview -->
            <div id="image-preview-container" class="mb-4" style="display: none;">
                <div class="bg-black rounded-lg overflow-hidden mb-2">
                    <img id="image-preview" src="" alt="Preview" class="max-w-full max-h-96 mx-auto block">
                </div>
                <button id="scan-from-image" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition w-full">
                    <i class="fas fa-search mr-2"></i>Quét QR từ ảnh này
                </button>
                <button id="remove-image" class="mt-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition w-full">
                    <i class="fas fa-times mr-2"></i>Xóa ảnh
                </button>
            </div>
            
            <!-- Manual Input -->
            <div class="mb-4">
                <label class="block text-gray-400 text-sm mb-2">Hoặc nhập mã vé thủ công:</label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="manual-ticket-id" 
                        placeholder="Nhập mã vé (ví dụ: 1234 hoặc ticket_id=1234)"
                        class="flex-1 bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-3 outline-none focus:border-[#F53003]"
                    >
                    <button 
                        id="check-manual" 
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition"
                    >
                        Kiểm tra
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Result Box -->
        <div id="result-box" class="hidden">
            <!-- Valid Ticket -->
            <div id="valid-result" class="hidden bg-green-900/30 border-2 border-green-500 rounded-lg p-6 mb-4">
                <div class="flex items-center mb-4">
                    <i class="fas fa-check-circle text-green-500 text-3xl mr-3"></i>
                    <h3 class="text-2xl font-bold text-green-500">VÉ HỢP LỆ</h3>
                </div>
                <div class="space-y-2 text-white">
                    <p><span class="text-gray-400">Tên phim:</span> <span id="result-movie" class="font-semibold"></span></p>
                    <p><span class="text-gray-400">Ghế:</span> <span id="result-seats" class="font-semibold"></span></p>
                    <p><span class="text-gray-400">Giờ chiếu:</span> <span id="result-showtime" class="font-semibold"></span></p>
                    <p class="text-sm text-gray-400 mt-4">Mã vé: <span id="result-ticket-code"></span></p>
                </div>
                <div class="mt-4 flex gap-2">
                    <button 
                        id="confirm-checkin" 
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition"
                    >
                        Xác nhận quét
                    </button>
                    <a 
                        id="view-detail-link"
                        href="#"
                        target="_blank"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition"
                    >
                        <i class="fas fa-eye mr-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
            
            <!-- Invalid Ticket -->
            <div id="invalid-result" class="hidden bg-red-900/30 border-2 border-red-500 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-times-circle text-red-500 text-3xl mr-3"></i>
                    <h3 class="text-2xl font-bold text-red-500">VÉ KHÔNG HỢP LỆ</h3>
                </div>
                <p id="error-message" class="text-white"></p>
                <button 
                    id="scan-again" 
                    class="mt-4 w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition"
                >
                    Quét lại
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
            <div class="text-sm text-[#a6a6b0]">Tổng vé đã thanh toán</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['total_paid'] ?? 0 }}</div>
        </div>
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
            <div class="text-sm text-[#a6a6b0]">Đã quét</div>
            <div class="text-2xl font-bold text-green-400 mt-1">{{ $stats['checked_in'] ?? 0 }}</div>
        </div>
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
            <div class="text-sm text-[#a6a6b0]">Chưa quét</div>
            <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $stats['not_checked'] ?? 0 }}</div>
        </div>
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
            <div class="text-sm text-[#a6a6b0]">Hôm nay đã quét</div>
            <div class="text-2xl font-bold text-blue-400 mt-1">{{ $stats['today_checked'] ?? 0 }}</div>
        </div>
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
            <div class="text-sm text-[#a6a6b0]">Hôm nay chưa quét</div>
            <div class="text-2xl font-bold text-red-400 mt-1">{{ $stats['today_not_checked'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <form method="GET" action="{{ route('admin.scan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Tìm kiếm</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Mã vé, tên phim, khách hàng..."
                    class="w-full bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-2 outline-none focus:border-[#F53003]"
                >
            </div>
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Trạng thái quét</label>
                <select 
                    name="status" 
                    class="w-full bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-2 outline-none focus:border-[#F53003]"
                >
                    <option value="">Tất cả</option>
                    <option value="checked" {{ request('status') === 'checked' ? 'selected' : '' }}>Đã quét</option>
                    <option value="not_checked" {{ request('status') === 'not_checked' ? 'selected' : '' }}>Chưa quét</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Ngày đặt vé</label>
                <input 
                    type="date" 
                    name="date" 
                    value="{{ request('date') }}"
                    class="w-full bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-2 outline-none focus:border-[#F53003]"
                >
            </div>
            <div>
                <label class="block text-sm text-[#a6a6b0] mb-2">Ngày chiếu</label>
                <input 
                    type="date" 
                    name="showtime_date" 
                    value="{{ request('showtime_date') }}"
                    class="w-full bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-2 outline-none focus:border-[#F53003]"
                >
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-[#F53003] hover:bg-[#ff4d4d] text-white rounded-lg font-medium transition"
                >
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                <a 
                    href="{{ route('admin.scan.index') }}" 
                    class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition"
                >
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl">
        <div class="px-5 py-4 border-b border-[#262833]">
            <h2 class="font-semibold text-white">Danh sách vé</h2>
        </div>
        <div class="p-5">
            <table class="w-full text-sm text-left text-[#a6a6b0]">
                <thead class="border-b border-[#262833]">
                    <tr>
                        <th class="py-3">ID</th>
                        <th>Mã vé</th>
                        <th>Phim</th>
                        <th>Ghế</th>
                        <th>Khách hàng</th>
                        <th>Ngày chiếu</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái quét</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr class="border-b border-[#262833] hover:bg-[#1a1d24]">
                            <td class="py-3">{{ $ticket->id }}</td>
                            <td>
                                <span class="font-mono text-white">{{ $ticket->ticket_code ?: sprintf('MV%06d', $ticket->id) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.scan.show', $ticket->id) }}" class="text-[#F53003] hover:underline">
                                    {{ $ticket->suatChieu->phim->ten_phim ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                {{ $ticket->chiTietDatVe->map(fn($d) => $d->ghe->so_ghe ?? 'N/A')->filter()->implode(', ') }}
                            </td>
                            <td>
                                {{ $ticket->ten_khach_hang ?? ($ticket->nguoiDung->ho_ten ?? 'N/A') }}
                                @if($ticket->so_dien_thoai)
                                    <br><span class="text-xs text-gray-500">{{ $ticket->so_dien_thoai }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $ticket->suatChieu->thoi_gian_bat_dau ? $ticket->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="text-white font-semibold">
                                {{ number_format($ticket->tong_tien ?? 0, 0, ',', '.') }} đ
                            </td>
                            <td>
                                @if($ticket->checked_in)
                                    <span class="px-2 py-1 bg-green-600 text-white rounded text-xs">
                                        <i class="fas fa-check-circle mr-1"></i>Đã quét
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-600 text-white rounded text-xs">
                                        <i class="fas fa-clock mr-1"></i>Chưa quét
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <a 
                                    href="{{ route('admin.scan.show', $ticket->id) }}" 
                                    class="btn-table-action btn-table-view"
                                    title="Xem chi tiết"
                                >
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-[#a6a6b0]">
                                Không tìm thấy vé nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- QR Code Scanner Library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
let stream = null;
let scanning = false;
let currentTicketId = null;

const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');
const cameraContainer = document.getElementById('camera-container');
const startCameraBtn = document.getElementById('start-camera');
const stopCameraBtn = document.getElementById('stop-camera');
const uploadImageInput = document.getElementById('upload-image');
const imagePreviewContainer = document.getElementById('image-preview-container');
const imagePreview = document.getElementById('image-preview');
const scanFromImageBtn = document.getElementById('scan-from-image');
const removeImageBtn = document.getElementById('remove-image');
const manualInput = document.getElementById('manual-ticket-id');
const checkManualBtn = document.getElementById('check-manual');
const resultBox = document.getElementById('result-box');
const validResult = document.getElementById('valid-result');
const invalidResult = document.getElementById('invalid-result');
const confirmCheckinBtn = document.getElementById('confirm-checkin');
const scanAgainBtn = document.getElementById('scan-again');
const viewDetailLink = document.getElementById('view-detail-link');

// Upload image handler
uploadImageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            imagePreviewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Scan QR from uploaded image
scanFromImageBtn.addEventListener('click', function() {
    if (!imagePreview.src) {
        alert('Vui lòng chọn ảnh trước');
        return;
    }
    scanQRFromImage(imagePreview);
});

// Remove image
removeImageBtn.addEventListener('click', function() {
    imagePreview.src = '';
    imagePreviewContainer.style.display = 'none';
    uploadImageInput.value = '';
});

// Function to scan QR from image
function scanQRFromImage(imgElement) {
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.width = imgElement.naturalWidth || imgElement.width;
    canvas.height = imgElement.naturalHeight || imgElement.height;
    
    ctx.drawImage(imgElement, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height);
    
    if (code) {
        console.log('QR Code detected from image:', code.data);
        checkTicket(code.data);
        imagePreviewContainer.style.display = 'none';
        uploadImageInput.value = '';
    } else {
        alert('Không tìm thấy mã QR trong ảnh. Vui lòng thử lại với ảnh khác.');
    }
}

// Stop camera helper function
function stopCameraStream() {
    if (stream) {
        stream.getTracks().forEach(track => {
            track.stop();
            track.enabled = false;
        });
        stream = null;
    }
    if (video.srcObject) {
        video.srcObject = null;
    }
    scanning = false;
}

// Start camera
startCameraBtn.addEventListener('click', async () => {
    try {
        // Stop any existing stream first
        stopCameraStream();
        
        // Wait a bit to ensure camera is released
        await new Promise(resolve => setTimeout(resolve, 300));
        
        // Request camera access
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        });
        
        video.srcObject = stream;
        cameraContainer.style.display = 'block';
        startCameraBtn.style.display = 'none';
        stopCameraBtn.style.display = 'inline-block';
        scanning = true;
        scanQR();
    } catch (error) {
        console.error('Error accessing camera:', error);
        stopCameraStream();
        
        let errorMessage = 'Không thể truy cập camera. ';
        if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            errorMessage += 'Camera đang được sử dụng bởi ứng dụng khác. Vui lòng đóng các ứng dụng khác đang dùng camera và thử lại.';
        } else if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            errorMessage += 'Vui lòng cấp quyền truy cập camera.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            errorMessage += 'Không tìm thấy camera.';
        } else {
            errorMessage += 'Vui lòng thử lại sau.';
        }
        
        alert(errorMessage);
    }
});

// Stop camera
stopCameraBtn.addEventListener('click', () => {
    stopCameraStream();
    cameraContainer.style.display = 'none';
    startCameraBtn.style.display = 'inline-block';
    stopCameraBtn.style.display = 'none';
});

// Scan QR code
function scanQR() {
    if (!scanning) return;
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.height = video.videoHeight;
        canvas.width = video.videoWidth;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        
        if (code) {
            console.log('QR Code detected:', code.data);
            checkTicket(code.data);
            return;
        }
    }
    
    requestAnimationFrame(scanQR);
}

// Manual check
checkManualBtn.addEventListener('click', () => {
    const ticketId = manualInput.value.trim();
    if (ticketId) {
        checkTicket(ticketId);
    } else {
        alert('Vui lòng nhập mã vé');
    }
});

manualInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        checkManualBtn.click();
    }
});

// Check ticket
async function checkTicket(ticketId) {
    try {
        scanning = false;
        
        const response = await fetch('{{ route("admin.scan.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ticket_id: ticketId })
        });
        
        const data = await response.json();
        currentTicketId = ticketId;
        
        if (data.valid) {
            showValidResult(data.ticket);
        } else {
            showInvalidResult(data.message);
        }
    } catch (error) {
        console.error('Error checking ticket:', error);
        showInvalidResult('Có lỗi xảy ra khi kiểm tra vé');
    } finally {
        setTimeout(() => {
            if (stream && stream.active && !scanning) {
                scanning = true;
                scanQR();
            }
        }, 2000);
    }
}

// Show valid result
function showValidResult(ticket) {
    resultBox.classList.remove('hidden');
    validResult.classList.remove('hidden');
    invalidResult.classList.add('hidden');
    
    document.getElementById('result-movie').textContent = ticket.movie;
    document.getElementById('result-seats').textContent = ticket.seats;
    document.getElementById('result-showtime').textContent = ticket.showtime;
    document.getElementById('result-ticket-code').textContent = ticket.ticket_code || ticket.id;
    
    // Set view detail link
    viewDetailLink.href = '{{ route("admin.scan.show", ":id") }}'.replace(':id', ticket.id);
}

// Show invalid result
function showInvalidResult(message) {
    resultBox.classList.remove('hidden');
    validResult.classList.add('hidden');
    invalidResult.classList.remove('hidden');
    document.getElementById('error-message').textContent = message;
}

// Confirm check-in
confirmCheckinBtn.addEventListener('click', async () => {
    if (!currentTicketId) return;
    
    try {
        const response = await fetch('{{ route("admin.scan.confirm") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ticket_id: currentTicketId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Xác nhận quét thành công!');
            resultBox.classList.add('hidden');
            currentTicketId = null;
            manualInput.value = '';
            // Reload page to update statistics
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Error confirming check-in:', error);
        alert('Có lỗi xảy ra khi xác nhận');
    }
});

// Scan again
scanAgainBtn.addEventListener('click', () => {
    resultBox.classList.add('hidden');
    currentTicketId = null;
    manualInput.value = '';
    if (stream && stream.active) {
        scanning = true;
        scanQR();
    }
});

// Cleanup when page unloads
window.addEventListener('beforeunload', () => {
    stopCameraStream();
});

// Cleanup when page is hidden
document.addEventListener('visibilitychange', () => {
    if (document.hidden && stream) {
        stopCameraStream();
        cameraContainer.style.display = 'none';
        startCameraBtn.style.display = 'inline-block';
        stopCameraBtn.style.display = 'none';
    }
});
</script>
@endpush
@endsection

