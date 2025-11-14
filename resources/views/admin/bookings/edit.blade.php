@extends('admin.layout')

@section('title', 'Chỉnh sửa vé')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Chỉnh sửa Đặt Vé #{{ $booking->id }}</h1>
            <p class="text-[#a6a6b0]">Cập nhật thông tin đặt vé của khách hàng</p>
        </div>
        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]">
            <i class="fas fa-eye mr-2"></i> Xem chi tiết
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="space-y-6" id="edit-booking-form">
            @csrf
            @method('PUT')

            <!-- Showtime Selection -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Thay đổi suất chiếu</h3>
                </div>
                <select id="suat-chieu-select" name="suat_chieu_id" class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white">
                    <!-- Options will be loaded via JavaScript -->
                </select>
                <p class="text-xs text-[#a6a6b0] mt-2">Chỉ hiển thị suất cùng phim, còn hiệu lực.</p>
            </div>

            <!-- Status Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                        <h3 class="text-white font-medium">Trạng thái đặt vé</h3>
                    </div>
                    <select name="trang_thai" class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white">
                        <option value="0" {{ $booking->trang_thai == 0 ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="1" {{ $booking->trang_thai == 1 ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="3" {{ $booking->trang_thai == 3 ? 'selected' : '' }}>Yêu cầu hủy</option>
                        <option value="2" {{ $booking->trang_thai == 2 ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>

                <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-wallet text-white text-sm"></i>
                        </div>
                        <h3 class="text-white font-medium">Trạng thái thanh toán</h3>
                    </div>
                    <select name="trang_thai_thanh_toan" class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white">
                        <option value="0" {{ ($booking->trang_thai_thanh_toan ?? ($booking->trang_thai == 1 ? 1 : 0)) == 0 ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="1" {{ ($booking->trang_thai_thanh_toan ?? ($booking->trang_thai == 1 ? 1 : 0)) == 1 ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="2" {{ ($booking->trang_thai_thanh_toan ?? ($booking->trang_thai == 1 ? 1 : 0)) == 2 ? 'selected' : '' }}>Đã hoàn tiền</option>
                    </select>
                </div>
            </div>

            <!-- Discount Code -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-tag text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Mã giảm giá</h3>
                </div>
                <input type="text" name="ma_km" value="{{ old('ma_km') }}" placeholder="Nhập mã (VD: DEMO10)"
                       class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white" />
                <p class="text-xs text-[#a6a6b0] mt-2">Mã hợp lệ sẽ được áp ngay khi lưu. Để bỏ mã, để trống trường này.</p>
            </div>

            <!-- Seat Selection -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-couch text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Thay đổi ghế</h3>
                </div>
                <div id="seat-map" class="grid grid-cols-8 gap-2 min-h-[100px]"></div>
                <input type="hidden" name="ghe_ids" id="ghe-ids">
                <p class="text-xs text-[#a6a6b0] mt-3">Chọn các ghế trống trên sơ đồ. Giá tự tính theo loại ghế.</p>
            </div>

            <!-- Combo Selection -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-popcorn text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Chọn combo (nếu có)</h3>
                </div>
                <select name="combo_ids[]" multiple class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white">
                    @foreach($combos as $combo)
                        <option value="{{ $combo->id }}">{{ $combo->ten }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-[#a6a6b0] mt-2">Giữ Ctrl/Cmd để chọn nhiều combo.</p>
            </div>

            <!-- Internal Notes -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-gray-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-sticky-note text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Ghi chú nội bộ</h3>
                </div>
                <textarea name="ghi_chu_noi_bo" rows="3" class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white" placeholder="Ghi chú nội bộ...">{{ $booking->ghi_chu_noi_bo ?? '' }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#262833]">
                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-times mr-2"></i> Hủy
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-save mr-2"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function(){
        const bookingId = {{ $booking->id }};
        const currentShowtimeId = {{ $booking->id_suat_chieu }};
        const showtimeSelect = document.getElementById('suat-chieu-select');
        const seatMap = document.getElementById('seat-map');
        const gheIdsInput = document.getElementById('ghe-ids');
        let selected = new Set();

        async function loadShowtimes(){
            const res = await fetch(`{{ route('admin.bookings.available-showtimes', ':id') }}`.replace(':id', bookingId));
            const items = await res.json();
            showtimeSelect.innerHTML = '';
            items.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it.id;
                opt.textContent = it.label;
                if (it.current) opt.selected = true;
                showtimeSelect.appendChild(opt);
            });
        }

        async function loadSeats(showtimeId){
            const url = `{{ route('admin.showtimes.seats', ':sid') }}`.replace(':sid', showtimeId) + `?exclude_booking_id=${bookingId}`;
            const res = await fetch(url);
            const data = await res.json();
            seatMap.innerHTML = '';
            selected.clear();
            
            if (data.seats && data.seats.length > 0) {
                data.seats.forEach(seat => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = seat.label;
                    btn.className = 'px-3 py-2 text-xs rounded-lg border transition-all ' + 
                        (seat.booked ? 
                            'bg-gray-700 border-gray-600 text-gray-400 cursor-not-allowed' : 
                            'bg-[#151822] border-[#262833] text-white hover:bg-[#222533] hover:border-[#F53003]');
                    btn.disabled = !!seat.booked;
                    btn.dataset.id = seat.id;
                    
                    if (!seat.booked) {
                        btn.addEventListener('click', () => {
                            if (selected.has(seat.id)) { 
                                selected.delete(seat.id); 
                                btn.classList.remove('bg-[#F53003]', 'border-[#F53003]', 'text-white'); 
                                btn.classList.add('bg-[#151822]', 'border-[#262833]');
                            } else { 
                                selected.add(seat.id); 
                                btn.classList.add('bg-[#F53003]', 'border-[#F53003]', 'text-white'); 
                                btn.classList.remove('bg-[#151822]', 'border-[#262833]');
                            }
                            gheIdsInput.value = Array.from(selected).join(',');
                        });
                    }
                    seatMap.appendChild(btn);
                });
            } else {
                seatMap.innerHTML = '<p class="text-[#a6a6b0] col-span-8 text-center">Không có ghế nào khả dụng</p>';
            }
        }

        // Initialize
        await loadShowtimes();
        await loadSeats(showtimeSelect.value || currentShowtimeId);
        
        showtimeSelect.addEventListener('change', async (e)=>{
            await loadSeats(e.target.value);
        });
    });
</script>
@endpush
@endsection
