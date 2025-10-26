@extends('layouts.admin')

@section('title', 'Quản lý Suất Chiếu')
@section('page-title', 'Quản lý Suất Chiếu')
@section('page-description', 'Danh sách và quản lý các suất chiếu')

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
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Suất chiếu</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Danh sách Suất Chiếu</h1>
        <p class="text-[#a6a6b0] mt-1">Quản lý tất cả các suất chiếu trong hệ thống</p>
      </div>
      <a href="{{ route('admin.suat-chieu.create') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
        <i class="fas fa-plus mr-2"></i>Tạo Suất Chiếu Mới
      </a>
    </div>

    @if(session('success'))
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
          <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
          </svg>
        </span>
      </div>
    @endif

    @if(session('error'))
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
          <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Close</title>
            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
          </svg>
        </span>
      </div>
    @endif

    <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#262833]">
          <thead class="bg-[#1a1d24]">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phim</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng Chiếu</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời Gian Bắt Đầu</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thời Gian Kết Thúc</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng Thái</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hành Động</th>
            </tr>
          </thead>
          <tbody class="bg-[#151822] divide-y divide-[#262833]">
            @forelse($suatChieu as $suat)
            <tr class="hover:bg-[#1a1d24] transition-colors duration-200">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $suat->id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $suat->phim->ten_phim }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $suat->phongChieu->ten_phong }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $suat->thoi_gian_bat_dau->format('d/m/Y H:i') }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $suat->thoi_gian_ket_thuc->format('d/m/Y H:i') }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $suat->trang_thai ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  {{ $suat->trang_thai ? 'Hoạt động' : 'Tạm dừng' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex flex-wrap gap-2">
                  <a href="{{ route('admin.suat-chieu.show', $suat) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200" title="Xem chi tiết">
                    <i class="fas fa-eye mr-1"></i>
                    <span class="hidden sm:inline">Xem</span>
                  </a>
                  <a href="{{ route('admin.suat-chieu.edit', $suat) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded-md transition-colors duration-200" title="Chỉnh sửa">
                    <i class="fas fa-edit mr-1"></i>
                    <span class="hidden sm:inline">Sửa</span>
                  </a>
                  <button type="button" class="inline-flex items-center px-3 py-1.5 {{ $suat->trang_thai ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-xs font-medium rounded-md transition-colors duration-200" 
                          onclick="updateStatus({{ $suat->id }}, {{ $suat->trang_thai ? 'false' : 'true' }})" 
                          title="{{ $suat->trang_thai ? 'Tạm dừng' : 'Kích hoạt' }}">
                    <i class="fas fa-{{ $suat->trang_thai ? 'pause' : 'play' }} mr-1"></i>
                    <span class="hidden sm:inline">{{ $suat->trang_thai ? 'Dừng' : 'Bật' }}</span>
                  </button>
                  <form action="{{ route('admin.suat-chieu.destroy', $suat) }}" method="POST" 
                        style="display: inline-block;" 
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa suất chiếu này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md transition-colors duration-200" title="Xóa">
                      <i class="fas fa-trash mr-1"></i>
                      <span class="hidden sm:inline">Xóa</span>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="px-6 py-4 text-center text-sm text-[#a6a6b0]">Không có suất chiếu nào</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-[#262833]">
        {{ $suatChieu->links() }}
      </div>
    </div>
  </div>

<script>
function updateStatus(id, status) {
    if (confirm('Bạn có chắc chắn muốn thay đổi trạng thái suất chiếu?')) {
        fetch(`/admin/suat-chieu/${id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                trang_thai: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi cập nhật trạng thái');
        });
    }
}
</script>
@endsection
