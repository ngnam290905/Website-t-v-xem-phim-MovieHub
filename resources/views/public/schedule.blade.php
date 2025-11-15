@extends('layouts.main')

@section('title', 'Lịch chiếu - MovieHub')

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Lịch chiếu</h1>
            <p class="text-[#a6a6b0]">Xem lịch chiếu phim theo ngày</p>
        </div>

        <!-- Date Picker -->
        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-white mb-4">Chọn ngày</h2>
            <div class="flex gap-3 overflow-x-auto pb-2">
                @foreach($availableDates as $dateItem)
                    <a href="{{ route('public.schedule', ['date' => $dateItem['date']]) }}"
                       class="flex-shrink-0 px-6 py-3 rounded-lg border transition-all
                       {{ $date === $dateItem['date'] 
                         ? 'bg-gradient-to-r from-[#F53003] to-orange-400 border-[#F53003] text-white' 
                         : 'bg-[#1a1d24] border-[#2A2F3A] text-[#a6a6b0] hover:border-[#F53003]' }}">
                        <div class="text-center">
                            <div class="text-xs opacity-75">
                                @if($dateItem['is_today']) 
                                    Hôm nay
                                @elseif($dateItem['is_tomorrow']) 
                                    Ngày mai
                                @else
                                    @php
                                        $dayNames = [
                                            'Monday' => 'Thứ 2',
                                            'Tuesday' => 'Thứ 3',
                                            'Wednesday' => 'Thứ 4',
                                            'Thursday' => 'Thứ 5',
                                            'Friday' => 'Thứ 6',
                                            'Saturday' => 'Thứ 7',
                                            'Sunday' => 'Chủ nhật'
                                        ];
                                    @endphp
                                    {{ $dayNames[$dateItem['day_name']] ?? 'Chủ nhật' }}
                                @endif
                            </div>
                            <div class="font-semibold mt-1">{{ $dateItem['formatted'] }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Showtimes by Movie -->
        @if($showtimes->count() > 0)
            <div class="space-y-6">
                @foreach($showtimes as $movieId => $movieShowtimes)
                    @php
                        $movie = $movieShowtimes->first()->phim;
                    @endphp
                    <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                        <div class="flex gap-6 mb-4">
                            <img src="{{ $movie->poster_url ?? $movie->poster ?? asset('images/no-poster.svg') }}" 
                                 alt="{{ $movie->ten_phim }}" 
                                 class="w-24 h-36 object-cover rounded-lg">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-white mb-2">{{ $movie->ten_phim }}</h2>
                                <p class="text-[#a6a6b0] text-sm mb-4">
                                    {{ $movie->formatted_duration ?? ($movie->do_dai ?? 0) . ' phút' }} • 
                                    @if($movie->do_tuoi) {{ $movie->do_tuoi }} • @endif
                                    {{ $movie->the_loai ?? 'N/A' }}
                                </p>
                                <a href="{{ route('movie-detail', $movie->id) }}" 
                                   class="inline-block text-[#F53003] hover:text-orange-400 text-sm font-semibold">
                                    Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($movieShowtimes as $showtime)
                                <div class="bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-[#F53003] transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <p class="font-bold text-xl text-white">{{ $showtime->thoi_gian_bat_dau->format('H:i') }}</p>
                                            <p class="text-sm text-[#a6a6b0]">{{ $showtime->thoi_gian_ket_thuc->format('H:i') }} kết thúc</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-[#a6a6b0] mb-3">
                                        <i class="fas fa-door-open text-[#FF784E]"></i>
                                        <span>{{ $showtime->phongChieu->name ?? $showtime->phongChieu->ten_phong ?? 'N/A' }}</span>
                                    </div>
                                    @auth
                                        <a href="{{ route('booking.seats', $showtime->id) }}" 
                                           class="block w-full text-center bg-gradient-to-r from-[#F53003] to-orange-400 text-white py-2 rounded-lg font-semibold hover:shadow-lg transition-all">
                                            Đặt vé
                                        </a>
                                    @else
                                        <a href="{{ route('booking.seats', $showtime->id) }}" 
                                           class="block w-full text-center bg-[#2A2F3A] text-white py-2 rounded-lg font-semibold hover:bg-[#3A3F4A] transition-all">
                                            Xem ghế
                                        </a>
                                    @endauth
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <i class="fas fa-calendar-times text-6xl text-[#a6a6b0] mb-4"></i>
                <p class="text-[#a6a6b0] text-lg">Không có suất chiếu nào cho ngày này</p>
            </div>
        @endif
    </div>
</div>
@endsection

