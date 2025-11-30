@extends('layouts.main')

@section('title', 'Chọn suất chiếu - ' . $movie->ten_phim)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Movie Info -->
    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 mb-6">
        <div class="flex gap-6">
            <img src="{{ $movie->poster_url ?? $movie->poster ?? asset('images/no-poster.svg') }}" 
                 alt="{{ $movie->ten_phim }}" 
                 class="w-32 h-48 object-cover rounded-lg">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-white mb-2">{{ $movie->ten_phim }}</h1>
                <p class="text-[#a6a6b0] text-sm mb-4">
                    {{ $movie->formatted_duration ?? ($movie->do_dai ?? 0) . ' phút' }} • 
                    @if($movie->do_tuoi) {{ $movie->do_tuoi }} • @endif
                    {{ $movie->the_loai ?? 'N/A' }}
                </p>
                <p class="text-[#a6a6b0] text-sm line-clamp-3">{{ $movie->mo_ta }}</p>
            </div>
        </div>
    </div>

    <!-- Date Picker -->
    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 mb-6">
        <h2 class="text-xl font-semibold text-white mb-4">Chọn ngày</h2>
        <div id="datePicker" class="flex gap-3 overflow-x-auto pb-2">
            <!-- Dates will be loaded here -->
        </div>
    </div>

    <!-- Showtimes -->
    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Chọn suất chiếu</h2>
        <div id="showtimesContainer" class="space-y-3">
            <div class="text-center py-8">
                <p class="text-[#a6a6b0]">Vui lòng chọn ngày để xem suất chiếu</p>
            </div>
        </div>
    </div>
</div>

<script>
const movieId = {{ $movie->id }};
const selectedDate = '{{ $selectedDate }}';

// Load available dates
async function loadAvailableDates() {
    try {
        const response = await fetch(`/api/booking/movie/${movieId}/dates`);
        const result = await response.json();
        
        if (result.success) {
            const datePicker = document.getElementById('datePicker');
            datePicker.innerHTML = '';
            
            result.data.forEach(date => {
                const dateBtn = document.createElement('button');
                dateBtn.className = `flex-shrink-0 px-6 py-3 rounded-lg border transition-all ${
                    date.date === selectedDate 
                        ? 'bg-[#F53003] border-[#F53003] text-white' 
                        : 'bg-[#0f0f12] border-[#262833] text-[#a6a6b0] hover:border-[#F53003]'
                }`;
                dateBtn.innerHTML = `
                    <div class="text-center">
                        <div class="text-xs opacity-75">${date.day_name === 'Monday' ? 'Thứ 2' : 
                            date.day_name === 'Tuesday' ? 'Thứ 3' :
                            date.day_name === 'Wednesday' ? 'Thứ 4' :
                            date.day_name === 'Thursday' ? 'Thứ 5' :
                            date.day_name === 'Friday' ? 'Thứ 6' :
                            date.day_name === 'Saturday' ? 'Thứ 7' : 'Chủ nhật'}</div>
                        <div class="font-semibold mt-1">${date.is_today ? 'Hôm nay' : date.is_tomorrow ? 'Ngày mai' : date.formatted}</div>
                    </div>
                `;
                dateBtn.onclick = () => {
                    window.location.href = `{{ route('booking.showtimes', $movie->id) }}?date=${date.date}`;
                };
                datePicker.appendChild(dateBtn);
            });
        }
    } catch (error) {
        console.error('Error loading dates:', error);
    }
}

// Load showtimes for selected date
async function loadShowtimes(date) {
    try {
        const response = await fetch(`/api/booking/movie/${movieId}/showtimes?date=${date}`);
        const result = await response.json();
        
        const container = document.getElementById('showtimesContainer');
        
        if (result.success && result.data.length > 0) {
            container.innerHTML = '';
            
            result.data.forEach(showtime => {
                const showtimeCard = document.createElement('div');
                showtimeCard.className = 'bg-[#222533] rounded-lg p-4 hover:bg-[#2a2d3a] transition-colors';
                showtimeCard.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-lg text-white">${showtime.time}</p>
                            <p class="text-sm text-[#a6a6b0]">${showtime.room_name}</p>
                            <p class="text-xs text-[#a6a6b0]">Kết thúc: ${showtime.end_time}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-[#F53003] mb-2">Từ 50.000đ</p>
                            <a href="{{ url('/shows') }}/${showtime.id}/seats" 
                               class="inline-block text-sm bg-[#F53003] text-white px-6 py-2 rounded hover:bg-[#ff4d4d] transition-colors">
                                Chọn ghế
                            </a>
                        </div>
                    </div>
                `;
                container.appendChild(showtimeCard);
            });
        } else {
            container.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-[#a6a6b0]">Không có suất chiếu nào cho ngày này</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading showtimes:', error);
        document.getElementById('showtimesContainer').innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-500">Có lỗi xảy ra khi tải suất chiếu</p>
            </div>
        `;
    }
}

// Initialize
loadAvailableDates();
loadShowtimes(selectedDate);
</script>
@endsection

