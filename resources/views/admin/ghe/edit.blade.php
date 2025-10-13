@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Ghế')

@section('content')
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Chỉnh Sửa Ghế</h1>
      <a href="{{ route('admin.ghe.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300">
        <i class="fas fa-arrow-left mr-2"></i>Quay lại
      </a>
    </div>

    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <form action="{{ route('admin.ghe.update', $ghe) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="id_phong" class="block text-sm font-medium text-white mb-2">Phòng Chiếu <span class="text-red-500">*</span></label>
            <select name="id_phong" id="id_phong" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-md text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('id_phong') border-red-500 @enderror" required>
              <option value="">Chọn phòng chiếu</option>
              @foreach($phongChieu as $phong)
              <option value="{{ $phong->id }}" {{ (old('id_phong', $ghe->id_phong) == $phong->id) ? 'selected' : '' }}>
                {{ $phong->ten_phong }} ({{ $phong->so_hang }}x{{ $phong->so_cot }})
              </option>
              @endforeach
            </select>
            @error('id_phong')
              <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="id_loai" class="block text-sm font-medium text-white mb-2">Loại Ghế <span class="text-red-500">*</span></label>
            <select name="id_loai" id="id_loai" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-md text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('id_loai') border-red-500 @enderror" required>
              <option value="">Chọn loại ghế</option>
              @foreach($loaiGhe as $loai)
              <option value="{{ $loai->id }}" {{ (old('id_loai', $ghe->id_loai) == $loai->id) ? 'selected' : '' }}>
                {{ $loai->ten_loai }} - {{ $loai->he_so_gia }}x
              </option>
              @endforeach
            </select>
            @error('id_loai')
              <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="so_ghe" class="block text-sm font-medium text-white mb-2">Số Ghế <span class="text-red-500">*</span></label>
            <input type="text" name="so_ghe" id="so_ghe" 
                   class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-md text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('so_ghe') border-red-500 @enderror" 
                   value="{{ old('so_ghe', $ghe->so_ghe) }}" required>
            @error('so_ghe')
              <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="so_hang" class="block text-sm font-medium text-white mb-2">Số Hàng <span class="text-red-500">*</span></label>
            <input type="number" name="so_hang" id="so_hang" 
                   class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-md text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('so_hang') border-red-500 @enderror" 
                   value="{{ old('so_hang', $ghe->so_hang) }}" min="1" required>
            @error('so_hang')
              <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="trang_thai" class="block text-sm font-medium text-white mb-2">Trạng Thái</label>
            <select name="trang_thai" id="trang_thai" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-md text-white focus:outline-none focus:ring-2 focus:ring-[#F53003]">
              <option value="1" {{ old('trang_thai', $ghe->trang_thai) == 1 ? 'selected' : '' }}>Hoạt động</option>
              <option value="0" {{ old('trang_thai', $ghe->trang_thai) == 0 ? 'selected' : '' }}>Tạm dừng</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <a href="{{ route('admin.ghe.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors duration-300">
            <i class="fas fa-times mr-2"></i>Hủy
          </a>
          <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] text-white rounded-md transition-colors duration-300">
            <i class="fas fa-save mr-2"></i>Cập Nhật Ghế
          </button>
        </div>
      </form>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phongSelect = document.getElementById('id_phong');
    const hangInput = document.getElementById('so_hang');
    
    // Update max values when room is selected
    phongSelect.addEventListener('change', function() {
        if (this.value) {
            // You can fetch room details via AJAX if needed
            // For now, we'll just set reasonable defaults
            hangInput.max = 20;
        }
    });
});
</script>
@endsection
