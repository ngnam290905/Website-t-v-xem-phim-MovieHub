@extends('admin.layout')

@section('title', 'Thêm Đồ ăn')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833] max-w-4xl">
  <h2 class="text-xl font-semibold mb-4">➕ Thêm Đồ ăn</h2>

  @if ($errors->any())
    <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-3">
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.foods.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 text-sm text-gray-300">Tên đồ ăn<span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" required />
      </div>
      <div>
        <label class="block mb-1 text-sm text-gray-300">Giá (VNĐ)<span class="text-red-500">*</span></label>
        <input type="number" name="price" value="{{ old('price') }}" min="0" step="1000" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" required />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 text-sm text-gray-300">Số lượng tồn kho<span class="text-red-500">*</span></label>
        <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" step="1" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" required />
      </div>
      <div>
        <label class="block mb-1 text-sm text-gray-300">Trạng thái<span class="text-red-500">*</span></label>
        <select name="is_active" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">
          <option value="1" {{ old('is_active', 1)==1 ? 'selected' : '' }}>Đang bán</option>
          <option value="0" {{ old('is_active')==='0' ? 'selected' : '' }}>Ngừng bán</option>
        </select>
      </div>
    </div>

    <div>
      <label class="block mb-1 text-sm text-gray-300">Ảnh</label>
      <input type="file" name="image" accept="image/*" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
      <p class="text-xs text-gray-400 mt-1">Chấp nhận: JPEG, PNG, JPG, GIF (tối đa 2MB)</p>
    </div>

    <div class="flex gap-2">
      <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] rounded text-white text-sm">Lưu</button>
      <a href="{{ route('admin.foods.index') }}" class="px-4 py-2 border border-[#2f3240] rounded text-sm text-gray-300">Hủy</a>
    </div>
  </form>
</div>
@endsection

