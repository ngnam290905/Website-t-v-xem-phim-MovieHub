@extends('layouts.main')

@section('title', 'Showtime Data')

@section('content')
    <div class="min-h-screen bg-[#0F1117] py-8">
        <div class="max-w-7xl mx-auto px-4">
            <a href="{{ route('booking.data') }}"
                class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-6 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại
            </a>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6 mb-6">
                <div class="flex gap-6">
                    <img src="{{ $showtime->phim->poster_url ?? ($showtime->phim->poster ?? asset('images/no-poster.svg')) }}"
                        alt="{{ $showtime->phim->ten_phim }}" class="w-24 h-36 object-cover rounded-lg"
                        onerror="this.src='{{ asset('images/no-poster.svg') }}'">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-white mb-2">{{ $showtime->phim->ten_phim }}</h1>
                        <div class="space-y-2 text-[#a6a6b0]">
                            <p>
                                <i class="far fa-calendar mr-2"></i>
                                {{ $showtime->thoi_gian_bat_dau->format('d/m/Y') }}
                            </p>
                            <p>
                                <i class="far fa-clock mr-2"></i>
                                {{ $showtime->thoi_gian_bat_dau->format('H:i') }} -
                                {{ $showtime->thoi_gian_ket_thuc->format('H:i') }}
                            </p>
                            <p>
                                <i class="fas fa-door-open mr-2"></i>
                                {{ $showtime->phongChieu->name ?? ($showtime->phongChieu->ten_phong ?? 'N/A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[#161A23] border border-[#2A2F3A] rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-chair text-[#FF784E]"></i>
                    <span>Sơ đồ ghế</span>
                </h2>

                <!-- Screen -->
                <div class="text-center mb-6">
                    <div
                        class="bg-gradient-to-b from-[#1a1a1a] to-[#0a0a0a] rounded-t-[20px] border-2 border-[#FF784E]/40 py-4 px-8 inline-block">
                        <span class="text-[#FF784E] font-bold">MÀN HÌNH</span>
                    </div>
                </div>

                <!-- Seat Grid -->
                <div class="overflow-x-auto mb-6">
                    <div class="inline-block min-w-full">
                        @foreach ($seatsByRow as $rowLabel => $rowSeats)
                            <div class="flex items-center gap-2 mb-2">
                                <div
                                    class="w-8 h-8 flex items-center justify-center text-sm font-bold text-white bg-[#2a2d3a] rounded shrink-0">
                                    {{ $rowLabel }}
                                </div>
                                <div class="flex gap-1 flex-wrap">
                                    @foreach ($rowSeats as $seat)
                                        @php
                                            // SỬA Ở ĐÂY: Dùng $seat->loaiGhe thay vì $seat->seatType
                                            $seatType = $seat->loaiGhe ?? null;

                                            // Kiểm tra tên loại ghế
                                            $isVip =
                                                $seatType &&
                                                strpos(strtolower($seatType->ten_loai ?? ''), 'vip') !== false;

                                            $isBooked = in_array($seat->id, $bookedSeatIds);
                                            $isDisabled = $seat->trang_thai == 0;
                                        @endphp
                                        <div
                                            class="w-8 h-8 flex items-center justify-center text-xs font-semibold rounded
                                        @if ($isBooked) bg-red-600/80 border-2 border-red-700 text-white
                                        @elseif($isDisabled) bg-gray-800/30 border border-dashed border-gray-700 text-gray-600
                                        @elseif($isVip) bg-gradient-to-br from-yellow-600 to-yellow-700 border-2 border-yellow-500 text-yellow-100
                                        @else bg-[#2a2d3a] border border-[#3a3d4a] text-white @endif">
                                            {{ preg_replace('/^[A-Z]/', '', $seat->so_ghe) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap gap-4 justify-center">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-[#2a2d3a] border border-[#3a3d4a] rounded"></div>
                        <span class="text-sm text-[#a6a6b0]">Trống</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div
                            class="w-6 h-6 bg-gradient-to-br from-yellow-600 to-yellow-700 border-2 border-yellow-500 rounded">
                        </div>
                        <span class="text-sm text-[#a6a6b0]">VIP</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-red-600/80 border-2 border-red-700 rounded"></div>
                        <span class="text-sm text-[#a6a6b0]">Đã đặt</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-gray-800/30 border border-dashed border-gray-700 rounded"></div>
                        <span class="text-sm text-[#a6a6b0]">Vô hiệu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
