@extends('layouts.main')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-white">Kiểm tra vé</h1>
    <p class="text-[#a6a6b0] text-sm">Nhập mã vé để xem thông tin và quét QR khi vào rạp.</p>
  </div>

  <div class="rounded-2xl border border-[#262833] bg-[#10131a] shadow-xl overflow-hidden">
    <div class="p-6 space-y-5">
      <div class="flex gap-3">
        <input id="ticket-id-input" type="text" placeholder="Nhập mã vé (ví dụ: 123 hoặc MV000123)" class="flex-1 bg-[#151822] border border-[#262833] text-white rounded-xl px-4 py-3 outline-none" />
        <button id="ticket-load" class="px-4 py-3 rounded-xl bg-gradient-to-r from-[#F53003] to-[#ff7849] text-white font-semibold">Xem vé</button>
      </div>
      <div id="ticket-error" class="hidden text-red-400 text-sm"></div>
      <div id="ticket-loading" class="hidden text-[#a6a6b0]">Đang tải vé...</div>
      <div id="ticket-view" class="hidden grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-3">
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <div class="text-[#a6a6b0]">Mã vé</div>
              <div id="t-code" class="text-white font-semibold">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Trạng thái thanh toán</div>
              <div id="t-status" class="text-white font-semibold">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Tên khách hàng</div>
              <div id="t-customer" class="text-white">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Ngày mua</div>
              <div id="t-created" class="text-white">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Phương thức thanh toán</div>
              <div id="t-method" class="text-white">—</div>
            </div>
            <div>
              <div class="text-[#a6a6b0]">Giá vé</div>
              <div id="t-price" class="text-white">—</div>
            </div>
          </div>
          <div class="rounded-xl border border-[#262833] p-4">
            <div class="text-[#a6a6b0] text-sm mb-2">Suất chiếu</div>
            <div id="t-show" class="text-white">—</div>
          </div>
          <div class="rounded-xl border border-[#262833] p-4">
            <div class="text-[#a6a6b0] text-sm mb-2">Ghế</div>
            <div id="t-seats" class="text-white">—</div>
          </div>
        </div>
        <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-[#262833] p-4">
          <img id="t-qr" alt="QR vé" class="w-44 h-44 bg-[#151822] rounded-md object-contain" />
          <div class="text-[#a6a6b0] text-xs">Quét mã để xác thực vé</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var btn = document.getElementById('ticket-load');
  var input = document.getElementById('ticket-id-input');
  var err = document.getElementById('ticket-error');
  var loading = document.getElementById('ticket-loading');
  var view = document.getElementById('ticket-view');
  var codeEl = document.getElementById('t-code');
  var statusEl = document.getElementById('t-status');
  var customerEl = document.getElementById('t-customer');
  var showEl = document.getElementById('t-show');
  var seatsEl = document.getElementById('t-seats');
  var priceEl = document.getElementById('t-price');
  var createdEl = document.getElementById('t-created');
  var methodEl = document.getElementById('t-method');
  var qrEl = document.getElementById('t-qr');

  function parseId(raw){ if(!raw) return null; raw = String(raw).trim(); var m = raw.match(/(\d+)/); return m? m[1] : null; }
  function formatVND(x){ try{ return Number(x).toLocaleString('vi-VN') + ' đ'; }catch(e){ return x; }}
  function statusLabel(s){ return s==1? 'Đã thanh toán' : (s===0? 'Chờ thanh toán' : '—'); }
  function methodLabel(m){ return m==1? 'Thanh toán online' : (m==2? 'Thanh toán tại quầy' : '—'); }

  function render(t){
    codeEl.textContent = t.code || '—';
    statusEl.textContent = statusLabel(t.status);
    customerEl.textContent = t.customer && t.customer.name ? t.customer.name : '—';
    createdEl.textContent = t.created_at || '—';
    methodEl.textContent = methodLabel(t.payment_method);
    priceEl.textContent = formatVND(t.price || 0);
    var showParts = [];
    if(t.showtime){ if(t.showtime.movie) showParts.push(t.showtime.movie); if(t.showtime.room) showParts.push(t.showtime.room); if(t.showtime.start) showParts.push(t.showtime.start); }
    showEl.textContent = showParts.join(' • ');
    seatsEl.textContent = Array.isArray(t.seats) ? t.seats.join(', ') : '—';
    if(t.qr && t.qr.image){ qrEl.src = t.qr.image; qrEl.alt = t.qr.data || 'QR'; }
  }

  function load(id){
    err.classList.add('hidden'); loading.classList.remove('hidden'); view.classList.add('hidden');
    fetch((window.location.origin||'') + '/api/ticket/' + id)
      .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(j){ if(!j.success) throw new Error(j.message||'Không tìm thấy'); render(j.ticket); view.classList.remove('hidden'); })
      .catch(function(e){ err.textContent = 'Lỗi: '+ e.message; err.classList.remove('hidden'); })
      .finally(function(){ loading.classList.add('hidden'); });
  }

  btn.addEventListener('click', function(){ var id = parseId(input.value); if(!id){ err.textContent='Vui lòng nhập mã vé hợp lệ'; err.classList.remove('hidden'); return;} load(id); });

  // Prefill from query string (?id=123 or ?ticket=123) and auto load
  (function initFromUrl(){
    var params = new URLSearchParams(window.location.search);
    var id = parseId(params.get('id') || params.get('ticket'));
    if(id){ input.value = 'MV'+String(id).padStart(6,'0'); load(id); }
  })();
})();
</script>
@endsection
