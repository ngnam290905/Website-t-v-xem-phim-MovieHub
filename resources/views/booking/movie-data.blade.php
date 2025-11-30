@extends('layouts.main')

@section('title', 'Movie Data - ' . $movie->ten_phim)

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <a href="{{ route('booking.data') }}" class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Quay lại
        </a>

        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
            <div class="flex gap-6">
                <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-32 h-48 object-cover rounded-lg">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $movie->ten_phim }}</h1>
                    <p class="text-[#a6a6b0] mb-4">
                        <span class="mr-4">{{ $movie->do_dai }} phút</span>
                        <span class="mr-4">{{ $movie->dao_dien }}</span>
                        <span>{{ $movie->the_loai ?? 'N/A' }}</span>
                    </p>
                    <p class="text-[#a6a6b0]">{{ $movie->mo_ta }}</p>
                </div>
            </div>
        </div>

        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-[#FF784E]"></i>
                <span>Suất chiếu ({{ $showtimesByDate->count() }} ngày)</span>
            </h2>

            @forelse($showtimesByDate as $date => $showtimes)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-3">
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        @if(\Carbon\Carbon::parse($date)->isToday())
                            <span class="text-sm text-[#FF784E]">(Hôm nay)</span>
                        @elseif(\Carbon\Carbon::parse($date)->isTomorrow())
                            <span class="text-sm text-[#FF784E]">(Ngày mai)</span>
                        @endif
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($showtimes as $showtime)
                            <a href="{{ route('booking.data.showtime', $showtime->id) }}" class="block bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-[#FF784E] transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-white text-lg">{{ $showtime->thoi_gian_bat_dau->format('H:i') }}</p>
                                        <p class="text-sm text-[#a6a6b0]">{{ $showtime->phongChieu->name ?? $showtime->phongChieu->ten_phong ?? 'N/A' }}</p>
                                    </div>
                                    <i class="fas fa-chevron-right text-[#a6a6b0]"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-[#a6a6b0] text-center py-8">Không có suất chiếu nào</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

