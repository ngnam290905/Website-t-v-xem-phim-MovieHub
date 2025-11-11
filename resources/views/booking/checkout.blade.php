@extends('layouts.main')

@section('title', 'Thanh toán - ' . $booking->suatChieu->phim->ten_phim)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#0f0f1a] via-[#151822] to-[#1a1d24] py-6 px-4">
  <div class="max-w-4xl mx-auto">
    
    <h1 class="text-2xl md:text-3xl font-bold text-white mb-6">Thanh toán</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Payment Form -->
      <div class="lg:col-span-2">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
          <h2 class="text-xl font-bold text-white mb-4">Thông tin thanh toán</h2>

          <form id="checkout-form" class="space-y-4">
            <!-- Customer Info -->
            <div>
              <label class="block text-sm font-semibold text-white mb-2">Họ tên <span class="text-red-400">*</span></label>
              <input type="text" name="customer_name" required
                     value="{{ $booking->ten_khach_hang ?? Auth::user()->ten ?? '' }}"
                     class="w-full px-4 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-white mb-2">Số điện thoại <span class="text-red-400">*</span></label>
                <input type="tel" name="customer_phone" required
                       value="{{ $booking->so_dien_thoai ?? Auth::user()->so_dien_thoai ?? '' }}"
                       class="w-full px-4 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
              </div>

              <div>
                <label class="block text-sm font-semibold text-white mb-2">Email</label>
                <input type="email" name="customer_email"
                       value="{{ $booking->email ?? Auth::user()->email ?? '' }}"
                       class="w-full px-4 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
              </div>
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-semibold text-white mb-2">Phương thức thanh toán <span class="text-red-400">*</span></label>
              <div class="grid grid-cols-2 gap-3">
                <label class="payment-method-option p-4 bg-[#1a1d24] border border-[#262833] rounded-lg cursor-pointer hover:border-[#F53003] transition-all">
                  <input type="radio" name="payment_method" value="vnpay" required class="mr-2">
                  <span class="text-white">VNPay</span>
                </label>
                <label class="payment-method-option p-4 bg-[#1a1d24] border border-[#262833] rounded-lg cursor-pointer hover:border-[#F53003] transition-all">
                  <input type="radio" name="payment_method" value="momo" required class="mr-2">
                  <span class="text-white">MoMo</span>
                </label>
                <label class="payment-method-option p-4 bg-[#1a1d24] border border-[#262833] rounded-lg cursor-pointer hover:border-[#F53003] transition-all">
                  <input type="radio" name="payment_method" value="credit_card" required class="mr-2">
                  <span class="text-white">Thẻ tín dụng</span>
                </label>
                <label class="payment-method-option p-4 bg-[#1a1d24] border border-[#262833] rounded-lg cursor-pointer hover:border-[#F53003] transition-all">
                  <input type="radio" name="payment_method" value="cash" required class="mr-2">
                  <span class="text-white">Thanh toán tại quầy</span>
                </label>
              </div>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-[#F53003] to-orange-400 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all duration-300">
              Thanh toán
              <i class="fas fa-arrow-right ml-2"></i>
            </button>
          </form>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="lg:col-span-1">
        <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 sticky top-6">
          <h3 class="text-xl font-bold text-white mb-4">Đơn hàng</h3>

          <div class="mb-4">
            <p class="text-sm text-[#a6a6b0] mb-2">{{ $booking->suatChieu->phim->ten_phim }}</p>
            <p class="text-xs text-[#a6a6b0]">{{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') }}</p>
          </div>

          <div class="space-y-2 mb-4">
            @foreach($booking->chiTietDatVe as $detail)
              <div class="flex justify-between text-sm">
                <span class="text-white">Ghế {{ $detail->ghe->so_ghe }}</span>
                <span class="text-[#F53003]">{{ number_format($detail->gia) }}đ</span>
              </div>
            @endforeach

            @foreach($booking->chiTietCombo as $combo)
              <div class="flex justify-between text-sm">
                <span class="text-white">{{ $combo->combo->ten }} x{{ $combo->so_luong }}</span>
                <span class="text-[#F53003]">{{ number_format($combo->gia_ap_dung * $combo->so_luong) }}đ</span>
              </div>
            @endforeach
          </div>

          <div class="border-t border-[#262833] pt-4">
            <div class="flex justify-between items-center">
              <span class="text-lg font-bold text-white">Tổng cộng:</span>
              <span class="text-2xl font-bold text-[#F53003]">{{ number_format($booking->tong_tien) }}đ</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('checkout-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const data = {
    customer_name: formData.get('customer_name'),
    customer_phone: formData.get('customer_phone'),
    customer_email: formData.get('customer_email'),
    payment_method: formData.get('payment_method')
  };

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  try {
    const response = await fetch(`/checkout/{{ $booking->id }}/payment`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    });

    const result = await response.json();
    
    if (result.success) {
      if (result.redirect) {
        window.location.href = result.redirect;
      } else {
        window.location.href = '/result?booking_id={{ $booking->id }}';
      }
    } else {
      alert(result.message || 'Có lỗi xảy ra');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Có lỗi xảy ra khi thanh toán');
  }
});
</script>
@endsection

