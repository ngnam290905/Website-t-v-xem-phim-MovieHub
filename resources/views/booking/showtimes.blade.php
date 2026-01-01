@extends('layouts.app')

@section('title', 'Chọn suất chiếu - ' . $movie->ten_phim)

@section('content')
<div class="min-h-screen bg-[#0d0f14] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Movie Info Card (Enhanced) -->
        <div class="bg-gradient-to-r from-[#1a1d24] to-[#151822] border border-[#262833] rounded-2xl p-6 mb-8 shadow-xl">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Poster -->
                <div class="flex-shrink-0">
                    <div class="relative group">
                        <img src="{{ $movie->poster_url ?? asset('images/no-poster.svg') }}" 
                             alt="{{ $movie->ten_phim }}" 
                             class="w-40 h-60 md:w-48 md:h-72 object-cover rounded-xl shadow-lg transition-transform duration-300 group-hover:scale-105"
                             onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                        @if($movie->trailer)
                            <button onclick="openTrailer('{{ $movie->trailer }}', '{{ $movie->ten_phim }}')" 
                                    class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl">
                                <i class="fas fa-play-circle text-5xl text-white"></i>
                            </button>
                        @endif
                    </div>
                </div>
                
                <!-- Movie Details -->
                <div class="flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">{{ $movie->ten_phim }}</h1>
                            @if($movie->ten_goc)
                                <p class="text-[#a6a6b0] italic mb-2">{{ $movie->ten_goc }}</p>
                            @endif
                        </div>
                        @if($movie->diem_danh_gia)
                            <div class="flex items-center gap-2 px-4 py-2 bg-yellow-500/20 border border-yellow-500/30 rounded-lg">
                                <i class="fas fa-star text-yellow-400"></i>
                                <span class="text-yellow-400 font-bold text-lg">{{ number_format($movie->diem_danh_gia, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-4 mb-4">
                        <span class="flex items-center gap-2 text-[#a6a6b0]">
                            <i class="far fa-clock text-[#F53003]"></i>
                            <span>{{ $movie->do_dai ?? 0 }} phút</span>
                        </span>
                        @if($movie->do_tuoi)
                            <span class="px-3 py-1 bg-[#F53003]/20 border border-[#F53003]/30 text-[#F53003] rounded-full font-semibold text-sm">
                                {{ $movie->do_tuoi }}
                            </span>
                        @endif
                        @if($movie->the_loai)
                            <span class="flex items-center gap-2 text-[#a6a6b0]">
                                <i class="fas fa-film text-[#F53003]"></i>
                                <span>{{ $movie->the_loai }}</span>
                            </span>
                        @endif
                        @if($movie->quoc_gia)
                            <span class="flex items-center gap-2 text-[#a6a6b0]">
                                <i class="fas fa-globe text-[#F53003]"></i>
                                <span>{{ $movie->quoc_gia }}</span>
                            </span>
                        @endif
                    </div>
                    
                    <p class="text-[#a6a6b0] text-sm line-clamp-3 mb-4">{{ $movie->mo_ta_ngan ?? substr($movie->mo_ta ?? '', 0, 200) }}</p>
                    
                    @if($movie->dao_dien || $movie->dien_vien)
                        <div class="flex flex-wrap gap-4 text-sm">
                            @if($movie->dao_dien)
                                <div>
                                    <span class="text-[#a6a6b0]">Đạo diễn:</span>
                                    <span class="text-white ml-2">{{ $movie->dao_dien }}</span>
                                </div>
                            @endif
                            @if($movie->dien_vien)
                                <div>
                                    <span class="text-[#a6a6b0]">Diễn viên:</span>
                                    <span class="text-white ml-2">{{ \Illuminate\Support\Str::limit($movie->dien_vien, 50) }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <!-- Left Column (70%) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Date Picker (Card Style) -->
                <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
                    <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-[#F53003]"></i>
                        Chọn ngày
                    </h2>
                    <div id="datePicker" class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                        <!-- Loading Skeleton -->
                        <div id="datePickerSkeleton" class="flex gap-3">
                            @for($i = 0; $i < 7; $i++)
                                <div class="flex-shrink-0 w-24 h-28 bg-[#1a1d24] rounded-lg animate-pulse"></div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Showtimes -->
                <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
                    <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-clock text-[#F53003]"></i>
                        Chọn suất chiếu
                    </h2>
                    <div id="showtimesContainer">
                        <!-- Loading Skeleton -->
                        <div id="showtimesSkeleton" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @for($i = 0; $i < 4; $i++)
                                <div class="h-32 bg-[#1a1d24] rounded-lg animate-pulse"></div>
                            @endfor
                        </div>
                        <!-- Empty State -->
                        <div id="showtimesEmpty" class="hidden text-center py-12">
                            <i class="fas fa-calendar-times text-5xl text-[#a6a6b0] mb-4"></i>
                            <p class="text-[#a6a6b0] text-lg">Vui lòng chọn ngày để xem suất chiếu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (30%) - Ticket Info -->
            <div class="lg:col-span-3">
                <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-ticket-alt text-[#F53003]"></i>
                        Thông tin vé
                    </h3>
                    
                    <div id="ticketInfo" class="space-y-4">
                        <!-- Movie -->
                        <div>
                            <p class="text-xs text-[#a6a6b0] mb-1">🎬 Phim</p>
                            <p class="text-white font-semibold" id="ticketMovie">{{ $movie->ten_phim }}</p>
                        </div>
                        
                        <!-- Date -->
                        <div>
                            <p class="text-xs text-[#a6a6b0] mb-1">📅 Ngày chiếu</p>
                            <p class="text-white font-semibold" id="ticketDate">--</p>
                        </div>
                        
                        <!-- Showtime -->
                        <div>
                            <p class="text-xs text-[#a6a6b0] mb-1">⏰ Suất chiếu</p>
                            <p class="text-white font-semibold" id="ticketShowtime">--</p>
                        </div>
                        
                        <!-- Room -->
                        <div>
                            <p class="text-xs text-[#a6a6b0] mb-1">🎭 Phòng chiếu</p>
                            <p class="text-white font-semibold" id="ticketRoom">--</p>
                        </div>
                        
                        <!-- Seats -->
                        <div>
                            <p class="text-xs text-[#a6a6b0] mb-1">🪑 Ghế</p>
                            <p class="text-white font-semibold" id="ticketSeats">--</p>
                        </div>
                        
                        <div class="border-t border-[#262833] pt-4 mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-[#a6a6b0]">Tổng tiền</span>
                                <span class="text-2xl font-bold text-yellow-400" id="ticketTotal">0 đ</span>
                            </div>
                        </div>
                        
                        <a href="#" id="continueBtn" 
                           class="hidden w-full mt-6 px-6 py-3 bg-gradient-to-r from-[#F53003] to-[#ff7849] text-white rounded-lg font-semibold text-center hover:shadow-lg hover:shadow-[#F53003]/50 transition-all">
                            <i class="fas fa-arrow-right mr-2"></i>
                            Tiếp tục
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-24 right-4 z-50 hidden">
    <div class="bg-[#151822] border border-[#262833] rounded-lg p-4 shadow-xl flex items-center gap-3 min-w-[300px]">
        <div id="toastIcon" class="text-2xl"></div>
        <p id="toastMessage" class="text-white flex-1"></p>
    </div>
</div>

<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.date-card {
    transition: all 0.3s ease;
}

.date-card:hover {
    transform: translateY(-2px);
}

.showtime-btn {
    transition: all 0.3s ease;
}

.showtime-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 48, 3, 0.3);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.toast-show {
    animation: slideIn 0.3s ease;
}
</style>

<script>
const movieId = {{ $movie->id }};
const selectedDate = '{{ $selectedDate }}';
let currentSelectedShowtime = null;

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');
    
    const icons = {
        success: '<i class="fas fa-check-circle text-green-400"></i>',
        error: '<i class="fas fa-exclamation-circle text-red-400"></i>',
        info: '<i class="fas fa-info-circle text-blue-400"></i>',
        warning: '<i class="fas fa-exclamation-triangle text-yellow-400"></i>'
    };
    
    toastIcon.innerHTML = icons[type] || icons.info;
    toastMessage.textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('toast-show');
    
    setTimeout(() => {
        toast.classList.add('hidden');
        toast.classList.remove('toast-show');
    }, 3000);
}

// Load available dates
async function loadAvailableDates() {
    try {
        const response = await fetch(`/api/booking/movie/${movieId}/dates`);
        const result = await response.json();
        
        if (result.success) {
            const datePicker = document.getElementById('datePicker');
            const skeleton = document.getElementById('datePickerSkeleton');
            skeleton.classList.add('hidden');
            
            datePicker.innerHTML = '';
            
            const dayNames = {
                'Monday': 'Thứ 2',
                'Tuesday': 'Thứ 3',
                'Wednesday': 'Thứ 4',
                'Thursday': 'Thứ 5',
                'Friday': 'Thứ 6',
                'Saturday': 'Thứ 7',
                'Sunday': 'CN'
            };
            
            result.data.forEach(date => {
                const dateBtn = document.createElement('button');
                const isSelected = date.date === selectedDate;
                const dayName = dayNames[date.day_name] || date.day_name;
                
                dateBtn.className = `flex-shrink-0 w-24 h-28 flex flex-col items-center justify-center rounded-lg border-2 transition-all date-card ${
                    isSelected 
                        ? 'bg-[#F53003] border-[#F53003] text-white shadow-lg shadow-[#F53003]/50' 
                        : 'bg-[#1a1d24] border-[#262833] text-[#a6a6b0] hover:border-[#F53003]/50 hover:bg-[#222533]'
                }`;
                
                dateBtn.innerHTML = `
                    <div class="text-xs font-medium mb-1">${dayName}</div>
                    <div class="text-2xl font-bold">${date.formatted.split('/')[0]}</div>
                    <div class="text-xs opacity-75">${date.formatted.split('/').slice(1).join('/')}</div>
                    ${date.is_today ? '<div class="text-xs mt-1 font-semibold">Hôm nay</div>' : ''}
                    ${date.is_tomorrow ? '<div class="text-xs mt-1 font-semibold">Ngày mai</div>' : ''}
                `;
                
                dateBtn.onclick = () => {
                    window.location.href = `{{ route('booking.showtimes', $movie->id) }}?date=${date.date}`;
                };
                
                datePicker.appendChild(dateBtn);
            });
        }
    } catch (error) {
        console.error('Error loading dates:', error);
        showToast('Có lỗi xảy ra khi tải danh sách ngày', 'error');
    }
}

// Load showtimes for selected date
async function loadShowtimes(date) {
    try {
        const container = document.getElementById('showtimesContainer');
        const skeleton = document.getElementById('showtimesSkeleton');
        const empty = document.getElementById('showtimesEmpty');
        
        skeleton.classList.remove('hidden');
        empty.classList.add('hidden');
        
        const response = await fetch(`/api/booking/movie/${movieId}/showtimes?date=${date}`);
        const result = await response.json();
        
        skeleton.classList.add('hidden');
        
        if (result.success && result.data.length > 0) {
            empty.classList.add('hidden');
            container.innerHTML = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="showtimesGrid"></div>';
            const grid = document.getElementById('showtimesGrid');
            
            result.data.forEach(showtime => {
                const showtimeCard = document.createElement('button');
                const isDisabled = showtime.available_seats === 0 || showtime.is_past;
                
                let statusClass = 'border-green-500/50 hover:border-green-500';
                let statusText = 'Còn nhiều ghế';
                let statusIcon = '<i class="fas fa-check-circle text-green-400"></i>';
                
                if (showtime.seat_status === 'sold_out') {
                    statusClass = 'border-red-500/50 opacity-60 cursor-not-allowed';
                    statusText = 'Hết ghế';
                    statusIcon = '<i class="fas fa-times-circle text-red-400"></i>';
                } else if (showtime.seat_status === 'low') {
                    statusClass = 'border-yellow-500/50 hover:border-yellow-500';
                    statusText = 'Sắp hết';
                    statusIcon = '<i class="fas fa-exclamation-triangle text-yellow-400"></i>';
                }
                
                showtimeCard.className = `showtime-btn bg-[#1a1d24] border-2 ${statusClass} rounded-xl p-5 text-left transition-all ${
                    isDisabled ? 'opacity-60 cursor-not-allowed' : ''
                }`;
                showtimeCard.disabled = isDisabled;
                
                showtimeCard.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <div class="text-3xl font-bold text-white mb-1">${showtime.time}</div>
                            <div class="text-sm text-[#a6a6b0]">Kết thúc: ${showtime.end_time}</div>
                        </div>
                        <div class="text-right">
                            ${statusIcon}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-door-open text-[#F53003]"></i>
                            <span class="text-white font-semibold">${showtime.room_name}</span>
                            <span class="px-2 py-0.5 bg-[#F53003]/20 text-[#F53003] rounded text-xs">${showtime.room_type}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-chair text-[#F53003]"></i>
                            <span class="text-[#a6a6b0]">Ghế trống: <span class="text-white font-semibold">${showtime.available_seats}</span></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-tags text-[#F53003]"></i>
                            <span class="text-[#a6a6b0]">Từ <span class="text-[#F53003] font-bold">${showtime.formatted_price}</span></span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-[#262833]">
                            <span class="text-xs text-[#a6a6b0]">${statusText}</span>
                        </div>
                    </div>
                `;
                
                if (!isDisabled) {
                    showtimeCard.onclick = (e) => {
                        selectShowtime(showtime, e);
                    };
                }
                
                grid.appendChild(showtimeCard);
            });
        } else {
            empty.classList.remove('hidden');
            container.innerHTML = '';
            container.appendChild(empty);
        }
    } catch (error) {
        console.error('Error loading showtimes:', error);
        showToast('Có lỗi xảy ra khi tải suất chiếu', 'error');
    }
}

// Select showtime
function selectShowtime(showtime, event) {
    currentSelectedShowtime = showtime;
    
    // Update ticket info
    document.getElementById('ticketDate').textContent = showtime.date.split('-').reverse().join('/');
    document.getElementById('ticketShowtime').textContent = showtime.time;
    document.getElementById('ticketRoom').textContent = `${showtime.room_name} - ${showtime.room_type}`;
    document.getElementById('ticketSeats').textContent = 'Chưa chọn';
    document.getElementById('ticketTotal').textContent = showtime.formatted_price;
    
    // Show continue button
    const continueBtn = document.getElementById('continueBtn');
    continueBtn.classList.remove('hidden');
    continueBtn.href = `/shows/${showtime.id}/seats`;
    
    // Update selected state
    document.querySelectorAll('.showtime-btn').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-[#F53003]', 'ring-offset-2', 'ring-offset-[#151822]');
    });
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('ring-2', 'ring-[#F53003]', 'ring-offset-2', 'ring-offset-[#151822]');
    }
    
    showToast('Đã chọn suất chiếu ' + showtime.time, 'success');
}

// Initialize
loadAvailableDates();
if (selectedDate) {
    loadShowtimes(selectedDate);
} else {
    document.getElementById('showtimesEmpty').classList.remove('hidden');
    document.getElementById('showtimesSkeleton').classList.add('hidden');
}
</script>
@endsection
