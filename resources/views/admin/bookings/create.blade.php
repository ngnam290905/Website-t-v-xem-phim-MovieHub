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
            <div class="p-4 bg-gradient-to-r from-blue-900/20 to-purple-900/20 border border-blue-500/30 rounded-xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center">
                        <i class="fas fa-user-tie text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">{{ $user->ho_ten ?? 'Staff' }}</h3>
                        <p class="text-sm text-gray-400">{{ $user->email ?? '' }} (Nhân viên đặt vé)</p>
                    </div>
                </div>
                
                <div class="border-t border-blue-500/30 pt-4">
                    <label class="block text-sm font-semibold text-white mb-2">
                        <i class="fas fa-user-circle mr-2"></i>
                        Chọn khách hàng <span class="text-red-500">*</span>
                    </label>
                    <select id="customer_id" name="customer_id" required
                        class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                        <option value="">-- Chọn khách hàng để đặt vé tại quầy --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->ho_ten }} 
                                @if($customer->email)
                                    ({{ $customer->email }})
                                @endif
                                @if($customer->sdt)
                                    - {{ $customer->sdt }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Vui lòng chọn khách hàng để đặt vé tại quầy. Đây là tính năng dành cho nhân viên đặt vé cho khách hàng.
                    </p>
                </div>
            </div>

            {{-- Bước 1: Chọn phim --}}
            <div id="step-1" class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">1</span>
                        <i class="fas fa-film text-blue-500"></i>
                        Chọn phim
                    </h3>
                </div>

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

                <div id="selected-movie-info" class="p-3 bg-[#10121a] rounded-lg border border-[#262833]" style="display: none;">
                    <p class="text-gray-400 text-sm">Vui lòng chọn phim</p>
                </div>

                <div class="flex justify-end">
                    <button type="button" id="btn-step-1-next" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Tiếp tục <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            {{-- Bước 2: Chọn suất chiếu --}}
            <div id="step-2" class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4" style="display: none;">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">2</span>
                        <i class="fas fa-clock text-blue-500"></i>
                        Chọn suất chiếu
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Ngày chiếu <span class="text-red-500">*</span></label>
                        <input type="date" id="show_date" name="show_date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+30 days')) }}" readonly
                            class="w-full bg-[#1a1d24] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-400 outline-none cursor-not-allowed"
                            title="Đặt vé tại quầy chỉ áp dụng cho ngày hôm nay">
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Đặt vé tại quầy chỉ áp dụng cho ngày hôm nay
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Chọn suất chiếu <span class="text-red-500">*</span></label>
                        <select id="showtime_id" name="showtime_id" required
                            class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                            <option value="">-- Chọn suất chiếu --</option>
                        </select>
                    </div>
                </div>

                <div id="selected-showtime-info" class="p-3 bg-[#10121a] rounded-lg border border-[#262833]" style="display: none;">
                    <p class="text-gray-400 text-sm">Vui lòng chọn suất chiếu</p>
                </div>

                <div class="flex justify-between">
                    <button type="button" id="btn-step-2-back" class="px-6 py-2 bg-[#262833] text-gray-300 rounded-lg hover:bg-[#374151] transition">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại
                    </button>
                    <button type="button" id="btn-step-2-next" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Tiếp tục <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            {{-- Bước 3: Chọn ghế và combo/đồ ăn --}}
            <div id="step-3" class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4" style="display: none;">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">3</span>
                        <i class="fas fa-chair text-blue-500"></i>
                        Chọn ghế và combo/đồ ăn
                    </h3>
                </div>

                <div id="seat-selection-section">

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

                {{-- Chọn combo --}}
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-white flex items-center gap-2 mb-4">
                        <i class="fas fa-box-open text-blue-500"></i>
                        Combo (tùy chọn)
                    </h4>

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

                {{-- Chọn đồ ăn --}}
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-white flex items-center gap-2 mb-4">
                        <i class="fas fa-utensils text-blue-500"></i>
                        Đồ ăn (tùy chọn)
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($foods as $food)
                            <div class="border border-[#262833] rounded-lg p-3 bg-[#10121a]">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="text-sm font-semibold text-white">{{ $food->name }}</h4>
                                        <p class="text-xs text-gray-400 mt-1">{{ number_format($food->price, 0, ',', '.') }} đ</p>
                                        @if($food->stock !== null)
                                            <p class="text-xs text-yellow-400 mt-1">Còn lại: {{ $food->stock }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="decrease-food w-8 h-8 bg-[#262833] text-white rounded hover:bg-[#374151] transition" data-food-id="{{ $food->id }}" data-food-stock="{{ $food->stock ?? 999 }}">-</button>
                                    <input type="number" name="food_quantities[{{ $food->id }}]" value="0" min="0" max="{{ $food->stock ?? 999 }}" 
                                        class="food-quantity w-12 text-center bg-[#1b1e28] border border-[#262833] rounded text-sm text-white" data-food-id="{{ $food->id }}" data-food-price="{{ $food->price }}" readonly>
                                    <button type="button" class="increase-food w-8 h-8 bg-[#262833] text-white rounded hover:bg-[#374151] transition" data-food-id="{{ $food->id }}" data-food-stock="{{ $food->stock ?? 999 }}">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" id="btn-step-3-back" class="px-6 py-2 bg-[#262833] text-gray-300 rounded-lg hover:bg-[#374151] transition">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại
                    </button>
                    <button type="button" id="btn-step-3-next" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Tiếp tục <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            {{-- Bước 4: Thanh toán và ghi chú --}}
            <div id="step-4" class="p-4 bg-[#1b1e28] rounded-xl border border-[#262833] space-y-4" style="display: none;">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">4</span>
                        <i class="fas fa-money-bill-wave text-blue-500"></i>
                        Thanh toán
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Phương thức thanh toán <span class="text-red-500">*</span></label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500 transition">
                            <option value="cash">Tiền mặt</option>
                            <option value="offline">Offline</option>
                            <option value="transfer">Chuyển khoản QR</option>
                        </select>
                        <p id="payment-method-info" class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="payment-method-text">Chọn phương thức thanh toán</span>
                        </p>
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

                <div class="flex justify-between mt-6">
                    <button type="button" id="btn-step-4-back" class="px-6 py-2 bg-[#262833] text-gray-300 rounded-lg hover:bg-[#374151] transition">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại
                    </button>
                    <div class="flex gap-4">
                        <a href="{{ route('admin.bookings.index') }}"
                            class="px-6 py-2 bg-[#262833] text-gray-300 rounded-lg hover:bg-[#374151] transition">
                            Hủy
                        </a>
                        <button type="submit" id="submit-btn" disabled
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-2"></i>
                            Xác nhận đặt vé
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let selectedSeats = [];
        let seatMap = null;
        let currentStep = 1;
        const BASE_PRICE = 100000;

        // Step navigation
        document.getElementById('btn-step-1-next')?.addEventListener('click', function() {
            const movieId = document.getElementById('movie_id').value;
            if (!movieId) {
                alert('Vui lòng chọn phim!');
                return;
            }
            goToStep(2);
        });

        document.getElementById('btn-step-2-back')?.addEventListener('click', function() {
            goToStep(1);
        });

        document.getElementById('btn-step-2-next')?.addEventListener('click', function() {
            const showtimeId = document.getElementById('showtime_id').value;
            if (!showtimeId) {
                alert('Vui lòng chọn suất chiếu!');
                return;
            }
            goToStep(3);
            loadSeatMap(showtimeId);
        });

        document.getElementById('btn-step-3-back')?.addEventListener('click', function() {
            goToStep(2);
        });

        document.getElementById('btn-step-3-next')?.addEventListener('click', function() {
            if (selectedSeats.length === 0) {
                alert('Vui lòng chọn ít nhất một ghế!');
                return;
            }
            goToStep(4);
        });

        document.getElementById('btn-step-4-back')?.addEventListener('click', function() {
            goToStep(3);
        });

        // Cập nhật thông báo khi chọn phương thức thanh toán
        document.getElementById('payment_method')?.addEventListener('change', function() {
            const method = this.value;
            const infoText = document.getElementById('payment-method-text');
            const infoDiv = document.getElementById('payment-method-info');
            
            if (method === 'transfer') {
                infoText.textContent = 'Bạn sẽ được chuyển đến trang thanh toán QR sau khi xác nhận đặt vé';
                infoDiv.className = 'text-xs text-blue-400 mt-1';
            } else if (method === 'cash') {
                infoText.textContent = 'Thanh toán bằng tiền mặt tại quầy';
                infoDiv.className = 'text-xs text-gray-400 mt-1';
            } else if (method === 'offline') {
                infoText.textContent = 'Thanh toán offline tại quầy';
                infoDiv.className = 'text-xs text-gray-400 mt-1';
            } else {
                infoText.textContent = 'Chọn phương thức thanh toán';
                infoDiv.className = 'text-xs text-gray-400 mt-1';
            }
        });

        function goToStep(step) {
            // Hide all steps
            for (let i = 1; i <= 4; i++) {
                const stepEl = document.getElementById(`step-${i}`);
                if (stepEl) {
                    stepEl.style.display = 'none';
                }
            }
            
            // Show target step
            const targetStep = document.getElementById(`step-${step}`);
            if (targetStep) {
                targetStep.style.display = 'block';
            }
            
            currentStep = step;
        }

        // Load showtimes when movie is selected (auto load for today)
        document.getElementById('movie_id')?.addEventListener('change', function() {
            const movieId = this.value;
            const movieName = this.options[this.selectedIndex].text;
            const btnNext = document.getElementById('btn-step-1-next');
            
            if (movieId) {
                btnNext.disabled = false;
                document.getElementById('selected-movie-info').style.display = 'block';
                document.getElementById('selected-movie-info').innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-white">Đã chọn: <strong>${movieName}</strong></span>
                    </div>
                `;
                // Tự động load suất chiếu của ngày hôm nay khi chọn phim
                loadShowtimes();
            } else {
                btnNext.disabled = true;
                document.getElementById('selected-movie-info').style.display = 'none';
            }
        });
        document.getElementById('showtime_id')?.addEventListener('change', function() {
            const showtimeId = this.value;
            const showtimeText = this.options[this.selectedIndex].text;
            const btnNext = document.getElementById('btn-step-2-next');
            
            if (showtimeId) {
                btnNext.disabled = false;
                document.getElementById('selected-showtime-info').style.display = 'block';
                document.getElementById('selected-showtime-info').innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-white">Đã chọn: <strong>${showtimeText}</strong></span>
                    </div>
                `;
            } else {
                btnNext.disabled = true;
                document.getElementById('selected-showtime-info').style.display = 'none';
            }
        });

        function loadShowtimes() {
            const movieId = document.getElementById('movie_id').value;
            // Luôn lấy ngày hôm nay cho đặt vé tại quầy
            const today = new Date().toISOString().split('T')[0];
            const select = document.getElementById('showtime_id');

            if (!movieId) {
                select.innerHTML = '<option value="">-- Chọn suất chiếu --</option>';
                return;
            }

            select.disabled = true;
            select.innerHTML = '<option value="">Đang tải suất chiếu hôm nay...</option>';

            fetch(`/admin/bookings/movie/${movieId}/showtimes?date=${today}`)
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
                        select.innerHTML = '<option value="">Không có suất chiếu hôm nay</option>';
                    }
                    select.disabled = false;
                })
                .catch(err => {
                    select.innerHTML = '<option value="">Lỗi khi tải suất chiếu</option>';
                    select.disabled = false;
                });
        }


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

            // Enable/disable submit button and step 3 next button
            const submitBtn = document.getElementById('submit-btn');
            const btnStep3Next = document.getElementById('btn-step-3-next');
            const canProceed = selectedSeats.length > 0;
            
            if (submitBtn) submitBtn.disabled = !canProceed;
            if (btnStep3Next) btnStep3Next.disabled = !canProceed;
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

        // Food quantity controls
        document.querySelectorAll('.increase-food').forEach(btn => {
            btn.addEventListener('click', function() {
                const foodId = this.dataset.foodId;
                const maxStock = parseInt(this.dataset.foodStock) || 999;
                const input = document.querySelector(`.food-quantity[data-food-id="${foodId}"]`);
                const current = parseInt(input.value) || 0;
                if (current < maxStock) {
                    input.value = current + 1;
                    calculateTotal();
                } else {
                    alert('Đã đạt số lượng tối đa!');
                }
            });
        });

        document.querySelectorAll('.decrease-food').forEach(btn => {
            btn.addEventListener('click', function() {
                const foodId = this.dataset.foodId;
                const input = document.querySelector(`.food-quantity[data-food-id="${foodId}"]`);
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

            let foodTotal = 0;

            document.querySelectorAll('.food-quantity').forEach(input => {
                const qty = parseInt(input.value) || 0;
                const price = parseFloat(input.dataset.foodPrice) || 0;
                if (qty > 0) {
                    foodTotal += qty * price;
                }
            });

            const total = seatTotal + comboTotal + foodTotal;

            document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
            
            const breakdown = document.getElementById('price-breakdown');
            breakdown.innerHTML = `
                <div>Ghế: ${new Intl.NumberFormat('vi-VN').format(seatTotal)} đ</div>
                ${comboTotal > 0 ? `<div>Combo: ${new Intl.NumberFormat('vi-VN').format(comboTotal)} đ</div>` : ''}
                ${foodTotal > 0 ? `<div>Đồ ăn: ${new Intl.NumberFormat('vi-VN').format(foodTotal)} đ</div>` : ''}
            `;
        }

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

            const paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod || !['cash', 'offline', 'transfer'].includes(paymentMethod)) {
                e.preventDefault();
                alert('Vui lòng chọn phương thức thanh toán!');
                return false;
            }

            // Nếu chọn chuyển khoản QR, hiển thị thông báo
            if (paymentMethod === 'transfer') {
                // Cho phép form submit bình thường, server sẽ redirect đến trang QR
                // Không cần preventDefault
            }

            // Đảm bảo tất cả dữ liệu được gửi đúng
            const form = this;
            const formData = new FormData(form);
            
            // Debug: Log form data (có thể xóa sau)
            console.log('Form data:', {
                showtime_id: formData.get('showtime_id'),
                seat_ids: formData.getAll('seat_ids[]'),
                payment_method: formData.get('payment_method'),
                customer_id: formData.get('customer_id')
            });
        });
    </script>
    @endpush
@endsection

