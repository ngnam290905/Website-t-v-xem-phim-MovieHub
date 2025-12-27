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
            <div id="camera-container" class="relative bg-black rounded-xl overflow-hidden mb-4 shadow-2xl" style="display: none;">
                <video id="video" width="100%" height="500" autoplay playsinline class="w-full"></video>
                <canvas id="canvas" style="display: none;"></canvas>
                
                <!-- Scanning Overlay -->
                <div id="scanning-overlay" class="absolute inset-0 pointer-events-none">
                    <!-- Corner borders -->
                    <div class="absolute top-0 left-0 w-20 h-20 border-t-4 border-l-4 border-[#F53003] rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 border-t-4 border-r-4 border-[#F53003] rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-20 h-20 border-b-4 border-l-4 border-[#F53003] rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-20 h-20 border-b-4 border-r-4 border-[#F53003] rounded-br-lg"></div>
                    
                    <!-- Scanning line -->
                    <div id="scanning-line" class="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#F53003] to-transparent opacity-80" style="top: 0%; animation: scanLine 2s linear infinite;"></div>
                    
                    <!-- Center crosshair -->
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <div class="w-48 h-48 border-2 border-[#F53003]/50 rounded-lg relative">
                            <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-[#F53003]/30"></div>
                            <div class="absolute left-1/2 top-0 bottom-0 w-0.5 bg-[#F53003]/30"></div>
                        </div>
                    </div>
                    
                    <!-- Status indicator -->
                    <div id="scan-status" class="absolute top-4 left-1/2 transform -translate-x-1/2 px-4 py-2 bg-black/70 backdrop-blur-sm rounded-lg text-white text-sm font-medium hidden">
                        <i class="fas fa-search mr-2"></i>
                        <span>Đang quét...</span>
                    </div>
                </div>
                
                <!-- Success/Error overlay -->
                <div id="scan-result-overlay" class="absolute inset-0 bg-black/90 backdrop-blur-sm flex items-center justify-center hidden z-50">
                    <div id="scan-success" class="hidden text-center">
                        <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mb-4 mx-auto animate-bounce">
                            <i class="fas fa-check text-white text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-green-400 mb-2">Quét thành công!</h3>
                        <p class="text-white">Đang kiểm tra vé...</p>
                    </div>
                    <div id="scan-error" class="hidden text-center">
                        <div class="w-24 h-24 bg-red-500 rounded-full flex items-center justify-center mb-4 mx-auto animate-pulse">
                            <i class="fas fa-times text-white text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-red-400 mb-2">Không tìm thấy QR</h3>
                        <p class="text-white">Vui lòng thử lại</p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 mb-4 flex-wrap">
                <button id="start-camera" class="group px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg shadow-blue-600/50 hover:shadow-xl hover:shadow-blue-600/70 transform hover:scale-105">
                    <i class="fas fa-camera mr-2"></i>Bật Camera
                </button>
                <button id="stop-camera" class="px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg" style="display: none;">
                    <i class="fas fa-stop mr-2"></i>Tắt Camera
                </button>
                <label for="upload-image" class="group px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg shadow-purple-600/50 hover:shadow-xl hover:shadow-purple-600/70 transform hover:scale-105 cursor-pointer">
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
            <div id="valid-result" class="hidden bg-gradient-to-br from-green-900/40 to-emerald-900/40 border-2 border-green-500 rounded-2xl p-8 mb-4 shadow-2xl shadow-green-500/20 animate-slide-in">
                <div class="flex items-center justify-center mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-75"></div>
                        <div class="relative w-20 h-20 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-4xl"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-green-400 ml-4">VÉ HỢP LỆ</h3>
                </div>
                
                <div class="bg-[#1a1d24]/50 backdrop-blur-sm rounded-xl p-6 mb-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-film text-green-400 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-400 mb-1">Tên phim</div>
                            <div id="result-movie" class="text-white font-bold text-lg"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chair text-blue-400 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-400 mb-1">Ghế</div>
                            <div id="result-seats" class="text-white font-semibold"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-purple-400 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-400 mb-1">Giờ chiếu</div>
                            <div id="result-showtime" class="text-white font-semibold"></div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-[#262833]">
                        <div class="text-xs text-gray-400 mb-1">Mã vé</div>
                        <div id="result-ticket-code" class="text-green-400 font-mono font-bold text-lg"></div>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button 
                        id="confirm-checkin" 
                        class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-bold transition-all duration-300 shadow-lg shadow-green-600/50 hover:shadow-xl hover:shadow-green-600/70 transform hover:scale-105"
                    >
                        <i class="fas fa-check-circle mr-2"></i>
                        Xác nhận quét
                    </button>
                    <a 
                        id="view-detail-link"
                        href="#"
                        target="_blank"
                        class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg shadow-blue-600/50 hover:shadow-xl hover:shadow-blue-600/70 transform hover:scale-105"
                    >
                        <i class="fas fa-eye mr-2"></i>Chi tiết
                    </a>
                </div>
            </div>
            
            <!-- Invalid Ticket -->
            <div id="invalid-result" class="hidden bg-gradient-to-br from-red-900/40 to-rose-900/40 border-2 border-red-500 rounded-2xl p-8 shadow-2xl shadow-red-500/20 animate-slide-in">
                <div class="flex items-center justify-center mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-red-500 rounded-full animate-pulse opacity-75"></div>
                        <div class="relative w-20 h-20 bg-red-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-times-circle text-white text-4xl"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-red-400 ml-4">VÉ KHÔNG HỢP LỆ</h3>
                </div>
                
                <div class="bg-[#1a1d24]/50 backdrop-blur-sm rounded-xl p-6 mb-6">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                        <p id="error-message" class="text-white text-lg font-medium"></p>
                    </div>
                </div>
                
                <button 
                    id="scan-again" 
                    class="w-full px-6 py-4 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl font-bold transition-all duration-300 shadow-lg shadow-red-600/50 hover:shadow-xl hover:shadow-red-600/70 transform hover:scale-105"
                >
                    <i class="fas fa-redo mr-2"></i>Quét lại
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
                                <div class="flex gap-2">
                                <a 
                                    href="{{ route('admin.scan.show', $ticket->id) }}" 
                                    class="btn-table-action btn-table-view"
                                    title="Xem chi tiết"
                                >
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                    <a 
                                        href="{{ route('admin.scan.show', $ticket->id) }}" 
                                        onclick="setTimeout(() => window.print(), 500); return false;"
                                        class="btn-table-action"
                                        style="background: #10b981;"
                                        title="In vé"
                                    >
                                        <i class="fas fa-print text-xs"></i>
                                    </a>
                                </div>
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

<style>
@keyframes scanLine {
    0% { top: 0%; }
    100% { top: 100%; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-in {
    animation: slideIn 0.5s ease-out;
}

.scanning-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}
</style>

<script>
let stream = null;
let scanning = false;
let currentTicketId = null;
let scanTimeout = null;

// Sound effects
function playSuccessSound() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.3);
}

function playErrorSound() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 400;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.2);
}

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
            playSuccessSound();
            showScanSuccess();
            setTimeout(() => {
                checkTicket(code.data);
                imagePreviewContainer.style.display = 'none';
                uploadImageInput.value = '';
            }, 500);
        } else {
            playErrorSound();
            showScanError();
            setTimeout(() => {
                alert('Không tìm thấy mã QR trong ảnh. Vui lòng thử lại với ảnh khác.');
            }, 500);
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
        
        // Show loading state
        startCameraBtn.disabled = true;
        startCameraBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang khởi động camera...';
        
        // Request camera access with enhanced settings
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment', // Use back camera on mobile
                width: { ideal: 1920, min: 640 },
                height: { ideal: 1080, min: 480 },
                aspectRatio: { ideal: 16/9 }
            } 
        });
        
        video.srcObject = stream;
        cameraContainer.style.display = 'block';
        startCameraBtn.style.display = 'none';
        stopCameraBtn.style.display = 'inline-block';
        
        // Show scanning status
        const statusDiv = document.getElementById('scan-status');
        if (statusDiv) {
            statusDiv.classList.remove('hidden');
        }
        
        // Wait for video to be ready
        video.addEventListener('loadedmetadata', () => {
            scanning = true;
            scanQR();
        }, { once: true });
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        stopCameraStream();
        startCameraBtn.disabled = false;
        startCameraBtn.innerHTML = '<i class="fas fa-camera mr-2"></i>Bật Camera';
        
        let errorMessage = 'Không thể truy cập camera. ';
        if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            errorMessage += 'Camera đang được sử dụng bởi ứng dụng khác. Vui lòng đóng các ứng dụng khác đang dùng camera và thử lại.';
        } else if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            errorMessage += 'Vui lòng cấp quyền truy cập camera trong cài đặt trình duyệt.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            errorMessage += 'Không tìm thấy camera. Vui lòng kiểm tra kết nối camera.';
        } else {
            errorMessage += 'Vui lòng thử lại sau.';
        }
        
        // Show error in a nicer way
        showInvalidResult(errorMessage);
    }
});

// Stop camera
stopCameraBtn.addEventListener('click', () => {
    stopCameraStream();
    cameraContainer.style.display = 'none';
    startCameraBtn.style.display = 'inline-block';
    stopCameraBtn.style.display = 'none';
});

// Scan QR code with enhanced detection
function scanQR() {
    if (!scanning) return;
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.height = video.videoHeight;
        canvas.width = video.videoWidth;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        
        // Try scanning with different inversion modes for better detection
        let code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'dontInvert'
        });
        
        if (!code) {
            code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'invertFirst'
            });
        }
        
        if (!code) {
            code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'attemptBoth'
            });
        }
        
        if (code) {
            console.log('QR Code detected:', code.data);
            scanning = false;
            showScanSuccess();
            playSuccessSound();
            
            // Wait a bit before checking ticket for better UX
            setTimeout(() => {
                checkTicket(code.data);
            }, 500);
            return;
        }
    }
    
    requestAnimationFrame(scanQR);
}

// Show scan success overlay
function showScanSuccess() {
    const overlay = document.getElementById('scan-result-overlay');
    const successDiv = document.getElementById('scan-success');
    const errorDiv = document.getElementById('scan-error');
    
    overlay.classList.remove('hidden');
    successDiv.classList.remove('hidden');
    errorDiv.classList.add('hidden');
    
    setTimeout(() => {
        overlay.classList.add('hidden');
        successDiv.classList.add('hidden');
    }, 1500);
}

// Show scan error overlay
function showScanError() {
    const overlay = document.getElementById('scan-result-overlay');
    const successDiv = document.getElementById('scan-success');
    const errorDiv = document.getElementById('scan-error');
    
    overlay.classList.remove('hidden');
    successDiv.classList.add('hidden');
    errorDiv.classList.remove('hidden');
    playErrorSound();
    
    setTimeout(() => {
        overlay.classList.add('hidden');
        errorDiv.classList.add('hidden');
        if (stream && stream.active) {
            scanning = true;
            scanQR();
        }
    }, 2000);
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
        
        // Show loading status
        const statusDiv = document.getElementById('scan-status');
        if (statusDiv) {
            statusDiv.classList.remove('hidden');
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Đang kiểm tra vé...</span>';
        }
        
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
        
        if (statusDiv) {
            statusDiv.classList.add('hidden');
        }
        
        if (data.valid) {
            showValidResult(data.ticket);
            // Scroll to result
            document.getElementById('result-box').scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            showInvalidResult(data.message);
            // Resume scanning after showing error
            setTimeout(() => {
                if (stream && stream.active) {
                    scanning = true;
                    scanQR();
                }
            }, 3000);
        }
    } catch (error) {
        console.error('Error checking ticket:', error);
        showInvalidResult('Có lỗi xảy ra khi kiểm tra vé');
        setTimeout(() => {
            if (stream && stream.active) {
                scanning = true;
                scanQR();
            }
        }, 3000);
    }
}

// Show valid result
function showValidResult(ticket) {
    resultBox.classList.remove('hidden');
    validResult.classList.remove('hidden');
    invalidResult.classList.add('hidden');
    
    // Animate result box
    resultBox.style.animation = 'slideIn 0.5s ease-out';
    
    document.getElementById('result-movie').textContent = ticket.movie;
    document.getElementById('result-seats').textContent = ticket.seats;
    document.getElementById('result-showtime').textContent = ticket.showtime;
    document.getElementById('result-ticket-code').textContent = ticket.ticket_code || ticket.id;
    
    // Set view detail link
    viewDetailLink.href = '{{ route("admin.scan.show", ":id") }}'.replace(':id', ticket.id);
    
    // Stop camera temporarily
    if (stream && stream.active) {
        scanning = false;
    }
}

// Show invalid result
function showInvalidResult(message) {
    resultBox.classList.remove('hidden');
    validResult.classList.add('hidden');
    invalidResult.classList.remove('hidden');
    resultBox.style.animation = 'slideIn 0.5s ease-out';
    document.getElementById('error-message').textContent = message;
    playErrorSound();
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
            // Show success animation
            const successOverlay = document.createElement('div');
            successOverlay.className = 'fixed inset-0 bg-green-500/20 backdrop-blur-sm flex items-center justify-center z-50';
            successOverlay.innerHTML = `
                <div class="bg-[#151822] border-2 border-green-500 rounded-2xl p-8 text-center">
                    <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mb-4 mx-auto animate-bounce">
                        <i class="fas fa-check text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-green-400 mb-2">Xác nhận thành công!</h3>
                    <p class="text-white">Vé đã được quét và xác nhận</p>
                </div>
            `;
            document.body.appendChild(successOverlay);
            playSuccessSound();
            
            setTimeout(() => {
                successOverlay.remove();
                resultBox.classList.add('hidden');
                currentTicketId = null;
                manualInput.value = '';
                // Reload page to update statistics
                location.reload();
            }, 2000);
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
    } else {
        // If camera not active, show start camera button
        startCameraBtn.click();
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

