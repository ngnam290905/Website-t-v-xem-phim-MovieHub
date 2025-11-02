@extends('admin.layout')

@section('title', 'Thêm Combo')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833] max-w-4xl">
  <h2 class="text-xl font-semibold mb-4">➕ Thêm Combo</h2>

  @if ($errors->any())
    <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-3">
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.combos.store') }}" method="POST" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 text-sm text-gray-300">Tên combo<span class="text-red-500">*</span></label>
        <input type="text" name="ten" value="{{ old('ten') }}" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" required />
      </div>
      <div>
        <label class="block mb-1 text-sm text-gray-300">Ảnh (URL)</label>
        <input type="text" name="anh" value="{{ old('anh') }}" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
      </div>
    </div>

    <div>
      <label class="block mb-1 text-sm text-gray-300">Mô tả</label>
      <textarea name="mo_ta" rows="3" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">{{ old('mo_ta') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block mb-1 text-sm text-gray-300">Giá<span class="text-red-500">*</span></label>
        <input type="number" name="gia" value="{{ old('gia') }}" min="0" step="1000" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" required />
      </div>
      <div>
        <label class="block mb-1 text-sm text-gray-300">Giá gốc</label>
        <input type="number" name="gia_goc" value="{{ old('gia_goc') }}" min="0" step="1000" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
      </div>
      <div class="flex items-end gap-2">
        <label class="inline-flex items-center text-sm text-gray-300">
          <input type="checkbox" name="combo_noi_bat" value="1" class="mr-2" {{ old('combo_noi_bat') ? 'checked' : '' }}>
          Combo nổi bật
        </label>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block mb-1 text-sm text-gray-300">Số lượng tối đa</label>
        <input type="number" name="so_luong_toi_da" value="{{ old('so_luong_toi_da') }}" min="1" step="1" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
      </div>
      <div>
        <label class="block mb-1 text-sm text-gray-300">Yêu cầu ít nhất vé</label>
        <input type="number" name="yeu_cau_it_nhat_ve" value="{{ old('yeu_cau_it_nhat_ve') }}" min="1" step="1" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
      </div>
      <div>
        <label class="block mb-1 text-sm text-gray-300">Trạng thái</label>
        <select name="trang_thai" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">
          <option value="1" {{ old('trang_thai', 1)==1 ? 'selected' : '' }}>Đang bán</option>
          <option value="0" {{ old('trang_thai')==='0' ? 'selected' : '' }}>Ngừng bán</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 text-sm text-gray-300">Ngày bắt đầu</label>
        <input type="datetime-local" name="ngay_bat_dau" value="{{ old('ngay_bat_dau') }}" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
      </div>
      <div>
        <label class="block mb-1 text-sm text-gray-300">Ngày kết thúc</label>
        <input type="datetime-local" name="ngay_ket_thuc" value="{{ old('ngay_ket_thuc') }}" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
      </div>
    </div>

    <div class="flex gap-2">
      <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] rounded text-white text-sm">Lưu</button>
      <a href="{{ route('admin.combos.index') }}" class="px-4 py-2 border border-[#2f3240] rounded text-sm text-gray-300">Hủy</a>
    </div>
  </form>
</div>
@endsection
