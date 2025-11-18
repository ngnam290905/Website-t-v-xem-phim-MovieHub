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
              @foreach($showtimes ?? [] as $st)
                <label class="cursor-pointer">
                  <input type="radio" name="showtime" value="{{ $st['id'] }}" class="sr-only peer">
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
    const seatButtons = document.querySelectorAll('.seat, .seat-couple');
    const payButton = document.getElementById('pay');
    const totalPriceElement = document.getElementById('total-price');
    const summarySeats = document.getElementById('summary-seats');
    const summaryShowtime = document.getElementById('summary-showtime');
    const summaryDate = document.getElementById('summary-date');
    const summaryTime = document.getElementById('summary-time');
    const summarySeatTypes = document.getElementById('summary-seat-types');
    const priceBreakdown = document.getElementById('price-breakdown');
    const comboRadios = document.querySelectorAll('input[name="combo"]');
    const promoSelect = document.getElementById('promotion');
    
    const selected = new Set();
    let selectedShowtime = null;
    let selectedCombo = null; // {id, price}
    let selectedPromotion = null; // {id, type, value}
    
    // Format price
    const format = (n) => n.toLocaleString('vi-VN') + 'đ';
    
    // Helpers
    const toNumber = (v) => {
        if (v === undefined || v === null) return 0;
        return parseInt(String(v).replace(/[^0-9.-]/g, '')) || 0;
    };
    // Price calculation - use price from data attribute
    const priceFor = (seatButton) => {
        return toNumber(seatButton.dataset.price) || 80000;
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

        const seatTotal = Array.from(selected).reduce((sum, seatButton) => sum + priceFor(seatButton), 0);
        const comboTotal = selectedCombo ? selectedCombo.price : 0;
        let discount = computePromotionDiscount(seatTotal + comboTotal, selectedPromotion);
        if (discount > seatTotal + comboTotal) discount = seatTotal + comboTotal;
        const total = Math.max(0, seatTotal + comboTotal - discount);
        totalPriceElement.textContent = format(total);
        
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
        }
        
        // Enable/disable pay button
        payButton.disabled = selected.size === 0 || !selectedShowtime;
    };
    
    // Seat selection
    seatButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.disabled) return;
            
            if (selected.has(button)) {
                selected.delete(button);
                button.classList.remove('bg-green-600', 'hover:bg-green-700');
                // Restore original color based on seat type
                button.classList.remove('selected');
                const seatType = button.dataset.type || '';
                if (seatType.includes('vip') || seatType.includes('VIP')) {
                    button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                } else if (seatType.includes('đôi') || seatType.includes('doi') || seatType.includes('couple')) {
                    button.classList.add('bg-pink-600', 'hover:bg-pink-700');
                } else {
                    button.classList.add('bg-gray-700', 'hover:bg-gray-600');
                }
            } else {
                selected.add(button);
                button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700');
                button.classList.add('selected');
                button.classList.add('bg-green-600', 'hover:bg-green-700');
            }
            
            updateUI();
        });
    });

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
                const label = radio.nextElementSibling;
                const timeText = label.querySelector('.font-semibold').textContent;
                const dateText = label.querySelector('.text-gray-400').textContent;
                summaryShowtime.textContent = '' + dateText + ' - ' + timeText + '';
                summaryDate.textContent = 'Ngày chiếu: ' + dateText + '';
                summaryTime.textContent = 'Giờ chiếu: ' + timeText + '';
                
                // Load seats for this showtime
                selected.clear(); // Clear previous selections
                loadSeatsForShowtime(selectedShowtime);
                
                updateUI();
            }
        });
    });
    
    // Load seats for showtime
    const loadSeatsForShowtime = async (showtimeId) => {
        try {
            const response = await fetch('/showtime-seats/' + showtimeId);
            const data = await response.json();
            
            // Update seat map with new data
            const seatButtons = document.querySelectorAll('.seat, .seat-couple');
            seatButtons.forEach(button => {
                const seatCode = button.dataset.seat;
                const seatData = data.seats[seatCode];
                
                if (seatData) {
                    // Update button based on actual seat data
                    button.disabled = !seatData.available;
                    button.dataset.price = seatData.price;
                    button.dataset.type = seatData.type;
                    
                    // Update button classes
                    button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700', 'bg-red-600', 'cursor-not-allowed');
                    
                    if (!seatData.available) {
                        button.classList.add('bg-red-600', 'cursor-not-allowed');
                    } else if (seatData.type.includes('vip') || seatData.type.includes('VIP')) {
                        button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                    } else if (seatData.type.includes('đôi') || seatData.type.includes('doi') || seatData.type.includes('couple')) {
                        button.classList.add('bg-pink-600', 'hover:bg-pink-700');
                        button.classList.add('w-12', 'h-8'); // Couple seats wider
                    } else {
                        button.classList.add('bg-gray-700', 'hover:bg-gray-600');
                    }
                    
                    // Update button text
                    button.textContent = seatCode.substring(1); // Show only number
                } else {
                    // Seat not found in database - disable it
                    button.disabled = true;
                    button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-yellow-600', 'hover:bg-yellow-700', 'bg-pink-600', 'hover:bg-pink-700');
                    button.classList.add('bg-red-600', 'cursor-not-allowed');
                    button.textContent = seatCode.substring(1); // Show only number
                }
            });
            
            // Load booked seats
            await loadBookedSeats(showtimeId);
        } catch (error) {
            console.error('Error loading seats:', error);
            alert('Không thể tải dữ liệu ghế: ' + error.message);
        }
    };
    
    // Load booked seats
    const loadBookedSeats = async (showtimeId) => {
        try {
            const response = await fetch('/api/booked-seats/' + showtimeId);
            const data = await response.json();
            
            // Reset all seats (keep selected as is)
            seatButtons.forEach(button => {
                if (!selected.has(button)) {
                    const seatType = button.dataset.type || '';
                    button.classList.remove('bg-red-600', 'cursor-not-allowed');
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
                    promotion: selectedPromotion ? selectedPromotion.id : null
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
    const initialShowtime = document.querySelector('input[name="showtime"]:checked');
    if (initialShowtime) {
        selectedShowtime = initialShowtime.value;
        const label = initialShowtime.nextElementSibling;
        const timeText = label.querySelector('.font-semibold').textContent;
        const dateText = label.querySelector('.text-gray-400').textContent;
        summaryShowtime.textContent = '' + dateText + ' - ' + timeText + '';
        summaryDate.textContent = 'Ngày chiếu: ' + dateText + '';
        summaryTime.textContent = 'Giờ chiếu: ' + timeText + '';
        selected.clear();
        loadSeatsForShowtime(selectedShowtime);
    } else {
        // No showtime selected - disable all seats
        selected.clear();
        const seatButtons = document.querySelectorAll('.seat');
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
                promotion: selectedPromotionId
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

