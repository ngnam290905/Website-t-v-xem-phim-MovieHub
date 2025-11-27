@extends('layouts.main')

@section('title', 'Đặt vé - MovieHub')

@section('content')
  @php
    $combos = App\Models\Combo::where('trang_thai', 1)->get();
    $khuyenmais = App\Models\KhuyenMai::where('trang_thai', 1)
        ->where('ngay_bat_dau', '<=', now())
        ->where('ngay_ket_thuc', '>=', now())
        ->get();
  @endphp

  <div class="min-h-screen bg-black text-white">
    <!-- Header -->
    <div class="bg-gray-900 border-b border-gray-800">
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <a href="{{ route('home') }}" class="text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </a>
          <h1 class="text-xl font-semibold">Đặt vé</h1>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-400">Đăng nhập để tích điểm</span>
          <button class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1 rounded">Đăng nhập</button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column - Movie Info and Seat Selection -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Movie Info -->
          <div class="bg-gray-900 rounded-lg p-6">
            <div class="flex gap-6">
              <img src="{{ $movie->poster ?? 'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg' }}" 
                   alt="{{ $movie->ten_phim ?? 'Movie' }}" 
                   class="w-32 h-48 object-cover rounded-lg">
              <div class="flex-1">
                <h2 class="text-2xl font-bold">{{ $movie->ten_phim ?? 'Movie Title' }}</h2>
                <div class="mt-2 space-y-1">
                  <p class="text-gray-400">{{ $movie->thoi_luong ?? '120' }} phút</p>
                  <p class="text-gray-400">Lượt xem: 2.5M</p>
                  <div class="flex items-center gap-2 mt-3">
                    <span class="bg-yellow-600 text-xs px-2 py-1 rounded">T13</span>
                    <span class="text-gray-400">Phim dành cho khán giả từ 13 tuổi trở lên</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Showtime Selection -->
          <div class="bg-gray-900 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Chọn suất chiếu</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              @foreach($showtimes ?? [] as $index => $st)
                <label class="cursor-pointer">
                  <input type="radio" name="showtime" value="{{ $st['id'] }}" class="sr-only peer" {{ $index === 0 ? 'checked' : '' }}>
                  <div class="border border-gray-700 rounded-lg p-3 text-center peer-checked:border-red-600 peer-checked:bg-red-600/20 hover:border-gray-600 transition">
                    <p class="font-semibold">{{ $st['time'] }}</p>
                    <p class="text-sm text-gray-400">{{ $st['date'] }}</p>
                    <p class="text-xs text-gray-500">{{ $st['room'] ?? '' }}</p>
                  </div>
                </label>
              @endforeach
            </div>
          </div>

          <!-- Screen -->
          <div class="text-center py-4">
            <div class="bg-gradient-to-r from-gray-600 to-gray-800 rounded-lg py-4 px-8 mx-auto max-w-2xl relative">
              <div class="text-white font-semibold text-lg">🎬 MÀN HÌNH</div>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent rounded-lg"></div>
            </div>
          </div>

          <!-- Seat Map -->
          <div class="bg-gray-900 rounded-lg p-6">
            @php
              // Get room info from controller (using first showtime as default)
              $defaultRoomInfo = null;
              $defaultSeatsData = [];
              if (isset($showtimes) && count($showtimes) > 0) {
                $firstShowtime = $showtimes[0]['id'] ?? null;
                if ($firstShowtime) {
                  $suatChieu = App\Models\SuatChieu::find($firstShowtime);
                  if ($suatChieu) {
                    $defaultRoomInfo = $suatChieu->phongChieu;
                    $defaultSeatsData = App\Models\Ghe::where('id_phong', $suatChieu->id_phong)
                      ->with('loaiGhe')
                      ->get()
                      ->keyBy('so_ghe');
                  }
                }
              }
              
              // Use room info from database
              $roomRows = isset($roomInfo) && $roomInfo ? (int)($roomInfo->so_hang ?? 10) : 10;
              $roomCols = isset($roomInfo) && $roomInfo ? (int)($roomInfo->so_cot ?? 15) : 15;
              
              // Generate row labels based on room rows
              $rows = [];
              for ($i = 1; $i <= $roomRows; $i++) {
                  $rows[] = chr(64 + $i); // A, B, C, etc.
              }
              
              $cols = range(1, $roomCols);
            @endphp
            <div id="seat-map" class="flex flex-col items-center gap-2">
              @foreach($rows as $r)
                <div class="flex items-center gap-2">
                  <div class="text-sm text-gray-400 font-medium w-6 text-center">{{ $r }}</div>
                  <div class="flex gap-1">
                    @foreach($cols as $c)
                      @php
                        $code = $r.$c;
                        $seat = $defaultSeatsData[$code] ?? null;
                        
                        if ($seat) {
                          $isAvailable = (int)($seat->trang_thai ?? 0) === 1;
                          $typeText = strtolower($seat->loaiGhe->ten_loai ?? 'thuong');
                          
                          if ($isAvailable) {
                            if (str_contains($typeText, 'vip')) { 
                              $btnClass = 'bg-yellow-600 hover:bg-yellow-700'; 
                              $price = 120000;
                            }
                            elseif (str_contains($typeText, 'đôi') || str_contains($typeText, 'doi') || str_contains($typeText, 'couple')) { 
                              $btnClass = 'bg-pink-600 hover:bg-pink-700 w-12 h-8'; 
                              $price = 200000;
                            }
                            else { 
                              $btnClass = 'bg-gray-700 hover:bg-gray-600'; 
                              $price = 80000;
                            }
                          } else {
                            $btnClass = 'bg-gray-500 cursor-not-allowed';
                            $price = 0;
                          }
                        } else {
                          $btnClass = 'bg-gray-800 hover:bg-gray-700';
                          $price = 80000;
                        }
                      @endphp
                      
                      <button type="button" 
                              class="seat w-8 h-8 rounded text-xs font-medium transition-all duration-200 {{ $btnClass }} {{ !$seat || !$isAvailable ? 'cursor-not-allowed' : '' }}"
                              data-seat="{{ $code }}"
                              data-price="{{ $price }}"
                              data-type="{{ $seat->loaiGhe->ten_loai ?? 'Thường' }}"
                              {{ !$seat || !$isAvailable ? 'disabled' : '' }}>
                        {{ $c }}
                      </button>
                    @endforeach
                  </div>
                  <div class="text-sm text-gray-400 font-medium w-6 text-center">{{ $r }}</div>
                </div>
              @endforeach
            </div>

            <!-- Legend -->
            <div class="mt-8 flex flex-wrap justify-center gap-6 text-sm">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-gray-700 rounded"></div>
                <span class="text-gray-400">Ghế thường (80.000đ)</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-yellow-600 rounded"></div>
                <span class="text-gray-400">Ghế VIP (120.000đ)</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-8 h-6 bg-pink-600 rounded"></div>
                <span class="text-gray-400">Ghế đôi (200.000đ)</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-red-600 rounded"></div>
                <span class="text-gray-400">Đã đặt</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-green-600 rounded"></div>
                <span class="text-gray-400">Đang chọn</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Payment Summary -->
        <div class="space-y-6">
          <!-- Summary -->
          <div class="bg-gray-900 rounded-lg p-6 sticky top-6">
            <h3 class="text-lg font-semibold mb-4">Thông tin đặt vé</h3>
            
            <div class="space-y-4">
              <!-- Movie Info -->
              <div>
                <p class="text-sm text-gray-400">Phim</p>
                <p class="font-medium">{{ $movie->ten_phim ?? 'Movie Title' }}</p>
                <p class="text-xs text-gray-500 mt-1">Thời lượng: {{ $movie->thoi_luong ?? '120' }} phút</p>
              </div>

              <!-- Showtime Info -->
              <div>
                <p class="text-sm text-gray-400">Suất chiếu</p>
                <p class="font-medium" id="summary-showtime">Chọn suất chiếu</p>
                <p class="text-xs text-gray-500 mt-1" id="summary-date">Chọn ngày chiếu</p>
                <p class="text-xs text-gray-500" id="summary-time">Chọn giờ chiếu</p>
              </div>

              <!-- Seats Info -->
              <div>
                <p class="text-sm text-gray-400">Ghế</p>
                <p class="font-medium" id="summary-seats">Chưa chọn ghế</p>
                <p class="text-xs text-gray-500 mt-1" id="summary-seat-types">Chưa chọn ghế</p>
              </div>

              <!-- Hold Timer Notification -->
              <div id="hold-notification" class="hidden border-t border-gray-800 pt-4">
                <div class="bg-yellow-600/20 border border-yellow-600/50 rounded-lg p-3">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm font-medium text-yellow-400">Ghế đã được giữ chỗ</p>
                  </div>
                  <p class="text-xs text-yellow-300" id="hold-timer-text">Thời gian còn lại: 5:00</p>
                  <p class="text-xs text-yellow-400/80 mt-1">Vui lòng hoàn tất thanh toán trong thời gian này</p>
                </div>
              </div>

              <!-- Price Breakdown -->
              <div class="border-t border-gray-800 pt-4 space-y-2" id="price-breakdown">
                <div class="flex justify-between text-sm text-gray-500">
                  <span>Chưa chọn ghế</span>
                  <span>0đ</span>
                </div>
              </div>

              <!-- Combo Selection -->
              <div class="border-t border-gray-800 pt-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Chọn Combo (tuỳ chọn)</label>
                <div class="space-y-2">
                  @forelse($combos as $c)
                    <label class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                      <input type="radio" name="combo" value="{{ $c->id }}" data-price="{{ (int)$c->gia }}" class="mr-3 text-red-600">
                      <div class="flex-1">
                        <div class="text-white font-medium">{{ $c->ten }}</div>
                        <div class="text-gray-400 text-sm">{{ number_format((int)$c->gia,0) }}đ</div>
                      </div>
                    </label>
                  @empty
                    <div class="text-sm text-gray-500">Hiện chưa có combo khả dụng</div>
                  @endforelse
                  <label class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                    <input type="radio" name="combo" value="" class="mr-3 text-red-600">
                    <div class="flex-1 text-gray-400 text-sm">Không chọn combo</div>
                  </label>
                </div>
              </div>

              <!-- Promotion Selection -->
              <div class="border-t border-gray-800 pt-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Khuyến mãi</label>
                <select id="promotion" class="w-full bg-gray-800 text-white rounded-lg p-2 border border-gray-700">
                  <option value="">Không áp dụng</option>
                  @foreach($khuyenmais as $km)
                    @php $min = $km->dieu_kien ? (int)preg_replace('/\D+/', '', $km->dieu_kien) : 0; @endphp
                    <option value="{{ $km->id }}" data-type="{{ $km->loai_giam }}" data-value="{{ (float)$km->gia_tri_giam }}" data-min="{{ $min }}">
                      {{ $km->ma_km }} - {{ $km->mo_ta }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Payment Method Selection -->
              <div class="border-t border-gray-800 pt-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Phương thức thanh toán</label>
                <div class="space-y-2">
                  <label class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                    <input type="radio" name="payment_method" value="online" checked class="mr-3 text-red-600">
                    <div class="flex-1">
                      <div class="text-white font-medium">Thanh toán online</div>
                      <div class="text-gray-400 text-sm">Chuyển khoản ngân hàng qua mã QR</div>
                    </div>
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                  </label>
                  <label class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                    <input type="radio" name="payment_method" value="offline" class="mr-3 text-red-600">
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

              <!-- Total -->
              <div class="border-t border-gray-800 pt-4">
                <div class="flex justify-between">
                  <span class="font-semibold">Tổng cộng</span>
                  <span class="text-xl font-bold text-red-500" id="total-price">0đ</span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="space-y-3 pt-4">
                <button id="pay" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-medium transition disabled:bg-gray-700 disabled:cursor-not-allowed" disabled>
                  Thanh toán
                </button>
                <p class="text-xs text-gray-500 text-center">
                  Bằng cách nhấp vào nút thanh toán, bạn đồng ý với điều khoản sử dụng của chúng tôi
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-gray-900 rounded-lg p-6 max-w-md w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold text-white">Thanh toán online</h3>
      <button onclick="closePaymentModal()" class="text-gray-400 hover:text-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="text-center mb-6">
      <p class="text-gray-300 mb-4">Quét mã QR để thanh toán</p>
      <div class="bg-white p-4 rounded-lg inline-block">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=MOCK_PAYMENT_{{ time() }}" alt="QR Code" class="w-48 h-48">
      </div>
      <p class="text-gray-400 text-sm mt-4">Mã thanh toán: MOCK{{ date('YmdHis') }}</p>
    </div>
    
    <div class="bg-gray-800 rounded-lg p-4 mb-4">
      <div class="flex justify-between text-white mb-2">
        <span>Số tiền:</span>
        <span id="paymentAmount" class="font-bold">0đ</span>
      </div>
      <div class="flex justify-between text-white mb-2">
        <span>Phương thức:</span>
        <span class="text-green-400">Chuyển khoản ngân hàng</span>
      </div>
      <div class="flex justify-between text-white">
        <span>Trạng thái:</span>
        <span id="paymentStatus" class="text-yellow-400">Chờ thanh toán</span>
      </div>
    </div>
    
    <div class="flex gap-3">
      <button onclick="console.log('Button clicked'); confirmPayment();" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
        Tôi đã thanh toán
      </button>
      <button onclick="closePaymentModal();" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
        Hủy
      </button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let seatButtons = document.querySelectorAll('.seat, .seat-couple');
    const payButton = document.getElementById('pay');
    const totalPriceElement = document.getElementById('total-price');
    const summarySeats = document.getElementById('summary-seats');
    const summaryShowtime = document.getElementById('summary-showtime');
    const summaryDate = document.getElementById('summary-date');
    const summaryTime = document.getElementById('summary-time');
    const summarySeatTypes = document.getElementById('summary-seat-types');
    const priceBreakdown = document.getElementById('price-breakdown');
    const totalPrice = document.getElementById('total-price');
    const comboRadios = document.querySelectorAll('input[name="combo"]');
    const promoSelect = document.getElementById('promotion');
    
    const selected = new Set();
    let selectedShowtime = null;
    let selectedCombo = null; // {id, price}
    let selectedPromotion = null; // {id, type, value}
    let holdExpiresAt = null;
    let holdTimer = null;
    let currentBookingId = null;
    
    // Global safety: prevent accidental GET navigation to select-seats API
    document.addEventListener('click', function(e){
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;
        const href = anchor.getAttribute('href') || '';
        if (/^\/api\/showtimes\/\d+\/select-seats(\b|\/|\?|#|$)/.test(href)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    // Function to attach seat selection event listeners
    function attachSeatListeners() {
        const seatMapContainer = document.getElementById('seat-map');
        if (!seatMapContainer) {
            console.error('seat-map container not found!');
            return;
        }
        
        console.log('Attaching seat listeners...');
        
        // Get all seat buttons
        const buttons = seatMapContainer.querySelectorAll('button.seat, button.seat-couple');
        console.log('Found', buttons.length, 'seat buttons');
        
        // Preserve selected seats when re-attaching listeners
        const selectedSeatCodes = Array.from(selected).map(btn => btn.dataset.seat);
        const selectedButtons = Array.from(selected); // Keep reference to buttons
        // Don't clear selected set - just preserve it
        
        // Attach listeners - check if already attached to avoid duplicates
        buttons.forEach((button, index) => {
            const seatCode = button.dataset.seat;
            
            // Skip if already has listener
            if (button._seatListenerAttached) {
                return;
            }
            
            // Mark as attached
            button._seatListenerAttached = true;
            // Ensure this button never submits a wrapping form
            try { button.setAttribute('type', 'button'); } catch (err) {}
            
            // Re-add to selected if it was selected before
            if (selectedSeatCodes.includes(seatCode)) {
                // Find the original button in selectedButtons and replace with new button reference
                const originalButton = selectedButtons.find(btn => btn.dataset.seat === seatCode);
                if (originalButton) {
                    selected.delete(originalButton);
                }
                selected.add(button);
                button.classList.add('selected', 'bg-green-600', 'hover:bg-green-700');
            }
            
            // Attach new listener - use click only to avoid double trigger
            let isProcessing = false;
            const handleSeatClick = async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Prevent double trigger
                if (isProcessing) {
                    console.log('Seat click already processing, ignoring...');
                    return;
                }
                isProcessing = true;
                
                console.log('=== SEAT CLICK DEBUG ===');
                console.log('Seat code:', button.dataset.seat);
                console.log('Disabled:', button.disabled);
                console.log('Selected showtime:', selectedShowtime);
                console.log('Button classes:', button.className);
                console.log('Button style pointer-events:', button.style.pointerEvents);
                console.log('Event type:', e.type);
                console.log('Is in selected set:', selected.has(button));
                
                if (button.disabled) {
                    console.warn('Seat is disabled, cannot select');
                    isProcessing = false;
                    return;
                }
                
                if (!selectedShowtime) {
                    console.error('No showtime selected!');
                    alert('Vui lòng chọn suất chiếu trước!');
                    isProcessing = false;
                    return;
                }
                
                console.log('Seat click is valid, proceeding...');
                
                if (selected.has(button)) {
                    // Deselect seat
                    selected.delete(button);
                    button.classList.remove('bg-green-600', 'hover:bg-green-700');
                    button.classList.remove('selected');
                    const seatType = button.dataset.type || '';
                    if (seatType.includes('vip') || seatType.includes('VIP')) {
                        button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                    } else if (seatType.includes('đôi') || seatType.includes('doi') || seatType.includes('couple')) {
                        button.classList.add('bg-pink-600', 'hover:bg-pink-700');
                    } else {
                        button.classList.add('bg-gray-700', 'hover:bg-gray-600');
                    }
                    
                    // If no seats selected, clear hold
                    if (selected.size === 0) {
                        clearHoldTimer();
                        currentBookingId = null;
                        holdExpiresAt = null;
                        hideHoldNotification();
                    } else {
                        // Re-hold remaining seats
                        await holdSelectedSeats();
                    }
                    updateUI();
                    isProcessing = false;
                } else {
                    // Validate seat selection before adding
                    const currentSelected = Array.from(selected).map(btn => btn.dataset.seat);
                    const newSeat = button.dataset.seat;
                    const allSeats = [...currentSelected, newSeat];
                    
                    // Frontend validation: Check same row
                    const rows = allSeats.map(seat => seat.charAt(0));
                    const uniqueRows = [...new Set(rows)];
                    if (uniqueRows.length > 1) {
                        alert('Tất cả ghế phải nằm trên cùng một hàng!');
                        isProcessing = false;
                        return;
                    }
                    
                    // Frontend validation: Check consecutive
                    if (allSeats.length > 1) {
                        const numbers = allSeats.map(seat => parseInt(seat.substring(1))).sort((a, b) => a - b);
                        for (let i = 1; i < numbers.length; i++) {
                            if (numbers[i] - numbers[i - 1] !== 1) {
                                alert('Các ghế phải liền nhau! Ví dụ: A5-A6-A7 (không được A5-A7).');
                                isProcessing = false;
                                return;
                            }
                        }
                    }
                    
                    // Select seat
                    selected.add(button);
                    button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700');
                    button.classList.add('selected');
                    button.classList.add('bg-green-600', 'hover:bg-green-700');
                    
                    // Hold seats via API (backend will do full validation including orphan seat check)
                    console.log('Before holdSelectedSeats - selected.size:', selected.size, 'seat:', button.dataset.seat);
                    const result = await holdSelectedSeats();
                    console.log('After holdSelectedSeats - result:', result, 'selected.size:', selected.size);
                    
                    // If API returns error, deselect the seat
                    if (result && !result.success) {
                        console.log('API failed, deselecting seat:', button.dataset.seat);
                        selected.delete(button);
                        button.classList.remove('bg-green-600', 'hover:bg-green-700', 'selected');
                        const seatType = button.dataset.type || '';
                        if (seatType.includes('vip') || seatType.includes('VIP')) {
                            button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                        } else if (seatType.includes('đôi') || seatType.includes('doi') || seatType.includes('couple')) {
                            button.classList.add('bg-pink-600', 'hover:bg-pink-700');
                        } else {
                            button.classList.add('bg-gray-700', 'hover:bg-gray-600');
                        }
                        // Show error message
                        if (result.message) {
                            alert(result.message);
                        }
                        // Update UI after deselecting
                        updateUI();
                        isProcessing = false;
                        return; // Don't update UI again
                    } else {
                        console.log('API succeeded, seat remains selected. Current selected.size:', selected.size);
                        // Update UI after successful selection - ensure selected seats are preserved
                        updateUI();
                    }
                    isProcessing = false;
                }
            };
            
            // Attach click event only (remove mousedown to avoid double trigger)
            button.addEventListener('click', handleSeatClick, { passive: false });
            
            // Also add touchstart for mobile
            button.addEventListener('touchstart', (e) => {
                e.preventDefault();
                handleSeatClick(e);
            }, { passive: false });
            
            // Ensure button is clickable
            if (!button.disabled) {
                button.style.cursor = 'pointer';
                button.style.pointerEvents = 'auto';
                button.title = 'Click to select seat ' + seatCode;
                console.log('Seat', seatCode, 'is enabled and clickable');
            } else {
                console.warn('Seat', seatCode, 'is disabled');
            }
        });
        
        console.log('Seat listeners attached to', buttons.length, 'buttons');
    }
    
    // Format price
    const format = (n) => n.toLocaleString('vi-VN') + 'đ';
    
    // Helpers
    const toNumber = (v) => {
        if (v === undefined || v === null) return 0;
        return parseInt(String(v).replace(/[^0-9.-]/g, '')) || 0;
    };
    // Price calculation - use price from data attribute
    const priceFor = (seatButton) => {
        const price = toNumber(seatButton.dataset.price);
        console.log('priceFor - seat:', seatButton.dataset.seat, 'price from dataset:', seatButton.dataset.price, 'parsed:', price);
        if (price > 0) {
            return price;
        }
        // Fallback: determine price by seat type
        const seatType = (seatButton.dataset.type || '').toLowerCase();
        if (seatType.includes('vip')) {
            return 120000;
        } else if (seatType.includes('đôi') || seatType.includes('doi') || seatType.includes('couple')) {
            return 200000;
        }
        return 80000; // Default regular seat price
    };
    // Compute promotion discount with condition and unit alignment
    const computePromotionDiscount = (subtotal, promo) => {
        if (!promo) return 0;
        const min = toNumber(promo.min || 0);
        if (subtotal < min) return 0;
        const type = (promo.type || '').toLowerCase();
        const val = toNumber(promo.value);
        if (type === 'phantram') return Math.round(subtotal * (val / 100));
        // Fixed amount: if value looks like VND (>=1000) use directly, else treat as thousands
        const fixed = val >= 1000 ? val : val * 1000;
        return Math.round(fixed);
    };
    
    // Update UI
    const updateUI = () => {

        const currentCombo = document.querySelector('input[name="combo"]:checked');
        if (currentCombo && currentCombo.value) {
            selectedCombo = { id: currentCombo.value, price: toNumber(currentCombo.dataset.price) };
        } else {
            selectedCombo = null;
        }
        if (promoSelect) {
            const opt = promoSelect.selectedOptions[0];
            if (promoSelect.value) {
                selectedPromotion = { id: promoSelect.value, type: opt.dataset.type, value: toNumber(opt.dataset.value), min: toNumber(opt.dataset.min) };
            } else {
                selectedPromotion = null;
            }
        }

        // Calculate seat total - ensure we have the selected seats
        const selectedArray = Array.from(selected);
        console.log('=== UPDATE UI DEBUG ===');
        console.log('Selected seats count:', selected.size);
        console.log('Selected seats array:', selectedArray.map(btn => btn.dataset.seat));
        
        const seatTotal = selectedArray.reduce((sum, seatButton) => {
            const price = priceFor(seatButton);
            console.log('Seat:', seatButton.dataset.seat, 'Price:', price);
            return sum + price;
        }, 0);
        
        const comboTotal = selectedCombo ? selectedCombo.price : 0;
        let discount = computePromotionDiscount(seatTotal + comboTotal, selectedPromotion);
        if (discount > seatTotal + comboTotal) discount = seatTotal + comboTotal;
        const total = Math.max(0, seatTotal + comboTotal - discount);
        
        console.log('Seat total:', seatTotal);
        console.log('Combo total:', comboTotal);
        console.log('Discount:', discount);
        console.log('Total:', total);
        console.log('Total price element:', totalPriceElement);
        
        if (totalPriceElement) {
            totalPriceElement.textContent = format(total);
            console.log('Updated total price to:', format(total));
        } else {
            console.error('totalPriceElement not found!');
        }
        
        if (selected.size > 0) {
            const seatCodes = Array.from(selected).map(btn => btn.dataset.seat);
            summarySeats.textContent = seatCodes.join(', ');
            
            // Count seat types and calculate prices
            let regularCount = 0, vipCount = 0, coupleCount = 0;
            let regularTotal = 0, vipTotal = 0, coupleTotal = 0;
            
            selected.forEach(button => {
                const price = priceFor(button);
                const seatType = button.dataset.type || '';
                
                if (seatType.includes('vip') || seatType.includes('VIP')) {
                    vipCount++;
                    vipTotal += price;
                } else if (seatType.includes('đôi') || seatType.includes('doi') || seatType.includes('couple')) {
                    coupleCount++;
                    coupleTotal += price;
                } else {
                    regularCount++;
                    regularTotal += price;
                }
            });
            
            // Update seat types summary
            const seatTypeInfo = [];
            if (regularCount > 0) seatTypeInfo.push('Ghế thường (' + regularCount + ')');
            if (vipCount > 0) seatTypeInfo.push('Ghế VIP (' + vipCount + ')');
            if (coupleCount > 0) seatTypeInfo.push('Ghế đôi (' + coupleCount + ')');
            summarySeatTypes.textContent = seatTypeInfo.join(', ');
            
            // Update price breakdown
            let breakdownHTML = '';
            if (regularCount > 0) {
                breakdownHTML += '<div class="flex justify-between text-sm">' +
                                 '<span class="text-gray-400">Ghế thường (' + regularCount + ')</span>' +
                                 '<span>' + format(regularTotal) + '</span>' +
                                 '</div>';
            }
            if (vipCount > 0) {
                breakdownHTML += '<div class="flex justify-between text-sm">' +
                                 '<span class="text-gray-400">Ghế VIP (' + vipCount + ')</span>' +
                                 '<span>' + format(vipTotal) + '</span>' +
                                 '</div>';
            }
            if (coupleCount > 0) {
                breakdownHTML += '<div class="flex justify-between text-sm">' +
                                 '<span class="text-gray-400">Ghế đôi (' + coupleCount + ')</span>' +
                                 '<span>' + format(coupleTotal) + '</span>' +
                                 '</div>';
            }
            if (comboTotal > 0) {
                breakdownHTML += '<div class="flex justify-between text-sm">' +
                                 '<span class="text-gray-400">Combo</span>' +
                                 '<span>' + format(comboTotal) + '</span>' +
                                 '</div>';
            }
            if (discount > 0) {
                breakdownHTML += '<div class="flex justify-between text-sm">' +
                                 '<span class="text-gray-400">Khuyến mãi</span>' +
                                 '<span>- ' + format(discount) + '</span>' +
                                 '</div>';
            }
            priceBreakdown.innerHTML = breakdownHTML || '<div class="flex justify-between text-sm text-gray-500"><span>Chưa chọn ghế</span><span>0đ</span></div>';
        } else {
            summarySeats.textContent = 'Chưa chọn ghế';
            summarySeatTypes.textContent = 'Chưa chọn ghế';
            const comboOnly = selectedCombo ? '<div class="flex justify-between text-sm"><span class="text-gray-400">Combo</span><span>' + format(selectedCombo.price) + '</span></div>' : '';
            const promoOnly = (selectedPromotion ? (function(){
                const base = (selectedCombo ? selectedCombo.price : 0);
                const d = computePromotionDiscount(base, selectedPromotion);
                return d>0 ? '<div class="flex justify-between text-sm"><span class="text-gray-400">Khuyến mãi</span><span>- ' + format(d) + '</span></div>' : '';
            })() : '');
            priceBreakdown.innerHTML = (comboOnly || promoOnly) ? comboOnly + promoOnly : '<div class="flex justify-between text-sm text-gray-500"><span>Chưa chọn ghế</span><span>0đ</span></div>';
            
            // Update total price (only combo/promo if no seats)
            const comboPrice = selectedCombo ? selectedCombo.price : 0;
            const promoDiscount = selectedPromotion ? computePromotionDiscount(comboPrice, selectedPromotion) : 0;
            const total = comboPrice - promoDiscount;
            if (totalPrice) {
                totalPrice.textContent = format(total);
            }
        }
        
        // Enable/disable pay button
        payButton.disabled = selected.size === 0 || !selectedShowtime;
    };
    
    // Load seats for showtime - MUST be defined before use
    const loadSeatsForShowtime = async (showtimeId) => {
        console.log('=== LOADING SEATS FOR SHOWTIME ===');
        console.log('Showtime ID:', showtimeId);
        try {
            const response = await fetch('/showtime-seats/' + showtimeId);
            console.log('API Response status:', response.status);
            const data = await response.json();
            console.log('API Response data:', data);
            console.log('Number of seats in response:', Object.keys(data.seats || {}).length);
            
            // Update seat map with new data
            const seatMapContainer = document.getElementById('seat-map');
            if (seatMapContainer) {
                seatMapContainer.querySelectorAll('button.seat, button.seat-couple').forEach(button => {
                    const seatCode = button.dataset.seat;
                    const seatData = data.seats && data.seats[seatCode] ? data.seats[seatCode] : null;
                    
                    if (seatData) {
                        // Update button based on actual seat data
                        button.disabled = !seatData.available;
                        button.dataset.price = seatData.price;
                        button.dataset.type = seatData.type;
                        
                        // Ensure button is clickable if available
                        if (seatData.available) {
                            button.style.pointerEvents = 'auto';
                            button.style.cursor = 'pointer';
                            button.removeAttribute('disabled');
                        } else {
                            button.setAttribute('disabled', 'disabled');
                        }
                        
                        // Update button classes
                        button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700', 'bg-red-600', 'cursor-not-allowed', 'bg-green-600', 'selected');
                        
                        if (!seatData.available) {
                            button.classList.add('bg-red-600', 'cursor-not-allowed');
                        } else if (seatData.type && (seatData.type.includes('vip') || seatData.type.includes('VIP'))) {
                            button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                        } else if (seatData.type && (seatData.type.includes('đôi') || seatData.type.includes('doi') || seatData.type.includes('couple'))) {
                            button.classList.add('bg-pink-600', 'hover:bg-pink-700');
                            button.classList.add('w-12', 'h-8');
                        } else {
                            button.classList.add('bg-gray-700', 'hover:bg-gray-600');
                        }
                        
                        // Update button text
                        button.textContent = seatCode.substring(1);
                    } else {
                        // Seat not found in API response - keep current state but log
                        console.log('Seat', seatCode, 'not found in API response, keeping current state');
                    }
                });
            }
            
            // Re-attach listeners after updating seats
            console.log('Re-attaching seat listeners...');
            attachSeatListeners();
            
            // Load booked seats
            console.log('Loading booked seats...');
            await loadBookedSeats(showtimeId);
            console.log('=== SEATS LOADED SUCCESSFULLY ===');
        } catch (error) {
            console.error('Error loading seats:', error);
            alert('Không thể tải dữ liệu ghế: ' + error.message);
        }
    };
    
    // Attach seat selection listeners initially
    console.log('Initializing seat listeners...');
    attachSeatListeners();
    
    // Also try after a short delay in case DOM is not fully ready
    setTimeout(() => {
        console.log('Re-attaching seat listeners after delay...');
        attachSeatListeners();
    }, 500);
    
    // Function to hold selected seats via API
    async function holdSelectedSeats() {
        console.log('holdSelectedSeats called - selected.size:', selected.size, 'showtime:', selectedShowtime);
        if (!selectedShowtime || selected.size === 0) {
            console.warn('Cannot hold seats - no showtime or no seats selected');
            return { success: false };
        }
        
        // Preserve selected seats before API call
        const selectedSeatsBefore = Array.from(selected).map(btn => btn.dataset.seat);
        const selectedButtonsBefore = Array.from(selected);
        
        try {
            const selectedSeats = Array.from(selected).map(btn => btn.dataset.seat);
            console.log('Holding seats:', selectedSeats);
            const response = await fetch('/api/showtimes/' + selectedShowtime + '/select-seats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    seats: selectedSeats
                })
            });
            
            const data = await response.json();
            console.log('API response:', data);
            
            if (data.success) {
                currentBookingId = data.booking_id;
                holdExpiresAt = new Date(data.hold_expires_at);
                
                // Ensure selected seats are preserved after successful hold
                console.log('Before preserving seats - selected.size:', selected.size);
                const currentSelected = Array.from(selected);
                console.log('Current selected seats:', currentSelected.map(btn => btn.dataset.seat));
                
                // If selected was cleared somehow, restore it
                if (selected.size === 0 && selectedSeatsBefore.length > 0) {
                    console.warn('Selected seats were cleared, restoring...');
                    selectedSeatsBefore.forEach(seatCode => {
                        const button = document.querySelector('[data-seat="' + seatCode + '"]');
                        if (button && !button.disabled) {
                            selected.add(button);
                            button.classList.add('selected', 'bg-green-600', 'hover:bg-green-700');
                            button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700');
                        }
                    });
                }
                
                startHoldTimer();
                console.log('Seats held successfully - selected.size after:', selected.size);
                return { success: true };
            } else {
                console.warn('API returned error:', data.message);
                // Don't show alert here - let caller handle it
                return { success: false, message: data.message };
            }
        } catch (error) {
            console.error('Error holding seats:', error);
            return { success: false, message: 'Có lỗi xảy ra khi giữ ghế.' };
        }
    }
    
    // Timer functions
    function startHoldTimer() {
        clearHoldTimer();
        
        if (!holdExpiresAt) return;
        
        // Show notification
        showHoldNotification();
        
        function updateTimer() {
            const now = new Date();
            const diff = holdExpiresAt - now;
            
            if (diff <= 0) {
                // Time expired
                clearHoldTimer();
                hideHoldNotification();
                alert('Thời gian giữ ghế đã hết! Vui lòng chọn lại ghế.');
                // Clear selections and reload seats
                selected.clear();
                const allSeatButtons = document.querySelectorAll('.seat, .seat-couple');
                allSeatButtons.forEach(btn => {
                    btn.classList.remove('bg-green-600', 'hover:bg-green-700', 'selected');
                    const seatType = btn.dataset.type || '';
                    if (seatType.includes('vip') || seatType.includes('VIP')) {
                        btn.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                    } else if (seatType.includes('đôi') || seatType.includes('doi') || seatType.includes('couple')) {
                        btn.classList.add('bg-pink-600', 'hover:bg-pink-700');
                    } else {
                        btn.classList.add('bg-gray-700', 'hover:bg-gray-600');
                    }
                });
                if (selectedShowtime) {
                    loadSeatsForShowtime(selectedShowtime);
                }
                updateUI();
                return;
            }
            
            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            const timeString = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            
            // Update notification text
            const timerText = document.getElementById('hold-timer-text');
            if (timerText) {
                timerText.textContent = 'Thời gian còn lại: ' + timeString;
            }
        }
        
        updateTimer();
        holdTimer = setInterval(updateTimer, 1000);
    }
    
    function clearHoldTimer() {
        if (holdTimer) {
            clearInterval(holdTimer);
            holdTimer = null;
        }
    }
    
    function showHoldNotification() {
        const notification = document.getElementById('hold-notification');
        if (notification) {
            notification.classList.remove('hidden');
        }
    }
    
    function hideHoldNotification() {
        const notification = document.getElementById('hold-notification');
        if (notification) {
            notification.classList.add('hidden');
        }
    }

    // Combo selection changes
    comboRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            updateUI();
        });
    });
    // Promotion selection changes
    if (promoSelect) {
        promoSelect.addEventListener('change', () => {
            updateUI();
        });
    }
    
    // Showtime selection
    const showtimeRadios = document.querySelectorAll('input[name="showtime"]');
    showtimeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.checked) {
                selectedShowtime = radio.value;
                console.log('Showtime selected:', selectedShowtime);
                const label = radio.nextElementSibling;
                const timeText = label.querySelector('.font-semibold').textContent;
                const dateText = label.querySelector('.text-gray-400').textContent;
                summaryShowtime.textContent = '' + dateText + ' - ' + timeText + '';
                summaryDate.textContent = 'Ngày chiếu: ' + dateText + '';
                summaryTime.textContent = 'Giờ chiếu: ' + timeText + '';
                
                // Load seats for this showtime
                selected.clear(); // Clear previous selections
                clearHoldTimer();
                currentBookingId = null;
                holdExpiresAt = null;
                loadSeatsForShowtime(selectedShowtime);
                
                updateUI();
            }
        });
        
        // Trigger change event if already checked (for auto-selected first showtime)
        if (radio.checked) {
            console.log('Auto-selected showtime found:', radio.value);
            radio.dispatchEvent(new Event('change'));
        }
    });
    
    // Load booked seats
    const loadBookedSeats = async (showtimeId) => {
        try {
            const response = await fetch('/api/booked-seats/' + showtimeId);
            const data = await response.json();
            
            // Reset all seats (keep selected as is)
            const allSeatButtons = document.querySelectorAll('.seat, .seat-couple');
            allSeatButtons.forEach(button => {
                if (!selected.has(button)) {
                    const seatType = button.dataset.type || '';
                    button.classList.remove('bg-red-600', 'cursor-not-allowed', 'bg-orange-500');
                    if (seatType.includes('vip') || seatType.includes('VIP')) {
                        button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                    } else if (seatType.includes('đôi') || seatType.includes('doi') || seatType.includes('couple')) {
                        button.classList.add('bg-pink-600', 'hover:bg-pink-700');
                    } else {
                        button.classList.add('bg-gray-700', 'hover:bg-gray-600');
                    }
                }
            });
            
            // Mark booked seats
            (data.seats || []).forEach(bookedSeat => {
                const button = document.querySelector('[data-seat="' + bookedSeat + '"]');
                if (button && !selected.has(button)) {
                    button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700');
                    button.classList.add('bg-red-600', 'cursor-not-allowed');
                    button.disabled = true;
                }
            });
            
            // Mark holding seats (different color - orange/yellow)
            (data.holding || []).forEach(holdingSeat => {
                const button = document.querySelector('[data-seat="' + holdingSeat + '"]');
                if (button && !selected.has(button)) {
                    button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700');
                    button.classList.add('bg-orange-500', 'cursor-not-allowed');
                    button.disabled = true;
                }
            });
        } catch (error) {
            console.error('Error loading booked seats:', error);
            // Optional: alert user
        }
    };
    
    // Payment
    payButton.addEventListener('click', async () => {
        if (selected.size === 0 || !selectedShowtime) {
            alert('Vui lòng chọn suất chiếu và ghế!');
            return;
        }
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (paymentMethod === 'online') {
            // Show payment modal
            showPaymentModal();
        } else {
            // Process offline payment directly
            processOfflinePayment();
        }
    });
    
    // Process offline payment
    async function processOfflinePayment() {
        try {
            const selectedSeats = Array.from(selected).map(btn => btn.dataset.seat);
        const response = await fetch('/booking/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                showtime: selectedShowtime,
                seats: selectedSeats,
                payment_method: 'offline',
                combo: selectedCombo ? { id: selectedCombo.id } : null,
                promotion: selectedPromotion ? selectedPromotion.id : null,
                booking_id: currentBookingId
            })
        });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Đặt vé thành công! Vui lòng đến quầy thanh toán trước giờ chiếu.');
                window.location.href = '/user/bookings';
            } else {
                alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại!');
            }
        } catch (error) {
            console.error('Error booking:', error);
            alert('Có lỗi xảy ra, vui lòng thử lại!');
        }
    }
    
    // Load initial showtime if any
    console.log('=== INITIALIZING BOOKING PAGE ===');
    const initialShowtime = document.querySelector('input[name="showtime"]:checked');
    console.log('Initial showtime checked:', initialShowtime ? initialShowtime.value : 'NONE');
    console.log('Total showtime radios:', document.querySelectorAll('input[name="showtime"]').length);
    
    if (initialShowtime) {
        selectedShowtime = initialShowtime.value;
        console.log('Setting selectedShowtime to:', selectedShowtime);
        const label = initialShowtime.nextElementSibling;
        const timeText = label.querySelector('.font-semibold').textContent;
        const dateText = label.querySelector('.text-gray-400').textContent;
        summaryShowtime.textContent = '' + dateText + ' - ' + timeText + '';
        summaryDate.textContent = 'Ngày chiếu: ' + dateText + '';
        summaryTime.textContent = 'Giờ chiếu: ' + timeText + '';
        selected.clear();
        loadSeatsForShowtime(selectedShowtime);
    } else {
        console.warn('No showtime selected initially - disabling all seats');
        // No showtime selected - disable all seats
        selected.clear();
        const seatButtons = document.querySelectorAll('.seat');
        console.log('Disabling', seatButtons.length, 'seat buttons');
        seatButtons.forEach(button => {
            button.disabled = true;
            button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700', 'bg-green-600', 'hover:bg-green-700');
            button.classList.add('bg-red-600', 'cursor-not-allowed');
        });
        updateUI();
    }

    // Initialize combo/promotion if already selected
    const initCombo = document.querySelector('input[name="combo"]:checked');
    if (initCombo && initCombo.value) {
        selectedCombo = { id: initCombo.value, price: parseInt(initCombo.dataset.price || '0') };
    }
    if (promoSelect && promoSelect.value) {
        const opt = promoSelect.selectedOptions[0];
        selectedPromotion = { id: promoSelect.value, type: opt.dataset.type, value: parseFloat(opt.dataset.value || '0') };
    }
    // Ensure total reflects combo/promo even before seat selection
    updateUI();
});

// Payment Modal Functions (Global scope)
function showPaymentModal() {
    const modal = document.getElementById('paymentModal');
    const paymentAmount = document.getElementById('paymentAmount');
    // Simply mirror the current total displayed in the summary
    const totalText = document.getElementById('total-price')?.textContent || '0đ';
    paymentAmount.textContent = totalText;
    modal.style.display = 'flex';
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'none';
    document.getElementById('paymentStatus').textContent = 'Chờ thanh toán';
    document.getElementById('paymentStatus').className = 'text-yellow-400';
}

async function confirmPayment() {
    const paymentStatus = document.getElementById('paymentStatus');
    
    // Update status to processing
    paymentStatus.textContent = 'Đang xử lý...';
    paymentStatus.className = 'text-blue-400';
    
    try {
        // Get selected seats using the same method as pay button
        const selectedButtons = document.querySelectorAll('.seat.selected, .seat-couple.selected');
        const selectedSeats = Array.from(selectedButtons).map(btn => btn.dataset.seat);
        const selectedShowtime = document.querySelector('input[name="showtime"]:checked')?.value;
        
        if (selectedSeats.length === 0 || !selectedShowtime) {
            paymentStatus.textContent = 'Vui lòng chọn suất chiếu và ghế!';
            paymentStatus.className = 'text-red-400';
            return;
        }
        
        
        // Read current combo/promotion directly from DOM to avoid scope issues
        const comboRadio = document.querySelector('input[name="combo"]:checked');
        const selectedComboPayload = (comboRadio && comboRadio.value) ? { id: comboRadio.value } : null;
        const promoSel = document.getElementById('promotion');
        const selectedPromotionId = (promoSel && promoSel.value) ? promoSel.value : null;

        const response = await fetch('/booking/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                showtime: selectedShowtime,
                seats: selectedSeats,
                payment_method: 'online',
                combo: selectedComboPayload,
                promotion: selectedPromotionId,
                booking_id: currentBookingId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update status to success
            paymentStatus.textContent = 'Thanh toán thành công!';
            paymentStatus.className = 'text-green-400';
            
            // Close modal after 2 seconds and redirect
            setTimeout(() => {
                closePaymentModal();
                alert('Đặt vé và thanh toán thành công!');
                window.location.href = '/user/bookings';
            }, 2000);
        } else {
            // Update status to error
            paymentStatus.textContent = 'Thanh toán thất bại';
            paymentStatus.className = 'text-red-400';
            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại!');
        }
    } catch (error) {
        console.error('Error booking:', error);
        paymentStatus.textContent = 'Thanh toán thất bại';
        paymentStatus.className = 'text-red-400';
        alert('Có lỗi xảy ra, vui lòng thử lại!');
    }
}

// Format function (Global scope)
function format(num) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0
    }).format(num);
}
</script>
@endsection

