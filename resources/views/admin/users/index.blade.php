@extends('admin.layout')

@section('title', 'Quản lý người dùng - Admin')

@section('content')
  <div class="space-y-6">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Tổng người dùng</div>
        <div class="text-2xl font-bold text-white mt-1">{{ $totalUsers ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Hoạt động 30 ngày</div>
        <div class="text-2xl font-bold text-green-400 mt-1">{{ $active30Days ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Đồng</div>
        <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $tierDong ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Bạc</div>
        <div class="text-2xl font-bold text-gray-200 mt-1">{{ $tierBac ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Vàng / Kim cương</div>
        <div class="text-2xl font-bold text-amber-300 mt-1">{{ ($tierVang ?? 0) + ($tierKimCuong ?? 0) }}</div>
      </div>
    </div>
    <div class="bg-[#151822] border border-[#262833] rounded-xl">
      <div class="px-5 py-4 border-b border-[#262833] flex items-center justify-between">
        <h2 class="font-semibold">Danh sách người dùng</h2>
        <div class="flex items-center gap-4">
          <a href="{{ route('admin.users.trash') }}" class="text-sm text-gray-400 hover:underline">Thùng rác</a>
          <a href="{{ route('admin.users.create') }}" class="text-sm text-[#F53003] hover:underline">Tạo mới</a>
        </div>
      </div>
      <div class="p-5">
        @if (session('success'))
          <div class="mb-4 text-sm text-green-400">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
          <div class="mb-4 text-sm text-red-500">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <table class="w-full text-sm text-left text-[#a6a6b0]">
          <thead class="border-b border-[#262833]">
            <tr>
              <th class="py-3">ID</th>
              <th>Họ tên</th>
              <th>Email</th>
              <th>Số ĐT</th>
              <th>Ngày đăng ký</th>
              <th>Lần hoạt động cuối</th>
              <th>Tổng đơn</th>
              <th>Tổng chi tiêu</th>
              <th>Hạng</th>
              <th>Điểm</th>
              <th>Vai trò</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $user)
              <tr class="border-b border-[#262833]">
                <td class="py-3">{{ $user->id }}</td>
                <td><a href="{{ route('admin.users.show', $user->id) }}" class="text-[#F53003] hover:underline">{{ $user->ho_ten }}</a></td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->sdt ?? $user->dien_thoai ?? '—' }}</td>
                <td>{{ optional($user->created_at)->format('d/m/Y H:i') ?? '—' }}</td>
                <td>{{ $user->last_active ? \Carbon\Carbon::parse($user->last_active)->format('d/m/Y H:i') : '—' }}</td>
                <td>{{ (int)($user->total_orders ?? 0) }}</td>
                <td>{{ number_format($user->total_spent ?? 0, 0) }} VNĐ</td>
                <td>
                  {{ $user->hangThanhVien->ten_hang
                      ?? ($user->total_spent >= 1500000 ? 'Kim cương'
                        : ($user->total_spent >= 1000000 ? 'Vàng'
                          : ($user->total_spent >= 500000 ? 'Bạc'
                            : ($user->total_spent >= 150000 ? 'Đồng' : '—')))) }}
                </td>
                <td>
                  @php
                    $points = $user->diemThanhVien->tong_diem ?? (int) floor(($user->total_spent ?? 0) / 1000);
                  @endphp
                  {{ number_format($points) }}
                </td>
                <td>{{ optional($user->vaiTro)->ten ?? 'Không có' }}</td>
                <td>{{ $user->trang_thai ? 'Hoạt động' : 'Khóa' }}</td>
                <td class="py-3">
                  <div class="flex items-center gap-2">
                    <a href="{{ route('admin.users.show', $user->id) }}" 
                       class="btn-table-action btn-table-view"
                       title="Xem chi tiết">
                      <i class="fas fa-eye text-xs"></i>
                    </a>
                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                       class="btn-table-action btn-table-edit"
                       title="Chỉnh sửa">
                      <i class="fas fa-edit text-xs"></i>
                    </a>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" 
                              class="btn-table-action btn-table-delete"
                              title="Xóa">
                        <i class="fas fa-trash text-xs"></i>
                      </button>
                    </form>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="13" class="py-3 text-center">Chưa có dữ liệu</td></tr>
            @endforelse
          </tbody>
        </table>
        {{ $users->links('pagination.custom') }}
      </div>
    </div>
  </div>
@endsection