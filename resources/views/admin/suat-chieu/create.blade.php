@extends('admin.layout')

@section('title', 'Tạo Suất Chiếu Mới')
@section('page-title', 'Tạo Suất Chiếu Mới')
@section('page-description', 'Thêm suất chiếu mới vào hệ thống')

@section('content')
  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-home mr-2"></i>
          Trang chủ
        </a>
      </li>
      <li>
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <a href="{{ route('admin.suat-chieu.index') }}" class="ml-1 text-sm font-medium text-[#a6a6b0] hover:text-white md:ml-2">Suất chiếu</a>
        </div>
      </li>
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Tạo mới</span>
        </div>
      </li>
    </ol>
  </nav>

  <!-- Form Card -->
  <div class="bg-[#151822] border border-[#262833] rounded-xl shadow-lg">
    <div class="px-6 py-4 border-b border-[#262833]">
      <h2 class="text-xl font-semibold text-white">Thông tin suất chiếu</h2>
      <p class="text-sm text-[#a6a6b0] mt-1">Điền thông tin để tạo suất chiếu mới</p>
    </div>
    
    <form action="{{ route('admin.suat-chieu.store') }}" method="POST" class="p-6">
      @csrf
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Phim -->
        <div class="space-y-2">
          <label for="id_phim" class="block text-sm font-medium text-gray-300">
            <i class="fas fa-film mr-2 text-[#F53003]"></i>Phim <span class="text-red-500">*</span>
          </label>
          <select name="id_phim" id="id_phim" class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('id_phim') border-red-500 @enderror" required>
            <option value="">Chọn phim</option>
            @foreach($phim as $movie)
            <option value="{{ $movie->id }}" data-duration="{{ $movie->do_dai }}" {{ old('id_phim') == $movie->id ? 'selected' : '' }}>
              {{ $movie->ten_phim }} ({{ $movie->do_dai }} phút)

            </option>
            @endforeach
          </select>
          @error('id_phim')
            <p class="mt-1 text-sm text-red-500 flex items-center">
              <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
          @enderror
        </div>

        <!-- Phòng Chiếu -->
        <div class="space-y-2">
          <label for="id_phong" class="block text-sm font-medium text-gray-300">
            <i class="fas fa-building mr-2 text-[#F53003]"></i>Phòng Chiếu <span class="text-red-500">*</span>
          </label>
          <select name="id_phong" id="id_phong" class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('id_phong') border-red-500 @enderror" required>
            <option value="">Chọn phòng chiếu</option>
            @foreach($phongChieu as $phong)
            <option value="{{ $phong->id }}" {{ old('id_phong') == $phong->id ? 'selected' : '' }}>
              {{ $phong->ten_phong }} ({{ $phong->suc_chua }} chỗ)
            </option>
            @endforeach
          </select>
          @error('id_phong')
            <p class="mt-1 text-sm text-red-500 flex items-center">
              <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
          @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- Thời Gian Bắt Đầu -->
        <div class="space-y-2">
          <label for="start_time" class="block text-sm font-medium text-gray-300">
            <i class="fas fa-play mr-2 text-[#F53003]"></i>Thời Gian Bắt Đầu <span class="text-red-500">*</span>
          </label>
          <input type="datetime-local" name="start_time" id="start_time" 
                 class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('start_time') border-red-500 @enderror" 
                 value="{{ old('start_time') }}" required>
          @error('start_time')
            <p class="mt-1 text-sm text-red-500 flex items-center">
              <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
          @enderror
        </div>

        <!-- Thời Gian Kết Thúc -->
        <div class="space-y-2">
          <label for="end_time" class="block text-sm font-medium text-gray-300">
            <i class="fas fa-stop mr-2 text-[#F53003]"></i>Thời Gian Kết Thúc <span class="text-red-500">*</span>
            <span class="text-xs text-green-400 ml-2" id="auto-calc-badge" style="display: none;">
              <i class="fas fa-magic mr-1"></i>Tự động tính
            </span>
          </label>
          <input type="datetime-local" name="end_time" id="end_time" 
                 class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('end_time') border-red-500 @enderror" 
                 value="{{ old('end_time') }}" required>
          <p class="text-xs text-[#a6a6b0] mt-1">
            <i class="fas fa-info-circle mr-1"></i>Thời gian kết thúc sẽ được tự động tính khi chọn thời gian bắt đầu và phim
          </p>
          @error('end_time')
            <p class="mt-1 text-sm text-red-500 flex items-center">
              <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
          @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- Trạng Thái -->
        <div class="space-y-2">
          <label for="status" class="block text-sm font-medium text-gray-300">
            <i class="fas fa-toggle-on mr-2 text-[#F53003]"></i>Trạng Thái
          </label>
          <select name="status" id="status" class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200">
            <option value="coming" {{ old('status', 'coming') == 'coming' ? 'selected' : '' }}>Sắp chiếu</option>
            <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Đang chiếu</option>
            <option value="finished" {{ old('status') == 'finished' ? 'selected' : '' }}>Đã kết thúc</option>
          </select>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 mt-8 pt-6 border-t border-[#262833]">
        <a href="{{ route('admin.suat-chieu.index') }}" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center">
          <i class="fas fa-arrow-left mr-2"></i>
          Quay lại
        </a>
        <button type="submit" class="px-6 py-3 bg-[#F53003] hover:bg-[#e02a00] text-white font-semibold rounded-lg transition-colors duration-200 flex items-center justify-center shadow-lg hover:shadow-xl">
          <i class="fas fa-save mr-2"></i>
          Tạo Suất Chiếu
        </button>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const startTimeInput = document.getElementById('start_time');
      const endTimeInput = document.getElementById('end_time');
      const movieSelect = document.getElementById('id_phim');
      const form = startTimeInput.closest('form');
      
      // Tạo thẻ hiển thị lỗi
      let startErrorDiv = document.createElement('div');
      startErrorDiv.id = 'start_time_error';
      startErrorDiv.className = 'mt-1 text-sm text-red-500 hidden';
      startTimeInput.parentElement.appendChild(startErrorDiv);
      
      let endErrorDiv = document.createElement('div');
      endErrorDiv.id = 'end_time_error';
      endErrorDiv.className = 'mt-1 text-sm text-red-500 hidden';
      endTimeInput.parentElement.appendChild(endErrorDiv);
      
      // Set minimum date/time to now (không cho phép chọn thời gian quá khứ)
      const now = new Date();
      // Thêm 1 phút để tránh vấn đề về thời gian chính xác
      now.setMinutes(now.getMinutes() + 1);
      const minDateTime = now.toISOString().slice(0, 16);
      startTimeInput.min = minDateTime;
      
      // Parse datetime-local to Date object (local time, not UTC)
      function parseDateTimeLocal(dateTimeString) {
        if (!dateTimeString) return null;
        const [datePart, timePart] = dateTimeString.split('T');
        if (!datePart || !timePart) return null;
        const [year, month, day] = datePart.split('-').map(Number);
        const [hours, minutes] = timePart.split(':').map(Number);
        return new Date(year, month - 1, day, hours, minutes);
      }
      
      // Kiểm tra thời gian quá khứ
      function checkPastTime(element, isStart) {
        if (!element.value) return true;
        const selectedTime = parseDateTimeLocal(element.value);
        if (!selectedTime) return true;
        const now = new Date();
        
        if (selectedTime <= now) {
          const message = isStart 
            ? 'Không thể tạo suất chiếu vào thời gian quá khứ.' 
            : 'Không thể tạo suất chiếu kết thúc trong quá khứ.';
          showError(element, message);
          return false;
        } else {
          hideError(element);
          return true;
        }
      }
      
      // Kiểm tra giờ hoạt động: 00:00 - 24:00 (cho phép suất chiếu ban đêm)
      function checkBusinessHours(dateTimeString, isStart) {
        if (!dateTimeString) return true;
        
        // Parse datetime-local format (YYYY-MM-DDTHH:mm) as local time
        const [datePart, timePart] = dateTimeString.split('T');
        if (!datePart || !timePart) return true;
        
        const [year, month, day] = datePart.split('-').map(Number);
        const [hours, minutes] = timePart.split(':').map(Number);
        
        // Giờ hoạt động: 00:00 - 24:00 (cho phép bất kỳ giờ nào trong ngày)
        // Bỏ giới hạn 8:00 để cho phép suất chiếu ban đêm từ 00:00 trở đi
        if (hours >= 24 && minutes > 0) {
          return false;
        }
        // Cho phép kết thúc đúng lúc 24:00 (00:00 ngày hôm sau)
        if (!isStart && hours === 24 && minutes === 0) {
          return true;
        }
        return hours < 24 || (hours === 24 && minutes === 0);
      }
      
      function showError(element, message) {
        const errorDiv = document.getElementById(element.id + '_error');
        if (errorDiv) {
          errorDiv.textContent = message;
          errorDiv.classList.remove('hidden');
        }
        element.classList.add('border-red-500');
      }
      
      function hideError(element) {
        const errorDiv = document.getElementById(element.id + '_error');
        if (errorDiv) {
          errorDiv.classList.add('hidden');
        }
        element.classList.remove('border-red-500');
      }
      
      // Function to round duration (làm tròn thời lượng)
      // 1-15 phút -> 30 phút
      // 16-45 phút -> 1 giờ (60 phút)
      // 46-75 phút -> 1.5 giờ (90 phút)
      // 76-105 phút -> 2 giờ (120 phút)
      function roundDuration(minutes) {
        const remainder = minutes % 30;
        if (remainder === 0) {
          return minutes; // Đã là bội số của 30
        } else if (remainder <= 15) {
          return minutes - remainder + 30; // Làm tròn lên đến 30 phút tiếp theo
        } else {
          return minutes - remainder + 60; // Làm tròn lên đến 60 phút (1 giờ tiếp theo)
        }
      }
      
      // Format date to datetime-local format (YYYY-MM-DDTHH:mm) without timezone conversion
      function formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
      }
      
      // Calculate end time based on movie duration and start time
      function calculateEndTime() {
        const selectedOption = movieSelect.options[movieSelect.selectedIndex];
        const duration = parseInt(selectedOption.getAttribute('data-duration')) || 0;
        const startTime = startTimeInput.value;
        const autoCalcBadge = document.getElementById('auto-calc-badge');
        
        if (duration > 0 && startTime) {
          const roundedDuration = roundDuration(duration);
          
          // Parse start time correctly (datetime-local format: YYYY-MM-DDTHH:mm)
          // Create date object treating input as local time, not UTC
          const [datePart, timePart] = startTime.split('T');
          const [year, month, day] = datePart.split('-').map(Number);
          const [hours, minutes] = timePart.split(':').map(Number);
          
          const startDate = new Date(year, month - 1, day, hours, minutes);
          const endDate = new Date(startDate.getTime() + (roundedDuration * 60 * 1000));
          
          // Format to datetime-local format without timezone conversion
          endTimeInput.value = formatDateTimeLocal(endDate);
          
          // Hiển thị badge tự động tính
          if (autoCalcBadge) {
            autoCalcBadge.style.display = 'inline';
          }
          
          // Kiểm tra giờ hoạt động cho end time
          if (!checkBusinessHours(endTimeInput.value, false)) {
            showError(endTimeInput, 'Giờ kết thúc không hợp lệ.');
          } else {
            hideError(endTimeInput);
          }
          
          // Show rounded duration info
          if (roundedDuration !== duration) {
            console.log(`Thời lượng gốc: ${duration} phút → Làm tròn: ${roundedDuration} phút`);
          }
        } else if (!movieSelect.value && startTime) {
          // Nếu chưa chọn phim nhưng đã chọn thời gian bắt đầu
          endTimeInput.value = '';
          if (autoCalcBadge) {
            autoCalcBadge.style.display = 'none';
          }
          // Không hiển thị lỗi, chỉ để trống
        } else {
          // Reset nếu không đủ thông tin
          if (autoCalcBadge) {
            autoCalcBadge.style.display = 'none';
          }
        }
      }
      
      // Kiểm tra thời lượng suất chiếu >= thời lượng phim
      function checkDuration() {
        if (!startTimeInput.value || !endTimeInput.value || !movieSelect.value) {
          return true;
        }
        
        const selectedOption = movieSelect.options[movieSelect.selectedIndex];
        const movieDuration = parseInt(selectedOption.getAttribute('data-duration')) || 0;
        
        if (movieDuration > 0) {
          const startDate = parseDateTimeLocal(startTimeInput.value);
          const endDate = parseDateTimeLocal(endTimeInput.value);
          
          if (!startDate || !endDate) return true;
          
          const showtimeDuration = Math.round((endDate - startDate) / (1000 * 60)); // phút
          
          if (showtimeDuration < movieDuration) {
            showError(endTimeInput, `Thời gian suất chiếu (${showtimeDuration} phút) không thể nhỏ hơn thời lượng phim (${movieDuration} phút).`);
            return false;
          } else {
            hideError(endTimeInput);
            return true;
          }
        }
        return true;
      }
      
      // Validate start time - tự động tính thời gian kết thúc khi chọn thời gian bắt đầu
      startTimeInput.addEventListener('change', function() {
        if (this.value) {
          // Kiểm tra thời gian quá khứ trước
          if (!checkPastTime(this, true)) {
            return;
          }
          
          endTimeInput.min = this.value;
          if (!checkBusinessHours(this.value, true)) {
            showError(this, 'Giờ bắt đầu không hợp lệ.');
          } else {
            hideError(this);
            // Tự động tính thời gian kết thúc khi chọn thời gian bắt đầu
            calculateEndTime();
            checkDuration();
          }
          } else {
          // Nếu xóa thời gian bắt đầu, xóa luôn thời gian kết thúc
          endTimeInput.value = '';
          const autoCalcBadge = document.getElementById('auto-calc-badge');
          if (autoCalcBadge) {
            autoCalcBadge.style.display = 'none';
          }
        }
      });
      
      // End time là readonly, không cho chỉnh sửa thủ công
      // Nhưng vẫn cần validate khi form submit
      
      // Validate khi chọn phim - tự động tính lại thời gian kết thúc nếu đã có thời gian bắt đầu
      movieSelect.addEventListener('change', function() {
        // Nếu đã có thời gian bắt đầu, tự động tính lại thời gian kết thúc
        if (startTimeInput.value) {
          calculateEndTime();
          checkDuration();
        }
      });
      
      // Validate form before submit
      form.addEventListener('submit', function(e) {
        let hasError = false;
        
        // Đảm bảo thời gian kết thúc đã được tính toán
        if (startTimeInput.value && movieSelect.value && !endTimeInput.value) {
          calculateEndTime();
        }
        
        // Kiểm tra thời gian quá khứ
        if (startTimeInput.value && !checkPastTime(startTimeInput, true)) {
          hasError = true;
        }
        
        if (endTimeInput.value && !checkPastTime(endTimeInput, false)) {
          hasError = true;
        }
        
        if (startTimeInput.value && !checkBusinessHours(startTimeInput.value, true)) {
          showError(startTimeInput, 'Giờ bắt đầu không hợp lệ.');
          hasError = true;
        }
        
        if (endTimeInput.value && !checkBusinessHours(endTimeInput.value, false)) {
          showError(endTimeInput, 'Giờ kết thúc không hợp lệ.');
          hasError = true;
        }
        
        // Kiểm tra thời lượng
        if (!checkDuration()) {
          hasError = true;
        }
        
        // Kiểm tra đã chọn phim và thời gian bắt đầu
        if (!movieSelect.value) {
          showError(movieSelect, 'Vui lòng chọn phim trước.');
          hasError = true;
        }
        
        if (!startTimeInput.value) {
          showError(startTimeInput, 'Vui lòng chọn thời gian bắt đầu.');
          hasError = true;
        }
        
        if (hasError) {
          e.preventDefault();
          alert('Vui lòng kiểm tra lại thông tin. Thời gian suất chiếu phải trong tương lai và >= thời lượng phim.');
        }
      });
    });
  </script>
@endsection