@extends('admin.layout')

@section('title', 'Tạo phòng chiếu mới - Admin')
@section('page-title', 'Tạo phòng chiếu mới')
@section('page-description', 'Thêm phòng chiếu mới vào hệ thống')

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-8">
      <form action="{{ route('admin.phong-chieu.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-info-circle mr-2 text-[#F53003]"></i>
            Thông tin cơ bản
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Room Name -->
            <div class="space-y-2">
              <label for="name" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-video mr-2 text-[#F53003]"></i>Tên phòng
              </label>
              <input type="text" 
                     name="name" 
                     id="name" 
                     class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('name') border-red-500 @enderror" 
                     value="{{ old('name') }}" 
                     placeholder="Ví dụ: Phòng 1, IMAX 1..." 
                     required>
              @error('name')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
              @enderror
            </div>

            <!-- Room Type -->
            <div class="space-y-2">
              <label for="type" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-film mr-2 text-[#F53003]"></i>Loại phòng
              </label>
              <select name="type" 
                      id="type" 
                      class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('type') border-red-500 @enderror" 
                      required>
                <option value="">Chọn loại phòng</option>
                <option value="2D" {{ old('type') == '2D' ? 'selected' : '' }}>2D - Chiếu phim 2D thông thường</option>
                <option value="3D" {{ old('type') == '3D' ? 'selected' : '' }}>3D - Chiếu phim 3D</option>
                <option value="IMAX" {{ old('type') == 'IMAX' ? 'selected' : '' }}>IMAX - Màn hình lớn, âm thanh cao cấp</option>
                <option value="4DX" {{ old('type') == '4DX' ? 'selected' : '' }}>4DX - Trải nghiệm đa giác quan</option>
              </select>
              @error('type')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <label for="description" class="block text-sm font-medium text-gray-300">
              <i class="fas fa-align-left mr-2 text-[#F53003]"></i>Mô tả phòng
            </label>
            <textarea name="description" 
                      id="description" 
                      rows="3" 
                      class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('description') border-red-500 @enderror" 
                      placeholder="Mô tả về phòng chiếu, đặc điểm nổi bật...">{{ old('description') }}</textarea>
            @error('description')
              <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Seat Layout Configuration -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-chair mr-2 text-[#F53003]"></i>
            Cấu hình sơ đồ ghế
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Number of Rows -->
            <div class="space-y-2">
              <label for="rows" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-sort-numeric-up mr-2 text-[#F53003]"></i>Số hàng ghế
              </label>
              <input type="number" 
                     name="rows" 
                     id="rows" 
                     min="1" 
                     max="20" 
                     class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('rows') border-red-500 @enderror" 
                     value="{{ old('rows', 10) }}" 
                     required>
              @error('rows')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
              @enderror
              <p class="text-xs text-[#a6a6b0]">Tối đa 20 hàng (A, B, C...)</p>
            </div>

            <!-- Number of Seats per Row -->
            <div class="space-y-2">
              <label for="cols" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-sort-numeric-up-alt mr-2 text-[#F53003]"></i>Số ghế mỗi hàng
              </label>
              <input type="number" 
                     name="cols" 
                     id="cols" 
                     min="1" 
                     max="30" 
                     class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('cols') border-red-500 @enderror" 
                     value="{{ old('cols', 15) }}" 
                     required>
              @error('cols')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
              @enderror
              <p class="text-xs text-[#a6a6b0]">Tối đa 30 ghế mỗi hàng</p>
            </div>

            <!-- Default Seat Type -->
            <div class="space-y-2">
              <label for="seat_type" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-star mr-2 text-[#F53003]"></i>Loại ghế mặc định
              </label>
              <select name="seat_type" 
                      id="seat_type" 
                      class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200">
                <option value="normal" {{ old('seat_type', 'normal') == 'normal' ? 'selected' : '' }}>Ghế thường</option>
                <option value="vip" {{ old('seat_type') == 'vip' ? 'selected' : '' }}>Ghế VIP</option>
                <option value="couple" {{ old('seat_type') == 'couple' ? 'selected' : '' }}>Ghế đôi</option>
              </select>
              <p class="text-xs text-[#a6a6b0]">Có thể thay đổi sau khi tạo</p>
            </div>
            <!-- Layout preset -->
            <div class="space-y-2">
              <label for="layout_preset" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-shapes mr-2 text-[#F53003]"></i>Kiểu ma trận ghế
              </label>
              <select name="layout_preset" id="layout_preset" class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200">
                <option value="grid">Lưới chuẩn</option>
                <option value="arc">Vòng cung</option>
                <option value="staggered">So le</option>
                <option value="cluster">Cụm/khu vực</option>
              </select>
              <p class="text-xs text-[#a6a6b0]">Có thể tùy biến chi tiết ở trang quản lý ghế.</p>
            </div>
          </div>

          <!-- Mixed groups (optional) -->
          <div class="space-y-3">
            <label class="block text-sm font-medium text-gray-300">
              <i class="fas fa-layer-group mr-2 text-[#F53003]"></i>Nhóm ghế theo loại (tùy chọn)
            </label>
            <p class="text-xs text-[#a6a6b0]">Tạo nhiều loại ghế trong 1 lần: ví dụ A:10 ghế Thường, B:5 ghế VIP, C:3 ghế Đôi.</p>

            <div id="segments-wrap" class="space-y-3"></div>

            <div>
              <button type="button" id="btn-add-seg" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                <i class="fas fa-plus mr-1"></i>Thêm nhóm
              </button>
            </div>
            <input type="hidden" name="segments" id="segments-input">
          </div>

          <!-- Seat Preview -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-300">
              <i class="fas fa-eye mr-2 text-[#F53003]"></i>Xem trước sơ đồ ghế
            </label>
            <div id="seat-preview" class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4 min-h-[200px] flex items-center justify-center">
              <p class="text-[#a6a6b0]">Nhập số hàng và số ghế để xem trước</p>
            </div>
          </div>
        </div>

        <!-- Technical Specifications -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-cogs mr-2 text-[#F53003]"></i>
            Thông số kỹ thuật
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Audio System -->
            <div class="space-y-2">
              <label for="audio_system" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-volume-up mr-2 text-[#F53003]"></i>Hệ thống âm thanh
              </label>
              <input type="text" 
                     name="audio_system" 
                     id="audio_system" 
                     class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('audio_system') border-red-500 @enderror" 
                     value="{{ old('audio_system') }}" 
                     placeholder="Ví dụ: Dolby Atmos, THX...">
              @error('audio_system')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
              @enderror
            </div>

            <!-- Screen Type -->
            <div class="space-y-2">
              <label for="screen_type" class="block text-sm font-medium text-gray-300">
                <i class="fas fa-tv mr-2 text-[#F53003]"></i>Loại màn hình
              </label>
              <input type="text" 
                     name="screen_type" 
                     id="screen_type" 
                     class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200 @error('screen_type') border-red-500 @enderror" 
                     value="{{ old('screen_type') }}" 
                     placeholder="Ví dụ: LED, Laser, IMAX...">
              @error('screen_type')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-toggle-on mr-2 text-[#F53003]"></i>
            Trạng thái
          </h3>
          
          <div class="space-y-2">
            <label for="status" class="block text-sm font-medium text-gray-300">
              <i class="fas fa-toggle-on mr-2 text-[#F53003]"></i>Trạng thái phòng
            </label>
            <select name="status" 
                    id="status" 
                    class="w-full px-4 py-3 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent transition-all duration-200">
              <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Hoạt động</option>
              <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
            </select>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-[#262833]">
          <a href="{{ route('admin.phong-chieu.index') }}" 
             class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 text-center">
            <i class="fas fa-times mr-2"></i>Hủy bỏ
          </a>
          <button type="submit" 
                  class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center">
            <i class="fas fa-save mr-2"></i>Tạo phòng chiếu
          </button>
        </div>
      </form>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rowsInput = document.getElementById('rows');
    const colsInput = document.getElementById('cols');
    const previewDiv = document.getElementById('seat-preview');

    function updateSeatPreview() {
        const rows = parseInt(rowsInput.value) || 0;
        const cols = parseInt(colsInput.value) || 0;
        
        if (rows > 0 && cols > 0) {
            let preview = '<div class="text-center">';
            preview += '<div class="text-sm text-[#a6a6b0] mb-2">Sơ đồ ghế: ' + rows + ' hàng × ' + cols + ' ghế</div>';
            preview += '<div class="flex flex-col items-center space-y-1">';
            
            for (let i = 0; i < Math.min(rows, 8); i++) { // Limit display to 8 rows
                const rowLabel = String.fromCharCode(65 + i);
                preview += '<div class="flex space-x-1">';
                preview += '<span class="text-xs text-[#a6a6b0] w-4 text-right">' + rowLabel + '</span>';
                
                for (let j = 1; j <= Math.min(cols, 15); j++) { // Limit display to 15 seats
                    preview += '<div class="w-4 h-4 bg-[#F53003]/30 border border-[#F53003]/50 rounded text-xs flex items-center justify-center text-[#F53003]">' + j + '</div>';
                }
                
                if (cols > 15) {
                    preview += '<span class="text-xs text-[#a6a6b0]">...+' + (cols - 15) + '</span>';
                }
                
                preview += '</div>';
            }
            
            if (rows > 8) {
                preview += '<div class="text-xs text-[#a6a6b0]">...+' + (rows - 8) + ' hàng nữa</div>';
            }
            
            preview += '</div></div>';
            previewDiv.innerHTML = preview;
        } else {
            previewDiv.innerHTML = '<p class="text-[#a6a6b0]">Nhập số hàng và số ghế để xem trước</p>';
        }
    }

    rowsInput.addEventListener('input', updateSeatPreview);
    colsInput.addEventListener('input', updateSeatPreview);
    
    // Initial preview
    updateSeatPreview();
});
</script>
<script>
// Dynamic segments UI
document.addEventListener('DOMContentLoaded', function(){
  const wrap = document.getElementById('segments-wrap');
  const btnAdd = document.getElementById('btn-add-seg');
  const inputHidden = document.getElementById('segments-input');
  const seatTypeSel = document.getElementById('seat_type');
  const formEl = document.querySelector('form[action="{{ route('admin.phong-chieu.store') }}"]');

  function segRowTpl(idx){
    return `
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end" data-seg-row>
        <div>
          <label class="block text-xs text-gray-400">Hàng</label>
          <input type="text" maxlength="1" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded text-white" data-row-label placeholder="A" />
        </div>
        <div>
          <label class="block text-xs text-gray-400">Số ghế</label>
          <input type="number" min="1" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded text-white" data-count placeholder="10" />
        </div>
        <div>
          <label class="block text-xs text-gray-400">Bắt đầu từ</label>
          <input type="number" min="1" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded text-white" data-start placeholder="1" />
        </div>
        <div>
          <label class="block text-xs text-gray-400">Loại ghế</label>
          <select class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded text-white" data-type>
            <option value="normal">Ghế thường</option>
            <option value="vip">Ghế VIP</option>
            <option value="couple">Ghế đôi</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button type="button" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm" data-remove>Gỡ</button>
        </div>
      </div>
    `;
  }

  function addSeg(){
    const div = document.createElement('div');
    div.innerHTML = segRowTpl(Date.now());
    const row = div.firstElementChild;
    row.querySelector('[data-remove]').addEventListener('click', ()=>{
      row.remove();
    });
    wrap.appendChild(row);
  }

  btnAdd?.addEventListener('click', addSeg);

  formEl?.addEventListener('submit', function(){
    const segs = [];
    wrap.querySelectorAll('[data-seg-row]').forEach(r => {
      const rowLabel = (r.querySelector('[data-row-label]')?.value || '').trim().toUpperCase();
      const count = parseInt(r.querySelector('[data-count]')?.value || '0');
      const start = parseInt(r.querySelector('[data-start]')?.value || '1');
      const typeKey = (r.querySelector('[data-type]')?.value || '').trim();
      if (!rowLabel || !count || !typeKey) return;
      // map type key to loai_ghe id via default seat_type selection name
      // server-side createSeatsForRoom maps name, còn bulk thì cần id; tạm gửi theo tên, backend sẽ tra theo tên.
      segs.push({ row_label: rowLabel, count: count, start_index: start, id_loai: typeKey });
    });
    if (segs.length > 0) {
      inputHidden.value = JSON.stringify(segs);
    } else {
      inputHidden.value = '';
    }
  });
});
</script>
@endsection

