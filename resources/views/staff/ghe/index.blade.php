@extends('layouts.admin')

@section('title', 'Xem Ghế - Staff')
@section('page-title', 'Xem Ghế')
@section('page-description', 'Danh sách các ghế (Chế độ xem)')

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
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Ghế</span>
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
            Bạn đang xem danh sách ghế. Chỉ có thể xem thông tin, không thể chỉnh sửa.
          </p>
        </div>
      </div>
    </div>

    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Danh sách Ghế</h1>
        <p class="text-[#a6a6b0] mt-1">Xem tất cả các ghế trong hệ thống</p>
      </div>
      <div class="flex items-center gap-2 text-sm text-[#a6a6b0]">
        <i class="fas fa-eye"></i>
        <span>Chế độ xem</span>
      </div>
    </div>

    @if(session('success'))
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
      </div>
    @endif

    @if(session('error'))
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
      </div>
    @endif

    <!-- Filters -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#a6a6b0] mb-2">Tìm kiếm</label>
          <input type="text" placeholder="Tìm theo số ghế..." class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white placeholder-[#a6a6b0] focus:outline-none focus:border-[#F53003]">
        </div>
        <div>
          <label class="block text-sm font-medium text-[#a6a6b0] mb-2">Phòng chiếu</label>
          <select class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
            <option value="">Tất cả phòng</option>
            <option value="1">Phòng 1</option>
            <option value="2">Phòng 2</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-[#a6a6b0] mb-2">Loại ghế</label>
          <select class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
            <option value="">Tất cả loại</option>
            <option value="1">Thường</option>
            <option value="2">VIP</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Ghe Table -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-[#1a1d24]">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số ghế</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hàng</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại ghế</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#262833]">
            @forelse($ghe as $gheItem)
            <tr class="hover:bg-[#1a1d24] transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 bg-[#262833] rounded flex items-center justify-center">
                    <i class="fas fa-chair text-[#a6a6b0]"></i>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-white">{{ $gheItem->so_ghe }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-white">{{ $gheItem->phongChieu->ten_phong ?? 'N/A' }}</div>
                <div class="text-sm text-[#a6a6b0]">{{ $gheItem->phongChieu->suc_chua ?? 'N/A' }} ghế</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-white">Hàng {{ $gheItem->so_hang }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-white">{{ $gheItem->loaiGhe->ten_loai ?? 'N/A' }}</div>
                <div class="text-sm text-[#a6a6b0]">Hệ số: {{ $gheItem->loaiGhe->he_so_gia ?? 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                @if($gheItem->trang_thai)
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    Hoạt động
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-times-circle mr-1"></i>
                    Tạm dừng
                  </span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="{{ route('staff.ghe.show', $gheItem->id) }}" class="text-blue-400 hover:text-blue-300 mr-3">
                  <i class="fas fa-eye"></i> Xem
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="text-[#a6a6b0]">
                  <i class="fas fa-chair text-4xl mb-4"></i>
                  <p class="text-lg">Không có ghế nào</p>
                  <p class="text-sm">Chưa có dữ liệu ghế trong hệ thống</p>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    @if($ghe->hasPages())
    <div class="flex items-center justify-between">
      <div class="text-sm text-[#a6a6b0]">
        Hiển thị {{ $ghe->firstItem() }} đến {{ $ghe->lastItem() }} trong {{ $ghe->total() }} kết quả
      </div>
      <div class="flex space-x-2">
        {{ $ghe->links() }}
      </div>
    </div>
    @endif
  </div>
@endsection
