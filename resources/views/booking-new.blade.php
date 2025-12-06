@extends('layouts.main')

@section('title', 'Đặt vé - MovieHub')

@section('content')
    @php
        $combos = App\Models\Combo::where('trang_thai', 1)->get();
        $khuyenmais = App\Models\KhuyenMai::where('trang_thai', 1)
            ->where('ngay_bat_dau', '<=', now())
            ->where('ngay_ket_thuc', '>=', now())
            ->get();
        
        // Group showtimes by date
        $showtimesByDate = [];
        foreach ($showtimes as $st) {
            $dateKey = $st['date'];
            if (!isset($showtimesByDate[$dateKey])) {
                $showtimesByDate[$dateKey] = [];
            }
            $showtimesByDate[$dateKey][] = $st;
        }
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-900 text-white">
        <!-- Header -->
        <div class="bg-gray-900/80 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Quay lại</span>
                    </a>
                    <h1 class="text-xl font-bold">Đặt vé xem phim</h1>
                    <div class="w-24"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Movie Info Card -->
            <div class="bg-gradient-to-r from-gray-800/50 to-gray-900/50 backdrop-blur-sm rounded-2xl p-6 mb-8 border border-gray-700/50">
                <div class="flex gap-6">
                    <img src="{{ $movie->poster ?? 'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg' }}"
                        alt="{{ $movie->ten_phim ?? 'Movie' }}" 
                        class="w-32 h-48 object-cover rounded-xl shadow-2xl">
                    <div class="flex-1">
                        <h2 class="text-3xl font-bold mb-2">{{ $movie->ten_phim ?? 'Movie Title' }}</h2>
                        <div class="flex items-center gap-4 text-gray-400 mb-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $movie->thoi_luong ?? '120' }} phút
                            </span>
                            <span class="bg-yellow-600/20 text-yellow-400 px-3 py-1 rounded-full text-sm font-medium">T13</span>
                        </div>
                        <p class="text-gray-300">{{ $movie->mo_ta ?? 'Mô tả phim...' }}</p>
                    </div>
                </div>
            </div>

            <!-- Stepper -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center flex-1">
                        <div class="step-item flex items-center flex-1" data-step="1">
                            <div class="step-number flex items-center justify-center w-12 h-12 rounded-full bg-red-600 text-white font-bold text-lg border-4 border-gray-900">
                                1
                            </div>
                            <div class="ml-4">
                                <p class="font-semibold text-white">Chọn ngày & giờ</p>
                                <p class="text-sm text-gray-400">Chọn ngày xem phim</p>
                            </div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-700 mx-4 step-line"></div>
                    </div>
                    
                    <div class="flex items-center flex-1">
                        <div class="step-item flex items-center flex-1" data-step="2">
                            <div class="step-number flex items-center justify-center w-12 h-12 rounded-full bg-gray-700 text-gray-400 font-bold text-lg border-4 border-gray-900">
                                2
                            </div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-400">Chọn suất chiếu</p>
                                <p class="text-sm text-gray-500">Chọn giờ chiếu phù hợp</p>
                            </div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-700 mx-4 step-line"></div>
                    </div>
                    
                    <div class="flex items-center flex-1">
                        <div class="step-item flex items-center flex-1" data-step="3">
                            <div class="step-number flex items-center justify-center w-12 h-12 rounded-full bg-gray-700 text-gray-400 font-bold text-lg border-4 border-gray-900">
                                3
                            </div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-400">Chọn ghế</p>
                                <p class="text-sm text-gray-500">Chọn ghế ngồi</p>
                            </div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-700 mx-4 step-line"></div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="step-item flex items-center" data-step="4">
                            <div class="step-number flex items-center justify-center w-12 h-12 rounded-full bg-gray-700 text-gray-400 font-bold text-lg border-4 border-gray-900">
                                4
                            </div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-400">Thanh toán</p>
                                <p class="text-sm text-gray-500">Hoàn tất đặt vé</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column - Steps Content -->
                <div class="lg:col-span-2">
                    <!-- Step 1: Select Date -->
                    <div id="step-1" class="step-content">
                        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700/50">
                            <h3 class="text-2xl font-bold mb-6">Chọn ngày xem phim</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4" id="date-selection">
                                @php
                                    $dates = array_keys($showtimesByDate);
                                    $today = now()->format('d/m/Y');
                                @endphp
                                @foreach($dates as $date)
                                    <button type="button" 
                                        class="date-btn p-4 rounded-xl border-2 border-gray-700 hover:border-red-600 transition-all duration-200 bg-gray-900/50 hover:bg-gray-800"
                                        data-date="{{ $date }}">
                                        <p class="text-sm text-gray-400 mb-1">{{ \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('D') }}</p>
                                        <p class="text-lg font-bold">{{ \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('d') }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('m/Y') }}</p>
                                        @if($date === $today)
                                            <span class="text-xs text-red-500 mt-1 block">Hôm nay</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Select Showtime -->
                    <div id="step-2" class="step-content hidden">
                        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700/50">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold">Chọn suất chiếu</h3>
                                <button type="button" id="back-to-date" class="text-gray-400 hover:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Đổi ngày
                                </button>
                            </div>
                            <p class="text-gray-400 mb-6" id="selected-date-text">Ngày: <span class="text-white font-semibold"></span></p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="showtime-selection">
                                <!-- Showtimes will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Select Seats -->
                    <div id="step-3" class="step-content hidden">
                        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700/50">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold">Chọn ghế</h3>
                                <button type="button" id="back-to-showtime" class="text-gray-400 hover:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Đổi suất chiếu
                                </button>
                            </div>
                            
                            <!-- Screen -->
                            <div class="text-center mb-8">
                                <div class="bg-gradient-to-r from-gray-600 to-gray-800 rounded-lg py-6 px-12 mx-auto max-w-3xl relative">
                                    <div class="text-white font-bold text-xl">🎬 MÀN HÌNH</div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent rounded-lg"></div>
                                </div>
                            </div>

                            <!-- Seat Map -->
                            <div id="seat-map-container" class="flex flex-col items-center gap-3 mb-8">
                                <!-- Seats will be loaded here -->
                            </div>

                            <!-- Legend -->
                            <div class="flex flex-wrap justify-center gap-6 text-sm bg-gray-900/50 p-4 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gray-700 rounded"></div>
                                    <span class="text-gray-300">Ghế thường (80.000đ)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-yellow-600 rounded"></div>
                                    <span class="text-gray-300">Ghế VIP (120.000đ)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-8 bg-pink-600 rounded"></div>
                                    <span class="text-gray-300">Ghế đôi (200.000đ)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-red-600 rounded"></div>
                                    <span class="text-gray-300">Đã đặt</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-green-600 rounded"></div>
                                    <span class="text-gray-300">Đang chọn</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Payment -->
                    <div id="step-4" class="step-content hidden">
                        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700/50">
                            <h3 class="text-2xl font-bold mb-6">Thanh toán</h3>
                            
                            <!-- Combo Selection -->
                            <div class="mb-6">
                                <label class="block text-lg font-semibold text-gray-300 mb-4">Chọn Combo (tuỳ chọn)</label>
                                <div class="space-y-3">
                                    @forelse($combos as $c)
                                        <label class="flex items-center p-4 bg-gray-900/50 rounded-xl cursor-pointer hover:bg-gray-800/50 transition border border-gray-700/50">
                                            <input type="radio" name="combo" value="{{ $c->id }}"
                                                data-price="{{ (int) $c->gia }}" class="mr-4 text-red-600 w-5 h-5">
                                            <div class="flex-1">
                                                <div class="text-white font-medium">{{ $c->ten }}</div>
                                                <div class="text-gray-400 text-sm">{{ number_format((int) $c->gia, 0) }}đ</div>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="text-sm text-gray-500 p-4 bg-gray-900/50 rounded-xl">Hiện chưa có combo khả dụng</div>
                                    @endforelse
                                    <label class="flex items-center p-4 bg-gray-900/50 rounded-xl cursor-pointer hover:bg-gray-800/50 transition border border-gray-700/50">
                                        <input type="radio" name="combo" value="" checked class="mr-4 text-red-600 w-5 h-5">
                                        <div class="flex-1 text-gray-400 text-sm">Không chọn combo</div>
                                    </label>
                                </div>
                            </div>

                            <!-- Promotion Selection -->
                            <div class="mb-6">
                                <label class="block text-lg font-semibold text-gray-300 mb-4">Khuyến mãi</label>
                                <select id="promotion"
                                    class="w-full bg-gray-900/50 text-white rounded-xl p-4 border border-gray-700/50 focus:border-red-600 focus:outline-none">
                                    <option value="">Không áp dụng</option>
                                    @foreach ($khuyenmais as $km)
                                        @php $min = $km->dieu_kien ? (int)preg_replace('/\D+/', '', $km->dieu_kien) : 0; @endphp
                                        <option value="{{ $km->id }}" data-type="{{ $km->loai_giam }}"
                                            data-value="{{ (float) $km->gia_tri_giam }}" data-min="{{ $min }}">
                                            {{ $km->ma_km }} - {{ $km->mo_ta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-6">
                                <label class="block text-lg font-semibold text-gray-300 mb-4">Phương thức thanh toán</label>
                                <div class="space-y-3">
                                    <label class="flex items-center p-4 bg-gray-900/50 rounded-xl cursor-pointer hover:bg-gray-800/50 transition border border-gray-700/50">
                                        <input type="radio" name="payment_method" value="online" checked
                                            class="mr-4 text-red-600 w-5 h-5">
                                        <div class="flex-1">
                                            <div class="text-white font-medium">Thanh toán online</div>
                                            <div class="text-gray-400 text-sm">Chuyển khoản ngân hàng</div>
                                        </div>
                                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                        </svg>
                                    </label>
                                    <label class="flex items-center p-4 bg-gray-900/50 rounded-xl cursor-pointer hover:bg-gray-800/50 transition border border-gray-700/50">
                                        <input type="radio" name="payment_method" value="offline"
                                            class="mr-4 text-red-600 w-5 h-5">
                                        <div class="flex-1">
                                            <div class="text-white font-medium">Thanh toán tại quầy</div>
                                            <div class="text-gray-400 text-sm">Thanh toán khi đến rạp</div>
                                        </div>
                                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/50 sticky top-24">
                        <h3 class="text-xl font-bold mb-6">Thông tin đặt vé</h3>

                        <div class="space-y-4 mb-6">
                            <!-- Movie Info -->
                            <div class="pb-4 border-b border-gray-700">
                                <p class="text-sm text-gray-400 mb-1">Phim</p>
                                <p class="font-semibold text-white">{{ $movie->ten_phim ?? 'Movie Title' }}</p>
                            </div>

                            <!-- Showtime Info -->
                            <div class="pb-4 border-b border-gray-700">
                                <p class="text-sm text-gray-400 mb-1">Suất chiếu</p>
                                <p class="font-semibold text-white" id="summary-showtime">Chọn suất chiếu</p>
                                <p class="text-xs text-gray-500 mt-1" id="summary-date">-</p>
                                <p class="text-xs text-gray-500" id="summary-time">-</p>
                            </div>

                            <!-- Seats Info -->
                            <div class="pb-4 border-b border-gray-700">
                                <p class="text-sm text-gray-400 mb-1">Ghế</p>
                                <p class="font-semibold text-white" id="summary-seats">Chưa chọn ghế</p>
                                <p class="text-xs text-gray-500 mt-1" id="summary-seat-types">-</p>
                            </div>

                            <!-- Price Breakdown -->
                            <div class="pt-4 space-y-2" id="price-breakdown">
                                <div class="flex justify-between text-sm text-gray-400">
                                    <span>Chưa chọn ghế</span>
                                    <span>0đ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t border-gray-700 pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold">Tổng cộng</span>
                                <span class="text-2xl font-bold text-red-500" id="total-price">0đ</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button id="pay-button"
                            class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white py-4 rounded-xl font-bold text-lg transition-all duration-200 shadow-lg shadow-red-600/20 disabled:bg-gray-700 disabled:cursor-not-allowed disabled:shadow-none"
                            disabled>
                            Thanh toán
                        </button>
                        <p class="text-xs text-gray-500 text-center mt-3">
                            Bằng cách nhấp vào nút thanh toán, bạn đồng ý với điều khoản sử dụng
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .step-item.active .step-number {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.5);
        }
        .step-item.active .step-line {
            background: linear-gradient(90deg, #dc2626, #991b1b);
        }
        .step-item.active p {
            color: white;
        }
        .date-btn.active {
            border-color: #dc2626;
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.2), rgba(185, 28, 28, 0.2));
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.3);
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Global state
        let currentStep = 1;
        let selectedDate = null;
        let selectedShowtime = null;
        let selectedSeats = new Set();
        let showtimesData = @json($showtimes);
        let showtimesByDate = @json($showtimesByDate);
        let currentBookingId = null;
        let holdExpiresAt = null;
        let holdTimer = null;
        let selectedCombo = null;
        let selectedPromotion = null;
        let roomInfo = @json($roomInfo ?? null);

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateStepper();
            attachEventListeners();
        });

        function updateStepper() {
            document.querySelectorAll('.step-item').forEach((item, index) => {
                const stepNum = index + 1;
                if (stepNum <= currentStep) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        function goToStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            
            // Show target step
            document.getElementById(`step-${step}`).classList.remove('hidden');
            
            currentStep = step;
            updateStepper();
        }

        function attachEventListeners() {
            // Date selection
            document.querySelectorAll('.date-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.date-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    selectedDate = this.dataset.date;
                    
                    // Load showtimes for this date
                    loadShowtimesForDate(selectedDate);
                    
                    // Go to step 2
                    setTimeout(() => goToStep(2), 300);
                });
            });

            // Back buttons
            document.getElementById('back-to-date')?.addEventListener('click', () => {
                selectedDate = null;
                selectedShowtime = null;
                goToStep(1);
            });

            document.getElementById('back-to-showtime')?.addEventListener('click', () => {
                selectedSeats.clear();
                goToStep(2);
            });

            // Payment button
            document.getElementById('pay-button')?.addEventListener('click', () => {
                if (selectedSeats.size === 0 || !selectedShowtime) {
                    alert('Vui lòng chọn ghế trước!');
                    return;
                }
                submitBooking();
            });
        }

        function loadShowtimesForDate(date) {
            const showtimes = showtimesByDate[date] || [];
            const container = document.getElementById('showtime-selection');
            const dateText = document.getElementById('selected-date-text');
            
            if (dateText) {
                dateText.innerHTML = `Ngày: <span class="text-white font-semibold">${date}</span>`;
            }

            container.innerHTML = showtimes.map(st => `
                <button type="button" 
                    class="showtime-btn p-4 rounded-xl border-2 border-gray-700 hover:border-red-600 transition-all duration-200 bg-gray-900/50 hover:bg-gray-800 text-left"
                    data-showtime-id="${st.id}"
                    data-showtime-time="${st.time}"
                    data-showtime-date="${st.date}"
                    data-showtime-room="${st.room}">
                    <p class="text-xl font-bold mb-1">${st.time}</p>
                    <p class="text-sm text-gray-400">${st.room}</p>
                </button>
            `).join('');

            // Attach click handlers
            container.querySelectorAll('.showtime-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.showtime-btn').forEach(b => b.classList.remove('active', 'border-red-600', 'bg-red-600/20'));
                    this.classList.add('active', 'border-red-600', 'bg-red-600/20');
                    
                    selectedShowtime = this.dataset.showtimeId;
                    const time = this.dataset.showtimeTime;
                    const date = this.dataset.showtimeDate;
                    const room = this.dataset.showtimeRoom;
                    
                    // Update summary
                    document.getElementById('summary-showtime').textContent = `${time} - ${room}`;
                    document.getElementById('summary-date').textContent = `Ngày: ${date}`;
                    document.getElementById('summary-time').textContent = `Giờ: ${time}`;
                    
                    // Load seats and go to step 3
                    loadSeatsForShowtime(selectedShowtime).then(() => {
                        setTimeout(() => goToStep(3), 300);
                    });
                });
            });
        }

        async function loadSeatsForShowtime(showtimeId) {
            try {
                // Load seat map
                const response = await fetch(`/api/showtimes/${showtimeId}/seats`);
                const data = await response.json();
                
                // Generate seat map
                generateSeatMap(data);
                
                // Load booked seats
                await loadBookedSeats(showtimeId);
            } catch (error) {
                console.error('Error loading seats:', error);
                alert('Không thể tải dữ liệu ghế');
            }
        }

        function generateSeatMap(data) {
            // This will be implemented based on your seat layout
            // For now, placeholder
            const container = document.getElementById('seat-map-container');
            container.innerHTML = '<p class="text-gray-400">Đang tải sơ đồ ghế...</p>';
            
            // You'll need to implement the actual seat map generation
            // based on your room layout from the database
        }

        async function loadBookedSeats(showtimeId) {
            // Implementation for loading booked seats
        }

        async function submitBooking() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            const combo = document.querySelector('input[name="combo"]:checked')?.value;
            const promo = document.getElementById('promotion')?.value;
            
            const seats = Array.from(selectedSeats);
            
            try {
                const response = await fetch('/booking/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        showtime: selectedShowtime,
                        seats: seats,
                        payment_method: paymentMethod,
                        combo: combo ? { id: combo } : null,
                        promotion: promo || null
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    if (paymentMethod === 'online' && result.payment_url) {
                        window.location.href = result.payment_url;
                    } else {
                        alert('Đặt vé thành công!');
                        window.location.href = '/user/bookings';
                    }
                } else {
                    alert(result.message || 'Có lỗi xảy ra');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi kết nối');
            }
        }
    </script>
@endsection

