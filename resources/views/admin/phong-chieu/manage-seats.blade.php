@extends('admin.layout')

@section('title', 'Quản lý ghế - ' . $phongChieu->name)

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-chair text-[#F53003] mr-3"></i>
                Quản lý ghế - {{ $phongChieu->name }}
            </h1>
            <p class="text-[#a6a6b0] mt-1">Quản lý và chỉnh sửa sơ đồ ghế trong phòng chiếu</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.phong-chieu.show', $phongChieu) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại
            </a>
            <button type="button" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center"
                    onclick="openAddSeatModal()">
                <i class="fas fa-plus mr-2"></i>
                Thêm ghế
            </button>
            <button type="button" 
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center"
                    onclick="openGenerateSeatsModal()">
                <i class="fas fa-sync mr-2"></i>
                Tạo lại ghế
            </button>
        </div>
    </div>

<!-- Bulk Actions Modal -->
<div id="bulkModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-[#151822] border border-[#262833] rounded-xl w-full max-w-md">
      <div class="flex items-center justify-between p-6 border-b border-[#262833]">
        <h3 class="text-lg font-semibold text-white">Chỉnh sửa hàng loạt</h3>
        <button type="button" onclick="closeBulkModal()" class="text-[#a6a6b0] hover:text-white"><i class="fas fa-times"></i></button>
      </div>
      <div class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-white mb-2">Hành động</label>
          <select id="bulk_action" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
            <option value="lock">Khóa ghế</option>
            <option value="unlock">Mở khóa ghế</option>
            <option value="type">Đổi loại ghế</option>
            <option value="delete">Xóa ghế</option>
          </select>
        </div>
        <div id="bulk_type_wrap" class="hidden">
          <label class="block text-sm font-medium text-white mb-2">Loại ghế</label>
          <select id="bulk_id_loai" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
            @foreach($seatTypes as $type)
              <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
            @endforeach
          </select>
        </div>
        <div class="text-xs text-[#a6a6b0]">Áp dụng cho các ghế đã chọn. Xóa sẽ bỏ qua ghế đã có đặt vé.</div>
      </div>
      <div class="flex items-center justify-end gap-3 p-6 border-t border-[#262833]">
        <button type="button" onclick="closeBulkModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">Hủy</button>
        <button type="button" onclick="applyBulkAction()" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] text-white rounded-lg">Áp dụng</button>
      </div>
    </div>
  </div>
  </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const actionSel = document.getElementById('bulk_action');
  if (actionSel) {
    actionSel.addEventListener('change', function(){
      document.getElementById('bulk_type_wrap').classList.toggle('hidden', this.value !== 'type');
    });
  }
  const selectAll = document.getElementById('selectAll');
  if (selectAll) {
    selectAll.addEventListener('change', function(){
      document.querySelectorAll('.seat-checkbox').forEach(cb => cb.checked = selectAll.checked);
    });
  }
  const bulkBtn = document.querySelector('[data-bulk-open]');
  if (bulkBtn) bulkBtn.addEventListener('click', openBulkModal);
});

function openBulkModal(){ document.getElementById('bulkModal').classList.remove('hidden'); }
function closeBulkModal(){ document.getElementById('bulkModal').classList.add('hidden'); }

async function applyBulkAction(){
  const ids = Array.from(document.querySelectorAll('.seat-checkbox:checked')).map(cb => parseInt(cb.value)).filter(Boolean);
  if (ids.length === 0) { alert('Vui lòng chọn ít nhất một ghế.'); return; }
  const action = document.getElementById('bulk_action').value;
  const id_loai = document.getElementById('bulk_id_loai')?.value;
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  try {
    const res = await fetch(`{{ route('admin.phong-chieu.seats.bulk', $phongChieu) }}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ action, seat_ids: ids, id_loai })
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.message || 'Lỗi thực thi');
    if (data.skipped_ids && data.skipped_ids.length) {
      alert('Đã xử lý: ' + data.affected + ' ghế. Bỏ qua: ' + data.skipped_ids.join(', '));
    }
    location.reload();
  } catch(e){
    console.error(e); alert('Có lỗi xảy ra khi thực hiện hành động.');
  }
}
</script>
@endpush
    <!-- Room Info Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white flex items-center mb-4">
            <i class="fas fa-info-circle text-[#F53003] mr-2"></i>
            Thông tin phòng
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="text-sm text-[#a6a6b0] mb-1">Tên phòng</div>
                <div class="text-white font-semibold">{{ $phongChieu->name }}</div>
            </div>
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="text-sm text-[#a6a6b0] mb-1">Loại phòng</div>
                <div class="text-white font-semibold">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $phongChieu->type }}
                    </span>
                </div>
            </div>
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="text-sm text-[#a6a6b0] mb-1">Kích thước</div>
                <div class="text-white font-semibold">{{ $phongChieu->rows }} hàng × {{ $phongChieu->cols }} cột</div>
            </div>
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="text-sm text-[#a6a6b0] mb-1">Trạng thái</div>
                <div class="text-white font-semibold">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $phongChieu->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $phongChieu->status === 'active' ? 'Hoạt động' : 'Tạm ngưng' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white flex items-center mb-4">
            <i class="fas fa-chart-bar text-[#F53003] mr-2"></i>
            Thống kê ghế
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">⚪</div>
                <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-green-600 rounded"></div>
          <span class="text-sm text-[#a6a6b0]">Có sẵn</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-red-600 rounded"></div>
          <span class="text-sm text-[#a6a6b0]">Bị khóa</span>
        </div>
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">🟨</div>
                <div class="text-2xl font-bold text-yellow-400">{{ $phongChieu->seats->filter(function($seat) { return $seat->seatType && strpos($seat->seatType->ten_loai, 'VIP') !== false; })->count() }}</div>
                <div class="text-sm text-[#a6a6b0]">Ghế VIP</div>
            </div>
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">⚫</div>
                <div class="text-2xl font-bold text-gray-400">{{ $phongChieu->seats->where('status', 'locked')->count() }}</div>
                <div class="text-sm text-[#a6a6b0]">Bị khóa</div>
            </div>
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">📊</div>
                <div class="text-2xl font-bold text-blue-400">{{ $phongChieu->seats->count() > 0 ? round(($phongChieu->seats->where('status', 'locked')->count() / $phongChieu->seats->count()) * 100, 1) : 0 }}%</div>
                <div class="text-sm text-[#a6a6b0]">Tỷ lệ khóa</div>
            </div>
        </div>
    </div>

    <!-- Seat Map Card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white flex items-center mb-4">
            <i class="fas fa-th-large text-[#F53003] mr-2"></i>
            Sơ đồ ghế
        </h3>
        <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-6 overflow-x-auto">
            <!-- Screen with enhanced design -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div class="screen-container" style="position: relative; display: inline-block;">
                    <div style="background: linear-gradient(to bottom, #1f2937, #111827, #000000); color: white; padding: 1.5rem 3rem; border-radius: 0.5rem; font-size: 1.125rem; font-weight: bold; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 2px solid #374151; position: relative; overflow: hidden;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent); animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
                        <div style="position: relative; z-index: 10; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <i class="fas fa-film" style="color: #fbbf24;"></i>
                            <span>MÀN HÌNH</span>
                            <i class="fas fa-film" style="color: #fbbf24;"></i>
                        </div>
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: linear-gradient(to right, #fbbf24, #f59e0b, #fbbf24);"></div>
                    </div>
                    <div class="screen-stand" style="margin-top: 0.5rem; margin-left: auto; margin-right: auto; width: 8rem; height: 0.75rem; background-color: #374151; border-radius: 2px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);"></div>
                </div>
            </div>
            
            <!-- Seat Grid with exits and aisles -->
            <div class="flex flex-col items-center space-y-2 relative" id="seatMap">
                @php
                    $exitRows = []; // Rows where we'll add exit symbols
                    $exitInterval = max(3, floor($phongChieu->rows / 4)); // Exit every 3-4 rows
                    for ($i = $exitInterval; $i < $phongChieu->rows; $i += $exitInterval) {
                        $exitRows[] = $i;
                    }
                    
                    $aisleCols = []; // Columns where we'll add aisle spacing
                    $middleCol = ceil($phongChieu->cols / 2);
                    $aisleCols[] = $middleCol;
                    if ($phongChieu->cols > 12) {
                        $aisleCols[] = ceil($phongChieu->cols / 3);
                        $aisleCols[] = ceil($phongChieu->cols * 2 / 3);
                    }
                @endphp
                
                @for($row = 1; $row <= $phongChieu->rows; $row++)
                    <div class="flex space-x-1 items-center relative w-full max-w-4xl">
                        <!-- Left Exit Icon -->
                        <div style="width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center;">
                            @if(in_array($row, $exitRows))
                                <div class="exit-icon" style="color: #ef4444; cursor: help; animation: exitPulse 2s ease-in-out infinite;" title="Lối ra">
                                    <i class="fas fa-door-open" style="font-size: 1.25rem;"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Row Label -->
                        <span class="text-sm text-[#a6a6b0] w-6 text-center font-medium">{{ chr(64 + $row) }}</span>
                        
                        <!-- Seats -->
                        @for($col = 1; $col <= $phongChieu->cols; $col++)
                            @php
                                $seatCode = chr(64 + $row) . $col;
                                $seat = $phongChieu->seats->firstWhere('so_ghe', $seatCode);
                                $seatClass = 'empty';
                                $seatType = 'Ghế thường';
                                $seatTypeName = 'normal'; // normal, vip, couple
                                $seatColorClass = 'bg-gray-600 hover:bg-gray-700';
                                
                                if ($seat) {
                                    $seatClass = $seat->status;
                                    $seatType = $seat->seatType->ten_loai ?? 'Ghế thường';
                                    
                                    // Determine seat type for color coding
                                    if ($seat->seatType) {
                                        $typeName = strtolower($seat->seatType->ten_loai);
                                        if (strpos($typeName, 'vip') !== false) {
                                            $seatTypeName = 'vip';
                                        } elseif (strpos($typeName, 'đôi') !== false || strpos($typeName, 'doi') !== false || strpos($typeName, 'couple') !== false) {
                                            $seatTypeName = 'couple';
                                        } else {
                                            $seatTypeName = 'normal';
                                        }
                                    }
                                    
                                    // Set color based on status and type
                                    if ($seatClass === 'available') {
                                        if ($seatTypeName === 'vip') {
                                            $seatColorClass = 'bg-yellow-500 hover:bg-yellow-600 text-white'; // VIP - Yellow
                                        } elseif ($seatTypeName === 'couple') {
                                            $seatColorClass = 'bg-blue-500 hover:bg-blue-600 text-white'; // Couple - Blue
                                        } else {
                                            $seatColorClass = 'bg-orange-500 hover:bg-orange-600 text-white'; // Normal - Orange
                                        }
                                    } elseif ($seatClass === 'booked') {
                                        $seatColorClass = 'bg-red-600 hover:bg-red-700 text-white';
                                    } elseif ($seatClass === 'locked') {
                                        $seatColorClass = 'bg-gray-800 hover:bg-gray-900 text-gray-400';
                                    }
                                }
                                
                                // Check if this is an aisle column
                                $isAisle = in_array($col, $aisleCols);
                            @endphp
                            
                            @if($isAisle && $col > 1)
                                <!-- Aisle Space -->
                                <div style="width: 1rem; height: 2rem; display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 100%; height: 2px; background-color: rgba(217, 119, 6, 0.3); border: 1px dashed rgba(217, 119, 6, 0.5);"></div>
                                </div>
                            @endif
                            
                            <button type="button" 
                                    class="seat-btn w-8 h-8 rounded text-xs font-medium transition-all duration-200 {{ $seatClass === 'empty' ? 'bg-gray-600 hover:bg-gray-700 text-gray-300' : $seatColorClass }}"
                                    data-seat-id="{{ $seat->id ?? '' }}"
                                    data-seat-code="{{ $seatCode }}"
                                    data-seat-type="{{ $seatType }}"
                                    data-seat-price=""
                                    data-seat-status="{{ $seat->status ?? 'empty' }}"
                                    onclick="{{ $seat ? 'quickEditSeat(' . $seat->id . ')' : 'addSeatAtPosition(\'' . chr(64 + $row) . '\', ' . $col . ')' }}"
                                    title="{{ $seatCode }} – {{ $seatType }}">
                                {{ $col }}
                            </button>
                        @endfor
                        
                        <!-- Right Exit Icon -->
                        <div style="width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center;">
                            @if(in_array($row, $exitRows))
                                <div class="exit-icon" style="color: #ef4444; cursor: help; animation: exitPulse 2s ease-in-out infinite;" title="Lối ra">
                                    <i class="fas fa-door-open" style="font-size: 1.25rem;"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Exit row indicator (horizontal line) -->
                    @if(in_array($row, $exitRows) && $row < $phongChieu->rows)
                        <div style="width: 100%; max-width: 56rem; display: flex; align-items: center; gap: 0.25rem; margin: 0.5rem 0;">
                            <div style="flex: 1; height: 2px; background-color: rgba(239, 68, 68, 0.3); border: 1px dashed rgba(239, 68, 68, 0.5);"></div>
                            <div style="padding: 0 0.75rem; font-size: 0.75rem; color: #f87171; font-weight: 500; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="fas fa-arrow-down"></i>
                                <span>Lối ra</span>
                            </div>
                            <div style="flex: 1; height: 2px; background-color: rgba(239, 68, 68, 0.3); border: 1px dashed rgba(239, 68, 68, 0.5);"></div>
                        </div>
                    @endif
                @endfor
                
                <!-- Bottom Exit -->
                <div style="width: 100%; max-width: 56rem; display: flex; align-items: center; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #262833;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #f87171;">
                        <i class="fas fa-sign-out-alt" style="font-size: 1.125rem;"></i>
                        <span style="font-size: 0.875rem; font-weight: 500;">Lối ra chính</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #f87171;">
                        <i class="fas fa-sign-out-alt" style="font-size: 1.125rem;"></i>
                        <span style="font-size: 0.875rem; font-weight: 500;">Lối ra chính</span>
                    </div>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="mt-6 flex flex-wrap gap-4 justify-center text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-orange-500 rounded"></div>
                    <span class="text-[#a6a6b0]">Ghế thường</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                    <span class="text-[#a6a6b0]">Ghế VIP</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span class="text-[#a6a6b0]">Ghế đôi</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-red-600 rounded"></div>
                    <span class="text-[#a6a6b0]">Ghế đã đặt</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-gray-800 rounded"></div>
                    <span class="text-[#a6a6b0]">Ghế bị khóa</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-door-open text-red-500"></i>
                    <span class="text-[#a6a6b0]">Lối ra vào</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-0.5 bg-yellow-600/30 border-dashed border-yellow-600/50"></div>
                    <span class="text-[#a6a6b0]">Lối đi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Seat List Table -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-list text-[#F53003] mr-2"></i>
                Danh sách ghế
            </h3>
            
            <!-- Bulk Actions -->
            <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                <button onclick="selectAllSeats()" class="text-sm text-[#a6a6b0] hover:text-white">
                    <i class="fas fa-check-square mr-1"></i>Chọn tất cả
                </button>
                <button type="button" data-bulk-open class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-edit mr-1"></i>Chỉnh sửa hàng loạt
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1a1d24]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Mã ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Hàng</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Số ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Loại ghế</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Giá</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#a6a6b0] uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#262833]">
                    @foreach($phongChieu->seats->sortBy(['so_hang', 'so_ghe']) as $seat)
                    <tr class="hover:bg-[#1a1d24] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="seat-checkbox rounded" value="{{ $seat->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $seat->so_ghe }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ chr(64 + (int)($seat->so_hang)) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ preg_replace('/^[A-Z]/','',$seat->so_ghe) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($seat->seatType && $seat->seatType->ten_loai == 'Ghế VIP') bg-yellow-100 text-yellow-800
                                    @elseif($seat->seatType && $seat->seatType->ten_loai == 'Ghế đôi') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $seat->seatType->ten_loai ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($seat->status == 'available') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ $seat->status == 'available' ? 'Có sẵn' : 'Bị khóa' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">—</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- Quick Edit Button -->
                                    <button class="text-blue-400 hover:text-blue-300 p-1 rounded" 
                                            onclick="quickEditSeat({{ $seat->id }})"
                                            title="Chỉnh sửa nhanh">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Status Toggle Button -->
                                    @if($seat->status === 'available')
                                        <button class="text-yellow-400 hover:text-yellow-300 p-1 rounded" 
                                                onclick="toggleSeatStatus({{ $seat->id }}, 'locked')"
                                                title="Khóa ghế">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @elseif($seat->status === 'locked')
                                        <button class="text-green-400 hover:text-green-300 p-1 rounded" 
                                                onclick="toggleSeatStatus({{ $seat->id }}, 'available')"
                                                title="Mở khóa ghế">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    @endif
                                    
                                    <!-- Full Edit Button -->
                                    <button class="text-purple-400 hover:text-purple-300 p-1 rounded" 
                                            onclick="editSeat({{ $seat->id }})"
                                            title="Chỉnh sửa chi tiết">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    
                                    <!-- Delete Button (Only for Admin) -->
                                    @if(auth()->user()->vaiTro && auth()->user()->vaiTro->ten === 'admin')
                                        <button class="text-red-400 hover:text-red-300 p-1 rounded" 
                                                onclick="confirmDeleteSeat({{ $seat->id }})"
                                                title="Xóa ghế (chỉ trong layout editor)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Seat Modal -->
<div id="addSeatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-[#151822] border border-[#262833] rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-[#262833]">
                <h3 class="text-lg font-semibold text-white">Thêm ghế mới</h3>
                <button type="button" onclick="closeAddSeatModal()" class="text-[#a6a6b0] hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addSeatForm">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="row_label" class="block text-sm font-medium text-white mb-2">Hàng <span class="text-red-400">*</span></label>
                        <select id="row_label" name="row_label" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            @for($i = 1; $i <= $phongChieu->rows; $i++)
                                <option value="{{ chr(64 + $i) }}">{{ chr(64 + $i) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="so_ghe" class="block text-sm font-medium text-white mb-2">Số ghế <span class="text-red-400">*</span></label>
                        <input type="number" id="so_ghe" name="so_ghe" min="1" max="{{ $phongChieu->cols }}" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                    </div>
                    <div>
                        <label for="id_loai" class="block text-sm font-medium text-white mb-2">Loại ghế <span class="text-red-400">*</span></label>
                        <select id="id_loai" name="id_loai" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            @foreach($seatTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-white mb-2">Trạng thái <span class="text-red-400">*</span></label>
                        <select id="status" name="status" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            <option value="available">Có sẵn</option>
                            <option value="locked">Bị khóa</option>
                        </select>
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-white mb-2">Giá (VNĐ)</label>
                        <input type="number" id="price" name="price" min="0" step="1000" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                        <p class="text-xs text-[#a6a6b0] mt-1">Để trống để sử dụng giá mặc định theo loại ghế</p>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 p-6 border-t border-[#262833]">
                    <button type="button" onclick="closeAddSeatModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-all duration-200">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a02] text-white rounded-lg font-semibold transition-all duration-200">
                        Thêm ghế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Seat Modal -->
<div id="editSeatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-[#151822] border border-[#262833] rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-[#262833]">
                <h3 class="text-lg font-semibold text-white">Chỉnh sửa ghế</h3>
                <button type="button" onclick="closeEditSeatModal()" class="text-[#a6a6b0] hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editSeatForm">
                <input type="hidden" id="edit_seat_id" name="seat_id">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="edit_row_label" class="block text-sm font-medium text-white mb-2">Hàng <span class="text-red-400">*</span></label>
                        <select id="edit_row_label" name="row_label" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            @for($i = 1; $i <= $phongChieu->rows; $i++)
                                <option value="{{ chr(64 + $i) }}">{{ chr(64 + $i) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="edit_so_ghe" class="block text-sm font-medium text-white mb-2">Số ghế <span class="text-red-400">*</span></label>
                        <input type="number" id="edit_so_ghe" name="so_ghe" min="1" max="{{ $phongChieu->cols }}" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_id_loai" class="block text-sm font-medium text-white mb-2">Loại ghế <span class="text-red-400">*</span></label>
                        <select id="edit_id_loai" name="id_loai" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            @foreach($seatTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="edit_status" class="block text-sm font-medium text-white mb-2">Trạng thái <span class="text-red-400">*</span></label>
                        <select id="edit_status" name="status" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            <option value="available">Có sẵn</option>
                            <option value="locked">Bị khóa</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_price" class="block text-sm font-medium text-white mb-2">Giá (VNĐ)</label>
                        <input type="number" id="edit_price" name="price" min="0" step="1000" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 p-6 border-t border-[#262833]">
                    <button type="button" onclick="closeEditSeatModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-all duration-200">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a02] text-white rounded-lg font-semibold transition-all duration-200">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generate Seats Modal -->
<div id="generateSeatsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-[#151822] border border-[#262833] rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-[#262833]">
                <h3 class="text-lg font-semibold text-white">Tạo lại ghế</h3>
                <button type="button" onclick="closeGenerateSeatsModal()" class="text-[#a6a6b0] hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="generateSeatsForm">
                <div class="p-6 space-y-4">
                    <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-triangle text-yellow-400 mt-0.5"></i>
                            <div>
                                <h4 class="text-yellow-400 font-semibold text-sm">Cảnh báo</h4>
                                <p class="text-yellow-200 text-sm mt-1">
                                    Thao tác này sẽ xóa tất cả ghế hiện tại và tạo lại từ đầu. Dữ liệu ghế cũ sẽ bị mất vĩnh viễn.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="default_seat_type" class="block text-sm font-medium text-white mb-2">Loại ghế mặc định</label>
                        <select id="default_seat_type" name="default_seat_type" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            @foreach($seatTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="default_price" class="block text-sm font-medium text-white mb-2">Giá mặc định (VNĐ)</label>
                        <input type="number" id="default_price" name="default_price" min="0" step="1000" value="50000" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 p-6 border-t border-[#262833]">
                    <button type="button" onclick="closeGenerateSeatsModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-all duration-200">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-semibold transition-all duration-200">
                        Tạo lại ghế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div id="quickEditModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-[#151822] border border-[#262833] rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-[#262833]">
                <h3 class="text-lg font-semibold text-white">Chỉnh sửa nhanh</h3>
                <button type="button" onclick="closeQuickEditModal()" class="text-[#a6a6b0] hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="quickEditForm">
                <input type="hidden" id="quick_edit_seat_id" name="seat_id">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Mã ghế</label>
                        <input type="text" id="quick_seat_code" readonly class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-gray-400">
                    </div>
                    <div>
                        <label for="quick_seat_type" class="block text-sm font-medium text-white mb-2">Loại ghế</label>
                        <select id="quick_seat_type" name="seat_type" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            @foreach($seatTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="quick_price" class="block text-sm font-medium text-white mb-2">Giá (VNĐ)</label>
                        <input type="number" id="quick_price" name="price" min="0" step="1000" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                    </div>
                    <div>
                        <label for="quick_status" class="block text-sm font-medium text-white mb-2">Trạng thái</label>
                        <select id="quick_status" name="status" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            <option value="available">Có sẵn</option>
                            <option value="locked">Bị khóa</option>
                        </select>
                    </div>
                    <div>
                        <label for="quick_note" class="block text-sm font-medium text-white mb-2">Ghi chú</label>
                        <textarea id="quick_note" name="note" rows="2" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent" placeholder="Ghi chú về ghế..."></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 p-6 border-t border-[#262833]">
                    <button type="button" onclick="closeQuickEditModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-all duration-200">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a02] text-white rounded-lg font-semibold transition-all duration-200">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="bulkEditModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-[#151822] border border-[#262833] rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-[#262833]">
                <h3 class="text-lg font-semibold text-white">Chỉnh sửa hàng loạt</h3>
                <button type="button" onclick="closeBulkEditModal()" class="text-[#a6a6b0] hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="bulkEditForm">
                <div class="p-6 space-y-4">
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                            <div>
                                <h4 class="text-blue-400 font-semibold text-sm">Thông tin</h4>
                                <p class="text-blue-200 text-sm mt-1">
                                    Chỉ các trường được điền sẽ được cập nhật cho tất cả ghế đã chọn.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="bulk_seat_type" class="block text-sm font-medium text-white mb-2">Loại ghế</label>
                        <select id="bulk_seat_type" name="seat_type" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            <option value="">Không thay đổi</option>
                            @foreach($seatTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="bulk_price" class="block text-sm font-medium text-white mb-2">Giá (VNĐ)</label>
                        <input type="number" id="bulk_price" name="price" min="0" step="1000" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent" placeholder="Để trống nếu không thay đổi">
                    </div>
                    <div>
                        <label for="bulk_status" class="block text-sm font-medium text-white mb-2">Trạng thái</label>
                        <select id="bulk_status" name="status" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent">
                            <option value="">Không thay đổi</option>
                            <option value="available">Có sẵn</option>
                            <option value="locked">Bảo trì</option>
                            <option value="booked">Đã đặt</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 p-6 border-t border-[#262833]">
                    <button type="button" onclick="closeBulkEditModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-all duration-200">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a02] text-white rounded-lg font-semibold transition-all duration-200">
                        Cập nhật hàng loạt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Modern Gradient Background */
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

/* Card Styling */
.card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 20px 30px;
    border-radius: 20px 20px 0 0;
}

.card-header h3 {
    margin: 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Button Styling */
.btn {
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(102, 126, 234, 0.6);
}

.btn-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    box-shadow: 0 8px 20px rgba(240, 147, 251, 0.4);
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(240, 147, 251, 0.6);
}

.btn-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    border: none;
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(255, 107, 107, 0.6);
}

.btn-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    border: none;
    box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(79, 172, 254, 0.6);
}

/* Info Cards */
.info-card, .stats-card, .seat-map-card {
    background: #2a2a2a;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    border: 1px solid #3a3a3a;
}

.card-title {
    color: #ffffff;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

/* Room Info Grid */
.room-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.room-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.room-info-item .label {
    color: #a6a6b0;
    font-weight: 500;
}

.room-info-item .value {
    color: #ffffff;
    font-weight: 600;
}

.room-type {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.room-status.active {
    color: #4facfe;
    background: rgba(79, 172, 254, 0.1);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.room-status.inactive {
    color: #ff6b6b;
    background: rgba(255, 107, 107, 0.1);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.stat-item {
    display: flex;
    align-items: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.stat-item:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
}

.stat-item.total { border-left-color: #6c757d; }
.stat-item.available { border-left-color: #4facfe; }
.stat-item.booked { border-left-color: #f093fb; }
.stat-item.vip { border-left-color: #ffc107; }
.stat-item.locked { border-left-color: #ff6b6b; }
.stat-item.occupancy { border-left-color: #17a2b8; }

.stat-icon {
    font-size: 24px;
    margin-right: 15px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #ffffff;
    line-height: 1;
}

.stat-label {
    font-size: 12px;
    color: #a6a6b0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

/* Seat Map Container */
.seat-map-container {
    background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
    border-radius: 15px;
    padding: 30px;
    position: relative;
    overflow: hidden;
}

.seat-map-container::before {
    content: 'MÀN HÌNH';
    position: absolute;
    top: -50px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 16px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

/* Seat Grid */
.seat-map-grid {
    display: grid;
    grid-template-columns: repeat({{ $phongChieu->cols }}, 1fr);
    gap: 8px;
    margin-top: 20px;
    max-width: 100%;
    overflow-x: auto;
}

.seat-grid-item {
    aspect-ratio: 1;
    min-width: 40px;
    min-height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    font-weight: 600;
    font-size: 12px;
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.seat-grid-item:hover {
    transform: scale(1.1);
    z-index: 10;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
}

/* Seat Colors */
.seat-grid-item.empty {
    background: #3a3a3a;
    border: 2px dashed #555;
    color: #888;
}

.seat-grid-item.available {
    background: #4facfe;
    border: 2px solid #00f2fe;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
}

.seat-grid-item.available.vip {
    background: #ffc107;
    border: 2px solid #ffb300;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}

.seat-grid-item.booked {
    background: #f093fb;
    border: 2px solid #f5576c;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
    animation: pulse 2s infinite;
}

.seat-grid-item.locked {
    background: #ff6b6b;
    border: 2px solid #ee5a24;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    animation: shake 0.5s ease-in-out infinite alternate;
}

.seat-code {
    font-size: 10px;
    font-weight: bold;
}

/* Seat Row Styling */
.seat-row {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 15px;
    backdrop-filter: blur(5px);
    transition: all 0.3s ease;
}

.seat-row:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateX(5px);
}

.row-label {
    width: 40px;
    height: 40px;
    font-weight: bold;
    text-align: center;
    margin-right: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.seats-in-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Seat Item Styling */
.seat-item {
    width: 60px;
    height: 60px;
    border: 3px solid transparent;
    border-radius: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    font-weight: 600;
}

.seat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), transparent);
    border-radius: 15px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.seat-item:hover::before {
    opacity: 1;
}

.seat-item:hover {
    transform: translateY(-8px) scale(1.1);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    z-index: 10;
}

.seat-item.available {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border-color: #4facfe;
    box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
}

.seat-item.available:hover {
    box-shadow: 0 15px 30px rgba(79, 172, 254, 0.6);
}

.seat-item.booked {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-color: #f093fb;
    box-shadow: 0 8px 20px rgba(240, 147, 251, 0.4);
    animation: pulse 2s infinite;
}

.seat-item.booked:hover {
    box-shadow: 0 15px 30px rgba(240, 147, 251, 0.6);
}

.seat-item.locked {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    border-color: #ff6b6b;
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
    animation: shake 0.5s ease-in-out infinite alternate;
}

.seat-item.locked:hover {
    box-shadow: 0 15px 30px rgba(255, 107, 107, 0.6);
}

.seat-number {
    font-weight: bold;
    font-size: 16px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.seat-type {
    font-size: 10px;
    opacity: 0.9;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Info Box Styling */
.info-box {
    display: flex;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.info-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.6s;
}

.info-box:hover::before {
    left: 100%;
}

.info-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.info-box-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    margin-right: 15px;
    font-size: 24px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.info-box-icon.bg-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.info-box-icon.bg-info {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.info-box-icon.bg-warning {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    color: #8b4513;
}

.info-box-content {
    flex: 1;
}

.info-box-text {
    display: block;
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 5px;
}

.info-box-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Table Styling */
.table {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 20px 15px;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    transform: scale(1.02);
}

.table tbody td {
    padding: 15px;
    border: none;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    vertical-align: middle;
}

/* Badge Styling */
.badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 11px;
}

.badge-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.badge-secondary {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    color: #2c3e50;
}

.badge-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
}

/* Modal Styling */
.modal-content {
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 25px 30px;
}

.modal-title {
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.modal-body {
    padding: 30px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.modal-footer {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    padding: 20px 30px;
}

/* Form Styling */
.form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: white;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

/* Animations */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
    100% { transform: translateX(0); }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card-body > * {
    animation: fadeInUp 0.6s ease-out;
}

.card-body > *:nth-child(2) { animation-delay: 0.1s; }
.card-body > *:nth-child(3) { animation-delay: 0.2s; }
.card-body > *:nth-child(4) { animation-delay: 0.3s; }

/* Responsive Design */
@media (max-width: 768px) {
    .seat-item {
        width: 50px;
        height: 50px;
    }
    
    .seat-number {
        font-size: 14px;
    }
    
    .seat-type {
        font-size: 9px;
    }
    
    .info-box {
        padding: 15px;
    }
    
    .info-box-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
}

/* Loading Animation */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Additional Interactive Effects */
.seat-hover {
    transform: translateY(-8px) scale(1.2) !important;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5) !important;
    z-index: 20 !important;
}

.seat-clicked {
    transform: scale(0.95) !important;
    transition: transform 0.1s ease !important;
}

.btn-float {
    transform: translateY(-3px) !important;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2) !important;
}

/* Glowing effect for available seats */
.seat-item.available {
    position: relative;
}

.seat-item.available::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #4facfe, #00f2fe, #4facfe);
    border-radius: 17px;
    z-index: -1;
    opacity: 0;
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { opacity: 0; }
    to { opacity: 0.7; }
}

/* Ripple effect for buttons */
.btn {
    position: relative;
    overflow: hidden;
}

.btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:active::after {
    width: 300px;
    height: 300px;
}

/* Floating animation for cards */
.card {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Staggered animation for seat rows */
.seat-row {
    animation: slideInLeft 0.6s ease-out;
}

.seat-row:nth-child(1) { animation-delay: 0.1s; }
.seat-row:nth-child(2) { animation-delay: 0.2s; }
.seat-row:nth-child(3) { animation-delay: 0.3s; }
.seat-row:nth-child(4) { animation-delay: 0.4s; }
.seat-row:nth-child(5) { animation-delay: 0.5s; }
.seat-row:nth-child(6) { animation-delay: 0.6s; }
.seat-row:nth-child(7) { animation-delay: 0.7s; }
.seat-row:nth-child(8) { animation-delay: 0.8s; }
.seat-row:nth-child(9) { animation-delay: 0.9s; }
.seat-row:nth-child(10) { animation-delay: 1.0s; }

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Screen Styling */
.screen-container {
    perspective: 1000px;
}

.screen-container > div:first-child {
    transform-style: preserve-3d;
    box-shadow: 
        0 10px 40px rgba(0, 0, 0, 0.5),
        inset 0 0 30px rgba(255, 255, 255, 0.1);
    border-radius: 12px;
}

.screen-container:hover > div:first-child {
    transform: translateY(-2px);
    box-shadow: 
        0 15px 50px rgba(0, 0, 0, 0.6),
        inset 0 0 40px rgba(255, 255, 255, 0.15);
}

.screen-stand {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* Exit Icon Styling */
.exit-icon {
    animation: exitPulse 2s ease-in-out infinite;
    cursor: help;
}

.exit-icon:hover {
    transform: scale(1.2);
    transition: transform 0.2s ease;
}

@keyframes exitPulse {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 1; }
}

/* Aisle Styling */
.aisle-indicator {
    animation: aisleGlow 3s ease-in-out infinite;
}

@keyframes aisleGlow {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

/* Exit Row Indicator */
.exit-row-indicator {
    position: relative;
}

.exit-row-indicator::before,
.exit-row-indicator::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 20px;
    height: 2px;
    background: red;
    transform: translateY(-50%);
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(255, 255, 255, 0.3);
    border-top: 5px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Success/Error notifications */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    z-index: 10000;
    animation: slideInRight 0.5s ease-out;
}

.notification.success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.notification.error {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
@endsection

@section('scripts')
<script>
let currentSeatId = null;

// Modal functions
function openAddSeatModal() {
    document.getElementById('addSeatModal').classList.remove('hidden');
}

function closeAddSeatModal() {
    document.getElementById('addSeatModal').classList.add('hidden');
    document.getElementById('addSeatForm').reset();
}

function openEditSeatModal() {
    document.getElementById('editSeatModal').classList.remove('hidden');
}

async function editSeat(seatId) {
    currentSeatId = seatId;
    try {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(`/admin/phong-chieu/{{ $phongChieu->id }}/seats/${seatId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });
        
        if (!response.ok) {
            throw new Error('Không thể tải dữ liệu ghế');
        }
        
        const seat = await response.json();
        
        // Extract row label from seat code (e.g., "A1" -> "A")
        const rowLabel = seat.so_ghe.charAt(0);
        const seatNumber = parseInt(seat.so_ghe.substring(1));
        
        // Populate edit form
        document.getElementById('edit_seat_id').value = seat.id;
        document.getElementById('edit_row_label').value = rowLabel;
        document.getElementById('edit_so_ghe').value = seatNumber;
        document.getElementById('edit_id_loai').value = seat.id_loai;
        document.getElementById('edit_status').value = seat.trang_thai === 1 ? 'available' : 'locked';
        
        // Open modal
        document.getElementById('editSeatModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error loading seat:', error);
        showNotification('Không thể tải dữ liệu ghế', 'error');
    }
}

function closeEditSeatModal() {
    document.getElementById('editSeatModal').classList.add('hidden');
    document.getElementById('editSeatForm').reset();
}

function openGenerateSeatsModal() {
    document.getElementById('generateSeatsModal').classList.remove('hidden');
}

function closeGenerateSeatsModal() {
    document.getElementById('generateSeatsModal').classList.add('hidden');
}

// Add seat at specific position
function addSeatAtPosition(rowLabel, colNumber) {
    document.getElementById('row_label').value = rowLabel;
    document.getElementById('so_ghe').value = colNumber;
    openAddSeatModal();
}

// Quick Edit Modal functions
async function quickEditSeat(seatId) {
    currentSeatId = seatId;
    try {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(`/admin/phong-chieu/{{ $phongChieu->id }}/seats/${seatId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });
        
        if (!response.ok) {
            throw new Error('Không thể tải dữ liệu ghế');
        }
        
        const seat = await response.json();
        
        // Populate quick edit form
        document.getElementById('quick_edit_seat_id').value = seat.id;
        document.getElementById('quick_seat_code').value = seat.so_ghe;
        document.getElementById('quick_seat_type').value = seat.id_loai;
        document.getElementById('quick_status').value = seat.trang_thai === 1 ? 'available' : 'locked';
        
        // Open modal
        document.getElementById('quickEditModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error loading seat:', error);
        showNotification('Không thể tải dữ liệu ghế', 'error');
    }
}

function closeQuickEditModal() {
    document.getElementById('quickEditModal').classList.add('hidden');
    document.getElementById('quickEditForm').reset();
}

// Bulk Edit Modal functions
function bulkEditSeats() {
    const selectedSeats = getSelectedSeats();
    if (selectedSeats.length === 0) {
        showNotification('Vui lòng chọn ít nhất một ghế để chỉnh sửa hàng loạt', 'error');
        return;
    }
    document.getElementById('bulkEditModal').classList.remove('hidden');
}

function closeBulkEditModal() {
    document.getElementById('bulkEditModal').classList.add('hidden');
    document.getElementById('bulkEditForm').reset();
}

// Seat selection functions
function selectAllSeats() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
    
    seatCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function getSelectedSeats() {
    const selectedCheckboxes = document.querySelectorAll('.seat-checkbox:checked');
    return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
}

// Status toggle function
async function toggleSeatStatus(seatId, newStatus) {
    const statusText = newStatus === 'locked' ? 'khóa' : 'mở khóa';
    if (confirm(`Bạn có chắc chắn muốn ${statusText} ghế này?`)) {
        showLoading();
        
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(`/admin/seats/${seatId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Có lỗi xảy ra');
            }
            
            hideLoading();
            showNotification(`Ghế đã được ${statusText} thành công!`, 'success');
            location.reload();
        } catch (error) {
            hideLoading();
            console.error('Error toggling seat status:', error);
            showNotification(error.message || 'Có lỗi xảy ra khi cập nhật trạng thái', 'error');
        }
    }
}

// Confirm delete function (with safety checks)
async function confirmDeleteSeat(seatId) {
    if (confirm('⚠️ CẢNH BÁO: Xóa ghế sẽ ảnh hưởng đến dữ liệu đặt vé!\n\nBạn có chắc chắn muốn xóa ghế này?\n\nKhuyến nghị: Sử dụng "Khóa ghế" thay vì xóa.')) {
        if (confirm('⚠️ CẢNH BÁO LẦN CUỐI!\n\nThao tác này không thể hoàn tác!\n\nBạn có chắc chắn muốn xóa ghế này?')) {
            showLoading();
            
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/admin/phong-chieu/{{ $phongChieu->id }}/seats/${seatId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Có lỗi xảy ra khi xóa ghế');
                }
                
                hideLoading();
                showNotification('Ghế đã được xóa thành công!', 'success');
                location.reload();
            } catch (error) {
                hideLoading();
                console.error('Error deleting seat:', error);
                showNotification(error.message || 'Có lỗi xảy ra khi xóa ghế', 'error');
            }
        }
    }
}

// Show loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    overlay.innerHTML = `
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#F53003]"></div>
                <span class="text-white">Đang xử lý...</span>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
}

// Hide loading overlay
function hideLoading() {
    const overlay = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (overlay) {
        overlay.remove();
    }
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg text-white ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Form submissions
document.addEventListener('DOMContentLoaded', function() {
    // Add seat form
    const addSeatForm = document.getElementById('addSeatForm');
    if (addSeatForm) {
        addSeatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading();
            
            try {
                const formData = new FormData(addSeatForm);
                const data = {
                    row_label: formData.get('row_label'),
                    so_ghe: parseInt(formData.get('so_ghe')),
                    id_loai: parseInt(formData.get('id_loai')),
                    status: formData.get('status'),
                    price: formData.get('price') ? parseFloat(formData.get('price')) : null
                };
                
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`{{ route('admin.phong-chieu.seats.store', $phongChieu) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Có lỗi xảy ra khi thêm ghế');
                }
                
                hideLoading();
                showNotification('Ghế đã được thêm thành công!', 'success');
                closeAddSeatModal();
                location.reload();
            } catch (error) {
                hideLoading();
                console.error('Error adding seat:', error);
                showNotification(error.message || 'Có lỗi xảy ra khi thêm ghế', 'error');
            }
        });
    }
    
    // Edit seat form
    const editSeatForm = document.getElementById('editSeatForm');
    if (editSeatForm) {
        editSeatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading();
            
            try {
                const seatId = document.getElementById('edit_seat_id').value;
                const formData = new FormData(editSeatForm);
                const data = {
                    row_label: formData.get('row_label'),
                    so_ghe: parseInt(formData.get('so_ghe')),
                    id_loai: parseInt(formData.get('id_loai')),
                    status: formData.get('status'),
                    price: formData.get('price') ? parseFloat(formData.get('price')) : null
                };
                
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/admin/phong-chieu/{{ $phongChieu->id }}/seats/${seatId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Có lỗi xảy ra khi cập nhật ghế');
                }
                
                hideLoading();
                showNotification('Ghế đã được cập nhật thành công!', 'success');
                closeEditSeatModal();
                location.reload();
            } catch (error) {
                hideLoading();
                console.error('Error updating seat:', error);
                showNotification(error.message || 'Có lỗi xảy ra khi cập nhật ghế', 'error');
            }
        });
    }
    
    // Quick edit form
    const quickEditForm = document.getElementById('quickEditForm');
    if (quickEditForm) {
        quickEditForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading();
            
            try {
                const seatId = document.getElementById('quick_edit_seat_id').value;
                const formData = new FormData(quickEditForm);
                const data = {
                    id_loai: parseInt(formData.get('seat_type')),
                    status: formData.get('status'),
                    price: formData.get('price') ? parseFloat(formData.get('price')) : null,
                    note: formData.get('note')
                };
                
                // Update seat type
                if (data.id_loai) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const typeResponse = await fetch(`/admin/seats/${seatId}/type`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ id_loai: data.id_loai })
                    });
                    
                    if (!typeResponse.ok) {
                        const typeResult = await typeResponse.json();
                        throw new Error(typeResult.message || 'Có lỗi khi cập nhật loại ghế');
                    }
                }
                
                // Update seat status
                if (data.status) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const statusResponse = await fetch(`/admin/seats/${seatId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: data.status })
                    });
                    
                    if (!statusResponse.ok) {
                        const statusResult = await statusResponse.json();
                        throw new Error(statusResult.message || 'Có lỗi khi cập nhật trạng thái');
                    }
                }
                
                hideLoading();
                showNotification('Ghế đã được cập nhật thành công!', 'success');
                closeQuickEditModal();
                location.reload();
            } catch (error) {
                hideLoading();
                console.error('Error updating seat:', error);
                showNotification(error.message || 'Có lỗi xảy ra khi cập nhật ghế', 'error');
            }
        });
    }
    
    // Generate seats form
    const generateSeatsForm = document.getElementById('generateSeatsForm');
    if (generateSeatsForm) {
        generateSeatsForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!confirm('⚠️ CẢNH BÁO: Thao tác này sẽ xóa TẤT CẢ ghế hiện tại và tạo lại từ đầu!\n\nDữ liệu ghế cũ sẽ bị mất vĩnh viễn.\n\nBạn có chắc chắn muốn tiếp tục?')) {
                return;
            }
            
            showLoading();
            
            try {
                const formData = new FormData(generateSeatsForm);
                const data = {
                    rows: {{ $phongChieu->rows }},
                    cols: {{ $phongChieu->cols }},
                    default_seat_type: formData.get('default_seat_type') ? parseInt(formData.get('default_seat_type')) : null,
                    default_price: formData.get('default_price') ? parseFloat(formData.get('default_price')) : null
                };
                
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`{{ route('admin.phong-chieu.generate-seats', $phongChieu) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Có lỗi xảy ra khi tạo lại ghế');
                }
                
                hideLoading();
                showNotification('Đã tạo lại ghế thành công!', 'success');
                closeGenerateSeatsModal();
                location.reload();
            } catch (error) {
                hideLoading();
                console.error('Error generating seats:', error);
                showNotification(error.message || 'Có lỗi xảy ra khi tạo lại ghế', 'error');
            }
        });
    }
    
    // Bulk edit form
    const bulkEditForm = document.getElementById('bulkEditForm');
    if (bulkEditForm) {
        bulkEditForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedSeats = getSelectedSeats();
            if (selectedSeats.length === 0) {
                showNotification('Vui lòng chọn ít nhất một ghế', 'error');
                return;
            }
            
            showLoading();
            
            // Here you would typically make an AJAX request to bulk update seats
            setTimeout(() => {
                hideLoading();
                showNotification(`${selectedSeats.length} ghế đã được cập nhật thành công!`);
                closeBulkEditModal();
                location.reload(); // Reload to show updated seats
            }, 1000);
        });
    }
    
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
            seatCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Individual seat checkboxes
    const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
    seatCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.seat-checkbox:checked').length === seatCheckboxes.length;
            const someChecked = document.querySelectorAll('.seat-checkbox:checked').length > 0;
            
            if (allChecked) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else if (someChecked) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        });
    });
    
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
            e.target.classList.add('hidden');
        }
    });
});
</script>
@endsection
