@extends('admin.layout')

@section('title', 'Tạo phòng chiếu mới - Admin')
@section('page-title', 'Tạo phòng chiếu mới')
@section('page-description', 'Thêm phòng chiếu mới vào hệ thống')

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-8">
      <form action="{{ route('admin.phong-chieu.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- 1. Basic Information -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F53003] text-white text-xs mr-2">1</span>
            Thông tin cơ bản
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Room Name with real-time validation -->
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
              <p id="nameError" class="text-red-400 text-sm mt-1 hidden">Tên phòng phải từ 2-50 ký tự, chỉ bao gồm chữ, số và khoảng trắng.</p>
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

        <!-- 2. Seat Layout Configuration -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F53003] text-white text-xs mr-2">2</span>
            Cấu hình sơ đồ ghế
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

            <!-- Default Seat Type as radio -->
            <div class="space-y-2">
              <span class="block text-sm font-medium text-gray-300">
                <i class="fas fa-star mr-2 text-[#F53003]"></i>Loại ghế mặc định
              </span>
              <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                  <input type="radio" name="seat_type" value="normal" class="text-[#F53003]" {{ old('seat_type','normal')=='normal'?'checked':'' }}> Ghế thường
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                  <input type="radio" name="seat_type" value="vip" class="text-[#F53003]" {{ old('seat_type')=='vip'?'checked':'' }}> Ghế VIP
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                  <input type="radio" name="seat_type" value="couple" class="text-[#F53003]" {{ old('seat_type')=='couple'?'checked':'' }}> Ghế đôi
                </label>
              </div>
              <p class="text-xs text-[#a6a6b0]">Có thể thay đổi sau khi tạo</p>
            </div>
          </div>

          <!-- Seat Preview (full grid, scrollable) -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-300">
              <i class="fas fa-eye mr-2 text-[#F53003]"></i>Xem trước sơ đồ ghế
            </label>
            <div id="seat-preview" class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4 min-h-[240px] max-h-[420px] overflow-auto">
              <div class="text-[#a6a6b0]">Nhập số hàng và số ghế để xem trước</div>
            </div>
            <!-- Legend -->
            <div class="flex items-center gap-4 text-xs text-[#a6a6b0]">
              <span class="inline-flex items-center gap-2"><span class="w-4 h-4 rounded" style="background:#3b82f6;"></span> Ghế thường</span>
              <span class="inline-flex items-center gap-2"><span class="w-4 h-4 rounded" style="background:gold;"></span> VIP</span>
              <span class="inline-flex items-center gap-2"><span class="w-4 h-4 rounded" style="background:hotpink;"></span> Ghế đôi</span>
            </div>
          </div>

          <!-- Advanced seat blocks -->
          <div class="space-y-4">
            <h4 class="text-sm font-semibold text-white flex items-center">
              <i class="fas fa-th-large mr-2 text-[#F53003]"></i>
              Phân vùng loại ghế (tuỳ chọn)
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
              <div>
                <label class="block text-xs text-gray-300 mb-1">Hàng từ</label>
                <input type="number" min="1" id="blk_row_from" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="1">
              </div>
              <div>
                <label class="block text-xs text-gray-300 mb-1">Hàng đến</label>
                <input type="number" min="1" id="blk_row_to" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="2">
              </div>
              <div>
                <label class="block text-xs text-gray-300 mb-1">Cột từ</label>
                <input type="number" min="1" id="blk_col_from" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="1">
              </div>
              <div>
                <label class="block text-xs text-gray-300 mb-1">Cột đến</label>
                <input type="number" min="1" id="blk_col_to" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="10">
              </div>
              <div class="md:col-span-1 col-span-2">
                <label class="block text-xs text-gray-300 mb-1">Loại ghế</label>
                <select id="blk_type" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
                  @php $__types = isset($loaiGhe) ? $loaiGhe : \App\Models\LoaiGhe::all(); @endphp
                  @foreach($__types as $type)
                    <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
                  @endforeach
                </select>
              </div>
              <div class="md:col-span-1 col-span-2">
                <button type="button" id="blk_add_btn" class="w-full bg-[#F53003] hover:bg-[#e02a00] text-white px-3 py-2 rounded-lg text-sm">
                  Thêm vùng
                </button>
              </div>
            </div>
            <input type="hidden" name="seat_blocks" id="seat_blocks" value="">
            <div id="blk_list" class="space-y-2"></div>
          </div>
        </div>

        <!-- 3. Technical Specifications -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F53003] text-white text-xs mr-2">3</span>
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

        <!-- 4. Status -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F53003] text-white text-xs mr-2">4</span>
            Trạng thái
          </h3>
          <div class="space-y-2">
            <span class="block text-sm font-medium text-gray-300"><i class="fas fa-toggle-on mr-2 text-[#F53003]"></i>Trạng thái phòng</span>
            <div class="flex items-center gap-6">
              <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                <input type="radio" name="status" value="active" {{ old('status','active')=='active'?'checked':'' }}> Hoạt động
              </label>
              <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                <input type="radio" name="status" value="inactive" {{ old('status')=='inactive'?'checked':'' }}> Tạm dừng
              </label>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-[#262833]">
          <a href="{{ route('admin.phong-chieu.index') }}" 
             class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 text-center">
            <i class="fas fa-times mr-2"></i>Hủy bỏ
          </a>
          <button type="submit" id="submitBtn"
                  class="bg-gray-600 cursor-not-allowed text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center"
                  disabled>
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
    const blkAddBtn = document.getElementById('blk_add_btn');
    const blkList = document.getElementById('blk_list');
    const seatBlocksInput = document.getElementById('seat_blocks');
    const blkRowFrom = document.getElementById('blk_row_from');
    const blkRowTo = document.getElementById('blk_row_to');
    const blkColFrom = document.getElementById('blk_col_from');
    const blkColTo = document.getElementById('blk_col_to');
    const blkType = document.getElementById('blk_type');
    const nameInput = document.getElementById('name');
    const nameError = document.getElementById('nameError');
    const submitBtn = document.getElementById('submitBtn');

    // Expose seat types for block list labels
    window.SEAT_TYPES = @json(\App\Models\LoaiGhe::select('id','ten_loai')->get());

    let seatBlocks = [];

    // Map typeId to color for preview
    function colorByTypeId(typeId) {
      const def = '#3b82f6'; // normal
      const types = window.SEAT_TYPES || [];
      const t = types.find(x => x.id == typeId);
      if (!t) return def;
      const name = (t.ten_loai || '').toLowerCase();
      if (name.includes('vip')) return 'gold';
      if (name.includes('đôi') || name.includes('doi') || name.includes('couple')) return 'hotpink';
      return def;
    }

    // Render full grid inside scrollable container (last-zone-wins)
    function updateSeatPreview() {
        const rows = parseInt(rowsInput.value) || 0;
        const cols = parseInt(colsInput.value) || 0;
        
        if (rows > 0 && cols > 0) {
            const wrapper = document.createElement('div');
            wrapper.className = 'inline-block';
            // Title
            const title = document.createElement('div');
            title.className = 'text-sm text-[#a6a6b0] mb-2';
            title.textContent = `Sơ đồ ghế: ${rows} hàng × ${cols} ghế`;
            wrapper.appendChild(title);

            // Build rows
            for (let i = 0; i < rows; i++) {
              const rowLabel = String.fromCharCode(65 + i);
              const rowWrap = document.createElement('div');
              rowWrap.className = 'flex items-center gap-1 mb-1';

              const labelEl = document.createElement('span');
              labelEl.className = 'text-xs text-[#a6a6b0] w-5 text-right';
              labelEl.textContent = rowLabel;
              rowWrap.appendChild(labelEl);

              const seatsLine = document.createElement('div');
              seatsLine.className = 'flex flex-wrap gap-1';
              for (let j = 1; j <= cols; j++) {
                const r = i + 1; const c = j;
                // last-zone-wins: iterate from end
                let block = null;
                for (let k = seatBlocks.length - 1; k >= 0; k--) {
                  const b = seatBlocks[k];
                  if (r >= b.row_from && r <= b.row_to && c >= b.col_from && c <= b.col_to) { block = b; break; }
                }
                let color;
                if (block) color = colorByTypeId(block.id_loai);
                else {
                  const def = (document.querySelector('input[name="seat_type"]:checked')?.value) || 'normal';
                  color = def === 'vip' ? 'gold' : (def === 'couple' ? 'hotpink' : '#3b82f6');
                }
                const seat = document.createElement('div');
                seat.className = 'w-6 h-6 rounded text-[10px] flex items-center justify-center text-white';
                seat.style.background = color;
                seat.textContent = j;
                seatsLine.appendChild(seat);
              }
              rowWrap.appendChild(seatsLine);
              wrapper.appendChild(rowWrap);
            }
            previewDiv.innerHTML = '';
            previewDiv.appendChild(wrapper);
        } else {
            previewDiv.innerHTML = '<p class="text-[#a6a6b0]">Nhập số hàng và số ghế để xem trước</p>';
        }
    }

    rowsInput.addEventListener('input', updateSeatPreview);
    colsInput.addEventListener('input', updateSeatPreview);
    
    // Validation for room name (2-50 chars, letters/numbers/spaces, incl. Vietnamese)
    function validateName() {
      const v = (nameInput.value || '').trim();
      // Allow Vietnamese letters and space
      const re = /^[0-9A-Za-zÀ-ỿăâđêôơưÁÀẢÃẠẮẰẲẴẶẤẦẨẪẬÉÈẺẼẸÍÌỈĨỊÓÒỎÕỌỐỒỔỖỘỚỜỞỠỢÚÙỦŨỤỨỪỬỮỰÝỲỶỸỴ ]{2,50}$/i;
      const ok = re.test(v);
      nameError.classList.toggle('hidden', ok);
      nameInput.classList.toggle('border-red-500', !ok);
      return ok;
    }

    function validateRowsCols() {
      const r = parseInt(rowsInput.value); const c = parseInt(colsInput.value);
      const ok = r>=1 && r<=20 && c>=1 && c<=30;
      return ok;
    }

    function updateSubmitState() {
      const ok = validateName() && validateRowsCols();
      submitBtn.disabled = !ok;
      submitBtn.className = ok
        ? 'bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center'
        : 'bg-gray-600 cursor-not-allowed text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center';
    }

    // Initial preview and validation
    updateSeatPreview();
    updateSubmitState();

    function syncBlocks() {
      seatBlocksInput.value = seatBlocks.length ? JSON.stringify(seatBlocks) : '';
      // render list
      blkList.innerHTML = '';
      if (!seatBlocks.length) return;
      seatBlocks.forEach((b, idx) => {
        const el = document.createElement('div');
        el.className = 'flex items-center justify-between bg-[#1a1d24] border border-[#262833] rounded-lg px-3 py-2 text-sm text-white';
        const typeName = (window.SEAT_TYPES||[]).find(x => x.id == b.id_loai)?.ten_loai || ('Loại #' + b.id_loai);
        const rowFromL = String.fromCharCode(64 + b.row_from);
        const rowToL = String.fromCharCode(64 + b.row_to);
        el.innerHTML = `<span>Hàng ${rowFromL}-${rowToL}, Cột ${b.col_from}-${b.col_to} → ${typeName}</span>
                        <button type="button" data-idx="${idx}" class="blk-remove text-red-400 hover:text-red-300">Xoá</button>`;
        blkList.appendChild(el);
      });
      // bind remove
      blkList.querySelectorAll('.blk-remove').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const i = parseInt(e.currentTarget.getAttribute('data-idx')); 
          if (!isNaN(i)) { seatBlocks.splice(i,1); syncBlocks(); updateSeatPreview(); updateSubmitState(); }
        });
      });
    }

    if (blkAddBtn) {
      blkAddBtn.addEventListener('click', function() {
        // reset borders
        [blkRowFrom, blkRowTo, blkColFrom, blkColTo].forEach(el => el.classList.remove('border-red-500'));

        const rf = parseInt(blkRowFrom.value); const rt = parseInt(blkRowTo.value);
        const cf = parseInt(blkColFrom.value); const ct = parseInt(blkColTo.value);
        const typeId = parseInt(blkType.value);
        const rowsMax = parseInt(rowsInput.value)||0; const colsMax = parseInt(colsInput.value)||0;

        // basic presence
        if (!rf || !rt || !cf || !ct || !typeId) {
          alert('Vui lòng nhập đầy đủ Hàng từ/đến, Cột từ/đến và Loại ghế.');
          return;
        }

        // normalize
        const row_from = Math.min(rf, rt);
        const row_to = Math.max(rf, rt);
        const col_from = Math.min(cf, ct);
        const col_to = Math.max(cf, ct);

        // strict bounds validation
        let invalid = false;
        if (!(row_from >= 1 && row_from <= rowsMax)) { blkRowFrom.classList.add('border-red-500'); invalid = true; }
        if (!(row_to >= 1 && row_to <= rowsMax && row_from <= row_to)) { blkRowTo.classList.add('border-red-500'); invalid = true; }
        if (!(col_from >= 1 && col_from <= colsMax)) { blkColFrom.classList.add('border-red-500'); invalid = true; }
        if (!(col_to >= 1 && col_to <= colsMax && col_from <= col_to)) { blkColTo.classList.add('border-red-500'); invalid = true; }
        if (invalid) { alert('Phạm vi vùng không hợp lệ! Vui lòng kiểm tra Hàng/Cột.'); return; }

        // overlap detection with existing zones (rect-intersect)
        const overlap = seatBlocks.some(b => (Math.max(b.row_from, row_from) <= Math.min(b.row_to, row_to)) && (Math.max(b.col_from, col_from) <= Math.min(b.col_to, col_to)) );
        if (overlap) {
          [blkRowFrom, blkRowTo, blkColFrom, blkColTo].forEach(el => el.classList.add('border-red-500'));
          alert('Vùng ghế trùng lặp! Vui lòng chọn vùng khác.');
          return;
        }

        // add
        seatBlocks.push({ row_from, row_to, col_from, col_to, id_loai: typeId });
        syncBlocks();
        updateSeatPreview();
        updateSubmitState();

        // auto-clear inputs
        blkRowFrom.value = '';
        blkRowTo.value = '';
        blkColFrom.value = '';
        blkColTo.value = '';
      });
    }

    // Re-render preview when default seat type changes
    document.querySelectorAll('input[name="seat_type"]').forEach(r => r.addEventListener('change', () => { updateSeatPreview(); updateSubmitState(); }));
    // Validate name in realtime
    nameInput.addEventListener('input', () => { validateName(); updateSubmitState(); });
    // Validate on rows/cols changes
    rowsInput.addEventListener('input', () => { updateSeatPreview(); updateSubmitState(); });
    colsInput.addEventListener('input', () => { updateSeatPreview(); updateSubmitState(); });
});
</script>
@endsection

