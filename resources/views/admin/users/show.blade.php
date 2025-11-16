@extends('admin.layout')

@section('title', 'Chi tiết người dùng')

@section('content')
<div class="space-y-6">
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold">Hồ sơ: {{ $user->ho_ten }}</h2>
      <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-300 hover:underline">← Quay lại</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <div class="text-sm text-gray-400">Email</div>
        <div>{{ $user->email }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Số điện thoại</div>
        <div>{{ $user->sdt ?? $user->dien_thoai ?? '—' }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Vai trò</div>
        <div>{{ optional($user->vaiTro)->ten ?? '—' }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Trạng thái</div>
        <div>{{ $user->trang_thai ? 'Hoạt động' : 'Khóa' }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Ngày đăng ký</div>
        <div>{{ optional($user->created_at)->format('d/m/Y H:i') ?? '—' }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Lần đăng nhập cuối</div>
        <div>{{ $lastActive ? \Carbon\Carbon::parse($lastActive)->format('d/m/Y H:i') : '—' }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Hạng thành viên</div>
        <div>{{ $user->hangThanhVien->ten_hang ?? '—' }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Điểm tích lũy</div>
        <div>{{ number_format($user->diemThanhVien->tong_diem ?? 0) }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Tổng đơn hàng</div>
        <div>{{ $totalOrders }}</div>
      </div>
      <div>
        <div class="text-sm text-gray-400">Tổng chi tiêu</div>
        <div>{{ number_format($totalSpent, 0) }} VNĐ</div>
      </div>
    </div>
  </div>
</div>
@endsection
