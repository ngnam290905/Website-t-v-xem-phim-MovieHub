@extends('layouts.user')

@section('title', 'Lịch sử đặt vé')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Lịch sử đặt vé</h1>
                <p class="text-gray-400 mt-1">Xem tất cả các vé bạn đã đặt</p>
            </div>
            <a href="{{ route('user.profile') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại tài khoản
            </a>
        </div>
    </div>

    <!-- Bookings List -->
    @if($bookings->count() > 0)
        <div class="space-y-4">
            @foreach($bookings as $booking)
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-gray-600 transition-colors">
                    <div class="flex items-start justify-between">
                        <!-- Movie Info -->
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-film text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">{{ $booking->suatChieu->phim->ten_phim }}</h3>
                                    <p class="text-gray-400 text-sm">
                                        {{ $booking->suatChieu->phongChieu->ten_phong }} • 
                                        {{ \Carbon\Carbon::parse($booking->suatChieu->thoi_gian_bat_dau)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Booking Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Mã đặt vé:</p>
                                    <p class="text-white font-medium">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Ngày đặt:</p>
                                    <p class="text-white">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Số lượng ghế:</p>
                                    <p class="text-white">{{ $booking->chiTietDatVe->count() }} ghế</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Tổng tiền:</p>
                                    <p class="text-white font-medium">{{ number_format($booking->tong_tien) }}đ</p>
                                </div>
                            </div>

                            <!-- Seats -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-400 mb-2">Ghế đã đặt:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($booking->chiTietDatVe as $detail)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-700 text-gray-300">
                                            {{ $detail->ghe->id_loai }}
                                            @if($detail->ghe->loaiGhe)
                                                <span class="ml-1 text-gray-500">({{ $detail->ghe->loaiGhe->ten_loai }})</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Combos -->
                            @if($booking->chiTietCombo->count() > 0)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-400 mb-2">Combo:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($booking->chiTietCombo as $combo)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-600/20 text-yellow-300">
                                                <i class="fas fa-popcorn mr-1"></i>
                                                {{ $combo->combo->ten }} × {{ $combo->so_luong }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Status and Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-700">
                                <div>
                                    @switch($booking->trang_thai)
                                        @case(0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-300">
                                                <i class="fas fa-clock mr-1"></i>Chờ xác nhận
                                            </span>
                                        @break
                                        @case(1)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300">
                                                <i class="fas fa-check-circle mr-1"></i>Đã xác nhận
                                            </span>
                                        @break
                                        @case(2)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-300">
                                                <i class="fas fa-times-circle mr-1"></i>Đã hủy
                                            </span>
                                        @break
                                        @case(3)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-500/20 text-orange-300">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Yêu cầu hủy
                                            </span>
                                        @break
                                    @endswitch
                                </div>

                                <div class="flex items-center space-x-2">
                                    @if(in_array($booking->trang_thai, [0, 1]) && $booking->suatChieu->thoi_gian_bat_dau > now()->addHours(2))
                                        <form action="{{ route('user.cancel-booking', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy vé này?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors">
                                                <i class="fas fa-times mr-1"></i>Hủy vé
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($booking->trang_thai == 1)
                                        <button class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors">
                                            <i class="fas fa-download mr-1"></i>Tải vé
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-400">
                Hiển thị {{ $bookings->firstItem() }} - {{ $bookings->lastItem() }} của {{ $bookings->total() }} kết quả
            </div>
            {{ $bookings->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-gray-800 rounded-xl p-12 border border-gray-700 text-center">
            <i class="fas fa-ticket-alt text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold text-white mb-2">Chưa có vé nào</h3>
            <p class="text-gray-400 mb-6">Bạn chưa đặt vé nào. Hãy khám phá các phim đang chiếu và đặt vé ngay!</p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                <i class="fas fa-film mr-2"></i>Khám phá phim
            </a>
        </div>
    @endif
</div>
@endsection
