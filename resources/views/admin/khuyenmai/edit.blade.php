@extends('admin.layout')

@section('title', 'Sửa mã khuyến mãi')

@section('content')
<div class="max-w-xl mx-auto bg-[#151822] p-6 rounded-xl border border-[#262833]">
    <h1 class="text-2xl font-bold mb-6">Sửa mã khuyến mãi</h1>
    <form action="{{ route('admin.khuyenmai.update', $khuyenmai->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Mã khuyến mãi</label>
            <input type="text" name="ma_km" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none" value="{{ old('ma_km', $khuyenmai->ma_km) }}">
            @error('ma_km')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Mô tả</label>
            <textarea name="mo_ta" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none">{{ old('mo_ta', $khuyenmai->mo_ta) }}</textarea>
            @error('mo_ta')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4 flex gap-4">
            <div class="flex-1">
                <label class="block mb-1 font-semibold">Ngày bắt đầu</label>
                <input type="date" name="ngay_bat_dau" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none" value="{{ old('ngay_bat_dau', $khuyenmai->ngay_bat_dau) }}">
                @error('ngay_bat_dau')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="flex-1">
                <label class="block mb-1 font-semibold">Ngày kết thúc</label>
                <input type="date" name="ngay_ket_thuc" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none" value="{{ old('ngay_ket_thuc', $khuyenmai->ngay_ket_thuc) }}">
                @error('ngay_ket_thuc')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-4 flex gap-4">
            <div class="flex-1">
                <label class="block mb-1 font-semibold">Loại giảm</label>
                <select name="loai_giam" id="loai_giam" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none">
                    <option value="phantram" {{ old('loai_giam', $khuyenmai->loai_giam) == 'phantram' ? 'selected' : '' }}>Giảm theo %</option>
                    <option value="codinh" {{ old('loai_giam', $khuyenmai->loai_giam) == 'codinh' ? 'selected' : '' }}>Giảm số tiền cố định</option>
                </select>
                @error('loai_giam')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="flex-1">
                <label class="block mb-1 font-semibold" id="label_gia_tri_giam">
                    {{ old('loai_giam', $khuyenmai->loai_giam) == 'codinh' ? 'Giá trị giảm (VNĐ)' : 'Giá trị giảm (%)' }}
                </label>
                <input type="number" name="gia_tri_giam" id="gia_tri_giam" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none" value="{{ old('gia_tri_giam', $khuyenmai->gia_tri_giam) }}">
                @error('gia_tri_giam')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="flex-1">
                <label class="block mb-1 font-semibold">Điều kiện</label>
                <input type="text" name="dieu_kien" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none" value="{{ old('dieu_kien', $khuyenmai->dieu_kien) }}">
                @error('dieu_kien')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('loai_giam');
            const label = document.getElementById('label_gia_tri_giam');
            select.addEventListener('change', function() {
                if (this.value === 'codinh') {
                    label.textContent = 'Giá trị giảm (VNĐ)';
                } else {
                    label.textContent = 'Giá trị giảm (%)';
                }
            });
        });
        </script>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Trạng thái</label>
            <select name="trang_thai" class="w-full px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none">
                <option value="1" {{ old('trang_thai', $khuyenmai->trang_thai) == 1 ? 'selected' : '' }}>Kích hoạt</option>
                <option value="0" {{ old('trang_thai', $khuyenmai->trang_thai) == 0 ? 'selected' : '' }}>Ẩn</option>
            </select>
            @error('trang_thai')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.khuyenmai.index') }}" class="px-4 py-2 rounded bg-gray-500 hover:bg-gray-600 text-white">Hủy</a>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold">Cập nhật</button>
        </div>
    </form>
</div>
@endsection
