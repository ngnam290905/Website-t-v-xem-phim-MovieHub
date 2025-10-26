@extends('layouts.admin')

@section('title', 'Bảng điều khiển - Staff')

@section('content')
  <div class="space-y-6">
    <!-- Staff Info Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 border border-blue-500 rounded-xl p-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-white font-semibold">Chào mừng, {{ auth()->user()->ho_ten }}!</h2>
          <p class="text-blue-100 text-sm">Bạn đang đăng nhập với quyền Staff - Chỉ có thể xem thông tin</p>
        </div>
      </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <a href="{{ route('staff.suat-chieu.index') }}" class="bg-[#151822] border border-[#262833] rounded-xl p-5 hover:border-blue-500 transition-colors group">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
            <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
          </div>
          <div>
            <div class="text-sm text-[#a6a6b0]">Xem Suất Chiếu</div>
            <div class="text-lg font-semibold text-white">Quản lý lịch chiếu</div>
          </div>
        </div>
      </a>

      <a href="{{ route('staff.ghe.index') }}" class="bg-[#151822] border border-[#262833] rounded-xl p-5 hover:border-green-500 transition-colors group">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center group-hover:bg-green-500/30 transition-colors">
            <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
            </svg>
          </div>
          <div>
            <div class="text-sm text-[#a6a6b0]">Xem Ghế</div>
            <div class="text-lg font-semibold text-white">Quản lý ghế ngồi</div>
          </div>
        </div>
      </a>

      <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
              <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div>
            <div class="text-sm text-[#a6a6b0]">Thống kê</div>
            <div class="text-lg font-semibold text-white">Chỉ xem</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl">
      <div class="px-5 py-4 border-b border-[#262833] flex items-center justify-between">
        <h2 class="font-semibold">Hoạt động gần đây</h2>
        <span class="text-sm text-[#a6a6b0]">Chế độ xem</span>
      </div>
      <div class="p-5">
        <div class="space-y-3">
          <div class="flex items-center gap-3 p-3 bg-[#1a1d24] rounded-lg">
            <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="flex-1">
              <div class="text-sm text-white">Xem danh sách suất chiếu</div>
              <div class="text-xs text-[#a6a6b0]">Vừa xong</div>
            </div>
          </div>
          
          <div class="flex items-center gap-3 p-3 bg-[#1a1d24] rounded-lg">
            <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="flex-1">
              <div class="text-sm text-white">Xem thông tin ghế</div>
              <div class="text-xs text-[#a6a6b0]">5 phút trước</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Permission Notice -->
    <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h3 class="text-yellow-400 font-semibold text-sm">Thông báo quyền hạn</h3>
          <p class="text-yellow-200 text-sm mt-1">
            Bạn đang sử dụng tài khoản Staff. Chỉ có thể xem thông tin suất chiếu và ghế. 
            Để có quyền chỉnh sửa, vui lòng liên hệ Admin.
          </p>
        </div>
      </div>
    </div>
  </div>
@endsection
