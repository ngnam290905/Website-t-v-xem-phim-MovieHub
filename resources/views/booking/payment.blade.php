<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanh toán</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:#0f1218; color:#e6e7eb; margin:0; }
    .container { max-width: 960px; margin: 24px auto; padding: 16px; }
    .card { background:#161a23; border:1px solid #2a2f3a; border-radius:16px; padding:20px; }
    .title { font-size: 20px; font-weight: 700; margin: 0 0 16px; }
    .grid { display:grid; grid-template-columns: 1fr; gap:16px; }
    .row { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-radius:12px; background:#1a1d24; border:1px solid #2a2f3a; }
    .muted { color:#a0a6b1; font-size:14px; }
    .total { font-weight:700; font-size:18px; }
    .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 16px; border-radius:12px; border:none; cursor:pointer; color:#fff; background: linear-gradient(90deg, #FF784E, #FFB25E); font-weight:700; text-decoration: none; font-size: 14px;}
    .btn:disabled { opacity:.6; cursor:not-allowed; }
    .btn-secondary { background: #2a2f3a; color: #e6e7eb; }
    .section-title { font-weight:700; font-size:16px; margin: 0 0 8px; }
    .header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
    .pill { background:#1a1d24; border:1px solid #2a2f3a; border-radius:999px; padding:6px 10px; font-size:12px; color:#a0a6b1; }
    
    /* Style cho radio thanh toán */
    .payment-option { cursor:pointer; align-items:center; gap:12px; justify-content: flex-start; transition: all 0.2s; }
    .payment-option:hover { border-color: #FF784E; }
    .payment-option input[type="radio"] { accent-color:#FF784E; width: 18px; height: 18px; }
    .payment-icon { width: 40px; height: 28px; background: #fff; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 20px;}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      @if(session('error'))
        <div style="margin-bottom:12px; padding:10px 12px; border-radius:12px; background:#3b1f20; border:1px solid #7f1d1d; color:#fecaca;">
          {{ session('error') }}
        </div>
      @endif
      @if(session('success'))
        <div style="margin-bottom:12px; padding:10px 12px; border-radius:12px; background:#1f3b2a; border:1px solid #166534; color:#bbf7d0;">
          {{ session('success') }}
        </div>
      @endif
      <div class="header">
        <h1 class="title">Thanh toán</h1>
        @if($showtime && $movie)
          <div class="pill">{{ $movie->ten_phim ?? 'Phim' }} • {{ optional($showtime->thoi_gian_bat_dau)->format('H:i d/m/Y') }}</div>
        @endif
      </div>

      <form method="POST" action="{{ url('/checkout/' . $holdId . '/payment') }}" id="payForm">
        @csrf
        <input type="hidden" name="promo_id" id="promo_id" value="" />
        <input type="hidden" name="promo_code" id="promo_code" value="" />

        <div class="grid">
            <div>
            <h2 class="section-title">Ghế đã chọn</h2>
            @forelse($seatDetails as $s)
                <div class="row">
                <div>
                    <div>{{ $s['code'] }}</div>
                    <div class="muted">{{ $s['type'] }}</div>
                </div>
                <div>{{ number_format($s['price'], 0, ',', '.') }}đ</div>
                </div>
            @empty
                <div class="muted">Chưa có ghế nào.</div>
            @endforelse
            </div>

            @if(isset($comboDetails) && count($comboDetails) > 0)
            <div>
            <h2 class="section-title">Combo đã chọn</h2>
            @foreach($comboDetails as $c)
                <div class="row">
                <div>
                    <div>{{ $c['name'] }} x{{ $c['qty'] }}</div>
                    <div class="muted">{{ number_format($c['price'], 0, ',', '.') }}đ / combo</div>
                </div>
                <div>{{ number_format($c['total'], 0, ',', '.') }}đ</div>
                </div>
            @endforeach
            </div>
            @endif

            @if(isset($foodDetails) && count($foodDetails) > 0)
            <div>
            <h2 class="section-title">Đồ ăn đã chọn</h2>
            @foreach($foodDetails as $f)
                <div class="row">
                <div>
                    <div>{{ $f['name'] }} x{{ $f['qty'] }}</div>
                    <div class="muted">{{ number_format($f['price'], 0, ',', '.') }}đ / món</div>
                </div>
                <div>{{ number_format($f['total'], 0, ',', '.') }}đ</div>
                </div>
            @endforeach
            </div>
            @endif


            @if(isset($khuyenmais))
            <div>
            <h2 class="section-title">Khuyến mãi</h2>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <label class="row" style="cursor:pointer;">
                <div>
                    <div>Không áp dụng</div>
                    <div class="muted">Thanh toán không dùng khuyến mãi</div>
                </div>
                <input type="radio" name="promo_pick" value="" checked style="accent-color:#FF784E;"/>
                </label>
                @foreach($khuyenmais as $km)
                @php
                    $type = strtolower($km->loai_giam);
                    $now = \Carbon\Carbon::now();
                    $start = $km->ngay_bat_dau ? \Carbon\Carbon::parse($km->ngay_bat_dau) : null;
                    $end = $km->ngay_ket_thuc ? \Carbon\Carbon::parse($km->ngay_ket_thuc) : null;
                    $active = ($km->trang_thai == 1) && (!$start || $start->lte($now)) && (!$end || $end->gte($now));
                @endphp
                <label class="row" style="cursor:pointer; align-items:flex-start; gap:12px; opacity: {{ $active ? '1' : '.6' }};">
                    <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                        <div style="font-weight:600;">{{ $km->ten_khuyen_mai ?? ('KM #' . $km->id) }}</div>
                        <span class="pill">Mã: {{ strtoupper($km->ma_km ?? '—') }}</span>
                    </div>
                    <div class="muted" style="margin-top:4px;">
                        @if($type === 'phantram')
                        Giảm {{ (float)$km->gia_tri_giam }}%
                        @else
                        Giảm {{ number_format(((float)$km->gia_tri_giam >= 1000) ? (float)$km->gia_tri_giam : ((float)$km->gia_tri_giam*1000), 0, ',', '.') }}đ
                        @endif
                        @if($start && $end)
                        • Hiệu lực: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}
                        @endif
                    </div>
                    @if(!empty($km->mo_ta))
                        <div class="muted" style="margin-top:4px;">{{ $km->mo_ta }}</div>
                    @endif
                    @if(!empty($km->dieu_kien))
                        <div class="muted" style="margin-top:4px;">Điều kiện: {{ $km->dieu_kien }}</div>
                    @endif
                    </div>
                    <input type="radio" name="promo_pick" value="{{ $km->id }}"
                        data-type="{{ strtolower($km->loai_giam) }}"
                        data-val="{{ (float)$km->gia_tri_giam }}"
                        data-code="{{ strtoupper($km->ma_km ?? '') }}"
                        {{ $active ? '' : 'disabled' }}
                        style="accent-color:#FF784E; margin-top:4px;"/>
                </label>
                @endforeach
            </div>
            </div>
            @endif

            <div>
                <h2 class="section-title" style="border-top:1px solid #2a2f3a; padding-top:16px;">Phương thức thanh toán</h2>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <label class="row payment-option">
                        <input type="radio" name="payment_method" value="online" checked />
                        <div class="payment-icon">
                            <img src="https://vnpay.vn/assets/img/logo-primary.svg" alt="VNPAY" style="height:16px;">
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:600;">Ví VNPAY / Ngân hàng</div>
                            <div class="muted">Thanh toán ngay qua cổng VNPAY</div>
                        </div>
                    </label>

                    <label class="row payment-option">
                        <input type="radio" name="payment_method" value="offline" />
                        <div class="payment-icon" style="background:#2a2f3a;">
                            🏪
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:600;">Thanh toán tại quầy</div>
                            <div class="muted">Đặt vé và thanh toán tại rạp trong 30 phút</div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="row" style="margin-top:8px;">
            <div class="total">Tổng tiền ghế</div>
            <div class="total" id="seatTotal" data-seat-total="{{ (int)($totalSeatPrice ?? 0) }}">{{ number_format($totalSeatPrice ?? 0, 0, ',', '.') }}đ</div>
            </div>

            @if(isset($comboTotal) && $comboTotal > 0)
            <div class="row">
            <div class="total">Tổng tiền combo</div>
            <div class="total" id="comboTotal" data-combo-total="{{ (int)($comboTotal ?? 0) }}">{{ number_format($comboTotal ?? 0, 0, ',', '.') }}đ</div>
            </div>
            @endif

            @if(isset($foodTotal) && $foodTotal > 0)
            <div class="row">
            <div class="total">Tổng tiền đồ ăn</div>
            <div class="total" id="foodTotal" data-food-total="{{ (int)($foodTotal ?? 0) }}">{{ number_format($foodTotal ?? 0, 0, ',', '.') }}đ</div>
            </div>
            @endif

            <div class="row" id="discountRow" style="display:none;">
            <div class="muted">Khuyến mãi</div>
            <div id="discountAmount">-0đ</div>
            </div>

            <div class="row" id="finalRow" style="margin-top:8px; background:#2a2f3a; border-color:#FF784E;">
            <div class="total" style="color:#FF784E;">Tổng thanh toán</div>
            <div class="total" id="finalTotal" style="color:#FF784E; font-size:20px;">{{ number_format(($totalSeatPrice ?? 0) + ($comboTotal ?? 0) + ($foodTotal ?? 0), 0, ',', '.') }}đ</div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:16px; gap:8px;">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Quay lại</a>
            
            <button type="submit" class="btn" id="btnSubmit">
                Thanh toán VNPAY
            </button>
            </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function(){
      const seatTotalEl = document.getElementById('seatTotal');
      const comboTotalEl = document.getElementById('comboTotal');
      const foodTotalEl = document.getElementById('foodTotal');
      const seatBase = parseInt(seatTotalEl?.getAttribute('data-seat-total') || '0');
      const comboBase = parseInt(comboTotalEl?.getAttribute('data-combo-total') || '0');
      const foodBase = parseInt(foodTotalEl?.getAttribute('data-food-total') || '0');
      const baseTotal = seatBase + comboBase + foodBase;
      const discountRow = document.getElementById('discountRow');
      const discountAmountEl = document.getElementById('discountAmount');
      const finalTotalEl = document.getElementById('finalTotal');
      const promoIdInput = document.getElementById('promo_id');
      const promoCodeHidden = document.getElementById('promo_code');
      const radios = document.querySelectorAll('input[name="promo_pick"]');
      
      // Elements cho thanh toán
      const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
      const btnSubmit = document.getElementById('btnSubmit');

      function fmt(n){
        return (n||0).toLocaleString('vi-VN');
      }

      function recalc(){
        let discount = 0;
        let selected = document.querySelector('input[name="promo_pick"]:checked');
        if (selected && selected.value) {
          const type = selected.getAttribute('data-type');
          const val = parseFloat(selected.getAttribute('data-val') || '0');
          if (type === 'phantram') {
            discount = Math.round(baseTotal * (val/100));
          } else {
            discount = (val >= 1000) ? val : (val * 1000);
          }
          if (discount < 0) discount = 0;
          if (discount > baseTotal) discount = baseTotal;
          discountRow.style.display = '';
          discountAmountEl.textContent = '-' + fmt(discount) + 'đ';
          promoIdInput.value = selected.value;
          promoCodeHidden.value = '';
        } else {
          discountRow.style.display = 'none';
          discountAmountEl.textContent = '-0đ';
          promoIdInput.value = '';
        }
        const final = Math.max(0, baseTotal - discount);
        finalTotalEl.textContent = fmt(final) + 'đ';
      }

      // Logic thay đổi nút bấm khi chọn phương thức thanh toán
      function updateButtonText() {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        if (method === 'online') {
            btnSubmit.textContent = 'Thanh toán VNPAY';
        } else {
            btnSubmit.textContent = 'Đặt vé giữ chỗ';
        }
      }

      radios.forEach(r => r.addEventListener('change', recalc));
      paymentRadios.forEach(r => r.addEventListener('change', updateButtonText));

      recalc();
      updateButtonText(); // Chạy lần đầu
    })();
  </script>
</body>
</html>