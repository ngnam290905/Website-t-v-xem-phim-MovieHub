@extends('admin.layout')

@section('title', 'Chỉnh sửa vé #' . $booking->id)

@section('content')
    <div class="space-y-6">
        {{-- Thông báo --}}
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
            <div class="bg-red-900/40 border border-red-600 text-sm text-red-100 px-4 py-3 rounded-md">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="bg-green-900/40 border border-green-600 text-sm text-green-100 px-4 py-3 rounded-md">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="bg-[#151822] border border-[#262833] rounded-2xl p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col md:flex-row gap-6">
                {{-- Cột trái --}}
                <div class="md:w-2/3 space-y-6">
                    {{-- Tóm tắt vé --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833]">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">🎟️ Vé #{{ $booking->id }}</h2>
                                <p class="text-xs text-gray-400">Đặt lúc {{ optional($booking->created_at)->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                  @class([
                                    'bg-yellow-900 text-yellow-300' => $booking->trang_thai === 0,
                                    'bg-green-900 text-green-300'  => $booking->trang_thai === 1,
                                    'bg-orange-900 text-orange-200'=> $booking->trang_thai === 3,
                                    'bg-red-900 text-red-300'      => $booking->trang_thai === 2,
                                  ])>
                                @switch($booking->trang_thai)
                                    @case(0) Chờ xác nhận @break
                                    @case(1) Đã xác nhận @break
                                    @case(3) Yêu cầu hủy @break
                                    @case(2) Đã hủy @break
                                    @default Không xác định
                                @endswitch
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-300">
                            <p><strong>Phim:</strong> {{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</p>
                            <p><strong>Phòng:</strong> {{ $booking->suatChieu?->phongChieu?->ten_phong ?? 'N/A' }}</p>
                            <p><strong>Suất chiếu:</strong> {{ optional($booking->suatChieu?->thoi_gian_bat_dau)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                            <p><strong>Kết thúc:</strong> {{ optional($booking->suatChieu?->thoi_gian_ket_thuc)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Ghế --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold">💺 Ghế</h3>
                            <span class="text-xs text-gray-400">@if($booking->chiTietDatVe->isEmpty()) Chưa có @else {{ $booking->chiTietDatVe->count() }} ghế @endif</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Suất chiếu</label>
                                <select id="suat_chieu_id" name="suat_chieu_id" class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200"></select>
                            </div>
                            <div class="text-xs text-gray-500">Chọn ghế trên sơ đồ để đồng bộ vào ô nhập bên dưới.</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-xs">
                            <span class="inline-flex items-center gap-2"><span class="inline-block w-4 h-4 rounded border border-[#262833] bg-[#374151]"></span> Ghế thường</span>
                            <span class="inline-flex items-center gap-2"><span class="inline-block w-4 h-4 rounded border border-[#b45309] bg-[#f59e0b]"></span> Ghế VIP</span>
                            <span class="inline-flex items-center gap-2"><span class="inline-block w-4 h-4 rounded border border-[#be185d] bg-[#ec4899]"></span> Ghế đôi</span>
                            <span class="inline-flex items-center gap-2"><span class="inline-block w-4 h-4 rounded border border-[#3a3d49] bg-[#2a2d39]"></span> Đã đặt</span>
                            <span class="inline-flex items-center gap-2"><span class="inline-block w-4 h-4 rounded border border-[#15803d] bg-[#22c55e]"></span> Đang chọn</span>
                        </div>
                        <div id="seat-map" class="mt-2 border border-dashed border-[#2a2d39] rounded-lg p-3 overflow-x-auto">
                            <div class="text-center text-gray-400 text-sm py-6">Đang tải sơ đồ ghế...</div>
                        </div>
                        <p class="text-sm text-gray-400">{{ $booking->chiTietDatVe->map(fn($d) => $d->ghe?->so_ghe)->filter()->implode(', ') ?: 'Không có ghế' }}</p>
                        <label class="text-xs text-gray-400">Nhập ID ghế cách nhau bằng dấu phẩy (để trống nếu không đổi).</label>
                        <input type="text" id="ghe_ids" name="ghe_ids"
                               value="{{ old('ghe_ids', implode(',', $booking->chiTietDatVe->pluck('id_ghe')->toArray())) }}"
                               class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200"
                               placeholder="vd: 101,102,103">
                    </div>

                    {{-- Combo --}}
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833]">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-semibold">🍿 Combo</h3>
                            <span class="text-xs text-gray-400">Chọn combo kèm và điều chỉnh số lượng</span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($combos as $combo)
                                @php
                                    $isSelected = in_array($combo->id, $selectedComboIds);
                                    $quantity = old('combo_quantities.' . $combo->id, $selectedComboQuantities[$combo->id] ?? 0);
                                @endphp
                                <div class="flex items-center gap-3 p-3 border border-[#262833] rounded-lg bg-[#13131b]">
                                    <input type="checkbox" id="combo_{{ $combo->id }}" name="combo_ids[]"
                                           value="{{ $combo->id }}" class="h-4 w-4 text-red-500"
                                           data-quantity-target="combo_qty_{{ $combo->id }}"
                                           {{ $isSelected ? 'checked' : '' }}>
                                    <label for="combo_{{ $combo->id }}" class="flex-1 text-sm text-gray-100">
                                        <span class="font-semibold">{{ $combo->ten }}</span>
                                        <span class="text-xs text-gray-400"> {{ number_format($combo->gia) }} VNĐ</span>
                                    </label>
                                    <input type="number" name="combo_quantities[{{ $combo->id }}]" id="combo_qty_{{ $combo->id }}"
                                           min="0" step="1" value="{{ $quantity }}"
                                           class="w-20 bg-[#10121a] border border-[#262833] rounded-lg px-2 py-1 text-sm text-gray-200"
                                           {{ $isSelected ? '' : 'disabled' }}>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Cột phải --}}
                <div class="md:w-1/3 space-y-4">
                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-2 text-sm text-gray-300">
                        <h3 class="font-semibold text-base">👤 Khách hàng</h3>
                        <p><strong>Họ tên:</strong> {{ $booking->ten_khach_hang ?? $booking->nguoiDung?->ho_ten ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $booking->email ?? $booking->nguoiDung?->email ?? 'N/A' }}</p>
                        <p><strong>SĐT:</strong> {{ $booking->so_dien_thoai ?? $booking->nguoiDung?->sdt ?? 'N/A' }}</p>
                        @if ($booking->nguoiDung)
                            <p><strong>Điểm tích lũy:</strong> {{ $booking->nguoiDung->diemThanhVien?->tong_diem ?? 0 }}</p>
                            <p><strong>Hạng:</strong> {{ $booking->nguoiDung->hangThanhVien->ten_hang ?? 'Chưa có' }}</p>
                        @endif
                    </div>

                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-3">
                        <h3 class="text-base font-semibold">💳 Thanh toán</h3>
                        <p class="text-sm text-gray-400">Tổng tiền hiện tại:</p>
                        <p class="text-xl font-bold text-green-400">{{ number_format($booking->tong_tien) }} VNĐ</p>
                        <p class="text-xs text-gray-500">{{ optional($booking->thanhToan)->phuong_thuc ?? 'Chưa thanh toán' }} • {{ number_format($booking->thanhToan?->so_tien ?? 0) }} VNĐ</p>
                        <hr class="border-[#262833]">
                        <label class="text-xs text-gray-400" for="ma_km">Mã khuyến mãi</label>
                        <input type="text" id="ma_km" name="ma_km" value="{{ old('ma_km', $booking->khuyenMai?->ma_km) }}"
                               class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200">
                    </div>

                    <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-3">
                        <h3 class="text-base font-semibold">⚙️ Trạng thái & ghi chú</h3>
                        <select name="trang_thai" class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200">
                            <option value="0" {{ old('trang_thai', $booking->trang_thai) == 0 ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="1" {{ old('trang_thai', $booking->trang_thai) == 1 ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="3" {{ old('trang_thai', $booking->trang_thai) == 3 ? 'selected' : '' }}>Yêu cầu hủy</option>
                            <option value="2" {{ old('trang_thai', $booking->trang_thai) == 2 ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                        <label class="text-xs text-gray-400" for="ghi_chu_noi_bo">Ghi chú nội bộ</label>
                        <textarea name="ghi_chu_noi_bo" id="ghi_chu_noi_bo" rows="3" class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200">{{ old('ghi_chu_noi_bo', $booking->ghi_chu_noi_bo ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 text-sm border border-[#262833] rounded-lg text-gray-200 hover:border-gray-500">← Trở về</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-semibold">Cập nhật vé</button>
            </div>
        </form>
    </div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[type="checkbox"][name="combo_ids[]"]').forEach(chk => {
                const targetId = chk.dataset.quantityTarget;
                const qtyInput = document.getElementById(targetId);
                const sync = () => { if (qtyInput) qtyInput.disabled = !chk.checked; if (!chk.checked && qtyInput) qtyInput.value = 0; };
                chk.addEventListener('change', sync);
                sync();
            });

            const bookingId = {{ (int) $booking->id }};
            const currentShowtimeId = {{ (int) $booking->id_suat_chieu }};
            const availableShowtimesUrl = @json(route('admin.bookings.available-showtimes', $booking->id));
            const seatsApiBase = @json(route('admin.showtimes.seats', ['suatChieu' => '__ID__']));

            const suatSelect = document.getElementById('suat_chieu_id');
            const seatMapEl = document.getElementById('seat-map');
            const gheIdsInput = document.getElementById('ghe_ids');

            function typeClass(seat) {
                // seat.type là id_loai: quy ước 1=thường, 2=VIP, 3=đôi (có thể khác, tùy DB)
                switch (seat.type) {
                    case 2: return {bg:'bg-[#f59e0b]', border:'border-[#b45309]', text:'text-black'}; // VIP vàng
                    case 3: return {bg:'bg-[#ec4899]', border:'border-[#be185d]', text:'text-white'}; // Đôi hồng
                    default: return {bg:'bg-[#374151]', border:'border-[#262833]', text:'text-white'}; // Thường xám
                }
            }

            function rowLabel(n) {
                // 1->A, 2->B ... 26->Z
                n = parseInt(n,10) || 0; return String.fromCharCode(64 + Math.min(Math.max(n,1), 26));
            }

            function renderSeatMap(data, selectedIds) {
                const byRow = {};
                data.seats.forEach(s => { (byRow[s.row] ||= []).push(s); });
                Object.values(byRow).forEach(arr => arr.sort((a,b) => a.label.localeCompare(b.label, undefined, {numeric:true})));
                let html = '';
                html += '<div class="mb-3 text-center"><span class="inline-block bg-[#0f121a] border border-[#262833] px-3 py-1 rounded text-xs text-gray-300">Màn hình</span></div>';
                html += '<div class="space-y-2">';
                Object.keys(byRow).sort((a,b)=>a-b).forEach(row => {
                    html += '<div class="flex items-center gap-2">';
                    const rLabel = rowLabel(row);
                    html += '<div class="w-6 text-xs text-gray-400 text-right">' + rLabel + '</div>';
                    html += '<div class="flex flex-wrap gap-2">';
                    byRow[row].forEach(seat => {
                        const isBooked = !!seat.booked;
                        const isSelected = selectedIds.has(seat.id);
                        const base = 'inline-flex items-center justify-center w-9 h-9 text-xs rounded border transition';
                        if (isBooked) {
                            html += `<button type="button" class="seat ${base} bg-[#2a2d39] border-[#3a3d49] text-gray-500 cursor-not-allowed" data-id="${seat.id}" title="${seat.label}">${seat.label}</button>`;
                        } else if (isSelected) {
                            html += `<button type="button" class="seat ${base} bg-[#22c55e] border-[#15803d] text-black" data-id="${seat.id}" title="${seat.label}">${seat.label}</button>`;
                        } else {
                            const t = typeClass(seat);
                            html += `<button type="button" class="seat ${base} ${t.bg} ${t.border} ${t.text} hover:brightness-110" data-id="${seat.id}" title="${seat.label}">${seat.label}</button>`;
                        }
                    });
                    html += `</div><div class="w-6 text-xs text-gray-400">${rLabel}</div></div>`;
                });
                html += '</div>';
                seatMapEl.innerHTML = html;
                seatMapEl.querySelectorAll('.seat').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = parseInt(btn.dataset.id, 10);
                        if (btn.classList.contains('cursor-not-allowed')) return;
                        const ids = new Set((gheIdsInput.value || '').split(',').filter(x=>x).map(x=>parseInt(x,10)).filter(n=>!isNaN(n)));
                        if (ids.has(id)) ids.delete(id); else ids.add(id);
                        gheIdsInput.value = Array.from(ids).sort((a,b)=>a-b).join(',');
                        // Toggle to green selection regardless of previous type
                        const selected = btn.classList.toggle('selected-flag');
                        if (selected) {
                            btn.classList.remove('text-white','text-gray-200','border-[#262833]');
                            btn.classList.add('bg-[#22c55e]','border-[#15803d]','text-black');
                        } else {
                            // Re-render the whole map to restore type colors accurately
                            loadSeats(parseInt(document.getElementById('suat_chieu_id').value || 0,10));
                        }
                    });
                });
            }

            function loadSeats(suatId) {
                const url = seatsApiBase.replace('__ID__', suatId) + '?exclude_booking_id=' + bookingId;
                seatMapEl.innerHTML = '<div class="text-center text-gray-400 text-sm py-6">Đang tải sơ đồ ghế...</div>';
                fetch(url, {headers:{'Accept':'application/json'}})
                    .then(r=>r.json())
                    .then(json => {
                        const preset = new Set((gheIdsInput.value || '').split(',').filter(x=>x).map(x=>parseInt(x,10)).filter(n=>!isNaN(n)));
                        renderSeatMap(json, preset);
                    })
                    .catch(()=>{
                        seatMapEl.innerHTML = '<div class="text-center text-red-300 text-sm py-6">Không tải được sơ đồ ghế.</div>';
                    });
            }

            function loadShowtimes() {
                suatSelect.innerHTML = '<option>Đang tải...</option>';
                fetch(availableShowtimesUrl, {headers:{'Accept':'application/json'}})
                    .then(r=>r.json())
                    .then(items => {
                        suatSelect.innerHTML = '';
                        items.forEach(it => {
                            const opt = document.createElement('option');
                            opt.value = it.id; opt.textContent = it.label; if (it.current || it.id === currentShowtimeId) opt.selected = true;
                            suatSelect.appendChild(opt);
                        });
                        const sid = parseInt(suatSelect.value || currentShowtimeId, 10);
                        loadSeats(sid);
                    })
                    .catch(() => {
                        suatSelect.innerHTML = `<option value="${currentShowtimeId}">Hiện tại</option>`;
                        loadSeats(currentShowtimeId);
                    });
            }

            suatSelect.addEventListener('change', function(){ loadSeats(parseInt(this.value,10)); });
            loadShowtimes();
        });
    </script>
@endpush

@endsection
