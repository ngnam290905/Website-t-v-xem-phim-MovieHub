@extends('admin.layout')

@section('title', 'Sửa người dùng - Admin')

@section('content')
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-5 max-w-lg mx-auto">
    @if (session('success'))
      <div class="mb-4 text-sm text-green-400">{{ session('success') }}</div>
    @endif
    <h2 class="font-semibold mb-4">Sửa tài khoản</h2>
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="space-y-4 text-sm">
        <div>
          <label class="block mb-1">Họ tên</label>
          <input type="text" name="ho_ten" value="{{ old('ho_ten', $user->ho_ten) }}" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2" required>
          @error('ho_ten') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2" required>
          @error('email') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block mb-1">Mật khẩu (để trống nếu không đổi)</label>
          <input type="password" name="mat_khau" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
          @error('mat_khau') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block mb-1">Xác nhận mật khẩu</label>
          <input type="password" name="mat_khau_confirmation" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
          @error('mat_khau_confirmation') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block mb-1">Điện thoại</label>
          <input type="text" name="sdt" value="{{ old('sdt', $user->sdt) }}" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
          @error('sdt') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block mb-1">Địa chỉ</label>
          <input type="text" name="dia_chi" value="{{ old('dia_chi', $user->dia_chi) }}" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
          @error('dia_chi') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block mb-1">Vai trò</label>
          <select name="id_vai_tro" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2" required>
            @foreach ($roles as $role)
              <option value="{{ $role->id }}" {{ old('id_vai_tro', $user->id_vai_tro) == $role->id ? 'selected' : '' }}>{{ $role->ten }}</option>
            @endforeach
          </select>
          @error('id_vai_tro') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="trang_thai" id="trang_thai" value="1" {{ old('trang_thai', $user->trang_thai) ? 'checked' : '' }}>
          <label for="trang_thai">Hoạt động</label>
          @error('trang_thai') <p class="text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="mt-4 inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#F53003] hover:opacity-90 transition">Cập nhật</button>
      </div>
    </form>
  </div>
@endsection