@extends('layouts.user')

@section('title', 'Thông tin tài khoản')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Thông tin tài khoản</h1>
                <p class="text-[#a6a6b0] mt-1">Quản lý thông tin cá nhân và lịch sử đặt vé</p>
            </div>
            <a href="{{ route('user.edit') }}" class="inline-flex items-center px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] text-white rounded-lg transition">
                <i class="fas fa-edit mr-2"></i>Chỉnh sửa hồ sơ
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
                <div class="text-center">
                    <div class="w-20 h-20 bg-[#F53003] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl font-bold">{{ strtoupper(substr($userData['ho_ten'], 0, 1)) }}</span>
                    </div>
                    <h2 class="text-xl font-semibold text-white mb-2">{{ $userData['ho_ten'] }}</h2>
                    <p class="text-[#a6a6b0] mb-4">{{ $userData['email'] }}</p>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[#a6a6b0]">Số điện thoại:</span>
                            <span class="text-white">{{ $userData['so_dien_thoai'] ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#a6a6b0]">Ngày sinh:</span>
                            <span class="text-white">{{ $userData['ngay_sinh'] ? \Carbon\Carbon::parse($userData['ngay_sinh'])->format('d/m/Y') : 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#a6a6b0]">Giới tính:</span>
                            <span class="text-white">{{ $userData['gioi_tinh'] ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#a6a6b0]">Ngày tham gia:</span>
                            <span class="text-white">{{ \Carbon\Carbon::parse($userData['created_at'])->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Tier & Loyalty Points -->
            <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 mt-6 space-y-5">
                <div>
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-yellow-600 to-orange-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-crown text-white"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-white">Hạng thành viên</h3>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-white">
                                    {{ ($memberTier['ten_hang'] ?? null) ?: ($computedTier ?? '—') }}
                                </div>
                                @if(!empty($memberTier['ngay_cap_nhat_hang']))
                                  <div class="text-xs text-gray-500 mt-1">Cập nhật: {{ \Carbon\Carbon::parse($memberTier['ngay_cap_nhat_hang'])->format('d/m/Y') }}</div>
                                @endif
                            </div>
                </div>

                <div>
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-yellow-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-star text-white"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-white">Điểm tích lũy</h3>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-yellow-400 mb-1">{{ number_format($loyaltyPoints['tong_diem'] ?? 0) }}</div>
                                <p class="text-[#a6a6b0] text-sm mb-2">điểm</p>
                                @if(!empty($loyaltyPoints['ngay_het_han']))
                                  <div class="text-xs text-gray-500">
                                      Hết hạn: {{ \Carbon\Carbon::parse($loyaltyPoints['ngay_het_han'])->format('d/m/Y') }}
                                  </div>
                                @else
                                  <div class="text-xs text-gray-500">Chưa có điểm</div>
                                @endif
                            </div>
                </div>
            </div>
        </div>

        <!-- Stats and Recent Bookings -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-ticket-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[#a6a6b0] text-sm">Tổng vé đã đặt</p>
                            <p class="text-2xl font-bold text-white">{{ $totalBookings }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[#a6a6b0] text-sm">Vé đã xác nhận</p>
                            <p class="text-2xl font-bold text-white">{{ $confirmedBookings }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-wallet text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[#a6a6b0] text-sm">Tổng chi tiêu</p>
                            <p class="text-2xl font-bold text-white">{{ number_format($totalSpent, 0) }}đ</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-history text-white"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Lịch sử đặt vé gần đây</h3>
                    </div>
                    <a href="{{ route('user.bookings') }}" class="text-[#F53003] hover:text-[#e02a00] text-sm">
                        Xem tất cả <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                @if($recentBookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentBookings as $booking)
                            <div class="bg-[#222533] rounded-lg p-4 hover:bg-[#2a2d3a] transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <div class="w-8 h-8 bg-[#F53003] rounded flex items-center justify-center mr-3">
                                                <i class="fas fa-film text-white text-xs"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-white font-medium">{{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}</h4>
                                                <p class="text-[#a6a6b0] text-sm">
                                                    {{ $booking->suatChieu->phongChieu->ten_phong ?? 'N/A' }} • 
                                                    {{ isset($booking->suatChieu->thoi_gian_bat_dau) ? \Carbon\Carbon::parse($booking->suatChieu->thoi_gian_bat_dau)->format('d/m/Y H:i') : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-[#a6a6b0]">
                                                {{ isset($booking->chiTietDatVe) ? $booking->chiTietDatVe->count() : 0 }} ghế • 
                                                {{ number_format($booking->tong_tien ?? 0) }}đ
                                            </span>
                                            @switch($booking->trang_thai)
                                                @case(0)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-300">
                                                        Chờ xác nhận
                                                    </span>
                                                @break
                                                @case(1)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300">
                                                        Đã xác nhận
                                                    </span>
                                                @break
                                                @case(2)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-300">
                                                        Đã hủy
                                                    </span>
                                                @break
                                                @case(3)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-500/20 text-orange-300">
                                                        Yêu cầu hủy
                                                    </span>
                                                @break
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-ticket-alt text-4xl text-[#a6a6b0] mb-3"></i>
                        <p class="text-[#a6a6b0]">Bạn chưa có vé nào</p>
                        <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] text-white rounded-lg mt-4 transition">
                            <i class="fas fa-film mr-2"></i>Đặt vé ngay
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
