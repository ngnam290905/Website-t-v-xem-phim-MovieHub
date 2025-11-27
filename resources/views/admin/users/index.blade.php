@extends('admin.layout')

@section('title', 'Danh sách người dùng - Admin')

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
=======
<div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-xl font-semibold">Danh sách người dùng</h2>
        <div class="flex gap-3 w-full sm:w-auto">
          <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 sm:flex-initial">
              <div class="relative flex items-stretch">
                  <!-- Ô nhập tìm kiếm -->
                  <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Tìm theo tên, email..."
                        class="flex-1 min-w-0 bg-[#0f0f12] border border-[#2f3240] rounded-l px-3 py-2 text-sm pr-10 focus:outline-none focus:border-[#F53003]">

                  <!-- Dấu X để xóa (nằm trong ô nhập, bên phải) -->
                  @if(request('search'))
                      <a href="{{ route('admin.users.index') }}"
                        class="flex items-center px-2 text-gray-400 hover:text-white transition"
                        title="Xóa tìm kiếm">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                      </a>
                  @else
                      <!-- Khoảng trống để căn đều khi không có X -->
                      <div class="w-9"></div>
                  @endif

                  <!-- Nút Tìm kiếm -->
                  <button type="submit"
                          class="px-4 bg-[#F53003] hover:opacity-90 rounded-r text-sm font-medium transition whitespace-nowrap flex items-center">
                      Tìm kiếm
                  </button>
              </div>
          </form>

            <a href="{{ route('admin.users.create') }}"
              class="px-4 py-2 bg-[#F53003] hover:opacity-90 rounded text-sm font-medium transition whitespace-nowrap">
                + Thêm người dùng
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-900 bg-opacity-50 border border-green-700 text-green-300 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#2f3240] text-left text-gray-400">
                    <th class="pb-3 pr-4">ID</th>
                    <th class="pb-3 pr-4">Họ tên</th>
                    <th class="pb-3 pr-4">Email</th>
                    <th class="pb-3 pr-4">Vai trò</th>
                    <th class="pb-3 pr-4 text-center">Thành viên</th>
                    <th class="pb-3 pr-4 text-center">Tổng chi</th>
                    <th class="pb-3 pr-4 text-center">Trạng thái</th>
                    <th class="pb-3 pr-4 text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b border-[#262833] hover:bg-[#1a1d2a] transition">
                        <td class="py-3 pr-4">#{{ $user->id }}</td>
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-2">
                                @if ($user->hinh_anh)
                                    <img src="{{ asset('storage/' . $user->hinh_anh) }}"
                                         alt="{{ $user->ho_ten }}"
                                         class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#F53003] to-[#ff6b3d] flex items-center justify-center text-xs text-white font-bold">
                                        {{ strtoupper(substr($user->ho_ten, 0, 2)) }}
                                    </div>
                                @endif
                                <span class="font-medium">{{ $user->ho_ten }}</span>
                            </div>
                        </td>
                        <td class="py-3 pr-4 text-gray-300">{{ $user->email }}</td>
                        <td class="py-3 pr-4">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @if($user->vaiTro->ten == 'admin') bg-red-900 text-red-300
                                @elseif($user->vaiTro->ten == 'staff') bg-blue-900 text-blue-300
                                @else bg-gray-700 text-gray-300 @endif">
                                {{ ucfirst($user->vaiTro->ten) }}
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-center">
                            <div class="text-xs leading-tight">
                                <div class="font-medium text-yellow-400">
                                    {{ $user->diemThanhVien?->tong_diem ?? 0 }} điểm
                                </div>
                                <div class="text-gray-400">
                                    {{ $user->hangThanhVien?->ten_hang ?? 'Thường' }}
                                </div>
                            </div>
                        </td>
                        <td class="py-3 pr-4 text-center font-medium text-green-400">
                            {{ number_format($user->tong_chi_tieu) }}đ
                        </td>
                        <td class="py-3 pr-4 text-center">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                {{ $user->trang_thai ? 'bg-green-900 text-green-300' : 'bg-gray-800 text-gray-500' }}">
                                {{ $user->trang_thai ? 'Hoạt động' : 'Khóa' }}
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-center">
                            <div class="flex items-center justify-center gap-2 text-xs">
                                <a href="{{ route('admin.users.show', $user->id) }}"
                                   class="text-blue-400 hover:text-blue-300" title="Xem">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="text-yellow-400 hover:text-yellow-300" title="Sửa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                      class="inline" onsubmit="return confirm('Xóa người dùng này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">
                            Không có người dùng nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <div class="mt-6">
        {{ $users->appends(request()->query())->links('pagination::tailwind') }}
    </div>

    <!-- Nút xem thùng rác -->
    <div class="mt-4 text-right">
        <a href="{{ route('admin.users.trash') }}"
           class="text-xs text-gray-400 hover:text-white underline">
            Xem thùng rác ({{ \App\Models\NguoiDung::onlyTrashed()->count() }})
        </a>
    </div>
</div>
@endsection