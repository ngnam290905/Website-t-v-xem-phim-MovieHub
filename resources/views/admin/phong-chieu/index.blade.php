@extends('admin.layout')

@section('title', 'Quản lý phòng chiếu - Admin')
@section('page-title', 'Quản lý phòng chiếu')
@section('page-description', 'Quản lý danh sách phòng chiếu và sơ đồ ghế')

@section('content')
  <div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Quản lý phòng chiếu</h1>
        <p class="text-[#a6a6b0] mt-1">Quản lý danh sách phòng chiếu và sơ đồ ghế</p>
      </div>

      <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
        <a href="{{ route('admin.phong-chieu.peak-hours') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center">
          <i class="fas fa-clock mr-2"></i>
          Cấu hình giờ cao điểm
        </a>
        <a href="{{ route('admin.phong-chieu.create') }}" 
           class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center">
          <i class="fas fa-plus mr-2"></i>
          Thêm phòng mới
        </a>
      </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
      <div class="bg-green-600/20 border border-green-600 text-green-200 px-4 py-3 rounded-lg" role="alert">
        <span class="font-semibold"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
      </div>
    @endif
    @if($errors->has('error'))
      <div class="bg-red-600/20 border border-red-600 text-red-200 px-4 py-3 rounded-lg" role="alert">
        <span class="font-semibold"><i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first('error') }}</span>
      </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Tổng số phòng</div>
        <div class="text-2xl font-bold text-white mt-1">{{ $totalRooms ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Đang hoạt động</div>
        <div class="text-2xl font-bold text-green-400 mt-1">{{ $activeRooms ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Tạm dừng</div>
        <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $pausedRooms ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Suất chiếu hôm nay</div>
        <div class="text-2xl font-bold text-blue-400 mt-1">{{ $showtimesToday ?? 0 }}</div>
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
          <a href="{{ route('admin.phong-chieu.index') }}" 
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
                  <div class="flex justify-center gap-1.5">
                    <a href="{{ route('admin.phong-chieu.show', $phong) }}" 
                       class="btn-table-action btn-table-view" 
                       title="Xem chi tiết">
                      <i class="fas fa-eye text-xs"></i>
                    </a>
                    <a href="{{ route('admin.phong-chieu.edit', $phong) }}" 
                       class="btn-table-action btn-table-edit" 
                       title="Chỉnh sửa">
                      <i class="fas fa-edit text-xs"></i>
                    </a>
                    <button type="button" 
                            class="btn-table-action {{ $phong->status === 'active' ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }}" 
                            onclick="attemptPause({{ $phong->id }}, '{{ $phong->status === 'active' ? 'inactive' : 'active' }}')" 
                            title="{{ $phong->status === 'active' ? 'Tạm dừng' : 'Kích hoạt' }}">
                      <i class="fas fa-{{ $phong->status === 'active' ? 'pause' : 'play' }} text-xs"></i>
                    </button>
                    <form action="{{ route('admin.phong-chieu.destroy', $phong) }}" 
                          method="POST" 
                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng chiếu này?')" 
                          class="room-delete-form inline"
                          data-room-id="{{ $phong->id }}">
                      @csrf
                      @method('DELETE')
                      <button type="submit" 
                              class="btn-table-action btn-table-delete" 
                              title="Xóa">
                        <i class="fas fa-trash text-xs"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <i class="fas fa-video text-4xl text-[#a6a6b0] mb-4"></i>
                    <h3 class="text-lg font-medium text-white mb-2">Chưa có phòng chiếu nào</h3>
                    <p class="text-[#a6a6b0] mb-4">Hãy tạo phòng chiếu đầu tiên để bắt đầu quản lý</p>
                    <a href="{{ route('admin.phong-chieu.create') }}" 
                       class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200">
                      <i class="fas fa-plus mr-2"></i>Tạo phòng mới
                    </a>
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

<!-- Modal cảnh báo không thể thao tác -->
<div id="roomBlockModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/60"></div>
  <div class="relative z-10 max-w-lg mx-auto my-24 bg-[#151822] border border-[#262833] rounded-xl shadow-xl">
    <div class="px-5 py-4 border-b border-[#262833] flex items-center justify-between">
      <h3 class="text-white font-semibold text-lg"><i class="fas fa-exclamation-triangle text-yellow-400 mr-2"></i>Không thể thực hiện</h3>
      <button type="button" class="text-[#a6a6b0] hover:text-white" onclick="closeBlockModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="p-5">
      <p id="roomBlockMessage" class="text-[#d1d5db]"></p>
      <div class="mt-5 text-right">
        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm" onclick="closeBlockModal()">Đã hiểu</button>
      </div>
    </div>
  </div>
  </div>

<script>
// Sử dụng URL sinh từ Blade để tránh sai prefix
const ROOM_STATUS_BASE = "{{ url('admin/phong-chieu') }}";
const ROOM_CAN_MODIFY_BASE = "{{ url('admin/phong-chieu') }}";

function showBlockModal(msg){
  const m = document.getElementById('roomBlockModal');
  const p = document.getElementById('roomBlockMessage');
  if (p) p.textContent = msg || 'Phòng chiếu đang có suất chiếu sắp diễn ra.';
  if (m) m.classList.remove('hidden');
}
function closeBlockModal(){
  const m = document.getElementById('roomBlockModal');
  if (m) m.classList.add('hidden');
}

async function attemptPause(id, status){
  try{
    const res = await fetch(`${ROOM_CAN_MODIFY_BASE}/${id}/can-modify`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
    const data = await res.json();
    if (!data.success) { showBlockModal('Không thể kiểm tra trạng thái phòng chiếu.'); return; }
    if (!data.can_pause) { showBlockModal('Không thể dừng phòng chiếu vì đang có suất chiếu sắp diễn ra.'); return; }
    updateStatus(id, status);
  }catch(e){ showBlockModal('Không thể kiểm tra trạng thái phòng chiếu.'); }
}

async function updateStatus(id, status) {
  if (!confirm('Bạn có chắc chắn muốn thay đổi trạng thái phòng chiếu?')) return;
  const url = `${ROOM_STATUS_BASE}/${id}/status`;
  try {
    const res = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ status })
    });
    let data = null;
    const text = await res.text();
    try { data = JSON.parse(text); } catch { data = { success: res.ok, message: text || 'Unknown' }; }
    if (!res.ok || !data.success) {
      alert(data.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
      return;
    }
    location.reload();
  } catch (e) {
    console.error(e);
    alert('Không thể kết nối máy chủ khi cập nhật trạng thái.');
  }
}

// Intercept delete to pre-check
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.room-delete-form').forEach(function(form){
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const id = form.getAttribute('data-room-id');
      if (!id) { form.submit(); return; }
      try{
        const res = await fetch(`${ROOM_CAN_MODIFY_BASE}/${id}/can-modify`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        if (!data.success) { showBlockModal('Không thể kiểm tra trạng thái phòng chiếu.'); return; }
        if (!data.can_delete) { showBlockModal('Không thể xóa phòng chiếu vì đang có suất chiếu sắp diễn ra.'); return; }
        if (confirm('Bạn có chắc chắn muốn xóa phòng chiếu này? Tất cả ghế và dữ liệu liên quan sẽ bị xóa!')) {
          form.submit();
        }
      }catch(err){ showBlockModal('Không thể kiểm tra trạng thái phòng chiếu.'); }
    });
  });
});
</script>
@endsection

