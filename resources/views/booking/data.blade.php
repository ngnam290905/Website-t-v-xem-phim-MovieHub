@extends('layouts.main')

@section('title', 'Booking Data Overview')

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-white mb-8">📊 Booking Data Overview</h1>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Phim đang chiếu</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_movies'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-[#FF784E]/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-film text-[#FF784E] text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Phòng chiếu</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_rooms'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-door-open text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Tổng ghế</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_seats'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chair text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Suất chiếu</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_shows'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-purple-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Combo</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_combos'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-yellow-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Tổng booking</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_bookings'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-indigo-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Đã thanh toán</p>
                        <p class="text-2xl font-bold text-green-400">{{ $stats['paid_bookings'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#a6a6b0] text-sm mb-1">Chờ thanh toán</p>
                        <p class="text-2xl font-bold text-yellow-400">{{ $stats['pending_bookings'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Movies -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-film text-[#FF784E]"></i>
                    <span>Phim đang chiếu</span>
                </h2>
                <div class="space-y-3">
                    @foreach($movies as $movie)
                        <a href="{{ route('booking.data.movie', $movie->id) }}" class="block bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-[#FF784E] transition-colors">
                            <div class="flex items-center gap-4">
                                <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}" class="w-16 h-24 object-cover rounded">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-white mb-1">{{ $movie->ten_phim }}</h3>
                                    <p class="text-sm text-[#a6a6b0]">{{ $movie->suat_chieu_count }} suất chiếu</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Rooms -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-door-open text-blue-400"></i>
                    <span>Phòng chiếu</span>
                </h2>
                <div class="space-y-3">
                    @foreach($rooms as $room)
                        <a href="{{ route('booking.data.room', $room->id) }}" class="block bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-blue-400 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-white mb-1">{{ $room->name ?? $room->ten_phong }}</h3>
                                    <p class="text-sm text-[#a6a6b0]">{{ $room->seats_count }} ghế</p>
                                </div>
                                <i class="fas fa-chevron-right text-[#a6a6b0]"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Combos -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-box text-yellow-400"></i>
                    <span>Combo</span>
                </h2>
                <div class="space-y-3">
                    @foreach($combos as $combo)
                        <div class="bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-white mb-1">{{ $combo->ten }}</h3>
                                    <p class="text-sm text-[#a6a6b0] line-clamp-1">{{ $combo->mo_ta }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-[#FF784E]">{{ number_format($combo->gia) }}đ</p>
                                    @if($combo->gia_goc && $combo->gia_goc > $combo->gia)
                                        <p class="text-xs text-[#a6a6b0] line-through">{{ number_format($combo->gia_goc) }}đ</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-indigo-400"></i>
                    <span>Booking gần đây</span>
                </h2>
                <div class="space-y-3">
                    @foreach($recentBookings as $booking)
                        <a href="{{ route('booking.data.booking', $booking->id) }}" class="block bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-indigo-400 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h3 class="font-semibold text-white">{{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</h3>
                                    <p class="text-sm text-[#a6a6b0]">{{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if($booking->trang_thai == 'PAID') bg-green-500/20 text-green-400
                                    @elseif($booking->trang_thai == 'PENDING') bg-yellow-500/20 text-yellow-400
                                    @else bg-gray-500/20 text-gray-400
                                    @endif">
                                    {{ $booking->trang_thai }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-[#a6a6b0]">
                                <i class="fas fa-chair"></i>
                                <span>{{ $booking->chiTietDatVe->count() }} ghế</span>
                                <span class="mx-2">•</span>
                                <i class="fas fa-money-bill"></i>
                                <span>{{ number_format($booking->tong_tien) }}đ</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

