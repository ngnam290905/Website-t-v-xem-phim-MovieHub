@extends('layouts.main')

@section('title', 'Đặt vé - MovieHub')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Chọn phim để đặt vé</h1>
        <p class="text-[#a6a6b0]">Chọn phim bạn muốn xem và tiếp tục đặt vé</p>
    </div>

    @if($movies->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($movies as $movie)
                <a href="{{ route('booking.showtimes', $movie->id) }}" 
                   class="group bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden hover:border-[#F53003] transition-all duration-300 movie-card">
                    <div class="relative overflow-hidden">
                        <img src="{{ $movie->poster_url ?? $movie->poster ?? asset('images/no-poster.svg') }}" 
                             alt="{{ $movie->ten_phim }}" 
                             class="w-full aspect-[2/3] object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/0 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                            <p class="text-white text-sm font-semibold line-clamp-2">{{ $movie->ten_phim }}</p>
                            <p class="text-[#a6a6b0] text-xs mt-1">{{ $movie->formatted_duration ?? ($movie->do_dai ?? 0) . ' phút' }}</p>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="text-white font-semibold text-sm line-clamp-1 group-hover:text-[#F53003] transition-colors">
                            {{ $movie->ten_phim }}
                        </h3>
                        <p class="text-[#a6a6b0] text-xs mt-1">{{ $movie->formatted_duration ?? ($movie->do_dai ?? 0) . ' phút' }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-16">
            <p class="text-[#a6a6b0] text-lg">Hiện tại không có phim nào đang chiếu.</p>
        </div>
    @endif
</div>
@endsection

