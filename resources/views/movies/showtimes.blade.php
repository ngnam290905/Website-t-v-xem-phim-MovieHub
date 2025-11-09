@extends('layouts.app')

@section('title', $title . ' - MovieHub')

@push('styles')
<style>
    .time-btn {
        transition: all 0.2s;
    }
    .time-btn:hover:not(.disabled) {
        background-color: #F53003 !important;
        border-color: #F53003 !important;
        color: white !important;
    }
    .disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .movie-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .movie-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
    }
    .coming-soon-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #f59e0b;
        color: #1f2937;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">{{ $title }}</h1>
        <p class="text-gray-400">{{ $description }}</p>
    </div>

    <!-- Date Navigation -->
    <div class="mb-8 overflow-x-auto">
        <div class="flex space-x-2 pb-2">
            @foreach($dates as $date)
            <a href="?date={{ $date['date'] }}" 
               class="flex-shrink-0 flex flex-col items-center justify-center w-16 h-20 rounded-lg {{ $activeDate === $date['date'] ? 'bg-[#F53003]' : 'bg-[#1b1d24] hover:bg-[#262833]' }} transition-colors">
                <span class="text-sm {{ $activeDate === $date['date'] ? 'text-white' : 'text-gray-400' }}">{{ $date['weekday'] }}</span>
                <span class="text-xl font-bold {{ $activeDate === $date['date'] ? 'text-white' : 'text-white' }}">{{ $date['day'] }}</span>
                @if($date['is_today'])
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-green-500 rounded-full"></span>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    <!-- Room Filter -->
    @if($rooms->isNotEmpty())
    <div class="mb-6">
        <select id="room-filter" class="bg-[#1b1d24] border border-[#262833] text-white text-sm rounded-lg focus:ring-[#F53003] focus:border-[#F53003] block w-full md:w-64 p-2.5">
            <option value="">Tất cả phòng chiếu</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}">{{ $room->ten_phong }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <!-- Movie Showtimes -->
    <div class="space-y-6">
        @forelse($movies as $movie)
        <div class="movie-card bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col md:flex-row">
                    <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6 relative">
                        <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-full md:w-40 h-56 object-cover rounded-lg">
                        @if($movie->trang_thai === 'sap_chieu')
                            <span class="coming-soon-badge">Sắp chiếu</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white">{{ $movie->ten_phim }}</h2>
                            <div class="flex items-center">
                                <span class="text-yellow-400 mr-1">★</span>
                                <span class="text-white">{{ number_format($movie->diem_danh_gia, 1) }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-400 mt-1">{{ $movie->the_loai }} • {{ $movie->do_dai }} phút</p>
                        
                        <div class="mt-4">
                            <h3 class="text-sm font-medium text-gray-300 mb-2">Suất chiếu:</h3>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $showtimes = $movie->suatChieus->where('ngay_chieu', $activeDate);
                                    $now = now();
                                    $activeDateTime = \Carbon\Carbon::parse($activeDate);
                                    $isToday = $activeDateTime->isToday();
                                @endphp
                                
                                @if($showtimes->count() > 0)
                                    @forelse($showtimes as $showtime)
                                    @php
                                        $showTime = \Carbon\Carbon::parse($showtime->gio_bat_dau);
                                        $isPastShowtime = $isToday && $showTime->lt($now);
                                    @endphp
                                    <a href="{{ !$isPastShowtime ? route('booking', ['movie' => $movie->id, 'showtime' => $showtime->id]) : '#' }}" 
                                       class="time-btn px-4 py-2 border rounded-md text-sm font-medium {{ $isPastShowtime ? 'border-gray-700 text-gray-500 bg-gray-800 cursor-not-allowed disabled' : 'border-gray-600 text-white hover:bg-[#F53003] hover:border-[#F53003]' }} transition-colors"
                                       @if($isPastShowtime) title="Suất chiếu đã qua" @endif>
                                        {{ $showTime->format('H:i') }}
                                        @if($isPastShowtime)
                                        <span class="text-xs block text-gray-500">Đã qua</span>
                                        @endif
                                    </a>
                                    @empty
                                    <p class="text-sm text-gray-400">Không có suất chiếu nào cho ngày này.</p>
                                    @endforelse
                                @else
                                    <p class="text-gray-400 text-sm">Không có suất chiếu nào vào ngày đã chọn.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <div class="text-5xl mb-4">🎬</div>
            <h3 class="text-xl font-medium text-white mb-2">Không có lịch chiếu</h3>
            <p class="text-gray-400">Không có suất chiếu nào cho ngày đã chọn.</p>
            <a href="{{ route('movies.index') }}" class="inline-block mt-4 px-6 py-2 bg-[#F53003] text-white rounded-md hover:bg-opacity-90 transition">
                Xem phim đang chiếu
            </a>
        </div>
        @endforelse
    </div>

    <!-- CTA Section -->
    <div class="mt-16 text-center">
        <h2 class="text-2xl font-bold text-white mb-4">Không tìm thấy suất chiếu phù hợp?</h2>
        <p class="text-gray-400 mb-6 max-w-2xl mx-auto">Hãy kiểm tra các ngày khác hoặc xem danh sách phim đang chiếu của chúng tôi.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('movies.index') }}" class="px-6 py-3 bg-[#F53003] text-white rounded-md hover:bg-opacity-90 transition font-medium">
                Xem tất cả phim
            </a>
            <a href="#" class="px-6 py-3 border border-gray-600 text-white rounded-md hover:bg-gray-800 transition font-medium">
                Liên hệ hỗ trợ
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Highlight current date in the date navigation
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const dateParam = urlParams.get('date');
        
        if (!dateParam) {
            // If no date is selected, redirect to today's date
            const today = new Date().toISOString().split('T')[0];
            window.location.href = '{{ route("showtimes") }}?date=' + today;
        }
    });
</script>
@endpush
@endsection
