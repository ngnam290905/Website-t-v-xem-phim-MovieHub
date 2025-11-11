@extends('admin.layout')

@section('title', 'Chi tiết người dùng')

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 bg-[#F53003] rounded-full flex items-center justify-center">
          <span class="text-2xl font-bold text-white">
            {{ strtoupper(substr($user->ho_ten, 0, 1)) }}
          </span>
        </div>
        <div>
          <h2 class="text-2xl font-semibold text-white">{{ $user->ho_ten }}</h2>
          <p class="text-gray-400">{{ $user->email }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
          <i class="fas fa-edit mr-2"></i>Chỉnh sửa
        </a>
        <a href="{{ route('admin.users.index') }}" class="text-gray-300 hover:text-white px-4 py-2 rounded-lg border border-gray-600 hover:border-gray-500 transition-colors duration-300">
          <i class="fas fa-arrow-left mr-2"></i>Quay lại
        </a>
      </div>
    </div>

    <!-- Status Badges -->
    <div class="flex flex-wrap gap-3">
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $user->trang_thai ? 'bg-green-500 bg-opacity-20 text-green-400' : 'bg-red-500 bg-opacity-20 text-red-400' }}">
        <i class="fas fa-circle text-xs mr-2"></i>
        {{ $user->trang_thai ? 'Hoạt động' : 'Đã khóa' }}
      </span>
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-500 bg-opacity-20 text-blue-400">
        <i class="fas fa-user-tag text-xs mr-2"></i>
        {{ optional($user->vaiTro)->ten ?? 'User' }}
      </span>
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-500 bg-opacity-20 text-purple-400">
        <i class="fas fa-crown text-xs mr-2"></i>
        {{ $user->hangThanhVien->ten_hang ?? 'Thành viên mới' }}
      </span>
    </div>
  </div>

  <!-- Information Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Basic Information -->
    <div class="lg:col-span-2 space-y-6">
      <!-- Personal Info -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-user-circle mr-2 text-[#F53003]"></i>
          Thông tin cá nhân
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-400 mb-1">Họ và tên</div>
            <div class="text-white font-medium">{{ $user->ho_ten }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Email</div>
            <div class="text-white font-medium">{{ $user->email }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Số điện thoại</div>
            <div class="text-white font-medium">{{ $user->sdt ?? $user->dien_thoai ?? '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Địa chỉ</div>
            <div class="text-white font-medium">{{ $user->dia_chi ?? '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Vai trò</div>
            <div class="text-white font-medium">{{ optional($user->vaiTro)->ten ?? '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Trạng thái</div>
            <div class="text-white font-medium">{{ $user->trang_thai ? 'Hoạt động' : 'Khóa' }}</div>
          </div>
        </div>
      </div>

      <!-- Activity Stats -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-chart-line mr-2 text-[#F53003]"></i>
          Thống kê hoạt động
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-[#0d0f14] rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-[#F53003] mb-2">{{ $totalOrders }}</div>
            <div class="text-gray-400 text-sm">Tổng đơn</div>
          </div>
          <div class="bg-[#0d0f14] rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-green-400 mb-2">{{ number_format($totalSpent, 0) }}</div>
            <div class="text-gray-400 text-sm">Tổng chi tiêu (VNĐ)</div>
          </div>
          <div class="bg-[#0d0f14] rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-yellow-400 mb-2">
              {{ $user->hangThanhVien->ten_hang
                  ?? ($totalSpent >= 1500000 ? 'Kim cương'
                    : ($totalSpent >= 1000000 ? 'Vàng'
                      : ($totalSpent >= 500000 ? 'Bạc'
                        : ($totalSpent >= 150000 ? 'Đồng' : 'Mới')))) }}
            </div>
            <div class="text-gray-400 text-sm">Hạng</div>
          </div>
          <div class="bg-[#0d0f14] rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-purple-400 mb-2">{{ number_format($user->diemThanhVien->tong_diem ?? (int) floor(($totalSpent ?? 0) / 1000)) }}</div>
            <div class="text-gray-400 text-sm">Điểm</div>
          </div>
        </div>
      </div>

      <!-- Recent Bookings -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-ticket-alt mr-2 text-[#F53003]"></i>
          Lịch sử đặt vé gần đây
        </h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-[#262833]">
                <th class="text-left py-3 text-gray-400">Mã vé</th>
                <th class="text-left py-3 text-gray-400">Phim</th>
                <th class="text-left py-3 text-gray-400">Suất chiếu</th>
                <th class="text-left py-3 text-gray-400">Tổng tiền</th>
                <th class="text-left py-3 text-gray-400">Thanh toán</th>
              </tr>
            </thead>
            <tbody>
              @php
                $recentBookings = \App\Models\DatVe::with(['suatChieu.phim', 'suatChieu.phongChieu'])
                  ->where('id_nguoi_dung', $user->id)
                  ->orderByDesc('created_at')
                  ->limit(5)
                  ->get();
              @endphp
              @if($recentBookings->count() > 0)
                @foreach($recentBookings as $booking)
                  <tr class="border-b border-[#262833] hover:bg-[#0d0f14]">
                    <td class="py-3 text-white">#{{ $booking->id }}</td>
                    <td class="py-3 text-white">{{ optional($booking->suatChieu->phim)->ten_phim ?? '—' }}</td>
                    <td class="py-3 text-gray-300">
                      {{ optional($booking->suatChieu)->ngay_gio_chieu ? \Carbon\Carbon::parse($booking->suatChieu->ngay_gio_chieu)->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td class="py-3 text-white">{{ number_format($booking->tong_tien ?? 0, 0) }} VNĐ</td>
                    <td class="py-3">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $booking->trang_thai ? 'bg-green-500 bg-opacity-20 text-green-400' : 'bg-red-500 bg-opacity-20 text-red-400' }}">
                        {{ $booking->trang_thai ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                      </span>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="5" class="py-8 text-center text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Chưa có lịch sử đặt vé</p>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
        @if($recentBookings->count() > 0)
          <div class="mt-4 text-center">
            <a href="#" class="text-[#F53003] hover:text-[#e02a00] text-sm font-medium">
              Xem tất cả lịch sử <i class="fas fa-arrow-right ml-1"></i>
            </a>
          </div>
        @endif
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
      <!-- Account Info -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-info-circle mr-2 text-[#F53003]"></i>
          Thông tin tài khoản
        </h3>
        <div class="space-y-3">
          <div>
            <div class="text-sm text-gray-400 mb-1">ID người dùng</div>
            <div class="text-white font-mono">#{{ $user->id }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Ngày đăng ký</div>
            <div class="text-white">{{ optional($user->created_at)->format('d/m/Y H:i') ?? '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Lần đăng nhập cuối</div>
            <div class="text-white">{{ $lastActive ? \Carbon\Carbon::parse($lastActive)->format('d/m/Y H:i') : '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-400 mb-1">Cập nhật lần cuối</div>
            <div class="text-white">{{ optional($user->updated_at)->format('d/m/Y H:i') ?? '—' }}</div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <i class="fas fa-bolt mr-2 text-[#F53003]"></i>
          Thao tác nhanh
        </h3>
        <div class="space-y-3">
          <a href="{{ route('admin.users.edit', $user->id) }}" class="w-full bg-[#F53003] hover:bg-[#e02a00] text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300 text-center block">
            <i class="fas fa-edit mr-2"></i>Chỉnh sửa thông tin
          </a>
          @if($user->trang_thai)
            <button class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
              <i class="fas fa-lock mr-2"></i>Khóa tài khoản
            </button>
          @else
            <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
              <i class="fas fa-unlock mr-2"></i>Mở khóa tài khoản
            </button>
          @endif
          <button class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
            <i class="fas fa-trash mr-2"></i>Xóa tài khoản
          </button>
          <button class="w-full bg-[#262833] hover:bg-[#2a2d3a] text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
            <i class="fas fa-redo mr-2"></i>Đặt lại mật khẩu
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
