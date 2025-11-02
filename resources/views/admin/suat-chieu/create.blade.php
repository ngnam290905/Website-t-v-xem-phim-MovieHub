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
          </label>
          <input type="datetime-local" name="end_time" id="end_time" 
                 class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('end_time') border-red-500 @enderror" 
                 value="{{ old('end_time') }}" required>
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
      const movieSelect = document.getElementById('id_phim');
      const startTimeInput = document.getElementById('start_time');
      const endTimeInput = document.getElementById('end_time');
      
      // Set minimum date to today
      const today = new Date().toISOString().slice(0, 16);
      startTimeInput.min = today;
      
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
      
      // Calculate end time based on movie duration and start time
      function calculateEndTime() {
        const selectedOption = movieSelect.options[movieSelect.selectedIndex];
        const duration = parseInt(selectedOption.getAttribute('data-duration')) || 0;
        const startTime = startTimeInput.value;
        
        if (duration > 0 && startTime) {
          const roundedDuration = roundDuration(duration);
          const startDate = new Date(startTime);
          const endDate = new Date(startDate.getTime() + (roundedDuration * 60 * 1000));
          endTimeInput.value = endDate.toISOString().slice(0, 16);
          
          // Show rounded duration info
          if (roundedDuration !== duration) {
            console.log(`Thời lượng gốc: ${duration} phút → Làm tròn: ${roundedDuration} phút`);
          }
        }
      }
      
      // Auto calculate when movie is selected
      movieSelect.addEventListener('change', calculateEndTime);
      
      // Auto calculate when start time changes
      startTimeInput.addEventListener('change', function() {
        if (this.value) {
          endTimeInput.min = this.value;
          calculateEndTime();
        }
      });
    });
  </script>
@endsection