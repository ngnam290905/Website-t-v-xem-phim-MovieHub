@extends('admin.layout')

@section('title', 'Quản lý người dùng - Admin')

@section('content')
  <div class="space-y-6">
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
              <th>Vai trò</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $user)
              <tr class="border-b border-[#262833]">
                <td class="py-3">{{ $user->id }}</td>
                <td>{{ $user->ho_ten }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ optional($user->vaiTro)->ten ?? 'Không có' }}</td>
                <td>{{ $user->trang_thai ? 'Hoạt động' : 'Khóa' }}</td>
                <td class="flex gap-2">
                  <a href="{{ route('admin.users.edit', $user->id) }}" class="text-[#F53003] hover:underline">Sửa</a>
                  <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:underline">Xóa</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="py-3 text-center">Chưa có dữ liệu</td></tr>
            @endforelse
          </tbody>
        </table>
        {{ $users->links() }}
      </div>
    </div>
  </div>
@endsection