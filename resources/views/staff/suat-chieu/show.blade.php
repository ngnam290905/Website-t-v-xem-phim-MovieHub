@extends('layouts.admin')

@section('title', 'Chi tiết Suất Chiếu - Staff')
@section('page-title', 'Chi tiết Suất Chiếu')
@section('page-description', 'Xem thông tin chi tiết suất chiếu')

@section('content')
  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-home mr-2"></i>
          Trang chủ
        </a>
      </li>
      <li class="inline-flex items-center">
        <a href="{{ route('staff.suat-chieu.index') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          Suất chiếu
        </a>
      </li>
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Chi tiết</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-6">
    <!-- Staff Notice -->
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h3 class="text-blue-400 font-semibold text-sm">Chế độ xem</h3>
          <p class="text-blue-200 text-sm mt-1">
            Bạn đang xem thông tin chi tiết suất chiếu. Chỉ có thể xem thông tin, không thể chỉnh sửa.
          </p>
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <div class="flex items-center gap-4">
      <a href="{{ route('staff.suat-chieu.index') }}" class="inline-flex items-center px-4 py-2 bg-[#262833] hover:bg-[#3a3d4a] text-white rounded-lg transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        Quay lại
      </a>
      <h1 class="text-2xl font-bold text-white">Chi tiết Suất Chiếu</h1>
    </div>

    <!-- Suat Chieu Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Basic Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Thông tin cơ bản</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Tên phim</label>
              <p class="text-white">{{ $suatChieu->phim->ten_phim ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Đạo diễn</label>
              <p class="text-white">{{ $suatChieu->phim->dao_dien ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Phòng chiếu</label>
              <p class="text-white">{{ $suatChieu->phongChieu->ten_phong ?? 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Sức chứa</label>
              <p class="text-white">{{ $suatChieu->phongChieu->suc_chua ?? 'N/A' }} ghế</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Thời gian bắt đầu</label>
              <p class="text-white">{{ \Carbon\Carbon::parse($suatChieu->thoi_gian_bat_dau)->format('d/m/Y H:i') }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#a6a6b0] mb-1">Thời gian kết thúc</label>
              <p class="text-white">{{ \Carbon\Carbon::parse($suatChieu->thoi_gian_ket_thuc)->format('d/m/Y H:i') }}</p>
            </div>
          </div>
        </div>

        <!-- Status Info -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h2 class="text-lg font-semibold text-white mb-4">Trạng thái</h2>
          <div class="flex items-center gap-4">
            @if($suatChieu->trang_thai)
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                <i class="fas fa-check-circle mr-2"></i>
                Hoạt động
              </span>
            @else
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                <i class="fas fa-times-circle mr-2"></i>
                Tạm dừng
              </span>
            @endif
            <div class="text-sm text-[#a6a6b0]">
              Cập nhật lần cuối: {{ $suatChieu->updated_at->format('d/m/Y H:i') }}
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Movie Poster -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Poster phim</h3>
          <div class="aspect-[2/3] bg-[#262833] rounded-lg flex items-center justify-center">
            @if($suatChieu->phim && $suatChieu->phim->poster)
              <img src="{{ $suatChieu->phim->poster }}" alt="{{ $suatChieu->phim->ten_phim }}" class="w-full h-full object-cover rounded-lg">
            @else
              <i class="fas fa-film text-4xl text-[#a6a6b0]"></i>
            @endif
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Hành động</h3>
          <div class="space-y-3">
            <a href="{{ route('staff.suat-chieu.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#262833] hover:bg-[#3a3d4a] text-white rounded-lg transition-colors">
              <i class="fas fa-list mr-2"></i>
              Danh sách suất chiếu
            </a>
            <a href="{{ route('staff.ghe.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
              <i class="fas fa-chair mr-2"></i>
              Xem ghế
            </a>
          </div>
        </div>

        <!-- Info -->
        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
              <h3 class="text-yellow-400 font-semibold text-sm">Thông báo</h3>
              <p class="text-yellow-200 text-sm mt-1">
                Bạn chỉ có thể xem thông tin suất chiếu. Để chỉnh sửa, vui lòng liên hệ Admin.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
