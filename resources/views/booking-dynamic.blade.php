@extends('layouts.app')

@section('title', 'Đặt vé - MovieHub')

@section('content')
  @php
    $movie = [
      'id' => 1,
      'title' => 'Hành Tinh Bí Ẩn',
      'poster' => 'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg',
      'duration' => 128,
      'rating' => 'T13'
    ];
    
    $cinemas = [
      ['id' => 1, 'name' => 'CGV Vincom Center', 'address' => '72 Lê Thánh Tôn, Q1, TP.HCM', 'distance' => '0.5km'],
      ['id' => 2, 'name' => 'CGV Landmark 81', 'address' => 'Vinhomes Central Park, Q.Bình Thạnh', 'distance' => '2.1km'],
      ['id' => 3, 'name' => 'Lotte Cinema Diamond', 'address' => '469 Nguyễn Hữu Thọ, Q7, TP.HCM', 'distance' => '3.2km']
    ];
    
    $showtimes = [
      ['id' => 1, 'time' => '14:30', 'format' => '2D', 'price' => 80000, 'available' => true],
      ['id' => 2, 'time' => '17:00', 'format' => '2D', 'price' => 80000, 'available' => true],
      ['id' => 3, 'time' => '19:30', 'format' => '3D', 'price' => 120000, 'available' => true],
      ['id' => 4, 'time' => '22:00', 'format' => 'IMAX', 'price' => 150000, 'available' => false]
    ];
  @endphp

  <div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="step-item active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">Chọn rạp</div>
          </div>
          <div class="step-line"></div>
          <div class="step-item" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">Giờ chiếu</div>
          </div>
          <div class="step-line"></div>
          <div class="step-item" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Chọn ghế</div>
          </div>
          <div class="step-line"></div>
          <div class="step-item" data-step="4">
            <div class="step-number">4</div>
            <div class="step-label">Thanh toán</div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Main Content -->
      <div class="lg:col-span-2">
        
        <!-- Step 1: Cinema Selection -->
        <div class="booking-step active" id="step-1">
          <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
              <span>🏢</span>
              <span>Chọn rạp chiếu</span>
            </h2>
            
            <div class="space-y-4">
              @foreach($cinemas as $cinema)
                <div class="cinema-card bg-[#222533] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer" data-cinema="{{ $cinema['id'] }}">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                      <div class="w-12 h-12 bg-gradient-to-r from-[#F53003] to-orange-400 rounded-lg flex items-center justify-center text-white font-bold">
                        {{ substr($cinema['name'], 0, 1) }}
                      </div>
                      <div>
                        <h3 class="font-semibold text-lg">{{ $cinema['name'] }}</h3>
                        <p class="text-[#a6a6b0] text-sm">{{ $cinema['address'] }}</p>
                        <div class="flex items-center gap-2 mt-1">
                          <span class="text-green-400">📍</span>
                          <span class="text-green-400 text-sm">{{ $cinema['distance'] }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="flex items-center gap-2">
                        <span class="text-yellow-400">⭐</span>
                        <span class="text-sm">4.8</span>
                      </div>
                      <button class="mt-2 px-4 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-colors">
                        Chọn
                      </button>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- Step 2: Showtime Selection -->
        <div class="booking-step" id="step-2">
          <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
              <span>🕒</span>
              <span>Chọn suất chiếu</span>
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              @foreach($showtimes as $showtime)
                <div class="showtime-card bg-[#222533] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer {{ !$showtime['available'] ? 'opacity-50 cursor-not-allowed' : '' }}" 
                     data-showtime="{{ $showtime['id'] }}" {{ !$showtime['available'] ? 'disabled' : '' }}>
                  <div class="text-center">
                    <p class="text-2xl font-bold">{{ $showtime['time'] }}</p>
                    <p class="text-sm text-[#a6a6b0]">{{ $showtime['format'] }}</p>
                    <p class="text-[#F53003] font-semibold mt-2">{{ number_format($showtime['price']) }}đ</p>
                    @if(!$showtime['available'])
                      <p class="text-red-400 text-xs mt-1">Hết vé</p>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- Step 3: Seat Selection -->
        <div class="booking-step" id="step-3">
          <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
              <span>🪑</span>
              <span>Chọn ghế</span>
            </h2>
            
            <!-- Cinema Screen -->
            <div class="text-center mb-8">
              <div class="bg-gradient-to-r from-gray-600 to-gray-800 rounded-lg py-4 px-8 mx-auto max-w-2xl relative">
                <div class="text-white font-semibold text-lg">🎬 MÀN HÌNH</div>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent rounded-lg"></div>
              </div>
            </div>

            <!-- Seat Map -->
            <div class="flex flex-col items-center gap-3 mb-8">
              @php
                $rows = range('A','J');
                $cols = range(1,12);
                $booked = ['B4','B5','C7','E8','H3','F6','G9','I2'];
                $vipRows = ['E','F'];
              @endphp
              @foreach($rows as $r)
                <div class="flex items-center gap-3">
                  <div class="text-sm text-[#a6a6b0] font-medium w-6 text-center">{{ $r }}</div>
                  <div class="flex gap-1">
                    @foreach($cols as $c)
                      @php
                        $code = $r.$c;
                        $isBooked = in_array($code,$booked);
                        $isVip = in_array($r,$vipRows);
                        $price = $isVip ? 120000 : 80000;
                      @endphp
                      <button
                        class="seat {{ $isVip ? 'seat-vip' : '' }} {{ $isBooked ? 'seat-booked' : '' }} relative group"
                        data-seat="{{ $code }}"
                        data-price="{{ $price }}"
                        {{ $isBooked ? 'disabled' : '' }}
                        aria-label="Ghế {{ $code }}"
                        title="Ghế {{ $code }} - {{ number_format($price) }}đ"
                      >
                        <span class="seat-number">{{ $c }}</span>
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-black/90 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-10">
                          Ghế {{ $code }} - {{ number_format($price) }}đ
                        </div>
                      </button>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>

            <!-- Combo Selection -->
            <div class="bg-[#222533] rounded-lg p-4">
              <h3 class="font-semibold mb-4 flex items-center gap-2">
                <span>🍿</span>
                <span>Combo & Đồ ăn</span>
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="combo-item bg-[#1b1d24] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer" data-combo="combo1" data-price="150000">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-lg flex items-center justify-center text-2xl">🍿</div>
                    <div class="flex-1">
                      <h4 class="font-semibold">Combo Classic</h4>
                      <p class="text-sm text-[#a6a6b0]">1 bắp + 2 nước</p>
                      <p class="text-[#F53003] font-bold">150.000đ</p>
                    </div>
                    <button class="combo-select-btn px-4 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-all duration-300">
                      Chọn
                    </button>
                  </div>
                </div>
                
                <div class="combo-item bg-[#1b1d24] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer" data-combo="combo2" data-price="200000">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-400 rounded-lg flex items-center justify-center text-2xl">🍕</div>
                    <div class="flex-1">
                      <h4 class="font-semibold">Combo Premium</h4>
                      <p class="text-sm text-[#a6a6b0]">1 bắp + 2 nước + 1 pizza</p>
                      <p class="text-[#F53003] font-bold">200.000đ</p>
                    </div>
                    <button class="combo-select-btn px-4 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-all duration-300">
                      Chọn
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Payment -->
        <div class="booking-step" id="step-4">
          <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
              <span>💳</span>
              <span>Thanh toán</span>
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Payment Methods -->
              <div>
                <h3 class="text-lg font-semibold mb-4">Phương thức thanh toán</h3>
                <div class="space-y-3">
                  <div class="payment-method bg-[#222533] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer" data-method="momo">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold">
                        M
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold">Ví MoMo</h4>
                        <p class="text-sm text-[#a6a6b0]">Thanh toán nhanh chóng</p>
                      </div>
                      <div class="text-green-400">✓</div>
                    </div>
                  </div>
                  
                  <div class="payment-method bg-[#222533] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer" data-method="zalopay">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center text-white font-bold">
                        Z
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold">ZaloPay</h4>
                        <p class="text-sm text-[#a6a6b0]">Thanh toán an toàn</p>
                      </div>
                      <div class="text-green-400">✓</div>
                    </div>
                  </div>
                  
                  <div class="payment-method bg-[#222533] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer" data-method="card">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-r from-gray-600 to-gray-800 rounded-lg flex items-center justify-center text-white font-bold">
                        💳
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold">Thẻ tín dụng/ghi nợ</h4>
                        <p class="text-sm text-[#a6a6b0]">Visa, Mastercard</p>
                      </div>
                      <div class="text-green-400">✓</div>
                    </div>
                  </div>
                  
                  <div class="payment-method bg-[#222533] rounded-lg p-4 border border-[#262833] hover:border-[#F53003] transition-all duration-300 cursor-pointer" data-method="qr">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center text-white font-bold">
                        QR
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold">QR Code</h4>
                        <p class="text-sm text-[#a6a6b0]">Quét mã thanh toán</p>
                      </div>
                      <div class="text-green-400">✓</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Order Summary -->
              <div>
                <h3 class="text-lg font-semibold mb-4">Tóm tắt đơn hàng</h3>
                <div class="bg-[#222533] rounded-lg p-4 space-y-3">
                  <div class="flex justify-between">
                    <span>Phim:</span>
                    <span class="font-medium">{{ $movie['title'] }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Rạp:</span>
                    <span class="font-medium" id="selected-cinema">-</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Suất chiếu:</span>
                    <span class="font-medium" id="selected-showtime">-</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Ghế:</span>
                    <span class="font-medium" id="selected-seats">-</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Combo:</span>
                    <span class="font-medium" id="selected-combo">-</span>
                  </div>
                  <hr class="border-[#262833]">
                  <div class="flex justify-between text-lg font-bold text-[#F53003]">
                    <span>Tổng cộng:</span>
                    <span id="total-amount">0đ</span>
                  </div>
                </div>
                
                <button id="confirm-payment" class="w-full mt-4 bg-gradient-to-r from-[#F53003] to-orange-400 text-white py-4 rounded-lg font-semibold text-lg hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-[#F53003]/25 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                  💳 Xác nhận thanh toán
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Movie Info -->
        <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 sticky top-6">
          <div class="flex items-center gap-4 mb-4">
            <img src="{{ $movie['poster'] }}" alt="{{ $movie['title'] }}" class="w-16 h-20 object-cover rounded">
            <div>
              <h3 class="font-semibold">{{ $movie['title'] }}</h3>
              <p class="text-sm text-[#a6a6b0]">{{ $movie['duration'] }} phút • {{ $movie['rating'] }}</p>
            </div>
          </div>
          
          <!-- Navigation Buttons -->
          <div class="space-y-3">
            <button id="prev-step" class="w-full px-4 py-2 bg-[#2a2d3a] text-white rounded-lg hover:bg-[#3a3d4a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
              ← Bước trước
            </button>
            <button id="next-step" class="w-full px-4 py-2 bg-gradient-to-r from-[#F53003] to-orange-400 text-white rounded-lg hover:scale-105 transition-all duration-300">
              Bước tiếp →
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    class BookingSystem {
      constructor() {
        this.currentStep = 1;
        this.maxSteps = 4;
        this.bookingData = {
          cinema: null,
          showtime: null,
          seats: [],
          combo: null,
          paymentMethod: null
        };
        
        this.init();
      }
      
      init() {
        this.bindEvents();
        this.updateStepDisplay();
      }
      
      bindEvents() {
        // Step navigation
        document.getElementById('next-step').addEventListener('click', () => this.nextStep());
        document.getElementById('prev-step').addEventListener('click', () => this.prevStep());
        
        // Cinema selection
        document.querySelectorAll('.cinema-card').forEach(card => {
          card.addEventListener('click', (e) => {
            const cinemaId = e.currentTarget.dataset.cinema;
            this.selectCinema(cinemaId);
          });
        });
        
        // Showtime selection
        document.querySelectorAll('.showtime-card').forEach(card => {
          card.addEventListener('click', (e) => {
            if (e.currentTarget.disabled) return;
            const showtimeId = e.currentTarget.dataset.showtime;
            this.selectShowtime(showtimeId);
          });
        });
        
        // Seat selection
        document.querySelectorAll('.seat').forEach(seat => {
          seat.addEventListener('click', (e) => {
            if (e.currentTarget.disabled) return;
            const seatCode = e.currentTarget.dataset.seat;
            this.toggleSeat(seatCode);
          });
        });
        
        // Combo selection
        document.querySelectorAll('.combo-item').forEach(item => {
          item.addEventListener('click', (e) => {
            const comboId = e.currentTarget.dataset.combo;
            this.selectCombo(comboId);
          });
        });
        
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
          method.addEventListener('click', (e) => {
            const methodId = e.currentTarget.dataset.method;
            this.selectPaymentMethod(methodId);
          });
        });
        
        // Confirm payment
        document.getElementById('confirm-payment').addEventListener('click', () => {
          this.confirmPayment();
        });
      }
      
      nextStep() {
        if (this.currentStep < this.maxSteps && this.canProceedToNextStep()) {
          this.currentStep++;
          this.updateStepDisplay();
        }
      }
      
      prevStep() {
        if (this.currentStep > 1) {
          this.currentStep--;
          this.updateStepDisplay();
        }
      }
      
      canProceedToNextStep() {
        switch(this.currentStep) {
          case 1: return this.bookingData.cinema !== null;
          case 2: return this.bookingData.showtime !== null;
          case 3: return this.bookingData.seats.length > 0;
          case 4: return this.bookingData.paymentMethod !== null;
          default: return false;
        }
      }
      
      updateStepDisplay() {
        // Update step indicators
        document.querySelectorAll('.step-item').forEach((item, index) => {
          const stepNumber = index + 1;
          if (stepNumber < this.currentStep) {
            item.classList.add('completed');
            item.classList.remove('active');
          } else if (stepNumber === this.currentStep) {
            item.classList.add('active');
            item.classList.remove('completed');
          } else {
            item.classList.remove('active', 'completed');
          }
        });
        
        // Update step content
        document.querySelectorAll('.booking-step').forEach((step, index) => {
          if (index + 1 === this.currentStep) {
            step.classList.add('active');
          } else {
            step.classList.remove('active');
          }
        });
        
        // Update navigation buttons
        const prevBtn = document.getElementById('prev-step');
        const nextBtn = document.getElementById('next-step');
        
        prevBtn.disabled = this.currentStep === 1;
        
        if (this.currentStep === this.maxSteps) {
          nextBtn.style.display = 'none';
        } else {
          nextBtn.style.display = 'block';
          nextBtn.disabled = !this.canProceedToNextStep();
        }
        
        this.updateOrderSummary();
      }
      
      selectCinema(cinemaId) {
        this.bookingData.cinema = cinemaId;
        document.querySelectorAll('.cinema-card').forEach(card => {
          card.classList.remove('selected');
        });
        document.querySelector(`[data-cinema="${cinemaId}"]`).classList.add('selected');
        this.updateOrderSummary();
      }
      
      selectShowtime(showtimeId) {
        this.bookingData.showtime = showtimeId;
        document.querySelectorAll('.showtime-card').forEach(card => {
          card.classList.remove('selected');
        });
        document.querySelector(`[data-showtime="${showtimeId}"]`).classList.add('selected');
        this.updateOrderSummary();
      }
      
      toggleSeat(seatCode) {
        const seatIndex = this.bookingData.seats.indexOf(seatCode);
        if (seatIndex > -1) {
          this.bookingData.seats.splice(seatIndex, 1);
          document.querySelector(`[data-seat="${seatCode}"]`).classList.remove('seat-selected');
        } else {
          if (this.bookingData.seats.length >= 8) {
            this.showNotification('Bạn chỉ có thể chọn tối đa 8 ghế!', 'warning');
            return;
          }
          this.bookingData.seats.push(seatCode);
          document.querySelector(`[data-seat="${seatCode}"]`).classList.add('seat-selected');
        }
        this.updateOrderSummary();
      }
      
      selectCombo(comboId) {
        this.bookingData.combo = comboId;
        document.querySelectorAll('.combo-item').forEach(item => {
          item.classList.remove('selected');
        });
        document.querySelector(`[data-combo="${comboId}"]`).classList.add('selected');
        this.updateOrderSummary();
      }
      
      selectPaymentMethod(methodId) {
        this.bookingData.paymentMethod = methodId;
        document.querySelectorAll('.payment-method').forEach(method => {
          method.classList.remove('selected');
        });
        document.querySelector(`[data-method="${methodId}"]`).classList.add('selected');
        this.updateOrderSummary();
      }
      
      updateOrderSummary() {
        // Update selected cinema
        const selectedCinema = document.querySelector('.cinema-card.selected');
        document.getElementById('selected-cinema').textContent = 
          selectedCinema ? selectedCinema.querySelector('h3').textContent : '-';
        
        // Update selected showtime
        const selectedShowtime = document.querySelector('.showtime-card.selected');
        document.getElementById('selected-showtime').textContent = 
          selectedShowtime ? selectedShowtime.querySelector('p').textContent : '-';
        
        // Update selected seats
        document.getElementById('selected-seats').textContent = 
          this.bookingData.seats.length > 0 ? this.bookingData.seats.join(', ') : '-';
        
        // Update selected combo
        const selectedCombo = document.querySelector('.combo-item.selected');
        document.getElementById('selected-combo').textContent = 
          selectedCombo ? selectedCombo.querySelector('h4').textContent : '-';
        
        // Calculate total
        let total = 0;
        
        // Seat prices
        this.bookingData.seats.forEach(seat => {
          const seatElement = document.querySelector(`[data-seat="${seat}"]`);
          total += parseInt(seatElement.dataset.price);
        });
        
        // Combo price
        if (this.bookingData.combo) {
          const comboElement = document.querySelector(`[data-combo="${this.bookingData.combo}"]`);
          total += parseInt(comboElement.dataset.price);
        }
        
        document.getElementById('total-amount').textContent = total.toLocaleString('vi-VN') + 'đ';
        
        // Enable/disable confirm button
        const confirmBtn = document.getElementById('confirm-payment');
        confirmBtn.disabled = !this.bookingData.paymentMethod;
      }
      
      confirmPayment() {
        const confirmBtn = document.getElementById('confirm-payment');
        confirmBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>Đang xử lý...';
        confirmBtn.disabled = true;
        
        // Simulate payment processing
        setTimeout(() => {
          this.showNotification('Đặt vé thành công! Kiểm tra email để xem vé.', 'success');
          confirmBtn.innerHTML = '✅ Đặt vé thành công';
          
          // Reset after 3 seconds
          setTimeout(() => {
            window.location.href = '/';
          }, 3000);
        }, 2000);
      }
      
      showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-20 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium shadow-lg transform translate-x-full transition-transform duration-300 ${
          type === 'success' ? 'bg-green-500' : 
          type === 'warning' ? 'bg-yellow-500' : 
          'bg-blue-500'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
          notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
          notification.style.transform = 'translateX(100%)';
          setTimeout(() => {
            document.body.removeChild(notification);
          }, 300);
        }, 3000);
      }
    }
    
    // Initialize booking system
    new BookingSystem();
  </script>
@endsection
