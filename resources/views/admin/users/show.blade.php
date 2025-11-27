@extends('admin.layout')

@section('title', 'Chi tiết người dùng')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-semibold">Hồ sơ người dùng</h2>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-300 hover:underline">← Quay lại</a>
      <a href="{{ route('admin.users.show', $user->id) }}" class="px-3 py-1 text-xs rounded bg-blue-600/20 text-blue-300 hover:bg-blue-600/30">Xem</a>
      <a href="{{ route('admin.users.edit', $user->id) }}" class="px-3 py-1 text-xs rounded bg-yellow-600/20 text-yellow-300 hover:bg-yellow-600/30">Sửa</a>
      @if (Route::has('admin.users.destroy'))
        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Xóa người dùng #{{ $user->id }}?');" class="inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="px-3 py-1 text-xs rounded bg-red-600/20 text-red-300 hover:bg-red-600/30">Xóa</button>
        </form>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile / Contact -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 space-y-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-[#F53003] flex items-center justify-center">
          <span class="text-white text-xl font-bold">{{ strtoupper(substr($user->ho_ten,0,1)) }}</span>
        </div>
        <div>
          <div class="text-lg font-semibold text-white">{{ $user->ho_ten }}</div>
          <div class="text-xs text-gray-400">ID: #{{ $user->id }}</div>
        </div>
      </div>
      <div class="grid grid-cols-1 gap-3 text-sm">
        <div>
          <div class="text-gray-400">Email</div>
          <div class="text-white">{{ $user->email }}</div>
        </div>
        <div>
          <div class="text-gray-400">Số điện thoại</div>
          <div class="text-white">{{ $user->sdt ?? $user->dien_thoai ?? '—' }}</div>
        </div>
        <div class="flex items-center gap-2">
          <div>
            <div class="text-gray-400">Vai trò</div>
            <div class="text-white">
              <span class="px-2 py-1 rounded-full text-xs {{ optional($user->vaiTro)->ten === 'admin' ? 'bg-red-900/30 text-red-300' : 'bg-blue-900/30 text-blue-300' }}">{{ optional($user->vaiTro)->ten ?? '—' }}</span>
            </div>
          </div>
          <div>
            <div class="text-gray-400">Trạng thái</div>
            <div class="text-white">
              <span class="px-2 py-1 rounded-full text-xs {{ $user->trang_thai ? 'bg-green-900/30 text-green-300' : 'bg-gray-700 text-gray-300' }}">{{ $user->trang_thai ? 'Hoạt động' : 'Khóa' }}</span>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <div class="text-gray-400">Ngày đăng ký</div>
            <div class="text-white">{{ optional($user->created_at)->format('d/m/Y H:i') ?? '—' }}</div>
          </div>
          <div>
            <div class="text-gray-400">Lần hoạt động cuối</div>
            <div class="text-white">{{ $user->last_active ? \Carbon\Carbon::parse($user->last_active)->format('d/m/Y H:i') : '—' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Membership / Points -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 space-y-4">
      @php
        $officialTier = optional($user->hangThanhVien)->ten_hang;
        $officialPoints = optional($user->diemThanhVien)->tong_diem;
        $spent = (float)($user->total_spent ?? 0);
        $computedTier = null;
        if ($spent >= 1500000) $computedTier = 'Kim cương';
        elseif ($spent >= 1000000) $computedTier = 'Vàng';
        elseif ($spent >= 500000) $computedTier = 'Bạc';
        elseif ($spent >= 150000) $computedTier = 'Đồng';
        $computedPoints = (int) floor($spent / 1000);
      @endphp
      <div class="text-gray-400 text-sm">Hạng thành viên</div>
      <div class="text-white font-semibold text-lg">
        {{ $officialTier ?? ($computedTier ?? '—') }}
        @if(!$officialTier && $computedTier)
          <span class="ml-2 px-2 py-0.5 text-xs rounded bg-yellow-900/30 text-yellow-300">tạm tính</span>
        @endif
      </div>
      <div class="text-gray-400 text-sm">Điểm tích lũy</div>
      <div class="text-white font-semibold">
        {{ number_format(($officialPoints ?? $computedPoints), 0) }}
        @if(is_null($officialPoints))
          <span class="ml-2 px-2 py-0.5 text-xs rounded bg-yellow-900/30 text-yellow-300">tạm tính</span>
        @endif
      </div>
      <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
          <div class="text-gray-400">Tổng đơn</div>
          <div class="text-white font-semibold">{{ (int)($user->total_orders ?? 0) }}</div>
        </div>
        <div>
          <div class="text-gray-400">Tổng chi tiêu</div>
          <div class="text-white font-semibold">{{ number_format((float)($user->total_spent ?? 0), 0) }} VNĐ</div>
        </div>
      </div>
    </div>

  </div>

  <!-- Recent Bookings (Full width below) -->
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold">Đặt vé gần đây</h3>
      <a href="{{ route('admin.bookings.index', ['nguoi_dung' => $user->ho_ten]) }}" class="text-xs text-[#F53003] hover:underline">Xem tất cả</a>
    </div>
    @php
      $recent = $user->datVe()
        ->with(['suatChieu.phim','suatChieu.phongChieu','chiTietDatVe.ghe'])
        ->orderBy('created_at','desc')->take(5)->get();
    @endphp
    @if($recent->isEmpty())
      <div class="text-sm text-gray-400">Chưa có đặt vé</div>
    @else
      <div class="space-y-3">
        @foreach($recent as $b)
          @php
            $movie = optional($b->suatChieu->phim);
            $room  = optional($b->suatChieu->phongChieu);
            $time  = optional($b->suatChieu->thoi_gian_bat_dau);
            $seats = $b->chiTietDatVe->map(function($ct){ return optional($ct->ghe)->so_ghe; })->filter()->implode(', ');
          @endphp
          <div class="flex items-center gap-4 bg-[#1a1d29] border border-[#262833] rounded-lg p-3">
            <img src="{{ $movie->poster ?? asset('images/default-poster.jpg') }}" alt="{{ $movie->ten_phim ?? 'Movie' }}" class="w-16 h-24 object-cover rounded hidden sm:block">
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <div class="text-white font-semibold">#{{ $b->id }} • {{ $movie->ten_phim ?? 'N/A' }}</div>
                <div class="text-[#F53003] font-bold">{{ number_format($b->tong_tien ?? 0,0) }}đ</div>
              </div>
              <div class="text-gray-400 text-xs mt-1">{{ $time ? $time->format('d/m/Y H:i') : 'N/A' }} • {{ $room->ten_phong ?? 'N/A' }}</div>
              <div class="text-gray-300 text-xs mt-1">Ghế: {{ $seats ?: 'N/A' }}</div>
            </div>
            <div class="flex items-center gap-2">
              @if (Route::has('admin.bookings.destroy'))
                <form method="POST" action="{{ route('admin.bookings.destroy', $b->id) }}" onsubmit="return confirm('Xóa đơn #{{ $b->id }}?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-300 rounded hover:bg-red-600/30">Xóa</button>
                </form>
              @else
                <a href="{{ route('admin.bookings.show', $b->id) }}" class="px-2 py-1 text-xs bg-blue-600/20 text-blue-300 rounded hover:bg-blue-600/30">Xem</a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
@endsection
=======
@section('title', 'Chi tiết người dùng - Admin')

@section('content')
<div class="bg-[#151822] border border-[#262833] rounded-xl p-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Chi tiết tài khoản #{{ $user->id }}</h2>
        <a href="{{ route('admin.users.index') }}"
           class="text-gray-400 hover:text-white underline text-sm">
            ← Quay lại danh sách
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-900 bg-opacity-50 border border-green-700 text-green-300 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Thông tin cơ bản -->
        <div class="space-y-4">
            <div class="bg-[#1a1d2a] p-4 rounded-lg border border-[#2f3240]">
                <h3 class="text-lg font-semibold text-yellow-400 mb-3">Thông tin cá nhân</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Họ tên:</span>
                        <span class="font-medium">{{ $user->ho_ten }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Email:</span>
                        <span class="font-medium">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Số điện thoại:</span>
                        <span class="font-medium">{{ $user->sdt ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Địa chỉ:</span>
                        <span class="font-medium">{{ $user->dia_chi ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Vai trò:</span>
                        <span class="px-2 py-1 rounded text-xs font-medium
                            @if($user->vaiTro->ten == 'admin') bg-red-900 text-red-300
                            @elseif($user->vaiTro->ten == 'staff') bg-blue-900 text-blue-300
                            @else bg-gray-700 text-gray-300 @endif">
                            {{ ucfirst($user->vaiTro->ten) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Trạng thái:</span>
                        <span class="px-2 py-1 rounded text-xs font-medium
                            {{ $user->trang_thai ? 'bg-green-900 text-green-300' : 'bg-gray-800 text-gray-500' }}">
                            {{ $user->trang_thai ? 'Hoạt động' : 'Bị khóa' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ngày tạo:</span>
                        <span class="font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Thành viên VIP -->
            <div class="bg-[#1a1d2a] p-4 rounded-lg border border-[#2f3240]">
                <h3 class="text-lg font-semibold text-yellow-400 mb-3">Thành viên </h3>
                <div class="grid grid-cols-1 gap-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Tổng chi tiêu:</span>
                        <span class="text-xl font-bold text-green-400">
                            {{ number_format($user->tong_chi_tieu) }}đ
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Điểm tích lũy:</span>
                        <span class="text-xl font-bold text-yellow-400">
                            {{ $user->diemThanhVien?->tong_diem ?? 0 }} điểm
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Hạng thành viên:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-bold
                            @if(!$user->hangThanhVien) bg-gray-700 text-gray-300
                            @elseif(str_contains(strtolower($user->hangThanhVien->ten_hang), 'vip')) bg-purple-900 text-purple-300
                            @elseif(str_contains(strtolower($user->hangThanhVien->ten_hang), 'gold')) bg-yellow-900 text-yellow-300
                            @elseif(str_contains(strtolower($user->hangThanhVien->ten_hang), 'silver')) bg-gray-600 text-gray-200
                            @else bg-gray-700 text-gray-300 @endif">
                            {{ $user->hangThanhVien?->ten_hang ?? 'Thường' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avatar + Hành động -->
        <div class="flex flex-col items-center justify-center space-y-4">
            <div class="relative">
                @if ($user->hinh_anh)
                    <img src="{{ asset('storage/' . $user->hinh_anh) }}"
                         alt="{{ $user->ho_ten }}"
                         class="w-32 h-32 rounded-full object-cover border-4 border-[#2f3240] shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-[#F53003] to-[#ff6b3d] flex items-center justify-center text-3xl text-white font-bold shadow-lg">
                        {{ strtoupper(substr($user->ho_ten, 0, 2)) }}
                    </div>
                @endif
            </div>

            <div class="flex gap-3 mt-4">
                <a href="{{ route('admin.users.edit', $user->id) }}"
                   class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 rounded text-sm font-medium transition">
                    Sửa thông tin
                </a>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                      onsubmit="return confirm('Xóa tài khoản này? Dữ liệu sẽ vào thùng rác.')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-sm font-medium transition">
                        Xóa tài khoản
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
