@extends('layouts.main')

@section('title', 'Chọn combo - ' . $booking->suatChieu->phim->ten_phim)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#0f0f1a] via-[#151822] to-[#1a1d24] py-6 px-4">
  <div class="max-w-6xl mx-auto">
    
    <!-- Header -->
    <div class="mb-6">
      <a href="{{ route('booking.seats', $booking->id_suat_chieu) }}" class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-4 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        Quay lại
      </a>
      <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Chọn combo</h1>
      <p class="text-[#a6a6b0]">{{ $booking->suatChieu->phim->ten_phim }} - {{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Combos List -->
      <div class="lg:col-span-2">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h2 class="text-xl font-bold text-white mb-4 flex items-center">
            <i class="fas fa-box text-[#F53003] mr-2"></i>
            Combo bắp nước
          </h2>

          @if($combos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              @foreach($combos as $combo)
                <div class="combo-card bg-[#1a1d24] border border-[#262833] rounded-lg p-4 hover:border-[#F53003] transition-all duration-300">
                  @if($combo->anh ?? $combo->hinh_anh ?? false)
                    <img src="{{ filter_var($combo->anh ?? $combo->hinh_anh, FILTER_VALIDATE_URL) ? ($combo->anh ?? $combo->hinh_anh) : asset('storage/' . ($combo->anh ?? $combo->hinh_anh)) }}" alt="{{ $combo->ten }}" 
                         class="w-full h-32 object-cover rounded-lg mb-3">
                  @else
                    <div class="w-full h-32 bg-gradient-to-br from-[#F53003] to-orange-400 rounded-lg mb-3 flex items-center justify-center">
                      <i class="fas fa-box text-white text-4xl"></i>
                    </div>
                  @endif
                  
                  <h3 class="font-bold text-white mb-1">{{ $combo->ten }}</h3>
                  @if($combo->mo_ta)
                    <p class="text-sm text-[#a6a6b0] mb-3">{{ \Illuminate\Support\Str::limit($combo->mo_ta, 60) }}</p>
                  @endif
                  
                  <div class="flex items-center justify-between mb-3">
                    <div>
                      <span class="text-lg font-bold text-[#F53003]">{{ number_format($combo->gia) }}đ</span>
                      @if($combo->gia_goc && $combo->gia_goc > $combo->gia)
                        <span class="text-sm text-[#a6a6b0] line-through ml-2">{{ number_format($combo->gia_goc) }}đ</span>
                      @endif
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-2">
                    <button onclick="decreaseCombo({{ $combo->id }})" 
                            class="w-8 h-8 rounded bg-[#262833] text-white hover:bg-[#F53003] transition-colors">
                      <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" 
                           id="combo-qty-{{ $combo->id }}"
                           value="{{ $selectedCombos->where('id_combo', $combo->id)->first()->so_luong ?? 0 }}"
                           min="0"
                           max="{{ $combo->so_luong_toi_da ?? 10 }}"
                           class="flex-1 text-center bg-[#151822] border border-[#262833] rounded text-white py-1"
                           readonly>
                    <button onclick="increaseCombo({{ $combo->id }})" 
                            class="w-8 h-8 rounded bg-[#262833] text-white hover:bg-[#F53003] transition-colors">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-12">
              <i class="fas fa-box text-6xl text-[#a6a6b0] mb-4"></i>
              <p class="text-[#a6a6b0]">Hiện tại không có combo nào</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Summary -->
      <div class="lg:col-span-1">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 sticky top-6">
          <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <i class="fas fa-receipt text-[#F53003] mr-2"></i>
            Tóm tắt đơn hàng
          </h3>

          <!-- Seats -->
          <div class="mb-4">
            <h4 class="text-sm font-semibold text-[#a6a6b0] mb-2">Ghế đã chọn</h4>
            <div class="space-y-2">
              @foreach($booking->chiTietDatVe as $detail)
                <div class="flex items-center justify-between text-sm">
                  <span class="text-white">{{ $detail->ghe->so_ghe }}</span>
                  <span class="text-[#F53003] font-semibold">{{ number_format($detail->gia) }}đ</span>
                </div>
              @endforeach
            </div>
            <div class="flex justify-between mt-3 pt-3 border-t border-[#262833]">
              <span class="text-white font-semibold">Tổng ghế:</span>
              <span class="text-white font-bold">{{ number_format($booking->chiTietDatVe->sum('gia')) }}đ</span>
            </div>
          </div>

          <!-- Combos -->
          <div class="mb-4">
            <h4 class="text-sm font-semibold text-[#a6a6b0] mb-2">Combo</h4>
            <div id="selected-combos" class="space-y-2">
              @foreach($selectedCombos as $selected)
                <div class="flex items-center justify-between text-sm combo-item" data-combo-id="{{ $selected->id_combo }}">
                  <span class="text-white">{{ $selected->combo->ten }} x{{ $selected->so_luong }}</span>
                  <span class="text-[#F53003] font-semibold">{{ number_format($selected->gia_ap_dung * $selected->so_luong) }}đ</span>
                </div>
              @endforeach
              @if($selectedCombos->count() === 0)
                <p class="text-sm text-[#a6a6b0]">Chưa chọn combo</p>
              @endif
            </div>
            <div class="flex justify-between mt-3 pt-3 border-t border-[#262833]">
              <span class="text-white font-semibold">Tổng combo:</span>
              <span id="combo-total" class="text-white font-bold">{{ number_format($selectedCombos->sum(function($item) { return $item->gia_ap_dung * $item->so_luong; })) }}đ</span>
            </div>
          </div>

          <!-- Total -->
          <div class="border-t border-[#262833] pt-4">
            <div class="flex justify-between items-center mb-4">
              <span class="text-lg font-bold text-white">Tổng cộng:</span>
              <span id="grand-total" class="text-2xl font-bold text-[#F53003]">
                {{ number_format($booking->tong_tien) }}đ
              </span>
            </div>
            
            <button onclick="updateCombos()" 
                    class="w-full bg-gradient-to-r from-[#F53003] to-orange-400 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all duration-300">
              Cập nhật
            </button>
            
            <a href="{{ route('booking.checkout', $booking->id) }}" 
               class="block w-full mt-3 bg-[#262833] hover:bg-[#2a2d3a] text-white py-3 rounded-lg font-semibold text-center transition-all duration-300">
              Tiếp tục thanh toán
              <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const bookingId = {{ $booking->id }};
const seatTotal = {{ $booking->chiTietDatVe->sum('gia') }};
const combos = @json($combos->mapWithKeys(function($combo) {
    return [$combo->id => [
        'name' => $combo->ten,
        'price' => $combo->gia,
        'max' => $combo->so_luong_toi_da ?? 10
    ]];
}));

function increaseCombo(comboId) {
  const input = document.getElementById(`combo-qty-${comboId}`);
  const max = combos[comboId]?.max || 10;
  const current = parseInt(input.value) || 0;
  if (current < max) {
    input.value = current + 1;
  }
}

function decreaseCombo(comboId) {
  const input = document.getElementById(`combo-qty-${comboId}`);
  const current = parseInt(input.value) || 0;
  if (current > 0) {
    input.value = current - 1;
  }
}

async function updateCombos() {
  const comboData = [];
  
  Object.keys(combos).forEach(comboId => {
    const qty = parseInt(document.getElementById(`combo-qty-${comboId}`).value) || 0;
    if (qty > 0) {
      comboData.push({
        id: parseInt(comboId),
        quantity: qty
      });
    }
  });

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  try {
    const response = await fetch(`/bookings/${bookingId}/addons`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ combos: comboData })
    });

    const data = await response.json();
    
    if (data.success) {
      // Update UI
      updateSummary(data.combo_total, data.total);
      alert('Đã cập nhật combo thành công!');
    } else {
      alert(data.message || 'Có lỗi xảy ra');
    }
  } catch (error) {
    console.error('Error updating combos:', error);
    alert('Có lỗi xảy ra khi cập nhật combo');
  }
}

function updateSummary(comboTotal, grandTotal) {
  document.getElementById('combo-total').textContent = formatPrice(comboTotal) + 'đ';
  document.getElementById('grand-total').textContent = formatPrice(grandTotal) + 'đ';
}

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN').format(price);
}
</script>
@endsection

