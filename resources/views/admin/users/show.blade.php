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
            <img src="{{ $movie->poster_url ?? $movie->poster ?? asset('images/no-poster.svg') }}" alt="{{ $movie->ten_phim ?? 'Movie' }}" class="w-16 h-24 object-cover rounded hidden sm:block" onerror="this.src='{{ asset('images/no-poster.svg') }}'">
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
