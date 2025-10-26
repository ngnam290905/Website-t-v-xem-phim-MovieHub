@extends('layouts.admin')

@section('title', 'Quản lý Ghế')
@section('page-title', 'Quản lý Ghế')
@section('page-description', 'Danh sách và quản lý các ghế trong phòng chiếu')

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
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Ghế</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Danh sách Ghế</h1>
        <p class="text-[#a6a6b0] mt-1">Quản lý tất cả các ghế trong hệ thống</p>
      </div>
      <div class="flex space-x-3">
        <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center shadow-lg hover:shadow-xl" onclick="openGenerateModal()">
          <i class="fas fa-magic mr-2"></i>Tạo Ghế Tự Động
        </button>
        <a href="{{ route('admin.ghe.create') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
          <i class="fas fa-plus mr-2"></i>Tạo Ghế Mới
        </a>
      </div>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Phòng</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số Ghế</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số Hàng</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại Ghế</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hệ Số Giá</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng Thái</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hành Động</th>
            </tr>
          </thead>
          <tbody class="bg-[#151822] divide-y divide-[#262833]">
            @forelse($ghe as $seat)
            <tr class="hover:bg-[#1a1d24] transition-colors duration-200">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $seat->id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $seat->phongChieu->ten_phong }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $seat->so_ghe }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $seat->so_hang }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $seat->loaiGhe->ten_loai ?? 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $seat->loaiGhe->he_so_gia ?? 0 }}x</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $seat->trang_thai ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  {{ $seat->trang_thai ? 'Hoạt động' : 'Tạm dừng' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <a href="{{ route('admin.ghe.show', $seat) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200" title="Xem chi tiết">
                    <i class="fas fa-eye mr-1"></i>
                    Xem
                  </a>
                  <a href="{{ route('admin.ghe.edit', $seat) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded-md transition-colors duration-200" title="Chỉnh sửa">
                    <i class="fas fa-edit mr-1"></i>
                    Sửa
                  </a>
                  <button type="button" class="inline-flex items-center px-3 py-1.5 {{ $seat->trang_thai ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-xs font-medium rounded-md transition-colors duration-200" 
                          onclick="updateStatus({{ $seat->id }}, {{ $seat->trang_thai ? 'false' : 'true' }})" 
                          title="{{ $seat->trang_thai ? 'Tạm dừng' : 'Kích hoạt' }}">
                    <i class="fas fa-{{ $seat->trang_thai ? 'pause' : 'play' }} mr-1"></i>
                    {{ $seat->trang_thai ? 'Dừng' : 'Bật' }}
                  </button>
                  <form action="{{ route('admin.ghe.destroy', $seat) }}" method="POST" 
                        style="display: inline-block;" 
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa ghế này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md transition-colors duration-200" title="Xóa">
                      <i class="fas fa-trash mr-1"></i>
                      Xóa
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="px-6 py-4 text-center text-sm text-[#a6a6b0]">Không có ghế nào</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-[#262833]">
        {{ $ghe->links() }}
      </div>
    </div>
  </div>

<!-- Generate Seats Modal -->
<div id="generateSeatsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-[#151822] border-[#262833]">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-white">Tạo Ghế Tự Động</h3>
                <button onclick="closeGenerateModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.ghe.generate') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="id_phong" class="block text-sm font-medium text-white mb-2">Phòng Chiếu <span class="text-red-500">*</span></label>
                        <select name="id_phong" id="id_phong" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-md text-white focus:outline-none focus:ring-2 focus:ring-[#F53003]" required>
                            <option value="">Chọn phòng chiếu</option>
                            @foreach(\App\Models\PhongChieu::where('trang_thai', 1)->get() as $phong)
                            <option value="{{ $phong->id }}">
                                {{ $phong->ten_phong }} ({{ $phong->so_hang }}x{{ $phong->so_cot }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="id_loai" class="block text-sm font-medium text-white mb-2">Loại Ghế <span class="text-red-500">*</span></label>
                        <select name="id_loai" id="id_loai" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-md text-white focus:outline-none focus:ring-2 focus:ring-[#F53003]" required>
                            <option value="">Chọn loại ghế</option>
                            @foreach(\App\Models\LoaiGhe::all() as $loaiGhe)
                            <option value="{{ $loaiGhe->id }}">
                                {{ $loaiGhe->ten_loai }} - {{ $loaiGhe->he_so_gia }}x
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                        <strong>Lưu ý:</strong> Hành động này sẽ xóa tất cả ghế hiện có trong phòng và tạo mới theo cấu hình phòng.
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors duration-300">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors duration-300">
                        Tạo Ghế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateStatus(id, status) {
    if (confirm('Bạn có chắc chắn muốn thay đổi trạng thái ghế?')) {
        fetch(`/admin/ghe/${id}/status`, {
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

function openGenerateModal() {
    document.getElementById('generateSeatsModal').classList.remove('hidden');
}

function closeGenerateModal() {
    document.getElementById('generateSeatsModal').classList.add('hidden');
}
</script>
@endsection
