@extends('layouts.main')

@section('title', 'Đặt vé - MovieHub')

@section('content')
  @php
    $movie = $movie ?? ['id'=>$id ?? 1,'title'=>'Hành Tinh Bí Ẩn','poster'=>'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg','duration'=>128,'rating'=>'T13'];
    $showtimes = [
      ['id'=>'st1','label'=>'Hôm nay • 13:30 • Phòng 2D'],
      ['id'=>'st2','label'=>'Hôm nay • 16:15 • Phòng 2D'],
      ['id'=>'st3','label'=>'Ngày mai • 19:45 • Phòng 3D'],
    ];
  @endphp

  <div class="grid lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 flex flex-col gap-6">
      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-4 flex gap-4">
        <img src="{{ $movie['poster'] }}" alt="{{ $movie['title'] }}" class="w-24 h-36 object-cover rounded-md">
        <div class="flex-1">
          <h1 class="text-xl font-semibold">{{ $movie['title'] }}</h1>
          <p class="text-xs text-[#a6a6b0] mt-1">{{ $movie['duration'] }} phút • {{ $movie['rating'] }}</p>
          <label class="block mt-4 text-sm">Suất chiếu</label>
          <select id="showtime" class="mt-2 bg-[#0f0f12] border border-[#2f3240] rounded px-3 py-2 w-full">
            @foreach($showtimes as $st)
              <option value="{{ $st['id'] }}">{{ $st['label'] }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="font-semibold text-xl">Chọn ghế</h2>
          <div class="flex items-center gap-4 text-xs text-[#a6a6b0]">
            <span class="flex items-center gap-2"><span class="seat inline-block"></span> Trống</span>
            <span class="flex items-center gap-2"><span class="seat seat-vip inline-block"></span> VIP</span>
            <span class="flex items-center gap-2"><span class="seat seat-booked inline-block"></span> Đã bán</span>
            <span class="flex items-center gap-2"><span class="seat seat-selected inline-block"></span> Đã chọn</span>
          </div>
        </div>
        
        <!-- Cinema Screen -->
        <div class="text-center mb-8">
          <div class="bg-gradient-to-r from-gray-600 to-gray-800 rounded-lg py-4 px-8 mx-auto max-w-2xl relative">
            <div class="text-white font-semibold text-lg">🎬 MÀN HÌNH</div>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent rounded-lg"></div>
          </div>
        </div>

        <div id="seat-map" class="flex flex-col items-center gap-3">
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
                    <!-- Tooltip -->
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
        <div class="mt-8 bg-[#222533] rounded-lg p-4">
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

    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 h-fit sticky top-6">
      <h3 class="font-semibold mb-6 text-lg">Tóm tắt đơn</h3>
      <div class="flex flex-col gap-4 text-sm">
        <div class="flex justify-between"><span>Phim</span><span class="font-medium">{{ $movie['title'] }}</span></div>
        <div class="flex justify-between"><span>Suất chiếu</span><span id="summary-showtime" class="font-medium">{{ $showtimes[0]['label'] }}</span></div>
        <div class="flex justify-between"><span>Ghế</span><span id="summary-seats" class="font-medium">-</span></div>
        <div class="flex justify-between"><span>Giá vé</span><span id="summary-seat-price" class="font-medium">0đ</span></div>
        <div class="flex justify-between"><span>Combo</span><span id="summary-combo" class="font-medium">-</span></div>
        <hr class="border-[#2f3240]">
        <div class="flex justify-between text-lg font-bold text-[#F53003]"><span>Tổng</span><span id="summary-total">0đ</span></div>
        
        <!-- Progress Steps -->
        <div class="mt-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 bg-[#F53003] rounded-full flex items-center justify-center text-white text-xs font-bold">1</div>
              <span class="text-sm">Chọn ghế</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 bg-[#2a2d3a] rounded-full flex items-center justify-center text-white text-xs font-bold">2</div>
              <span class="text-sm text-[#a6a6b0]">Thanh toán</span>
            </div>
          </div>
          <div class="w-full bg-[#2a2d3a] rounded-full h-2">
            <div class="bg-gradient-to-r from-[#F53003] to-orange-400 h-2 rounded-full transition-all duration-500" style="width: 50%"></div>
          </div>
        </div>
        
        <button id="pay" class="mt-4 inline-flex items-center justify-center px-6 py-3 rounded-lg bg-gradient-to-r from-[#F53003] to-orange-400 text-white font-semibold hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-[#F53003]/25 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
          <span>💳</span>
          <span class="ml-2">Tiếp tục thanh toán</span>
        </button>
        <p class="text-xs text-[#a6a6b0] text-center">Chỉ là giao diện minh hoạ. Bạn có thể kết nối cổng thanh toán sau.</p>
      </div>
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const map = document.getElementById('seat-map');
      const summarySeats = document.getElementById('summary-seats');
      const summarySeatPrice = document.getElementById('summary-seat-price');
      const summaryCombo = document.getElementById('summary-combo');
      const summaryTotal = document.getElementById('summary-total');
      const summaryShowtime = document.getElementById('summary-showtime');
      const showtime = document.getElementById('showtime');
      const payButton = document.getElementById('pay');
      const selected = new Set();
      let selectedCombo = null;

      const priceFor = (seat) => {
        const row = seat[0];
        return ['E','F'].includes(row) ? 120000 : 80000;
      };

      const format = (n) => n.toLocaleString('vi-VN') + 'đ';

      const render = () => {
        // Update seats
        summarySeats.textContent = selected.size ? Array.from(selected).sort().join(', ') : '-';
        
        // Calculate seat price
        let seatTotal = 0;
        selected.forEach(s => seatTotal += priceFor(s));
        summarySeatPrice.textContent = seatTotal > 0 ? format(seatTotal) : '0đ';
        
        // Update combo
        summaryCombo.textContent = selectedCombo ? selectedCombo.name : '-';
        
        // Calculate total
        let total = seatTotal + (selectedCombo ? selectedCombo.price : 0);
        summaryTotal.textContent = format(total);
        
        // Enable/disable pay button
        payButton.disabled = selected.size === 0;
      };

      // Seat selection
      map.addEventListener('click', (e) => {
        const btn = e.target.closest('button.seat');
        if (!btn || btn.disabled) return;
        
        const code = btn.dataset.seat;
        const price = parseInt(btn.dataset.price);
        
        if (btn.classList.contains('seat-selected')) {
          btn.classList.remove('seat-selected');
          selected.delete(code);
        } else {
          if (selected.size >= 8) {
            // Show animated notification
            showNotification('Bạn chỉ có thể chọn tối đa 8 ghế!', 'warning');
            return;
          }
          btn.classList.add('seat-selected');
          selected.add(code);
          
          // Add selection animation
          btn.style.transform = 'scale(1.2)';
          setTimeout(() => {
            btn.style.transform = 'scale(1)';
          }, 200);
        }
        render();
      });

      // Combo selection
      document.querySelectorAll('.combo-item').forEach(item => {
        item.addEventListener('click', () => {
          const comboName = item.querySelector('h4').textContent;
          const comboPrice = parseInt(item.dataset.price);
          
          // Remove previous selection
          document.querySelectorAll('.combo-item').forEach(i => {
            i.classList.remove('border-[#F53003]', 'bg-[#2a2d3a]');
            i.classList.add('border-[#262833]');
          });
          
          // Add selection to clicked item
          item.classList.remove('border-[#262833]');
          item.classList.add('border-[#F53003]', 'bg-[#2a2d3a]');
          
          selectedCombo = { name: comboName, price: comboPrice };
          render();
          
          // Show success notification
          showNotification(`Đã chọn ${comboName}!`, 'success');
        });
      });

      // Showtime change
      showtime.addEventListener('change', () => {
        summaryShowtime.textContent = showtime.options[showtime.selectedIndex].textContent;
      });

      // Pay button click
      payButton.addEventListener('click', () => {
        if (selected.size === 0) return;
        
        // Show loading state
        payButton.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>Đang xử lý...';
        payButton.disabled = true;
        
        // Simulate payment processing
        setTimeout(() => {
          showNotification('Đặt vé thành công! Kiểm tra email để xem vé.', 'success');
          payButton.innerHTML = '<span>✅</span><span class="ml-2">Đặt vé thành công</span>';
          
          // Reset after 3 seconds
          setTimeout(() => {
            payButton.innerHTML = '<span>💳</span><span class="ml-2">Tiếp tục thanh toán</span>';
            payButton.disabled = false;
          }, 3000);
        }, 2000);
      });

      // Notification function
      function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-20 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium shadow-lg transform translate-x-full transition-transform duration-300 ${
          type === 'success' ? 'bg-green-500' : 
          type === 'warning' ? 'bg-yellow-500' : 
          'bg-blue-500'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
          notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
          notification.style.transform = 'translateX(100%)';
          setTimeout(() => {
            document.body.removeChild(notification);
          }, 300);
        }, 3000);
      }

      render();
    });
  </script>
@endsection


