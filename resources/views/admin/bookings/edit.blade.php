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
        <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4 overflow-x-auto">
          <div class="flex justify-center mb-4">
            <div class="bg-[#262833] text-white px-4 py-2 rounded-lg text-sm font-medium">Màn hình</div>
          </div>
          <div id="seat-map" class="flex flex-col items-center space-y-1"></div>
        </div>
        <input type="hidden" name="ghe_ids" id="ghe-ids">
        <div class="mt-3 flex flex-wrap gap-4 justify-center">
          <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-blue-600 rounded"></div>
            <span class="text-xs text-[#a6a6b0]">Ghế thường</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-yellow-600 rounded"></div>
            <span class="text-xs text-[#a6a6b0]">Ghế VIP</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-pink-600 rounded"></div>
            <span class="text-xs text-[#a6a6b0]">Ghế đôi</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-gray-800 rounded"></div>
            <span class="text-xs text-[#a6a6b0]">Bị khóa/đã đặt</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-transparent rounded ring-2 ring-[#F53003]"></div>
            <span class="text-xs text-[#a6a6b0]">Ghế đang chọn/đã đặt bởi vé này</span>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Chọn các ghế trống trên sơ đồ. Giá tự tính theo loại ghế.</p>
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
    // Map: seat type id -> ten_loai
    const seatTypeMap = @json(\App\Models\LoaiGhe::pluck('ten_loai','id'));

    document.addEventListener('DOMContentLoaded', async function(){
      const bookingId = {{ $booking->id }};
      const currentShowtimeId = {{ $booking->id_suat_chieu }};
      const showtimeSelect = document.getElementById('suat-chieu-select');
      const seatMap = document.getElementById('seat-map');
      const gheIdsInput = document.getElementById('ghe-ids');
      const currentSeatIds = @json($booking->chiTietDatVe->pluck('id_ghe'));
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

        // If loading current showtime, preselect seats of this booking
        const isCurrentShowtime = String(showtimeId) === String(currentShowtimeId);
        if (isCurrentShowtime && Array.isArray(currentSeatIds)) {
          currentSeatIds.forEach(id => selected.add(Number(id)));
          gheIdsInput.value = Array.from(selected).join(',');
        }

        // Group seats by row label derived from seat.label (e.g., A1, B10)
        const rows = {};
        let maxCol = 0;
        (data.seats || []).forEach(seat => {
          const m = String(seat.label || '').match(/^([A-Za-z]+)(\d+)$/);
          const rowLabel = m ? m[1].toUpperCase() : '';
          const col = m ? parseInt(m[2], 10) : 0;
          maxCol = Math.max(maxCol, col);
          if (!rows[rowLabel]) rows[rowLabel] = [];
          rows[rowLabel][col] = seat;
        });

        // Sort row labels alphabetically by first character
        const rowKeys = Object.keys(rows).sort((a,b)=>a.localeCompare(b, 'vi'));

        rowKeys.forEach(rowKey => {
          const rowWrap = document.createElement('div');
          rowWrap.className = 'flex space-x-1 items-center';

          const rowSpan = document.createElement('span');
          rowSpan.className = 'text-sm text-[#a6a6b0] w-6 text-center font-medium';
          rowSpan.textContent = rowKey || '';
          rowWrap.appendChild(rowSpan);

          for (let c = 1; c <= maxCol; c++) {
            const seat = (rows[rowKey] || [])[c];
            if (seat) {
              const isBooked = !!seat.booked || seat.status === 'locked' || seat.status === 'unavailable';
              const typeText = String(seatTypeMap[String(seat.type)] || '').toLowerCase();
              let btnClass = '';
              if (!isBooked) {
                if (typeText.includes('vip')) btnClass = 'bg-yellow-600 hover:bg-yellow-700 text-white';
                else if (typeText.includes('đôi') || typeText.includes('doi') || typeText.includes('couple')) btnClass = 'bg-pink-600 hover:bg-pink-700 text-white';
                else btnClass = 'bg-blue-600 hover:bg-blue-700 text-white';
              } else {
                btnClass = 'bg-gray-800 hover:bg-gray-900 text-gray-400 cursor-not-allowed';
              }

              const btn = document.createElement('button');
              btn.type = 'button';
              btn.title = seat.label;
              btn.textContent = seat.label;
              btn.className = `seat-btn w-8 h-8 rounded text-xs font-medium transition-all duration-200 ${btnClass}`;
              btn.disabled = isBooked;
              btn.dataset.id = seat.id;

              // Highlight if preselected
              if (!isBooked && selected.has(seat.id)) {
                btn.classList.add('ring-2','ring-[#F53003]');
              }
              btn.addEventListener('click', () => {
                if (selected.has(seat.id)) {
                  selected.delete(seat.id);
                  btn.classList.remove('ring-2','ring-[#F53003]');
                } else {
                  selected.add(seat.id);
                  btn.classList.add('ring-2','ring-[#F53003]');
                }
                gheIdsInput.value = Array.from(selected).join(',');
              });
              rowWrap.appendChild(btn);
            } else {
              const empty = document.createElement('span');
              empty.className = 'w-8 h-8 rounded text-xs inline-flex items-center justify-center text-[#4b5563] border border-[#2f3240]';
              empty.innerHTML = '&nbsp;';
              rowWrap.appendChild(empty);
            }
          }

          seatMap.appendChild(rowWrap);
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
