@extends('admin.layout')

@section('title', 'Chỉnh sửa phòng chiếu - Admin')
@section('page-title', 'Chỉnh sửa phòng chiếu')
@section('page-description', 'Cập nhật thông tin phòng chiếu')

@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-8">
      <form action="{{ route('admin.phong-chieu.update', $phongChieu) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
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
                     value="{{ old('name', $phongChieu->name) }}" 
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
                <option value="2D" {{ old('type', $phongChieu->type) == '2D' ? 'selected' : '' }}>2D - Chiếu phim 2D thông thường</option>
                <option value="3D" {{ old('type', $phongChieu->type) == '3D' ? 'selected' : '' }}>3D - Chiếu phim 3D</option>
                <option value="IMAX" {{ old('type', $phongChieu->type) == 'IMAX' ? 'selected' : '' }}>IMAX - Màn hình lớn, âm thanh cao cấp</option>
                <option value="4DX" {{ old('type', $phongChieu->type) == '4DX' ? 'selected' : '' }}>4DX - Trải nghiệm đa giác quan</option>
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
                      placeholder="Mô tả về phòng chiếu, đặc điểm nổi bật...">{{ old('description', $phongChieu->description) }}</textarea>
            @error('description')
              <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Current Seat Layout Info -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <i class="fas fa-chair mr-2 text-[#F53003]"></i>
            Sơ đồ ghế hiện tại
          </h3>
          
          <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-white">{{ $phongChieu->rows }}</div>
                <div class="text-sm text-[#a6a6b0]">Hàng ghế</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-white">{{ $phongChieu->cols }}</div>
                <div class="text-sm text-[#a6a6b0]">Ghế mỗi hàng</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-white">{{ $phongChieu->seats_count }}</div>
                <div class="text-sm text-[#a6a6b0]">Tổng số ghế</div>
              </div>
            </div>
            <div class="mt-4 text-center">
              <a href="{{ route('admin.phong-chieu.show', $phongChieu) }}" 
                 class="text-[#F53003] hover:text-[#e02a00] text-sm font-medium">
                <i class="fas fa-external-link-alt mr-1"></i>
                Xem và chỉnh sửa sơ đồ ghế
              </a>
            </div>
          </div>

          <!-- Regenerate seats -->
          <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
              <div class="text-white font-semibold">Tạo lại sơ đồ ghế</div>
              <div class="text-xs text-[#a6a6b0]">Cảnh báo: thao tác này sẽ xóa toàn bộ ghế hiện tại và tạo mới</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
              <div>
                <label class="block text-xs text-gray-300 mb-1">Số hàng</label>
                <input type="number" id="regen_rows" min="1" max="20" value="{{ (int)($phongChieu->rows ?? 10) }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
              </div>
              <div>
                <label class="block text-xs text-gray-300 mb-1">Số ghế mỗi hàng</label>
                <input type="number" id="regen_cols" min="1" max="30" value="{{ (int)($phongChieu->cols ?? 15) }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
              </div>
              <div class="md:col-span-3">
                <span class="block text-xs text-gray-300 mb-1">Loại ghế mặc định</span>
                <div class="flex items-center gap-4">
                  <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                    <input type="radio" name="regen_type" value="normal" checked> Ghế thường
                  </label>
                  <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                    <input type="radio" name="regen_type" value="vip"> Ghế VIP
                  </label>
                  <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                    <input type="radio" name="regen_type" value="couple"> Ghế đôi
                  </label>
                </div>
              </div>
            </div>
            <div class="flex items-center justify-end mt-4">
              <button type="button" id="btnRegen" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                <i class="fas fa-recycle mr-2"></i>Tạo lại ghế
              </button>
            </div>
            <div id="regen_msg" class="text-sm mt-2 hidden"></div>
          </div>

          <!-- Inline seat editor -->
          @php $phongChieu->loadMissing(['seats.seatType']); @endphp
          <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
              <div class="text-white font-semibold">Chỉnh sửa trực tiếp sơ đồ ghế</div>
              <div class="text-xs text-[#a6a6b0]">Nhấp vào ghế để chuyển trạng thái Có sẵn/Bị khóa</div>
            </div>
            <div class="overflow-x-auto">
              <div class="flex flex-col items-center space-y-2">
                @for($row = 1; $row <= (int)($phongChieu->rows ?? 0); $row++)
                  <div class="flex space-x-2 items-center">
                    <span class="text-sm text-[#a6a6b0] w-6 text-center font-medium">{{ chr(64 + $row) }}</span>
                    @for($col = 1; $col <= (int)($phongChieu->cols ?? 0); $col++)
                      @php
                        $code = chr(64 + $row) . $col;
                        $seat = $phongChieu->seats->firstWhere('so_ghe', $code);
                        $status = $seat ? $seat->status : 'empty';
                        $typeName = $seat && $seat->seatType ? strtolower($seat->seatType->ten_loai) : 'thuong';
                        $typeId = $seat->seatType->id ?? '';
                        $typeClass = '';
                        if ($status === 'available') {
                          if (str_contains($typeName, 'vip')) { $typeClass = 'bg-yellow-600 hover:bg-yellow-700 text-white'; }
                          elseif (str_contains($typeName, 'đôi') || str_contains($typeName, 'doi') || str_contains($typeName, 'couple')) { $typeClass = 'bg-pink-600 hover:bg-pink-700 text-white'; }
                          else { $typeClass = 'bg-blue-600 hover:bg-blue-700 text-white'; }
                        } else {
                          $typeClass = $status==='locked' ? 'bg-gray-800 hover:bg-gray-900 text-gray-400' : 'bg-gray-600 hover:bg-gray-700 text-gray-300';
                        }
                      @endphp
                      <button type="button"
                              class="w-8 h-8 rounded text-xs font-medium transition-colors {{ $typeClass }}"
                              data-seat-id="{{ $seat->id ?? '' }}"
                              data-seat-code="{{ $code }}"
                              data-seat-type-id="{{ $typeId }}"
                              title="{{ $code }}"
                              onclick="{{ $seat ? 'toggleSeatStatusInline(' . $seat->id . ')' : '' }}">
                        {{ $col }}
                      </button>
                    @endfor
                  </div>
                @endfor
              </div>
            </div>
            <div class="mt-3 text-xs text-[#a6a6b0] flex items-center gap-4">
              <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-600 inline-block"></span> Ghế thường</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-600 inline-block"></span> Ghế VIP</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-pink-600 inline-block"></span> Ghế đôi</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-800 inline-block"></span> Bị khóa</span>
              <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-600 inline-block"></span> Trống</span>
            </div>
          </div>

          <!-- Region-based seat type change -->
          <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
              <div class="text-white font-semibold">Đổi loại ghế theo vùng</div>
              <div class="text-xs text-[#a6a6b0]">Chọn dải hàng/cột để áp loại ghế</div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-7 gap-3 items-end">
              <div>
                <label class="block text-xs text-gray-300 mb-1">Hàng từ</label>
                <input type="number" id="rb_row_from" min="1" max="{{ (int)$phongChieu->rows }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="1">
              </div>
              <div>
                <label class="block text-xs text-gray-300 mb-1">Hàng đến</label>
                <input type="number" id="rb_row_to" min="1" max="{{ (int)$phongChieu->rows }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="2">
              </div>
              <div>
                <label class="block text-xs text-gray-300 mb-1">Cột từ</label>
                <input type="number" id="rb_col_from" min="1" max="{{ (int)$phongChieu->cols }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="1">
              </div>
              <div>
                <label class="block text-xs text-gray-300 mb-1">Cột đến</label>
                <input type="number" id="rb_col_to" min="1" max="{{ (int)$phongChieu->cols }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white" placeholder="10">
              </div>
              <div class="md:col-span-2 col-span-2">
                <label class="block text-xs text-gray-300 mb-1">Loại ghế</label>
                <select id="rb_type" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white">
                  @foreach($loaiGhe as $type)
                    <option value="{{ $type->id }}">{{ $type->ten_loai }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <button type="button" id="rb_apply" class="w-full bg-[#F53003] hover:bg-[#e02a00] text-white px-3 py-2 rounded-lg text-sm">Áp dụng</button>
              </div>
            </div>
            <div id="rb_msg" class="text-sm mt-2 hidden"></div>
          </div>

        
          @push('scripts')
          <script>
          async function toggleSeatStatusInline(id) {
            try {
              const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
              // Lấy trạng thái hiện tại từ nút để quyết định trạng thái mới
              const btn = document.querySelector('[data-seat-id="' + id + '"]');
              const isAvailable = btn && btn.classList.contains('bg-green-600');
              const newStatus = isAvailable ? 'locked' : 'available';
              const res = await fetch(`/admin/seats/${id}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ status: newStatus })
              });
              const data = await res.json();
              if (!res.ok || !data.success) throw new Error(data.message || 'Lỗi cập nhật');
              // Cập nhật UI nhanh theo trạng thái mới
              if (newStatus === 'available') {
                btn.classList.remove('bg-gray-800','text-gray-400');
                btn.classList.add('bg-green-600','text-white');
              } else {
                btn.classList.remove('bg-green-600','text-white');
                btn.classList.add('bg-gray-800','text-gray-400');
              }
            } catch (e) {
              alert('Có lỗi xảy ra khi cập nhật ghế');
              console.error(e);
            }
          }
          </script>
          @endpush
          @push('scripts')
          <script>
          document.addEventListener('DOMContentLoaded', function(){
            const btn = document.getElementById('rb_apply');
            const msg = document.getElementById('rb_msg');
            if (!btn) return;
            btn.addEventListener('click', async function(){
              const rf = parseInt(document.getElementById('rb_row_from').value||'0');
              const rt = parseInt(document.getElementById('rb_row_to').value||'0');
              const cf = parseInt(document.getElementById('rb_col_from').value||'0');
              const ct = parseInt(document.getElementById('rb_col_to').value||'0');
              const typeId = parseInt(document.getElementById('rb_type').value||'0');
              const rowsMax = {{ (int)$phongChieu->rows }}; const colsMax = {{ (int)$phongChieu->cols }};
              // validate
              const row_from = Math.min(rf, rt), row_to = Math.max(rf, rt);
              const col_from = Math.min(cf, ct), col_to = Math.max(cf, ct);
              if (!(row_from>=1 && row_to<=rowsMax && col_from>=1 && col_to<=colsMax && typeId>0)) { alert('Phạm vi hoặc loại ghế không hợp lệ.'); return; }
              // collect seat ids in rectangle from rendered grid
              const seatBtns = Array.from(document.querySelectorAll('[data-seat-id][data-seat-code]'));
              const ids = seatBtns.filter(b => {
                const code = b.getAttribute('data-seat-code');
                if (!code) return false;
                const rowChar = code.charAt(0);
                const colNum = parseInt(code.substring(1));
                const rowNum = rowChar.charCodeAt(0) - 64;
                return rowNum>=row_from && rowNum<=row_to && colNum>=col_from && colNum<=col_to && b.getAttribute('data-seat-id');
              }).map(b => parseInt(b.getAttribute('data-seat-id'))).filter(Boolean);
              if (ids.length===0) { alert('Không có ghế nào trong vùng đã chọn.'); return; }
              try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch("{{ route('admin.phong-chieu.seats.bulk', $phongChieu) }}", {
                  method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
                  body: JSON.stringify({ action:'type', seat_ids: ids, id_loai: typeId })
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Lỗi áp dụng');
                msg.className='text-green-400 text-sm mt-2'; msg.textContent = `Đã đổi loại ${data.affected ?? ids.length} ghế.`; msg.classList.remove('hidden');
                // Update colors in-place
                seatBtns.forEach(b => {
                  const code = b.getAttribute('data-seat-code');
                  const rowChar = code.charAt(0);
                  const colNum = parseInt(code.substring(1));
                  const rowNum = rowChar.charCodeAt(0) - 64;
                  const inRect = rowNum>=row_from && rowNum<=row_to && colNum>=col_from && colNum<=col_to;
                  if (inRect) {
                    b.setAttribute('data-seat-type-id', String(typeId));
                    if (b.classList.contains('bg-gray-800')) return; // locked keeps gray
                    b.classList.remove('bg-blue-600','hover:bg-blue-700','bg-yellow-600','hover:bg-yellow-700','bg-pink-600','hover:bg-pink-700');
                    // naive mapping by type name from select text
                    const typeText = (document.querySelector('#rb_type option:checked')?.textContent || '').toLowerCase();
                    if (typeText.includes('vip')) { b.classList.add('bg-yellow-600','hover:bg-yellow-700','text-white'); }
                    else if (typeText.includes('đôi') || typeText.includes('doi') || typeText.includes('couple')) { b.classList.add('bg-pink-600','hover:bg-pink-700','text-white'); }
                    else { b.classList.add('bg-blue-600','hover:bg-blue-700','text-white'); }
                  }
                });
              } catch(e) {
                msg.className='text-red-400 text-sm mt-2'; msg.textContent = e.message || 'Có lỗi xảy ra'; msg.classList.remove('hidden');
              }
            });
          });
          </script>
          @endpush
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
                     value="{{ old('audio_system', $phongChieu->audio_system) }}" 
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
                     value="{{ old('screen_type', $phongChieu->screen_type) }}" 
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
              <option value="active" {{ old('status', $phongChieu->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
              <option value="inactive" {{ old('status', $phongChieu->status) == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
            </select>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-[#262833]">
          <a href="{{ route('admin.phong-chieu.show', $phongChieu) }}" 
             class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 text-center">
            <i class="fas fa-times mr-2"></i>Hủy bỏ
          </a>
          <button type="submit" 
                  class="bg-[#F53003] hover:bg-[#e02a00] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center">
            <i class="fas fa-save mr-2"></i>Cập nhật phòng chiếu
          </button>
        </div>
      </form>
    </div>
  </div>
  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const btn = document.getElementById('btnRegen');
      const msg = document.getElementById('regen_msg');
      if (btn) {
        btn.addEventListener('click', async function(){
          const rows = parseInt(document.getElementById('regen_rows').value||'0');
          const cols = parseInt(document.getElementById('regen_cols').value||'0');
          const type = (document.querySelector('input[name="regen_type"]:checked')||{}).value || 'normal';
          if (!(rows>=1 && rows<=20 && cols>=1 && cols<=30)) { alert('Giá trị hàng/cột không hợp lệ.'); return; }
          if (!confirm('Thao tác này sẽ xóa toàn bộ ghế hiện tại và tạo mới. Tiếp tục?')) return;
          try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch("{{ route('admin.phong-chieu.generate-seats', $phongChieu) }}", {
              method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
              body: JSON.stringify({ rows, cols, seat_type: type })
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Lỗi tạo lại ghế');
            msg.className = 'text-green-400 text-sm mt-2'; msg.textContent = data.message || 'Đã tạo lại ghế thành công'; msg.classList.remove('hidden');
            setTimeout(()=>{ window.location.reload(); }, 800);
          } catch(e) {
            msg.className = 'text-red-400 text-sm mt-2'; msg.textContent = e.message || 'Có lỗi xảy ra'; msg.classList.remove('hidden');
          }
        });
      }
    });
  </script>
  @endpush
@endsection

