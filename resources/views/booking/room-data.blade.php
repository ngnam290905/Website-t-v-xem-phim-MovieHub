@extends('layouts.main')

@section('title', 'Room Data - ' . ($room->name ?? $room->ten_phong))

@section('content')
<div class="min-h-screen bg-[#0F1117] py-8">
    <div class="max-w-7xl mx-auto px-4">
        <a href="{{ route('booking.data') }}" class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Quay lại
        </a>

        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
            <h1 class="text-3xl font-bold text-white mb-4">{{ $room->name ?? $room->ten_phong }}</h1>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-[#a6a6b0] text-sm mb-1">Số hàng</p>
                    <p class="text-xl font-bold text-white">{{ $room->rows ?? $room->so_hang ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-[#a6a6b0] text-sm mb-1">Số cột</p>
                    <p class="text-xl font-bold text-white">{{ $room->cols ?? $room->so_cot ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-[#a6a6b0] text-sm mb-1">Sức chứa</p>
                    <p class="text-xl font-bold text-white">{{ $room->capacity ?? $room->suc_chua ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-[#a6a6b0] text-sm mb-1">Tổng ghế</p>
                    <p class="text-xl font-bold text-white">{{ $room->seats->count() }}</p>
                </div>
            </div>
            @if($room->description ?? $room->mo_ta)
                <p class="text-[#a6a6b0] mt-4">{{ $room->description ?? $room->mo_ta }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Seat Map -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-chair text-[#FF784E]"></i>
                    <span>Sơ đồ ghế</span>
                </h2>
                
                <!-- Screen -->
                <div class="text-center mb-6">
                    <div class="bg-gradient-to-b from-[#1a1a1a] to-[#0a0a0a] rounded-t-[20px] border-2 border-[#FF784E]/40 py-4 px-8 inline-block">
                        <span class="text-[#FF784E] font-bold">MÀN HÌNH</span>
                    </div>
                </div>

                <!-- Seat Grid -->
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full">
                        @foreach($seatsByRow as $rowLabel => $rowSeats)
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 flex items-center justify-center text-sm font-bold text-white bg-[#2a2d3a] rounded shrink-0">
                                    {{ $rowLabel }}
                                </div>
                                <div class="flex gap-1 flex-wrap">
                                    @foreach($rowSeats as $seat)
                                        @php
                                            $seatType = $seat->seatType ?? null;
                                            $isVip = $seatType && strpos(strtolower($seatType->ten_loai ?? ''), 'vip') !== false;
                                        @endphp
                                        <div class="w-8 h-8 flex items-center justify-center text-xs font-semibold rounded
                                            @if($seat->trang_thai == 0) bg-gray-800/30 border border-dashed border-gray-700 text-gray-600
                                            @elseif($isVip) bg-gradient-to-br from-yellow-600 to-yellow-700 border-2 border-yellow-500 text-yellow-100
                                            @else bg-[#2a2d3a] border border-[#3a3d4a] text-white
                                            @endif">
                                            {{ preg_replace('/^[A-Z]/', '', $seat->so_ghe) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-6 flex flex-wrap gap-4 justify-center">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-[#2a2d3a] border border-[#3a3d4a] rounded"></div>
                        <span class="text-sm text-[#a6a6b0]">Thường</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-gradient-to-br from-yellow-600 to-yellow-700 border-2 border-yellow-500 rounded"></div>
                        <span class="text-sm text-[#a6a6b0]">VIP</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-gray-800/30 border border-dashed border-gray-700 rounded"></div>
                        <span class="text-sm text-[#a6a6b0]">Vô hiệu</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Showtimes -->
            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-400"></i>
                    <span>Suất chiếu sắp tới</span>
                </h2>
                <div class="space-y-3">
                    @forelse($room->showtimes as $showtime)
                        <a href="{{ route('booking.data.showtime', $showtime->id) }}" class="block bg-[#1a1d24] border border-[#2A2F3A] rounded-lg p-4 hover:border-blue-400 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-white mb-1">{{ $showtime->phim->ten_phim ?? 'N/A' }}</h3>
                                    <p class="text-sm text-[#a6a6b0">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $showtime->thoi_gian_bat_dau->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <i class="fas fa-chevron-right text-[#a6a6b0]"></i>
                            </div>
                        </a>
                    @empty
                        <p class="text-[#a6a6b0] text-center py-8">Không có suất chiếu nào</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

