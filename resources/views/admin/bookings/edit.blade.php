@extends('admin.layout')

@section('title', 'Chỉnh sửa vé #' . $booking->id)

@section('content')
    <div class="space-y-6">
        {{-- Thông báo lỗi/thành công --}}
        @if ($errors->any())
            <div class="bg-red-900/40 border border-red-600 text-sm text-red-100 px-4 py-3 rounded-md">
                <p class="font-semibold">Có lỗi xảy ra:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-900/40 border border-red-600 text-sm text-red-100 px-4 py-3 rounded-md">
                {{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="bg-green-900/40 border border-green-600 text-sm text-green-100 px-4 py-3 rounded-md">
                {{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST"
            class="bg-[#151822] border border-[#262833] rounded-2xl p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col md:flex-row gap-6">
                {{-- Cột trái: Thông tin chính và Chọn ghế --}}
                <div class="md:w-2/3 space-y-6">
                    {{-- Header Vé --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833]">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-white">🎟️ Vé #{{ $booking->id }}</h2>
                                <p class="text-xs text-gray-400">Đặt lúc
                                    {{ optional($booking->created_at)->format('d/m/Y H:i') }}</p>
                            </div>
                            
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-300">
                            <p><strong>Phim:</strong> {{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</p>
                            <p><strong>Phòng:</strong> {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
                            <p><strong>Suất chiếu:</strong>
                                {{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                            <p><strong>Kết thúc:</strong>
                                {{ optional($booking->suatChieu?->thoi_gian_ket_thuc)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Sơ đồ ghế --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-white">💺 Chọn Ghế</h3>
                            <span class="text-xs text-gray-400">
                                @if ($booking->chiTietDatVe->isEmpty())
                                    Chưa có ghế
                                @else
                                    {{ $booking->chiTietDatVe->count() }} ghế hiện tại
                                @endif
                            </span>
                        </div>

                        {{-- Chọn suất chiếu khác (nếu muốn đổi) --}}
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Đổi suất chiếu (nếu cần)</label>
                            <select id="suat_chieu_id" name="suat_chieu_id"
                                class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                                {{-- JS sẽ load option vào đây --}}
                            </select>
                        </div>

                        {{-- Chú thích ghế --}}
                        <div class="flex flex-wrap items-center gap-3 text-xs select-none">
                            <div class="flex items-center gap-1"><span
                                    class="w-3 h-3 rounded bg-[#374151] border border-[#262833]"></span> Thường</div>
                            <div class="flex items-center gap-1"><span
                                    class="w-3 h-3 rounded bg-[#f59e0b] border border-[#b45309]"></span> VIP</div>
                            <div class="flex items-center gap-1"><span
                                    class="w-3 h-3 rounded bg-[#ec4899] border border-[#be185d]"></span> Đôi</div>
                            <div class="flex items-center gap-1"><span
                                    class="w-3 h-3 rounded bg-[#2a2d39] border border-[#3a3d49]"></span> Đã đặt</div>
                            <div class="flex items-center gap-1"><span
                                    class="w-3 h-3 rounded bg-[#22c55e] border border-[#15803d]"></span> Đang chọn</div>
                        </div>

                        {{-- Màn hình & Sơ đồ --}}
                        <div class="mt-2 relative">
                            <div id="seat-map-container" class="overflow-x-auto pb-2">
                                <div id="seat-map" class="min-w-max mx-auto">
                                    <div class="text-center text-gray-400 text-sm py-6">Đang tải sơ đồ ghế...</div>
                                </div>
                            </div>
                        </div>

                        {{-- Input ẩn lưu ID ghế --}}
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">ID Ghế (Cập nhật tự động)</label>
                            <input type="text" id="ghe_ids" name="ghe_ids"
                                value="{{ old('ghe_ids', implode(',', $booking->chiTietDatVe->pluck('id_ghe')->toArray())) }}"
                                class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-400 cursor-not-allowed"
                                readonly>
                            <p class="text-[10px] text-gray-500 mt-1">Các ghế hiện tại:
                                {{ $booking->chiTietDatVe->map(fn($d) => $d->ghe?->so_ghe)->filter()->implode(', ') ?: 'Trống' }}
                            </p>
                        </div>
                    </div>

                    {{-- Chọn Combo --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833]">
                        <h3 class="text-base font-semibold text-white mb-3">🍿 Combo / Bắp nước</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($combos as $combo)
                                @php
                                    $isSelected = in_array($combo->id, $selectedComboIds);
                                    $quantity = old(
                                        'combo_quantities.' . $combo->id,
                                        $selectedComboQuantities[$combo->id] ?? 0,
                                    );
                                @endphp
                                <div
                                    class="flex items-center gap-3 p-3 border border-[#262833] rounded-lg bg-[#13131b] hover:border-gray-600 transition">
                                    <input type="checkbox" id="combo_{{ $combo->id }}" name="combo_ids[]"
                                        value="{{ $combo->id }}"
                                        class="h-4 w-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500"
                                        data-quantity-target="combo_qty_{{ $combo->id }}"
                                        {{ $isSelected ? 'checked' : '' }}>
                                    
                                    <div class="flex-1">
                                        <label for="combo_{{ $combo->id }}"
                                            class="block text-sm font-medium text-gray-200 cursor-pointer">
                                            {{ $combo->ten }}
                                        </label>
                                        <span class="text-xs text-gray-500">{{ number_format($combo->gia) }} đ</span>
                                    </div>

                                    <input type="number" name="combo_quantities[{{ $combo->id }}]"
                                        id="combo_qty_{{ $combo->id }}" min="1" max="10"
                                        value="{{ $quantity > 0 ? $quantity : 1 }}"
                                        class="w-16 bg-[#262833] border border-[#374151] rounded-md px-2 py-1 text-sm text-white text-center focus:border-blue-500 outline-none disabled:opacity-30 disabled:cursor-not-allowed"
                                        {{ $isSelected ? '' : 'disabled' }}>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Thông tin & Hành động --}}
                <div class="md:w-1/3 space-y-4">
                    {{-- Card Khách hàng --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-3">
                        <h3 class="font-semibold text-base text-white border-b border-[#262833] pb-2">👤 Khách hàng</h3>
                        <div class="text-sm text-gray-300 space-y-2">
                            <p>
                                <span class="text-gray-500">Tên:</span>
                                <span
                                    class="font-medium text-white ml-1">{{ $booking->ten_khach_hang ?? ($booking->nguoiDung?->ho_ten ?? 'N/A') }}</span>
                            </p>
                            <p>
                                <span class="text-gray-500">Email:</span>
                                <span
                                    class="ml-1">{{ $booking->email ?? ($booking->nguoiDung?->email ?? 'N/A') }}</span>
                            </p>
                            <p>
                                <span class="text-gray-500">SĐT:</span>
                                <span
                                    class="ml-1">{{ $booking->so_dien_thoai ?? ($booking->nguoiDung?->sdt ?? 'N/A') }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Card Cập nhật & Ghi chú --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4">
                        <h3 class="font-semibold text-base text-white border-b border-[#262833] pb-2">⚙️ Cập nhật</h3>


                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Ghi chú nội bộ</label>
                            <textarea name="ghi_chu_noi_bo" rows="3"
                                class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 placeholder-gray-600"
                                placeholder="Nhập ghi chú...">{{ old('ghi_chu_noi_bo', $booking->ghi_chu_noi_bo ?? '') }}</textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition shadow-lg shadow-blue-900/20">
                                Lưu thay đổi
                            </button>
                            <a href="{{ route('admin.bookings.index') }}"
                                class="block text-center mt-3 text-sm text-gray-400 hover:text-white transition">
                                Hủy bỏ, quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Xử lý Checkbox Combo
                document.querySelectorAll('input[type="checkbox"][name="combo_ids[]"]').forEach(chk => {
                    const targetId = chk.dataset.quantityTarget;
                    const qtyInput = document.getElementById(targetId);
                    
                    const sync = () => {
                        if (qtyInput) {
                            qtyInput.disabled = !chk.checked;
                            if (chk.checked && qtyInput.value == 0) qtyInput.value = 1;
                        }
                    };
                    
                    chk.addEventListener('change', sync);
                    // Init state
                    sync();
                });

                // 2. Cấu hình sơ đồ ghế
                const bookingId = {{ (int) $booking->id }};
                const currentShowtimeId = {{ (int) $booking->id_suat_chieu }};
                const availableShowtimesUrl = @json(route('admin.bookings.available-showtimes', $booking->id));
                const seatsApiBase = @json(route('admin.showtimes.seats', ['suatChieu' => '__ID__']));

                const suatSelect = document.getElementById('suat_chieu_id');
                const seatMapEl = document.getElementById('seat-map');
                const gheIdsInput = document.getElementById('ghe_ids');

                // Helper: Lấy màu ghế theo loại
                function getSeatClasses(seatType) {
                    // id_loai: 1=Thường, 2=VIP, 3=Đôi (Check DB của bạn để map đúng ID)
                    switch (seatType) {
                        case 2: // VIP
                            return 'bg-[#f59e0b] border-[#b45309] text-black';
                        case 3: // Đôi
                            return 'bg-[#ec4899] border-[#be185d] text-white w-16'; // Ghế đôi rộng hơn
                        default: // Thường
                            return 'bg-[#374151] border-[#262833] text-white';
                    }
                }

                function rowLabel(n) {
                    n = parseInt(n, 10) || 0;
                    return String.fromCharCode(64 + Math.min(Math.max(n, 1), 26));
                }

                function renderSeatMap(data, selectedIds) {
                    const byRow = {};
                    let maxCol = 0;

                    // Group ghế theo hàng và tìm cột lớn nhất
                    data.seats.forEach(s => {
                        const label = String(s.label || '');
                        const m = label.match(/(\d+)/); // Lấy số ghế
                        const col = m ? parseInt(m[1], 10) : 0;
                        maxCol = Math.max(maxCol, col);
                        (byRow[s.row] ||= [])[col] = s;
                    });

                    let html = '<div class="inline-block p-4 bg-[#10121a] rounded-xl border border-[#262833]">';
                    
                    // Màn hình
                    html += '<div class="mb-6 flex justify-center"><div class="w-2/3 h-1.5 bg-gray-600 rounded-full shadow-[0_2px_10px_rgba(255,255,255,0.2)]"></div></div>';
                    
                    html += '<div class="flex flex-col gap-2">'; // Container các hàng

                    Object.keys(byRow).sort((a, b) => a - b).forEach(row => {
                        const rLabel = rowLabel(row);
                        html += '<div class="flex items-center gap-3">';
                        
                        // Tên hàng (Trái)
                        html += `<div class="w-6 text-xs font-bold text-gray-400 text-center">${rLabel}</div>`;
                        
                        html += '<div class="flex gap-2">';
                        for (let c = 1; c <= maxCol; c++) {
                            const seat = byRow[row][c];
                            if (!seat) {
                                // Khoảng trống (lối đi)
                                html += '<div class="w-8 h-8"></div>';
                                continue;
                            }

                            const isBooked = !!seat.booked;
                            const isSelected = selectedIds.has(seat.id);
                            
                            // Base classes
                            let btnClass = 'inline-flex items-center justify-center w-8 h-8 text-[10px] font-medium rounded border transition-all duration-200 shadow-sm';
                            
                            if (isBooked) {
                                // Ghế đã bán (Của người khác)
                                btnClass += ' bg-[#1f2937] border-[#374151] text-gray-600 cursor-not-allowed opacity-50';
                            } else if (isSelected) {
                                // Ghế đang chọn (Xanh lá)
                                btnClass += ' bg-[#22c55e] border-[#15803d] text-black shadow-[0_0_10px_rgba(34,197,94,0.4)] scale-105';
                            } else {
                                // Ghế trống theo loại
                                btnClass += ' ' + getSeatClasses(seat.type) + ' hover:brightness-110 hover:-translate-y-0.5 cursor-pointer';
                            }

                            html += `<button type="button" 
                                        class="seat-btn ${btnClass}" 
                                        data-id="${seat.id}" 
                                        data-type="${seat.type}"
                                        ${isBooked ? 'disabled' : ''}
                                        title="${seat.label}">
                                        ${seat.label.replace(/[A-Z]/, '')}
                                     </button>`;
                        }
                        html += '</div>'; // End row seats
                        html += '</div>'; // End row container
                    });

                    html += '</div></div>'; // End seat map
                    seatMapEl.innerHTML = html;

                    // Gán sự kiện Click (Dùng DOM Delegation hoặc gán trực tiếp)
                    seatMapEl.querySelectorAll('.seat-btn:not(:disabled)').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = parseInt(this.dataset.id, 10);
                            const currentIds = new Set((gheIdsInput.value || '').split(',').filter(Boolean).map(Number));

                            if (currentIds.has(id)) {
                                // Bỏ chọn
                                currentIds.delete(id);
                                // Restore style cũ
                                const typeClass = getSeatClasses(parseInt(this.dataset.type));
                                this.className = `seat-btn inline-flex items-center justify-center w-8 h-8 text-[10px] font-medium rounded border transition-all duration-200 shadow-sm ${typeClass} hover:brightness-110 hover:-translate-y-0.5 cursor-pointer`;
                            } else {
                                // Chọn
                                currentIds.add(id);
                                // Set style xanh
                                this.className = `seat-btn inline-flex items-center justify-center w-8 h-8 text-[10px] font-medium rounded border transition-all duration-200 shadow-sm bg-[#22c55e] border-[#15803d] text-black shadow-[0_0_10px_rgba(34,197,94,0.4)] scale-105`;
                            }

                            // Update input
                            gheIdsInput.value = Array.from(currentIds).sort((a, b) => a - b).join(',');
                        });
                    });
                }

                function loadSeats(suatId) {
                    if (!suatId) return;
                    const url = seatsApiBase.replace('__ID__', suatId) + '?exclude_booking_id=' + bookingId;
                    seatMapEl.innerHTML = '<div class="text-center text-gray-400 text-sm py-6"><i class="fas fa-spinner fa-spin mr-2"></i>Đang tải sơ đồ ghế...</div>';

                    fetch(url, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(r => r.json())
                        .then(json => {
                            // Lấy danh sách ID ghế đang có trong input
                            const preset = new Set((gheIdsInput.value || '').split(',').filter(Boolean).map(Number));
                            renderSeatMap(json, preset);
                        })
                        .catch(() => {
                            seatMapEl.innerHTML = '<div class="text-center text-red-400 text-sm py-6">Không tải được sơ đồ ghế.</div>';
                        });
                }

                function loadShowtimes() {
                    suatSelect.innerHTML = '<option>Đang tải...</option>';
                    fetch(availableShowtimesUrl, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(r => r.json())
                        .then(items => {
                            suatSelect.innerHTML = '';
                            if (items.length === 0) {
                                const opt = document.createElement('option');
                                opt.text = "Không có suất chiếu khả dụng";
                                suatSelect.appendChild(opt);
                            }
                            
                            items.forEach(it => {
                                const opt = document.createElement('option');
                                opt.value = it.id;
                                opt.textContent = it.label;
                                if (it.current || it.id === currentShowtimeId) opt.selected = true;
                                suatSelect.appendChild(opt);
                            });

                            // Load ghế của suất chiếu đang chọn
                            const sid = parseInt(suatSelect.value || currentShowtimeId, 10);
                            loadSeats(sid);
                        })
                        .catch(() => {
                            suatSelect.innerHTML = `<option value="${currentShowtimeId}">Hiện tại</option>`;
                            loadSeats(currentShowtimeId);
                        });
                }

                // Sự kiện đổi suất chiếu -> Load lại ghế và Reset input ghế
                suatSelect.addEventListener('change', function() {
                    const newSid = parseInt(this.value, 10);
                    if (newSid && newSid !== currentShowtimeId) {
                        if(confirm('Đổi suất chiếu sẽ reset danh sách ghế đã chọn. Tiếp tục?')) {
                             gheIdsInput.value = ''; // Reset ghế khi đổi suất
                             loadSeats(newSid);
                        } else {
                            // Revert select
                            this.value = currentShowtimeId; 
                        }
                    } else {
                         loadSeats(newSid);
                    }
                });

                // Init
                loadShowtimes();
            });
        </script>
    @endpush
@endsection