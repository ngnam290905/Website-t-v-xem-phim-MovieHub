@extends('admin.layout')

@section('title', 'Chỉnh sửa vé')

@section('content')
<div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
  <h2 class="text-xl font-semibold mb-4">✏️ Chỉnh sửa Đặt Vé #{{ $booking->id }}</h2>

  <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="space-y-4" id="edit-booking-form">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-1 text-sm text-gray-300">Thay đổi suất chiếu</label>
          <select id="suat-chieu-select" name="suat_chieu_id" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200"></select>
          <p class="text-xs text-gray-400 mt-1">Chỉ hiển thị suất cùng phim, còn hiệu lực.</p>
        </div>

        <div>
          <label class="block mb-1 text-sm text-gray-300">Ghi chú nội bộ</label>
          <textarea name="ghi_chu_noi_bo" rows="3" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" placeholder="Ghi chú nội bộ...">{{ $booking->ghi_chu_noi_bo ?? '' }}</textarea>
        </div>
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-300">Trạng thái</label>
        <select name="trang_thai" class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">
          <option value="0" {{ $booking->trang_thai == 0 ? 'selected' : '' }}>Chờ xác nhận</option>
          <option value="1" {{ $booking->trang_thai == 1 ? 'selected' : '' }}>Đã xác nhận</option>
          <option value="3" {{ $booking->trang_thai == 3 ? 'selected' : '' }}>Yêu cầu hủy</option>
          <option value="2" {{ $booking->trang_thai == 2 ? 'selected' : '' }}>Đã hủy</option>
        </select>
      </div>

      <div>
        <label class="block mb-1 text-sm text-gray-300">Mã giảm giá</label>
        <input type="text" name="ma_km" value="{{ old('ma_km') }}" placeholder="Nhập mã (VD: DEMO10)"
               class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
        <p class="text-xs text-gray-400 mt-1">Mã hợp lệ sẽ được áp ngay khi lưu. Để bỏ mã, để trống trường này.</p>
      </div>

      <div>
        <label class="block mb-2 text-sm text-gray-300">Thay đổi ghế</label>
        <div id="seat-map" class="grid grid-cols-8 gap-2"></div>
        <input type="hidden" name="ghe_ids" id="ghe-ids">
        <p class="text-xs text-gray-400 mt-1">Chọn các ghế trống trên sơ đồ. Giá tự tính theo loại ghế.</p>
      </div>

      <div>
          <label class="block mb-1 text-sm text-gray-300">Chọn combo (nếu có)</label>
          <select name="combo_ids[]" multiple class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">
              @foreach($combos as $combo)
                  <option value="{{ $combo->id }}">{{ $combo->ten }}</option>
              @endforeach
          </select>
      </div>

      <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded text-white text-sm">Lưu thay đổi</button>
      <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded text-white text-sm">Hủy</a>
  </form>

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
        data.seats.forEach(seat => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.textContent = seat.label;
          btn.className = 'px-2 py-1 text-xs rounded border ' + (seat.booked ? 'bg-gray-700 border-gray-600 text-gray-400 cursor-not-allowed' : 'bg-[#1d202a] border-[#262833] text-gray-200 hover:bg-[#232735]');
          btn.disabled = !!seat.booked;
          btn.dataset.id = seat.id;
          btn.addEventListener('click', () => {
            if (selected.has(seat.id)) { selected.delete(seat.id); btn.classList.remove('ring-2','ring-[#F53003]'); }
            else { selected.add(seat.id); btn.classList.add('ring-2','ring-[#F53003]'); }
            gheIdsInput.value = Array.from(selected).join(',');
          });
          seatMap.appendChild(btn);
        });
      }

      // Init
      await loadShowtimes();
      await loadSeats(showtimeSelect.value || currentShowtimeId);
      showtimeSelect.addEventListener('change', async (e)=>{
        await loadSeats(e.target.value);
      });
    });
  </script>
  @endpush
</div>
@endsection
