@extends('admin.layout')

@section('title', 'Thêm mã khuyến mãi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Thêm mã khuyến mãi</h1>
        <a href="{{ route('admin.khuyenmai.index') }}" class="px-4 py-2 rounded bg-gray-500 hover:bg-gray-600 text-white transition-colors">
            ← Quay lại
        </a>
    </div>

    <form action="{{ route('admin.khuyenmai.store') }}" method="POST" class="bg-[#151822] p-8 rounded-xl border border-[#262833]">
        @csrf
        
        <!-- Thông tin cơ bản -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4 text-blue-400 border-b border-[#262833] pb-2">Thông tin cơ bản</h2>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block mb-2 font-semibold text-gray-300">Tên khuyến mãi <span class="text-red-500">*</span></label>
                    <textarea name="mo_ta" rows="2" class="w-full px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-blue-500 transition-colors" placeholder="VD: Giảm giá cho khách hàng thân thiết">{{ old('mo_ta') }}</textarea>
                    @error('mo_ta')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-gray-300">Mã khuyến mãi <span class="text-red-500">*</span></label>
                    <input type="text" name="ma_km" class="w-full px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-blue-500 transition-colors uppercase" placeholder="VD: SUMMER2025" value="{{ old('ma_km') }}">
                    @error('ma_km')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <!-- Thời gian hiệu lực -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4 text-green-400 border-b border-[#262833] pb-2">Thời gian hiệu lực</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2 font-semibold text-gray-300">Ngày giờ bắt đầu <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-5 gap-2">
                        <input type="date" name="ngay_bat_dau_date" id="ngay_bat_dau_date" class="col-span-3 px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-green-500 transition-colors" value="{{ old('ngay_bat_dau') ? date('Y-m-d', strtotime(old('ngay_bat_dau'))) : '' }}">
                        <input type="number" name="ngay_bat_dau_hour" id="ngay_bat_dau_hour" min="0" max="23" placeholder="Giờ" class="px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-green-500 transition-colors text-center" value="{{ old('ngay_bat_dau') ? date('H', strtotime(old('ngay_bat_dau'))) : '' }}">
                        <input type="number" name="ngay_bat_dau_minute" id="ngay_bat_dau_minute" min="0" max="59" placeholder="Phút" class="px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-green-500 transition-colors text-center" value="{{ old('ngay_bat_dau') ? date('i', strtotime(old('ngay_bat_dau'))) : '' }}">
                    </div>
                    <input type="hidden" name="ngay_bat_dau" id="ngay_bat_dau_hidden">
                    <small class="text-gray-400 text-xs mt-1 block">Chọn ngày, giờ (0-23) và phút (0-59)</small>
                    @error('ngay_bat_dau')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-gray-300">Ngày giờ kết thúc <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-5 gap-2">
                        <input type="date" name="ngay_ket_thuc_date" id="ngay_ket_thuc_date" class="col-span-3 px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-green-500 transition-colors" value="{{ old('ngay_ket_thuc') ? date('Y-m-d', strtotime(old('ngay_ket_thuc'))) : '' }}">
                        <input type="number" name="ngay_ket_thuc_hour" id="ngay_ket_thuc_hour" min="0" max="23" placeholder="Giờ" class="px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-green-500 transition-colors text-center" value="{{ old('ngay_ket_thuc') ? date('H', strtotime(old('ngay_ket_thuc'))) : '' }}">
                        <input type="number" name="ngay_ket_thuc_minute" id="ngay_ket_thuc_minute" min="0" max="59" placeholder="Phút" class="px-3 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-green-500 transition-colors text-center" value="{{ old('ngay_ket_thuc') ? date('i', strtotime(old('ngay_ket_thuc'))) : '' }}">
                    </div>
                    <input type="hidden" name="ngay_ket_thuc" id="ngay_ket_thuc_hidden">
                    <small class="text-gray-400 text-xs mt-1 block">Chọn ngày, giờ (0-23) và phút (0-59)</small>
                    @error('ngay_ket_thuc')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <!-- Thông tin giảm giá -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4 text-yellow-400 border-b border-[#262833] pb-2">Thông tin giảm giá</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-2 font-semibold text-gray-300">Loại giảm <span class="text-red-500">*</span></label>
                    <select name="loai_giam" id="loai_giam" class="w-full px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-yellow-500 transition-colors">
                        <option value="phantram" {{ old('loai_giam', 'phantram') == 'phantram' ? 'selected' : '' }}>Giảm theo %</option>
                        <option value="codinh" {{ old('loai_giam') == 'codinh' ? 'selected' : '' }}>Giảm số tiền cố định</option>
                    </select>
                    @error('loai_giam')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-gray-300" id="label_gia_tri_giam">
                        Giá trị giảm (%) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="1" min="0" name="gia_tri_giam" id="gia_tri_giam" class="w-full px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-yellow-500 transition-colors" placeholder="0" value="{{ old('gia_tri_giam') }}">
                    <small class="text-gray-400 text-xs mt-1 block" id="hint_gia_tri_giam">Tối đa 40% cho giảm theo phần trăm</small>
                    @error('gia_tri_giam')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-gray-300">Giá trị giảm tối đa (VNĐ)</label>
                    <input type="number" step="0.01" min="0" name="gia_tri_giam_toi_da" id="gia_tri_giam_toi_da" class="w-full px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-yellow-500 transition-colors" placeholder="0" value="{{ old('gia_tri_giam_toi_da') }}">
                    <small class="text-gray-400 text-xs mt-1 block">Áp dụng cho loại giảm theo %</small>
                    @error('gia_tri_giam_toi_da')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <!-- Điều kiện áp dụng -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4 text-purple-400 border-b border-[#262833] pb-2">Điều kiện áp dụng</h2>
            <div>
                <label class="block mb-2 font-semibold text-gray-300">Điều kiện được áp dụng mã giảm giá</label>
                <input type="text" name="dieu_kien" class="w-full px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-purple-500 transition-colors" placeholder="VD: Áp dụng cho đơn hàng từ 200.000đ" value="{{ old('dieu_kien') }}">
                @error('dieu_kien')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        <!-- Trạng thái -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4 text-red-400 border-b border-[#262833] pb-2">Trạng thái</h2>
            <div>
                <label class="block mb-2 font-semibold text-gray-300">Trạng thái <span class="text-red-500">*</span></label>
                <select name="trang_thai" class="w-full px-4 py-2 rounded bg-[#222533] border border-[#262833] focus:outline-none focus:border-red-500 transition-colors">
                    <option value="1" {{ old('trang_thai', 1) == 1 ? 'selected' : '' }}>✓ Kích hoạt</option>
                    <option value="0" {{ old('trang_thai') == 0 ? 'selected' : '' }}>✗ Ẩn</option>
                </select>
                @error('trang_thai')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t border-[#262833]">
            <a href="{{ route('admin.khuyenmai.index') }}" class="px-6 py-2.5 rounded bg-gray-500 hover:bg-gray-600 text-white transition-colors">
                Hủy
            </a>
            <button type="submit" class="px-6 py-2.5 rounded bg-green-600 hover:bg-green-700 text-white font-semibold transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Lưu
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('loai_giam');
    const label = document.getElementById('label_gia_tri_giam');
    const input = document.getElementById('gia_tri_giam');
    const hint = document.getElementById('hint_gia_tri_giam');
    
    // Hàm kiểm tra giá trị
    function validateGiaTriGiam() {
        const value = parseFloat(input.value);
        
        // Kiểm tra số âm
        if (value < 0) {
            input.setCustomValidity('Giá trị không được là số âm');
            return;
        }
        
        // Kiểm tra phần trăm
        if (select.value === 'phantram' && value > 40) {
            input.setCustomValidity('Giá trị giảm theo phần trăm không được lớn hơn 40%');
        } else {
            input.setCustomValidity('');
        }
    }
    
    // Thay đổi label khi chọn loại giảm
    select.addEventListener('change', function() {
        if (this.value === 'codinh') {
            label.innerHTML = 'Giá trị giảm (VNĐ) <span class="text-red-500">*</span>';
            hint.textContent = 'Nhập số tiền giảm cố định';
            input.removeAttribute('max');
            input.placeholder = '0';
        } else {
            label.innerHTML = 'Giá trị giảm (%) <span class="text-red-500">*</span>';
            hint.textContent = 'Tối đa 40% cho giảm theo phần trăm';
            input.setAttribute('max', '40');
            input.placeholder = '0';
        }
        validateGiaTriGiam();
    });
    
    // Kiểm tra khi nhập giá trị
    input.addEventListener('input', validateGiaTriGiam);
    
    // Thiết lập max ban đầu nếu là phần trăm
    if (select.value === 'phantram') {
        input.setAttribute('max', '40');
    }

    // Xử lý datetime picker
    function updateHiddenDateTime(dateId, hourId, minuteId, hiddenId) {
        const dateInput = document.getElementById(dateId);
        const hourInput = document.getElementById(hourId);
        const minuteInput = document.getElementById(minuteId);
        const hiddenInput = document.getElementById(hiddenId);
        
        if (dateInput.value && hourInput.value !== '' && minuteInput.value !== '') {
            const hour = String(hourInput.value).padStart(2, '0');
            const minute = String(minuteInput.value).padStart(2, '0');
            hiddenInput.value = dateInput.value + ' ' + hour + ':' + minute + ':00';
        } else {
            hiddenInput.value = '';
        }
    }

    // Validate giờ và phút
    function validateTime(input, max) {
        let value = parseInt(input.value);
        if (isNaN(value) || value < 0) {
            input.value = 0;
        } else if (value > max) {
            input.value = max;
        }
        // Pad với 0 nếu là số đơn
        if (input.value !== '' && input.value.length === 1) {
            input.value = String(input.value).padStart(2, '0');
        }
    }

    // Lắng nghe sự kiện cho ngày bắt đầu
    document.getElementById('ngay_bat_dau_date').addEventListener('change', function() {
        updateHiddenDateTime('ngay_bat_dau_date', 'ngay_bat_dau_hour', 'ngay_bat_dau_minute', 'ngay_bat_dau_hidden');
    });
    document.getElementById('ngay_bat_dau_hour').addEventListener('input', function() {
        updateHiddenDateTime('ngay_bat_dau_date', 'ngay_bat_dau_hour', 'ngay_bat_dau_minute', 'ngay_bat_dau_hidden');
    });
    document.getElementById('ngay_bat_dau_hour').addEventListener('blur', function() {
        validateTime(this, 23);
        updateHiddenDateTime('ngay_bat_dau_date', 'ngay_bat_dau_hour', 'ngay_bat_dau_minute', 'ngay_bat_dau_hidden');
    });
    document.getElementById('ngay_bat_dau_minute').addEventListener('input', function() {
        updateHiddenDateTime('ngay_bat_dau_date', 'ngay_bat_dau_hour', 'ngay_bat_dau_minute', 'ngay_bat_dau_hidden');
    });
    document.getElementById('ngay_bat_dau_minute').addEventListener('blur', function() {
        validateTime(this, 59);
        updateHiddenDateTime('ngay_bat_dau_date', 'ngay_bat_dau_hour', 'ngay_bat_dau_minute', 'ngay_bat_dau_hidden');
    });

    // Lắng nghe sự kiện cho ngày kết thúc
    document.getElementById('ngay_ket_thuc_date').addEventListener('change', function() {
        updateHiddenDateTime('ngay_ket_thuc_date', 'ngay_ket_thuc_hour', 'ngay_ket_thuc_minute', 'ngay_ket_thuc_hidden');
    });
    document.getElementById('ngay_ket_thuc_hour').addEventListener('input', function() {
        updateHiddenDateTime('ngay_ket_thuc_date', 'ngay_ket_thuc_hour', 'ngay_ket_thuc_minute', 'ngay_ket_thuc_hidden');
    });
    document.getElementById('ngay_ket_thuc_hour').addEventListener('blur', function() {
        validateTime(this, 23);
        updateHiddenDateTime('ngay_ket_thuc_date', 'ngay_ket_thuc_hour', 'ngay_ket_thuc_minute', 'ngay_ket_thuc_hidden');
    });
    document.getElementById('ngay_ket_thuc_minute').addEventListener('input', function() {
        updateHiddenDateTime('ngay_ket_thuc_date', 'ngay_ket_thuc_hour', 'ngay_ket_thuc_minute', 'ngay_ket_thuc_hidden');
    });
    document.getElementById('ngay_ket_thuc_minute').addEventListener('blur', function() {
        validateTime(this, 59);
        updateHiddenDateTime('ngay_ket_thuc_date', 'ngay_ket_thuc_hour', 'ngay_ket_thuc_minute', 'ngay_ket_thuc_hidden');
    });
});
</script>
@endsection
