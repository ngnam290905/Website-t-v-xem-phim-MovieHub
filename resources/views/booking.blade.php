@extends('layouts.app')

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

      <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold">Chọn ghế</h2>
          <div class="flex items-center gap-4 text-xs text-[#a6a6b0]">
            <span class="flex items-center gap-2"><span class="seat inline-block"></span> Trống</span>
            <span class="flex items-center gap-2"><span class="seat seat-vip inline-block"></span> VIP</span>
            <span class="flex items-center gap-2"><span class="seat seat-booked inline-block"></span> Đã bán</span>
            <span class="flex items-center gap-2"><span class="seat seat-selected inline-block"></span> Đã chọn</span>
          </div>
        </div>

        <div id="seat-map" class="flex flex-col items-center gap-2">
          <div class="w-full max-w-xl text-center text-xs text-[#a6a6b0] mb-2">Màn hình</div>
          @php
            $rows = range('A','J');
            $cols = range(1,12);
            $booked = ['B4','B5','C7','E8','H3'];
            $vipRows = ['E','F'];
          @endphp
          @foreach($rows as $r)
            <div class="flex gap-2">
              @foreach($cols as $c)
                @php
                  $code = $r.$c;
                  $isBooked = in_array($code,$booked);
                  $isVip = in_array($r,$vipRows);
                @endphp
                <button
                  class="seat {{ $isVip ? 'seat-vip' : '' }} {{ $isBooked ? 'seat-booked' : '' }}"
                  data-seat="{{ $code }}"
                  {{ $isBooked ? 'disabled' : '' }}
                  aria-label="Ghế {{ $code }}"
                ></button>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-4 h-fit sticky top-6">
      <h3 class="font-semibold mb-4">Tóm tắt đơn</h3>
      <div class="flex flex-col gap-3 text-sm">
        <div class="flex justify-between"><span>Phim</span><span>{{ $movie['title'] }}</span></div>
        <div class="flex justify-between"><span>Suất chiếu</span><span id="summary-showtime">{{ $showtimes[0]['label'] }}</span></div>
        <div class="flex justify-between"><span>Ghế</span><span id="summary-seats">-</span></div>
        <div class="flex justify-between"><span>Giá vé</span><span>Ghế thường 80.000đ • VIP 120.000đ</span></div>
        <hr class="border-[#2f3240]">
        <div class="flex justify-between text-base font-semibold"><span>Tổng</span><span id="summary-total">0đ</span></div>
        <button id="pay" class="mt-2 inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#F53003] hover:opacity-90 transition">Thanh toán</button>
        <p class="text-xs text-[#a6a6b0]">Chỉ là giao diện minh hoạ. Bạn có thể kết nối cổng thanh toán sau.</p>
      </div>
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const map = document.getElementById('seat-map');
      const summarySeats = document.getElementById('summary-seats');
      const summaryTotal = document.getElementById('summary-total');
      const summaryShowtime = document.getElementById('summary-showtime');
      const showtime = document.getElementById('showtime');
      const selected = new Set();

      const priceFor = (seat) => {
        const row = seat[0];
        return ['E','F'].includes(row) ? 120000 : 80000;
      };

      const format = (n) => n.toLocaleString('vi-VN') + 'đ';

      const render = () => {
        summarySeats.textContent = selected.size ? Array.from(selected).sort().join(', ') : '-';
        let total = 0;
        selected.forEach(s => total += priceFor(s));
        summaryTotal.textContent = format(total);
      };

      map.addEventListener('click', (e) => {
        const btn = e.target.closest('button.seat');
        if (!btn || btn.disabled) return;
        const code = btn.dataset.seat;
        if (btn.classList.contains('seat-selected')) {
          btn.classList.remove('seat-selected');
          selected.delete(code);
        } else {
          if (selected.size >= 8) return alert('Bạn chỉ có thể chọn tối đa 8 ghế.');
          btn.classList.add('seat-selected');
          selected.add(code);
        }
        render();
      });

      showtime.addEventListener('change', () => {
        summaryShowtime.textContent = showtime.options[showtime.selectedIndex].textContent;
      });

      render();
    });
  </script>
@endsection


