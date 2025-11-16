@extends('admin.layout')

@section('title', 'Chỉnh sửa vé')

@section('content')
<<<<<<< HEAD
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Chỉnh sửa Đặt Vé #{{ $booking->id }}</h1>
            <p class="text-[#a6a6b0]">Cập nhật thông tin đặt vé của khách hàng</p>
        </div>
        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]">
            <i class="fas fa-eye mr-2"></i> Xem chi tiết
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="space-y-6" id="edit-booking-form">
            @csrf
            @method('PUT')

            <!-- Showtime Selection -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Thay đổi suất chiếu</h3>
                </div>
                <select id="suat-chieu-select" name="suat_chieu_id" class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white">
                    <!-- Options will be loaded via JavaScript -->
                </select>
                <p class="text-xs text-[#a6a6b0] mt-2">Chỉ hiển thị suất cùng phim, còn hiệu lực.</p>
            </div>

            <!-- Status Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                        <h3 class="text-white font-medium">Trạng thái đặt vé</h3>
                    </div>
                    <select name="trang_thai" class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white">
                        <option value="0" {{ $booking->trang_thai == 0 ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="1" {{ $booking->trang_thai == 1 ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="3" {{ $booking->trang_thai == 3 ? 'selected' : '' }}>Yêu cầu hủy</option>
                        <option value="2" {{ $booking->trang_thai == 2 ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>

                <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-wallet text-white text-sm"></i>
                        </div>
                        <h3 class="text-white font-medium">Trạng thái thanh toán</h3>
                    </div>
                    <select name="trang_thai_thanh_toan" class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white">
                        <option value="0" {{ ($booking->trang_thai_thanh_toan ?? ($booking->trang_thai == 1 ? 1 : 0)) == 0 ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="1" {{ ($booking->trang_thai_thanh_toan ?? ($booking->trang_thai == 1 ? 1 : 0)) == 1 ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="2" {{ ($booking->trang_thai_thanh_toan ?? ($booking->trang_thai == 1 ? 1 : 0)) == 2 ? 'selected' : '' }}>Đã hoàn tiền</option>
                    </select>
                </div>
            </div>

            <!-- Discount Code -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-tag text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Mã giảm giá</h3>
                </div>
                <input type="text" name="ma_km" value="{{ old('ma_km') }}" placeholder="Nhập mã (VD: DEMO10)"
                       class="w-full bg-[#151822] border border-[#262833] rounded-lg px-4 py-2 text-white" />
                <p class="text-xs text-[#a6a6b0] mt-2">Mã hợp lệ sẽ được áp ngay khi lưu. Để bỏ mã, để trống trường này.</p>
            </div>

            <!-- Seat Selection -->
            <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-couch text-white text-sm"></i>
                    </div>
                    <h3 class="text-white font-medium">Thay đổi ghế</h3>
                </div>
                <div id="seat-map" class="grid grid-cols-8 gap-2 min-h-[100px]"></div>
                @section('content')
                    <div class="bg-[#151822] p-6 rounded-xl border border-[#262833]">
                        <h2 class="text-xl font-semibold mb-4">✏️ Chỉnh sửa Đặt Vé #{{ $booking->id }}</h2>
                        @if ($errors->any())
                            <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg mb-4" role="alert">
                                <strong class="font-bold">Có lỗi xảy ra!</strong>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg mb-4" role="alert">
                                <strong class="font-bold">Lỗi!</strong>
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="space-y-6"
                            id="edit-booking-form">
                            @csrf
                            @method('PUT')

                            {{-- Giữ nguyên Suất chiếu, Ghi chú, Trạng thái, Mã giảm giá --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block mb-1 text-sm text-gray-300">Thay đổi suất chiếu</label>
                                    <select id="suat-chieu-select" name="suat_chieu_id"
                                        class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200"></select>
                                    <p class="text-xs text-gray-400 mt-1">Chỉ hiển thị suất cùng phim, còn hiệu lực.</p>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm text-gray-300">Ghi chú nội bộ</label>
                                    <textarea name="ghi_chu_noi_bo" rows="3"
                                        class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200"
                                        placeholder="Ghi chú nội bộ...">{{ old('ghi_chu_noi_bo', $booking->ghi_chu_noi_bo ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block mb-1 text-sm text-gray-300">Thay đổi Trạng thái</label>
                                    <select name="trang_thai"
                                        class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200">

                                        @switch($booking->trang_thai)
                                            @case(0)
                                                {{-- Đang: Chờ xác nhận --}}
                                                <option value="0" selected>Chờ xác nhận</option>
                                                <option value="1">✅ Xác nhận vé</option>
                                                <option value="3">⚠️ Đánh dấu Yêu cầu hủy</option>
                                                <option value="2">❌ Hủy vé</option>
                                            @break

                                            @case(1)
                                                {{-- Đang: Đã xác nhận --}}
                                                <option value="1" selected>Đã xác nhận</option>
                                                <option value="2">❌ Hủy vé</option>
                                            @break

                                            @case(3)
                                                {{-- Đang: Yêu cầu hủy --}}
                                                <option value="3" selected>Yêu cầu hủy</option>
                                                <option value="2">👍 Chấp nhận hủy</option>
                                                <option value="1">🚫 Từ chối hủy</option>
                                            @break

                                            @case(2)
                                                {{-- Đang: Đã hủy --}}
                                                <option value="2" selected disabled>Đã hủy</option>
                                            @break
                                        @endswitch

                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm text-gray-300">Mã giảm giá</label>
                                    <input type="text" name="ma_km" value="{{ old('ma_km', optional($booking->khuyenMai)->ma_km) }}"
                                        placeholder="Nhập mã (VD: DEMO10)"
                                        class="w-full bg-[#1d202a] border border-[#262833] rounded p-2 text-sm text-gray-200" />
                                    <p class="text-xs text-gray-400 mt-1">Mã hợp lệ sẽ được áp ngay khi lưu. Để bỏ mã, để trống trường
                                        này.</p>
                                </div>
                            </div>

                            {{-- Giữ nguyên Sơ đồ ghế --}}
                            <div>
                                <label class="block mb-2 text-sm text-gray-300">Thay đổi ghế</label>
                                <div id="seat-map-container" class="p-4 bg-[#1d202a] border border-[#262833] rounded-lg">
                                    <div id="seat-map" class="grid grid-cols-10 md:grid-cols-12 lg:grid-cols-16 gap-2 place-items-center">
                                        <p class="col-span-10 text-gray-400">Đang tải sơ đồ ghế...</p>
                                    </div>
                                </div>
                                <input type="hidden" name="ghe_ids" id="ghe-ids" value="{{ old('ghe_ids') }}">
                                <p class="text-xs text-gray-400 mt-1">Chọn các ghế trống trên sơ đồ. Giá tự tính theo loại ghế.</p>
                                <p class="text-xs text-gray-400 mt-1">Lưu ý: Nếu đổi suất chiếu, các ghế đã chọn sẽ được làm mới.</p>
                            </div>


                            <div>
                                <label class="block mb-1 text-sm text-gray-300">Chọn combo (nếu có)</label>
                                <div
                                    class="w-full bg-[#1d202a] border border-[#262833] rounded p-4 text-sm text-gray-200 h-48 overflow-y-auto space-y-3">
                                    @if (isset($combos) && $combos->count() > 0)
                                        @foreach ($combos as $combo)
                                            @php
                                                // Lấy số lượng cũ (nếu có)
                                                $oldQuantity = old(
                                                    'combo_quantities.' . $combo->id,
                                                    $selectedComboQuantities[$combo->id] ?? 1,
                                                );
                                                // Kiểm tra check cũ (nếu có)
                                                $isChecked = in_array($combo->id, old('combo_ids', $selectedComboIds ?? []));
                                            @endphp
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <input type="checkbox" name="combo_ids[]" value="{{ $combo->id }}"
                                                        id="combo_{{ $combo->id }}" class="mr-2 rounded"
                                                        {{ $isChecked ? 'checked' : '' }}>
                                                    <label for="combo_{{ $combo->id }}">
                                                        {{ $combo->ten }} - <span
                                                            class="text-yellow-400">{{ number_format($combo->gia, 0, ',', '.') }}đ</span>
                                                    </label>
                                                </div>
                                                <input type="number" name="combo_quantities[{{ $combo->id }}]"
                                                    value="{{ $oldQuantity }}" min="1"
                                                    class="w-20 bg-[#262833] border border-[#3a3d4a] rounded p-1 text-sm text-center">
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-gray-400">Không có combo nào hợp lệ</p>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Chọn combo và nhập số lượng mong muốn.</p>
                            </div>


                            <div class="flex items-center gap-4 pt-4 border-t border-[#262833]">
                                <button type="submit"
                                    class="px-5 py-2 bg-green-600 hover:bg-green-700 rounded text-white text-sm font-medium">Lưu thay
                                    đổi</button>
                                <a href="{{ route('admin.bookings.index') }}"
                                    class="px-5 py-2 bg-gray-600 hover:bg-gray-700 rounded text-white text-sm">Hủy bỏ</a>
                            </div>
                        </form>

                        @push('scripts')
                            <script>
                                document.addEventListener('DOMContentLoaded', async function() {
                                    const bookingId = {{ $booking->id }};
                                    const currentShowtimeId = {{ $booking->id_suat_chieu }};
                                    const currentSelectedGheIds = @json($selectedGheIds ?? []);

                                    const showtimeSelect = document.getElementById('suat-chieu-select');
                                    const seatMap = document.getElementById('seat-map');
                                    const gheIdsInput = document.getElementById('ghe-ids');

                                    let selected = new Set();

                                    async function loadShowtimes() {
                                        try {
                                            const res = await fetch(`{{ route('admin.bookings.available-showtimes', ':id') }}`
                                                .replace(':id', bookingId));
                                            if (!res.ok) throw new Error('Không thể tải suất chiếu');
                                            const items = await res.json();
                                            showtimeSelect.innerHTML = '';
                                            items.forEach(it => {
                                                const opt = document.createElement('option');
                                                opt.value = it.id;
                                                opt.textContent = it.label;
                                                if (it.current) opt.selected = true;
                                                showtimeSelect.appendChild(opt);
                                            });
                                        } catch (error) {
                                            console.error(error);
                                            showtimeSelect.innerHTML = '';
                                            showtimeSelect.innerHTML =
                                                `<option value="${currentShowtimeId}">Lỗi tải DS suất chiếu. Giữ suất hiện tại.</option>`;
                                        }
                                    }

                                    async function loadSeats(showtimeId) {
                                        if (!showtimeId) {
                                            seatMap.innerHTML =
                                                '<p class="col-span-10 text-gray-400">Vui lòng chọn suất chiếu.</p>';
                                            return;
                                        }

                                        const url = `{{ route('admin.showtimes.seats', ':sid') }}`.replace(':sid', showtimeId) +
                                            `?exclude_booking_id=${bookingId}`;

                                        try {
                                            const res = await fetch(url);
                                            if (!res.ok) throw new Error('Không thể tải sơ đồ ghế');
                                            const data = await res.json();

                                            seatMap.innerHTML = '';
                                            selected.clear();

                                            // Chỉ nạp các ghế đã chọn nếu admin đang xem suất chiếu GỐC
                                            if (parseInt(showtimeId) === currentShowtimeId) {
                                                currentSelectedGheIds.forEach(id => selected.add(id));
                                            }

                                            // 💡 Tự động điều chỉnh số cột dựa trên dữ liệu (nếu có)
                                            // Ví dụ: tìm 'col' max trong 'seat.label' hoặc dùng 'so_cot' từ API (nếu bạn thêm)
                                            // Tạm thời giữ cố định
                                            // seatMap.className = `grid grid-cols-${data.room.cols || 12} ...`

                                            data.seats.forEach(seat => {
                                                const btn = document.createElement('button');
                                                btn.type = 'button';
                                                btn.textContent = seat.label;

                                                let classes = 'w-10 h-10 text-xs rounded border ';
                                                if (seat.booked) {
                                                    classes +=
                                                        'bg-gray-700 border-gray-600 text-gray-400 cursor-not-allowed opacity-50';
                                                } else {
                                                    classes +=
                                                        'bg-[#1d202a] border-[#262833] text-gray-200 hover:bg-[#232735]';
                                                }
                                                btn.className = classes;
                                                btn.disabled = !!seat.booked;
                                                btn.dataset.id = seat.id;

                                                if (selected.has(seat.id) && !seat.booked) {
                                                    btn.classList.add('ring-2', 'ring-[#F53003]', 'bg-[#232735]');
                                                }

                                                btn.addEventListener('click', () => {
                                                    if (seat.booked) return;

                                                    if (selected.has(seat.id)) {
                                                        selected.delete(seat.id);
                                                        btn.classList.remove('ring-2', 'ring-[#F53003]',
                                                            'bg-[#232735]');
                                                        btn.classList.add('bg-[#1d202a]', 'border-[#262833]');
                                                    } else {
                                                        selected.add(seat.id);
                                                        btn.classList.add('ring-2', 'ring-[#F53003]',
                                                            'bg-[#232735]');
                                                        btn.classList.remove('bg-[#1d202a]', 'border-[#262833]');
                                                    }
                                                    gheIdsInput.value = Array.from(selected).join(',');
                                                });
                                                seatMap.appendChild(btn);
                                            });

                                            gheIdsInput.value = Array.from(selected).join(',');

                                        } catch (error) {
                                            console.error(error);
                                            seatMap.innerHTML = `<p class="col-span-10 text-red-400">Lỗi khi tải sơ đồ ghế.</p>`;
                                        }
                                    }

                                    // --- Khởi tạo ---
                                    await loadShowtimes();
                                    await loadSeats(showtimeSelect.value || currentShowtimeId);

                                    showtimeSelect.addEventListener('change', async (e) => {
                                        await loadSeats(e.target.value);
                                    });
                                });
                            </script>
                        @endpush
                    </div>
                @endsection

