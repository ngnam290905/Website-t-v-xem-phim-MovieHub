@extends('admin.layout')

@section('title', 'Cấu hình suất chiếu giờ cao điểm - Admin')
@section('page-title', 'Cấu hình suất chiếu giờ cao điểm')
@section('page-description', 'Tạo hàng loạt suất chiếu cho nhiều phòng trong khung giờ cao điểm')

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-8">
      <form action="{{ route('admin.phong-chieu.peak-hours.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- 1. Chọn phim -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F53003] text-white text-xs mr-2">1</span>
            Chọn phim
          </h3>
          
          <div class="space-y-4">
            <select name="phim_id" id="phim_id" class="w-full bg-[#1E2129] border border-[#2D3038] rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-[#F53003] focus:border-transparent" required>
              <option value="">-- Chọn phim --</option>
              @foreach($phims as $phim)
                  <option value="{{ $phim->id }}" data-duration="{{ $phim->thoi_luong }}">
                      {{ $phim->ten_phim }} ({{ $phim->thoi_luong }} phút)
                  </option>
              @endforeach
            </select>
            <p class="text-sm text-gray-400">Thời lượng phim sẽ được sử dụng để tính toán thời gian kết thúc</p>
          </div>
        </div>

        <!-- 2. Chọn phòng chiếu -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F53003] text-white text-xs mr-2">2</span>
            Chọn phòng chiếu
          </h3>
          
          <div class="space-y-4">
            <div class="flex items-center mb-2">
              <input type="checkbox" id="select-all-rooms" class="rounded border-gray-600 text-[#F53003] focus:ring-[#F53003] mr-2">
              <label for="select-all-rooms" class="text-white">Chọn tất cả</label>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto p-2 bg-[#1E2129] rounded-lg">
              @foreach($phongChieus as $phong)
                <div class="flex items-center">
                  <input type="checkbox" name="phong_chieu[]" value="{{ $phong->id }}" 
                         class="room-checkbox rounded border-gray-600 text-[#F53003] focus:ring-[#F53003] mr-2">
                  <label class="text-white">{{ $phong->ten_phong }} ({{ $phong->so_hang }}x{{ $phong->so_cot }} ghế)</label>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- 3. Cài đặt thời gian -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F53003] text-white text-xs mr-2">3</span>
            Cài đặt thời gian
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-300">Ngày chiếu</label>
              <input type="date" name="ngay_chieu" id="ngay_chieu" 
                     min="{{ date('Y-m-d') }}" 
                     class="w-full bg-[#1E2129] border border-[#2D3038] rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-[#F53003] focus:border-transparent"
                     required>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-300">Thời gian nghỉ giữa các suất (phút)</label>
              <input type="number" name="thoi_gian_ngung" id="thoi_gian_ngung" 
                     min="15" max="60" value="15" 
                     class="w-full bg-[#1E2129] border border-[#2D3038] rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-[#F53003] focus:border-transparent"
                     required>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-300">Giờ bắt đầu (24h)</label>
              <input type="time" name="gio_bat_dau" id="gio_bat_dau" 
                     class="w-full bg-[#1E2129] border border-[#2D3038] rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-[#F53003] focus:border-transparent"
                     step="60" min="08:00" max="23:59" required>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-300">Giờ kết thúc (24h)</label>
              <input type="time" name="gio_ket_thuc" id="gio_ket_thuc" 
                     class="w-full bg-[#1E2129] border border-[#2D3038] rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-[#F53003] focus:border-transparent"
                     step="60" min="09:00" max="23:59" required>
            </div>
          </div>
          
          <div class="bg-[#1E2129] p-4 rounded-lg">
            <h4 class="text-sm font-semibold text-white mb-2">Xem trước lịch chiếu:</h4>
            <div id="schedule-preview" class="space-y-2 text-sm text-gray-300">
              <p class="text-gray-400">Chọn phim và thời gian để xem trước lịch chiếu</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <a href="{{ route('admin.phong-chieu.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
            Hủy bỏ
          </a>
          <button type="submit" id="submit-btn" class="px-6 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#D42A03] transition flex items-center">
            <i class="fas fa-plus-circle mr-2"></i>
            Tạo suất chiếu
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
      // Select all rooms
      const selectAllCheckbox = document.getElementById('select-all-rooms');
      const roomCheckboxes = document.querySelectorAll('.room-checkbox');
      
      selectAllCheckbox.addEventListener('change', function() {
          roomCheckboxes.forEach(checkbox => {
              checkbox.checked = selectAllCheckbox.checked;
          });
          updateSubmitState();
      });
      
      // Update select all when individual checkboxes change
      roomCheckboxes.forEach(checkbox => {
          checkbox.addEventListener('change', function() {
              const allChecked = Array.from(roomCheckboxes).every(cb => cb.checked);
              selectAllCheckbox.checked = allChecked;
              updateSubmitState();
          });
      });
      
      // Update schedule preview
      const phimSelect = document.getElementById('phim_id');
      const ngayChieuInput = document.getElementById('ngay_chieu');
      const gioBatDauInput = document.getElementById('gio_bat_dau');
      const gioKetThucInput = document.getElementById('gio_ket_thuc');
      const thoiGianNgungInput = document.getElementById('thoi_gian_ngung');
      const schedulePreview = document.getElementById('schedule-preview');
      
      [phimSelect, ngayChieuInput, gioBatDauInput, gioKetThucInput, thoiGianNgungInput].forEach(element => {
          element.addEventListener('change', updateSchedulePreview);
      });
      
      function updateSchedulePreview() {
          const phimId = phimSelect.value;
          const ngayChieu = ngayChieuInput.value;
          const gioBatDau = gioBatDauInput.value;
          const gioKetThuc = gioKetThucInput.value;
          const thoiGianNgung = parseInt(thoiGianNgungInput.value) || 15;
          
          if (!phimId || !ngayChieu || !gioBatDau || !gioKetThuc) {
              return;
          }
          
          const phimOption = phimSelect.options[phimSelect.selectedIndex];
          const thoiLuongPhut = parseInt(phimOption.dataset.duration) || 120;
          
          const batDau = new Date(`${ngayChieu}T${gioBatDau}`);
          const ketThuc = new Date(`${ngayChieu}T${gioKetThuc}`);
          
          if (batDau >= ketThuc) {
              schedulePreview.innerHTML = '<p class="text-red-400">Giờ kết thúc phải sau giờ bắt đầu</p>';
              return;
          }
          
          let html = '<div class="space-y-2">';
          let currentTime = new Date(batDau);
          let showtimeCount = 0;
          
          while (currentTime < ketThuc) {
              const showtimeEnd = new Date(currentTime.getTime() + thoiLuongPhut * 60000);
              
              if (showtimeEnd > ketThuc) break;
              
              html += `
                  <div class="flex justify-between items-center p-2 bg-[#262833] rounded">
                      <span>${formatTime(currentTime)} - ${formatTime(showtimeEnd)}</span>
                      <span class="text-green-400 text-xs">${thoiLuongPhut} phút</span>
                  </div>
              `;
              
              currentTime = new Date(showtimeEnd.getTime() + thoiGianNgung * 60000);
              showtimeCount++;
          }
          
          if (showtimeCount === 0) {
              html = '<p class="text-yellow-400">Không đủ thời gian để tạo suất chiếu nào trong khoảng thời gian này</p>';
          } else {
              html += `<p class="text-sm text-gray-400 mt-2">Sẽ tạo tổng cộng ${showtimeCount} suất chiếu</p>`;
          }
          
          html += '</div>';
          schedulePreview.innerHTML = html;
      }
      
      function formatTime(date) {
          // Sử dụng định dạng 24 giờ
          return date.getHours().toString().padStart(2, '0') + ':' + 
                 date.getMinutes().toString().padStart(2, '0');
      }
      
      function updateSubmitState() {
          const submitBtn = document.getElementById('submit-btn');
          const hasSelectedRooms = Array.from(roomCheckboxes).some(cb => cb.checked);
          
          submitBtn.disabled = !hasSelectedRooms;
      }
      
      // Initialize
      updateSchedulePreview();
      updateSubmitState();
  });
  </script>
@endsection
