@extends('admin.layout')

@section('title', 'Đặt vé mới')

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
            <div class="bg-red-900/40 border border-red-600 text-sm text-red-100 px-4 py-3 rounded-md">
                {{ session('error') }}
            </div>
        @endif

        <form id="staff-booking-form" action="{{ route('admin.bookings.store') }}" method="POST"
            class="bg-[#151822] border border-[#262833] rounded-2xl p-6 space-y-6">
            @csrf

            {{-- Thông tin người đặt --}}
            <div class="p-4 bg-gradient-to-r from-blue-900/20 to-purple-900/20 border border-blue-500/30 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">{{ $user->ho_ten ?? 'Staff' }}</h3>
                        <p class="text-sm text-gray-400">{{ $user->email ?? '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Bước 1: Chọn phim và suất chiếu --}}
            <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fas fa-film text-blue-500"></i>
                    Chọn phim và suất chiếu
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Chọn phim <span class="text-red-500">*</span></label>
                        <select id="movie_id" name="movie_id" required
                            class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                            <option value="">-- Chọn phim --</option>
                            @foreach($movies as $movie)
                                <option value="{{ $movie->id }}">{{ $movie->ten_phim }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Chọn ngày <span class="text-red-500">*</span></label>
                        <input type="date" id="show_date" name="show_date" value="{{ old('show_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+30 days')) }}"
                            class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Chọn suất chiếu <span class="text-red-500">*</span></label>
                        <select id="showtime_id" name="showtime_id" required
                            class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                            <option value="">-- Chọn suất chiếu --</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Bước 2: Chọn ghế --}}
            <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4" id="seat-selection-section" style="display: none;">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fas fa-chair text-blue-500"></i>
                    Chọn ghế
                </h3>

                <style>
                    .seat-map-wrapper {
                        background: linear-gradient(180deg, #0f1117 0%, #1a1d24 100%);
                        border-radius: 16px;
                        padding: 24px;
                        border: 1px solid #262833;
                    }
                    
                    .screen-display {
                        background: linear-gradient(180deg, #2a2d3a 0%, #1a1d24 100%);
                        border: 2px solid #3a3d4a;
                        border-radius: 12px;
                        padding: 16px 32px;
                        margin: 0 auto 32px;
                        max-width: 600px;
                        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                        position: relative;
                    }
                    
                    .screen-display::before {
                        content: '';
                        position: absolute;
                        top: -2px;
                        left: 50%;
                        transform: translateX(-50%);
                        width: 80%;
                        height: 4px;
                        background: linear-gradient(90deg, transparent, #F53003, transparent);
                        border-radius: 2px;
                    }
                    
                    .seat-btn {
                        width: 44px;
                        height: 44px;
                        border-radius: 8px;
                        border: 2px solid;
                        font-size: 12px;
                        font-weight: 700;
                        transition: all 0.2s ease;
                        position: relative;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                    }
                    
                    .seat-btn.seat-available {
                        background: linear-gradient(135deg, #2a2d3a 0%, #1a1d24 100%);
                        border-color: #3a3d4a;
                        color: #e6e7eb;
                    }
                    
                    .seat-btn.seat-available:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
                        border-color: #3b82f6;
                    }
                    
                    .seat-btn.seat-vip {
                        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                        border-color: #b45309;
                        color: #fff;
                        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
                    }
                    
                    .seat-btn.seat-vip:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.5);
                    }
                    
                    .seat-btn.seat-couple {
                        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
                        border-color: #be185d;
                        color: #fff;
                        box-shadow: 0 2px 8px rgba(236, 72, 153, 0.3);
                    }
                    
                    .seat-btn.seat-couple:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 16px rgba(236, 72, 153, 0.5);
                    }
                    
                    .seat-btn.seat-selected {
                        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                        border-color: #15803d;
                        color: #fff;
                        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.5);
                        transform: scale(1.05);
                    }
                    
                    .seat-btn.seat-booked {
                        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
                        border-color: #374151;
                        color: #6b7280;
                        opacity: 0.6;
                        cursor: not-allowed;
                    }
                    
                    .seat-row {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 6px;
                        margin-bottom: 8px;
                    }
                    
                    .row-label {
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: 700;
                        font-size: 14px;
                        color: #9ca3af;
                        background: #1a1d24;
                        border-radius: 6px;
                    }
                </style>

                <div class="seat-map-wrapper">
                    <div class="screen-display text-center">
                        <div class="text-white font-bold text-lg tracking-wider">MÀN HÌNH</div>
                        <div class="text-gray-400 text-xs mt-1">Screen</div>
                    </div>
                    
                    <div id="seat-map-container" class="overflow-x-auto pb-4">
                        <div id="seat-map" class="min-w-max mx-auto">
                            <p class="text-center text-gray-400 py-8">Vui lòng chọn suất chiếu trước</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-gray-300 bg-[#10121a] p-4 rounded-lg border border-[#262833]">
                    <div class="flex items-center gap-2">
                        <span class="seat-btn seat-available w-5 h-5"></span>
                        <span>Thường (100k)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="seat-btn seat-vip w-5 h-5"></span>
                        <span>VIP (150k)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="seat-btn seat-couple w-5 h-5"></span>
                        <span>Đôi (200k)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="seat-btn seat-booked w-5 h-5"></span>
                        <span>Đã đặt</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="seat-btn seat-selected w-5 h-5"></span>
                        <span>Đang chọn</span>
                    </div>
                </div>

                <div id="seat-ids-container"></div>
                <div id="selected-seats-display" class="text-sm text-gray-300 bg-[#10121a] p-3 rounded-lg border border-[#262833]">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <span>Chưa chọn ghế nào</span>
                </div>
            </div>

            {{-- Bước 3: Chọn combo --}}
            <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fas fa-box-open text-blue-500"></i>
                    Combo (tùy chọn)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($combos as $combo)
                        <div class="border border-[#262833] rounded-lg p-3 bg-[#10121a]">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="text-sm font-semibold text-white">{{ $combo->ten }}</h4>
                                    <p class="text-xs text-gray-400 mt-1">{{ number_format($combo->gia, 0, ',', '.') }} đ</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="decrease-combo w-8 h-8 bg-[#262833] text-white rounded hover:bg-[#374151] transition" data-combo-id="{{ $combo->id }}">-</button>
                                <input type="number" name="combo_quantities[{{ $combo->id }}]" value="0" min="0" max="10" 
                                    class="combo-quantity w-12 text-center bg-[#1b1e28] border border-[#262833] rounded text-sm text-white" data-combo-id="{{ $combo->id }}" data-combo-price="{{ $combo->gia }}" readonly>
                                <button type="button" class="increase-combo w-8 h-8 bg-[#262833] text-white rounded hover:bg-[#374151] transition" data-combo-id="{{ $combo->id }}">+</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bước 4: Khuyến mãi --}}
            <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fas fa-gift text-blue-500"></i>
                    Khuyến mãi (tùy chọn)
                </h3>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Chọn mã khuyến mãi</label>
                    <select id="promotion_id" name="promotion_id"
                        class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                        <option value="">-- Không sử dụng khuyến mãi --</option>
                        @foreach($promotions as $promo)
                            <option value="{{ $promo->id }}" data-type="{{ $promo->loai_giam }}" data-value="{{ $promo->gia_tri_giam }}">
                                {{ $promo->ten_km }} - 
                                @if($promo->loai_giam === 'phantram')
                                    {{ $promo->gia_tri_giam }}%
                                @else
                                    {{ number_format($promo->gia_tri_giam, 0, ',', '.') }} đ
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Bước 5: Thanh toán và ghi chú --}}
            <div class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fas fa-money-bill-wave text-blue-500"></i>
                    Thanh toán
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Phương thức thanh toán <span class="text-red-500">*</span></label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                            <option value="online">Thanh toán online (VNPay)</option>
                            <option value="cash">Tiền mặt</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Ghi chú nội bộ</label>
                        <textarea name="notes" rows="2"
                            class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition"
                            placeholder="Ghi chú về đơn hàng này...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Tổng tiền --}}
            <div class="p-4 bg-gradient-to-r from-blue-900/30 to-purple-900/30 border border-blue-500/30 rounded-xl">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold text-white">Tổng tiền:</span>
                    <span id="total-amount" class="text-2xl font-bold text-green-400">0 đ</span>
                </div>
                <div id="price-breakdown" class="mt-2 text-sm text-gray-300 space-y-1"></div>
            </div>

            {{-- Nút submit --}}
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('admin.bookings.index') }}"
                    class="px-6 py-2 bg-[#262833] text-gray-300 rounded-lg hover:bg-[#374151] transition">
                    Hủy
                </a>
                <button type="submit" id="submit-btn" disabled
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check mr-2"></i>
                    Xác nhận đặt vé
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let selectedSeats = [];
        let seatMap = null;
        const BASE_PRICE = 100000;

        // Load showtimes when movie and date are selected
        document.getElementById('movie_id')?.addEventListener('change', loadShowtimes);
        document.getElementById('show_date')?.addEventListener('change', loadShowtimes);

        function loadShowtimes() {
            const movieId = document.getElementById('movie_id').value;
            const date = document.getElementById('show_date').value;
            const select = document.getElementById('showtime_id');

            if (!movieId || !date) {
                select.innerHTML = '<option value="">-- Chọn suất chiếu --</option>';
                return;
            }

            select.disabled = true;
            select.innerHTML = '<option value="">Đang tải...</option>';

            fetch(`/admin/bookings/movie/${movieId}/showtimes?date=${date}`)
                .then(res => res.json())
                .then(data => {
                    select.innerHTML = '<option value="">-- Chọn suất chiếu --</option>';
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(st => {
                            const option = document.createElement('option');
                            option.value = st.id;
                            option.textContent = `${st.time} - ${st.room_name} (${st.available_seats} ghế trống)`;
                            select.appendChild(option);
                        });
                    } else {
                        select.innerHTML = '<option value="">Không có suất chiếu</option>';
                    }
                    select.disabled = false;
                })
                .catch(err => {
                    select.innerHTML = '<option value="">Lỗi khi tải suất chiếu</option>';
                    select.disabled = false;
                });
        }

        // Load seat map when showtime is selected
        document.getElementById('showtime_id')?.addEventListener('change', function() {
            const showtimeId = this.value;
            if (!showtimeId) {
                document.getElementById('seat-selection-section').style.display = 'none';
                return;
            }

            loadSeatMap(showtimeId);
        });

        function loadSeatMap(showtimeId) {
            const container = document.getElementById('seat-map');
            container.innerHTML = '<p class="text-center text-gray-400 py-8">Đang tải sơ đồ ghế...</p>';
            document.getElementById('seat-selection-section').style.display = 'block';

            fetch(`/admin/showtimes/${showtimeId}/seats`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        container.innerHTML = `<p class="text-center text-red-400 py-8">${data.error}</p>`;
                        return;
                    }

                    renderSeatMap(data);
                })
                .catch(err => {
                    container.innerHTML = '<p class="text-center text-red-400 py-8">Lỗi khi tải sơ đồ ghế</p>';
                });
        }

        function renderSeatMap(data) {
            const container = document.getElementById('seat-map');
            container.innerHTML = '';
            
            // Clear seat data map
            seatDataMap = {};

            // Group seats by row
            const seatsByRow = {};
            data.seats.forEach(seat => {
                const row = seat.row || seat.label.charAt(0);
                if (!seatsByRow[row]) {
                    seatsByRow[row] = [];
                }
                seatsByRow[row].push(seat);
            });

            // Render rows
            Object.keys(seatsByRow).sort().forEach(row => {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'seat-row';
                
                // Row label
                const rowLabel = document.createElement('div');
                rowLabel.className = 'row-label';
                rowLabel.textContent = row;
                rowDiv.appendChild(rowLabel);

                // Seats
                seatsByRow[row].sort((a, b) => a.col - b.col).forEach(seat => {
                    // Store seat data for price calculation
                    seatDataMap[seat.id] = seat;
                    
                    const seatBtn = document.createElement('button');
                    seatBtn.type = 'button';
                    seatBtn.classList.add('seat-btn');
                    seatBtn.dataset.seatId = seat.id;
                    seatBtn.dataset.seatLabel = seat.label;

                    // Determine seat class based on type and booking status
                    // Ensure type is valid (default to 1 if undefined/null)
                    const seatType = seat.type || 1;
                    
                    if (seat.booked) {
                        seatBtn.classList.add('seat-booked');
                        seatBtn.disabled = true;
                    } else {
                        // Set class based on seat type
                        if (seatType === 2) { // VIP
                            seatBtn.classList.add('seat-vip');
                        } else if (seatType === 3) { // Couple
                            seatBtn.classList.add('seat-couple');
                        } else { // Normal (type 1 or default)
                            seatBtn.classList.add('seat-available');
                        }
                        seatBtn.addEventListener('click', () => toggleSeat(seat.id, seat.label, seatBtn));
                    }

                    seatBtn.textContent = seat.label.replace(row, '');
                    rowDiv.appendChild(seatBtn);
                });

                container.appendChild(rowDiv);
            });
        }

        function toggleSeat(seatId, seatLabel, btn) {
            const index = selectedSeats.findIndex(s => s.id === seatId);
            const seatInfo = seatDataMap[seatId];
            // Ensure type is valid (default to 1 if undefined/null)
            const seatType = (seatInfo && seatInfo.type) ? seatInfo.type : 1;
            
            if (index > -1) {
                // Deselect seat - restore original class based on seat type
                selectedSeats.splice(index, 1);
                btn.classList.remove('seat-selected');
                
                // Restore original class based on seat type
                if (seatType === 2) {
                    // VIP seat
                    btn.classList.remove('seat-available', 'seat-couple');
                    btn.classList.add('seat-vip');
                } else if (seatType === 3) {
                    // Couple seat
                    btn.classList.remove('seat-available', 'seat-vip');
                    btn.classList.add('seat-couple');
                } else {
                    // Normal seat (type 1 or default)
                    btn.classList.remove('seat-vip', 'seat-couple');
                    btn.classList.add('seat-available');
                }
            } else {
                // Select seat
                selectedSeats.push({ id: seatId, label: seatLabel });
                btn.classList.remove('seat-available', 'seat-vip', 'seat-couple');
                btn.classList.add('seat-selected');
            }

            updateSelectedSeats();
            calculateTotal();
        }

        function updateSelectedSeats() {
            // Update hidden inputs for seat_ids array
            const container = document.getElementById('seat-ids-container');
            container.innerHTML = ''; // Clear existing inputs
            
            selectedSeats.forEach(seat => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'seat_ids[]';
                input.value = seat.id;
                container.appendChild(input);
            });
            
            const display = document.getElementById('selected-seats-display');
            if (selectedSeats.length > 0) {
                display.innerHTML = `
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="font-semibold">Đã chọn ${selectedSeats.length} ghế:</span>
                    <span class="ml-2">${selectedSeats.map(s => s.label).join(', ')}</span>
                `;
            } else {
                display.innerHTML = `
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <span>Chưa chọn ghế nào</span>
                `;
            }

            // Enable/disable submit button
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = selectedSeats.length === 0 || !document.getElementById('showtime_id').value;
        }

        // Combo quantity controls
        document.querySelectorAll('.increase-combo').forEach(btn => {
            btn.addEventListener('click', function() {
                const comboId = this.dataset.comboId;
                const input = document.querySelector(`.combo-quantity[data-combo-id="${comboId}"]`);
                const current = parseInt(input.value) || 0;
                if (current < 10) {
                    input.value = current + 1;
                    calculateTotal();
                }
            });
        });

        document.querySelectorAll('.decrease-combo').forEach(btn => {
            btn.addEventListener('click', function() {
                const comboId = this.dataset.comboId;
                const input = document.querySelector(`.combo-quantity[data-combo-id="${comboId}"]`);
                const current = parseInt(input.value) || 0;
                if (current > 0) {
                    input.value = current - 1;
                    calculateTotal();
                }
            });
        });

        // Store seat data for price calculation
        let seatDataMap = {};

        // Calculate total
        function calculateTotal() {
            let seatTotal = 0;
            selectedSeats.forEach(seat => {
                const seatInfo = seatDataMap[seat.id];
                if (seatInfo) {
                    // id_loai: 1 = Thường (100k), 2 = VIP (150k), 3 = Đôi (200k)
                    const seatType = seatInfo.type || 1;
                    let price = BASE_PRICE;
                    if (seatType === 2) price = 150000; // VIP
                    else if (seatType === 3) price = 200000; // Couple
                    seatTotal += price;
                } else {
                    seatTotal += BASE_PRICE; // Default
                }
            });
            
            let comboTotal = 0;

            document.querySelectorAll('.combo-quantity').forEach(input => {
                const qty = parseInt(input.value) || 0;
                const price = parseFloat(input.dataset.comboPrice) || 0;
                if (qty > 0) {
                    comboTotal += qty * price;
                }
            });

            const subtotal = seatTotal + comboTotal;
            let discount = 0;

            const promoSelect = document.getElementById('promotion_id');
            if (promoSelect.value) {
                const option = promoSelect.options[promoSelect.selectedIndex];
                const type = option.dataset.type;
                const value = parseFloat(option.dataset.value) || 0;

                if (type === 'phantram') {
                    discount = Math.round(subtotal * (value / 100));
                } else {
                    discount = value;
                }
            }

            const total = Math.max(0, subtotal - discount);

            document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
            
            const breakdown = document.getElementById('price-breakdown');
            breakdown.innerHTML = `
                <div>Ghế: ${new Intl.NumberFormat('vi-VN').format(seatTotal)} đ</div>
                ${comboTotal > 0 ? `<div>Combo: ${new Intl.NumberFormat('vi-VN').format(comboTotal)} đ</div>` : ''}
                ${discount > 0 ? `<div class="text-green-400">Giảm: -${new Intl.NumberFormat('vi-VN').format(discount)} đ</div>` : ''}
            `;
        }

        // Recalculate on promo change
        document.getElementById('promotion_id')?.addEventListener('change', calculateTotal);

        // Form validation
        document.getElementById('staff-booking-form')?.addEventListener('submit', function(e) {
            if (selectedSeats.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một ghế!');
                return false;
            }

            if (!document.getElementById('showtime_id').value) {
                e.preventDefault();
                alert('Vui lòng chọn suất chiếu!');
                return false;
            }

        });
    </script>
    @endpush
@endsection

