@extends('layouts.main')

@section('title', 'Scan vé - Nhân viên')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#0f0f1a] via-[#151822] to-[#1a1d24] py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <h1 class="text-2xl font-bold text-white mb-6">Scan vé – Nhân viên</h1>
            
            <!-- Camera Section -->
            <div class="mb-6">
                <div id="camera-container" class="bg-black rounded-lg overflow-hidden mb-4" style="display: none;">
                    <video id="video" width="100%" height="400" autoplay playsinline></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                </div>
                
                <div class="flex gap-3 mb-4 flex-wrap">
                    <button id="start-camera" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        <i class="fas fa-camera mr-2"></i>Bật Camera để quét QR
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
                            class="flex-1 bg-[#1a1d24] border border-[#262833] text-white rounded-lg px-4 py-3 outline-none focus:border-blue-500"
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
                        <h2 class="text-2xl font-bold text-green-500">VÉ HỢP LỆ</h2>
                    </div>
                    <div class="space-y-2 text-white">
                        <p><span class="text-gray-400">Tên phim:</span> <span id="result-movie" class="font-semibold"></span></p>
                        <p><span class="text-gray-400">Ghế:</span> <span id="result-seats" class="font-semibold"></span></p>
                        <p><span class="text-gray-400">Giờ chiếu:</span> <span id="result-showtime" class="font-semibold"></span></p>
                        <p class="text-sm text-gray-400 mt-4">Mã vé: <span id="result-ticket-code"></span></p>
                    </div>
                    <!-- Warning message for too early -->
                    <div id="too-early-warning" class="hidden mt-4 p-4 bg-yellow-900/30 border-2 border-yellow-500 rounded-lg">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                            <p id="too-early-message" class="text-yellow-300 font-semibold"></p>
                        </div>
                        <p id="minutes-info" class="text-yellow-200 text-sm"></p>
                    </div>
                    <button 
                        id="confirm-checkin" 
                        class="mt-4 w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition"
                    >
                        Xác nhận
                    </button>
                </div>
                
                <!-- Invalid Ticket -->
                <div id="invalid-result" class="hidden bg-red-900/30 border-2 border-red-500 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-times-circle text-red-500 text-3xl mr-3"></i>
                        <h2 class="text-2xl font-bold text-red-500">VÉ KHÔNG HỢP LỆ</h2>
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
    </div>
</div>

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
const manualInput = document.getElementById('manual-ticket-id');
const checkManualBtn = document.getElementById('check-manual');
const resultBox = document.getElementById('result-box');
const validResult = document.getElementById('valid-result');
const invalidResult = document.getElementById('invalid-result');
const confirmCheckinBtn = document.getElementById('confirm-checkin');
const scanAgainBtn = document.getElementById('scan-again');
const uploadImageInput = document.getElementById('upload-image');
const imagePreviewContainer = document.getElementById('image-preview-container');
const imagePreview = document.getElementById('image-preview');
const scanFromImageBtn = document.getElementById('scan-from-image');
const removeImageBtn = document.getElementById('remove-image');

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
    
    // Set canvas size to image size
    canvas.width = imgElement.naturalWidth || imgElement.width;
    canvas.height = imgElement.naturalHeight || imgElement.height;
    
    // Draw image to canvas
    ctx.drawImage(imgElement, 0, 0, canvas.width, canvas.height);
    
    // Get image data
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    
    // Scan QR code
    const code = jsQR(imageData.data, imageData.width, imageData.height);
    
    if (code) {
        console.log('QR Code detected from image:', code.data);
        checkTicket(code.data);
        // Hide image preview after successful scan
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
                facingMode: 'environment', // Use back camera on mobile
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
        // Stop scanning temporarily
        scanning = false;
        
        const response = await fetch('{{ route("staff.scan.check") }}', {
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
        // Resume scanning after a delay
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
    
    // Hide warning and enable confirm button
    const warningDiv = document.getElementById('too-early-warning');
    const confirmBtn = document.getElementById('confirm-checkin');
    warningDiv.classList.add('hidden');
    confirmBtn.disabled = false;
    confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    confirmBtn.textContent = 'Xác nhận';
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
        const response = await fetch('{{ route("staff.scan.confirm") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ticket_id: currentTicketId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Xác nhận thành công!');
            // Reset
            resultBox.classList.add('hidden');
            currentTicketId = null;
            manualInput.value = '';
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
@endsection

