@extends('layouts.admin')

@section('title', 'Quản lý phòng chiếu - Staff')
@section('page-title', 'Quản lý phòng chiếu')
@section('page-description', 'Xem danh sách phòng chiếu')

@section('content')
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Quản lý phòng chiếu</h1>
        <p class="text-[#a6a6b0] mt-1">Xem danh sách phòng chiếu và thông tin chi tiết</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
      <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label for="search" class="block text-sm font-medium text-white mb-2">Tìm kiếm</label>
            <input type="text" 
                   id="search" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Tên phòng..."
                   class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
          </div>
          
          <!-- Type Filter -->
          <div>
            <label for="type" class="block text-sm font-medium text-white mb-2">Loại phòng</label>
            <select id="type" 
                    name="type" 
                    class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
              <option value="">Tất cả loại</option>
              <option value="2D" {{ request('type') === '2D' ? 'selected' : '' }}>2D</option>
              <option value="3D" {{ request('type') === '3D' ? 'selected' : '' }}>3D</option>
              <option value="IMAX" {{ request('type') === 'IMAX' ? 'selected' : '' }}>IMAX</option>
              <option value="4DX" {{ request('type') === '4DX' ? 'selected' : '' }}>4DX</option>
            </select>
          </div>
          
          <!-- Status Filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-white mb-2">Trạng thái</label>
            <select id="status" 
                    name="status" 
                    class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
              <option value="">Tất cả trạng thái</option>
              <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
              <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
            </select>
          </div>
          
          <!-- Sort -->
          <div>
            <label for="sort_by" class="block text-sm font-medium text-white mb-2">Sắp xếp</label>
            <div class="flex space-x-2">
              <select id="sort_by" 
                      name="sort_by" 
                      class="flex-1 px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Tên phòng</option>
                <option value="type" {{ request('sort_by') == 'type' ? 'selected' : '' }}>Loại phòng</option>
                <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Trạng thái</option>
                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
              </select>
              <select name="sort_order" 
                      class="px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>A-Z</option>
                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Z-A</option>
              </select>
            </div>
          </div>
        </div>
        
        <!-- Filter Actions -->
        <div class="flex flex-wrap gap-4">
          <button type="submit" 
                  class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center">
            <i class="fas fa-search mr-2"></i>Tìm kiếm
          </button>
          <a href="{{ route('staff.phong-chieu.index') }}" 
             class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center">
            <i class="fas fa-times mr-2"></i>Xóa bộ lọc
          </a>
          
          @if(request()->hasAny(['search', 'type', 'status', 'sort_by', 'sort_order']))
            <span class="text-sm text-[#a6a6b0] flex items-center">
              <i class="fas fa-filter mr-1"></i>
              Đang áp dụng bộ lọc
            </span>
          @endif
        </div>
      </form>
    </div>

    <!-- Rooms Table -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#262833]">
          <thead class="bg-[#1a1d24]">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Sơ đồ ghế</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số ghế</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Suất chiếu</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
            </tr>
          </thead>
          <tbody class="bg-[#151822] divide-y divide-[#262833]">
            @forelse($phongChieu as $phong)
              <tr class="hover:bg-[#1a1d24] transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-[#F53003]/20 rounded-lg flex items-center justify-center mr-3">
                      <i class="fas fa-video text-[#F53003]"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-white">{{ $phong->name }}</div>
                      <div class="text-sm text-[#a6a6b0]">{{ $phong->audio_system ?? 'Chưa cập nhật' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                    @if($phong->type === '2D') bg-blue-100 text-blue-800
                    @elseif($phong->type === '3D') bg-green-100 text-green-800
                    @elseif($phong->type === 'IMAX') bg-purple-100 text-purple-800
                    @else bg-orange-100 text-orange-800
                    @endif">
                    {{ $phong->type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                  {{ $phong->rows }} hàng × {{ $phong->cols }} ghế
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                  {{ $phong->seats_count }} ghế
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                  {{ $phong->showtimes_count }} suất
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $phong->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $phong->status === 'active' ? 'Hoạt động' : 'Tạm dừng' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <a href="{{ route('staff.phong-chieu.show', $phong) }}" 
                     class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200" 
                     title="Xem chi tiết">
                    <i class="fas fa-eye mr-1"></i>
                    <span class="hidden sm:inline">Xem</span>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <i class="fas fa-video text-4xl text-[#a6a6b0] mb-4"></i>
                    <h3 class="text-lg font-medium text-white mb-2">Chưa có phòng chiếu nào</h3>
                    <p class="text-[#a6a6b0]">Liên hệ admin để thêm phòng chiếu</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-[#262833]">
        {{ $phongChieu->links() }}
      </div>
    </div>
  </div>
@endsection

