@extends('admin.layout')

@section('title', 'Người dùng đã xóa - Admin')

@section('content')
  <div class="space-y-6">
    <div class="bg-[#151822] border border-[#262833] rounded-xl">
      <div class="px-5 py-4 border-b border-[#262833] flex items-center justify-between">
        <h2 class="font-semibold">Người dùng đã xóa</h2>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-[#F53003] hover:underline">← Quay lại danh sách</a>
      </div>
      <div class="p-5">
        @if (session('success'))
          <div class="mb-4 text-sm text-green-400">{{ session('success') }}</div>
        @endif
        <table class="w-full text-sm text-left text-[#a6a6b0]">
          <thead class="border-b border-[#262833]">
            <tr>
              <th class="py-3">ID</th>
              <th>Họ tên</th>
              <th>Email</th>
              <th>Vai trò</th>
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
                <td>
                  <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" onsubmit="return confirm('Khôi phục tài khoản này?');">
                    @csrf
                    <button type="submit" class="text-green-400 hover:underline">Khôi phục</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="py-3 text-center">Không có tài khoản nào trong thùng rác</td></tr>
            @endforelse
          </tbody>
        </table>
        {{ $users->links() }}
      </div>
    </div>
  </div>
@endsection
