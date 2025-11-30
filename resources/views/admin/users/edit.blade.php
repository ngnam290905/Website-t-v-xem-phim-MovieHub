@extends('admin.layout')

@section('title', 'Sửa người dùng - Admin')

@section('content')
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-5 max-w-2xl mx-auto">
    @if (session('success'))
      <div class="mb-4 text-sm text-green-400">{{ session('success') }}</div>
    @endif

    <h2 class="font-semibold mb-4 text-lg">Sửa tài khoản #{{ $user->id }}</h2>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

        <!-- Thông tin cơ bản -->
        <div>
          <label class="block mb-1">Họ tên</label>
          <input type="text" name="ho_ten" value="{{ old('ho_ten', $user->ho_ten) }}"
                 class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2" required>
          @error('ho_ten') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}"
                 class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2" required>
          @error('email') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block mb-1">Mật khẩu (để trống nếu không đổi)</label>
          <input type="password" name="mat_khau"
                 class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
          @error('mat_khau') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block mb-1">Xác nhận mật khẩu</label>
          <input type="password" name="mat_khau_confirmation"
                 class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
        </div>

        <div>
          <label class="block mb-1">Điện thoại</label>
          <input type="text" name="sdt" value="{{ old('sdt', $user->sdt) }}"
                 class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
          @error('sdt') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block mb-1">Địa chỉ</label>
          <input type="text" name="dia_chi" value="{{ old('dia_chi', $user->dia_chi) }}"
                 class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
          @error('dia_chi') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block mb-1">Vai trò</label>
          <select name="id_vai_tro" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2" required>
            @foreach ($roles as $role)
              <option value="{{ $role->id }}" {{ old('id_vai_tro', $user->id_vai_tro) == $role->id ? 'selected' : '' }}>
                {{ $role->ten }}
              </option>
            @endforeach
          </select>
          @error('id_vai_tro') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" name="trang_thai" id="trang_thai" value="1"
                 {{ old('trang_thai', $user->trang_thai) ? 'checked' : '' }}>
          <label for="trang_thai">Tài khoản hoạt động</label>
        </div>

        <!-- Thành viên VIP -->
        <div class="md:col-span-2 border-t border-[#2f3240] pt-4 mt-2">
          <h3 class="font-medium mb-3 text-yellow-400">Thông tin thành viên</h3>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block mb-1">Tổng chi tiêu (VNĐ)</label>
              <input type="number" step="0.01" name="tong_chi_tieu"
                     value="{{ old('tong_chi_tieu', $user->tong_chi_tieu) }}"
                     class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
              @error('tong_chi_tieu') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block mb-1">Điểm thành viên</label>
              <input type="number" name="tong_diem"
                     value="{{ old('tong_diem', $user->diemThanhVien?->tong_diem) }}"
                     class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
              @error('tong_diem') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
            </div>

            <!-- Thay thế phần "Hạng thành viên" -->
            <div>
                <label class="block mb-1">Hạng thành viên</label>
                <select name="ten_hang" class="w-full bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2">
                    <option value="Thường" {{ ($user->hangThanhVien?->ten_hang ?? 'Thường') == 'Thường' ? 'selected' : '' }}>
                        Thường
                    </option>
                    <option value="Bạc" {{ $user->hangThanhVien?->ten_hang == 'Bạc' ? 'selected' : '' }}>
                        Bạc
                    </option>
                    <option value="Vàng" {{ $user->hangThanhVien?->ten_hang == 'Vàng' ? 'selected' : '' }}>
                        Vàng
                    </option>
                    <option value="Bạch Kim" {{ $user->hangThanhVien?->ten_hang == 'Bạch Kim' ? 'selected' : '' }}>
                        Bạch Kim
                    </option>
                    <option value="Kim Cương" {{ $user->hangThanhVien?->ten_hang == 'Kim Cương' ? 'selected' : '' }}>
                        Kim Cương
                    </option>
                </select>
                @error('ten_hang') <p class="text-red-500 mt-1 text-xs">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

      </div>

      <div class="mt-6 flex justify-between items-center">
        <a href="{{ route('admin.users.index') }}"
           class="text-gray-400 hover:text-white hover:underline text-sm">← Quay lại</a>
        <button type="submit"
                class="px-5 py-2 rounded-md bg-[#F53003] hover:opacity-90 transition font-medium">
          Cập nhật tài khoản
        </button>
      </div>
    </form>
  </div>
@endsection