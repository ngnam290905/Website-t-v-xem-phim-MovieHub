@extends('layouts.main')

@section('title', 'Kết quả thanh toán')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#0f0f1a] via-[#151822] to-[#1a1d24] py-12 px-4">
  <div class="max-w-2xl mx-auto">
    
    @if($booking->trang_thai === 'PAID')
      <!-- Success -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-8 text-center">
        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-check text-white text-3xl"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-white mb-2">Thanh toán thành công!</h1>
        <p class="text-[#a6a6b0] mb-6">Vé của bạn đã được xác nhận</p>

        <!-- Ticket Code -->
        <div class="bg-[#1a1d24] border border-[#262833] rounded-lg p-6 inline-block mb-6">
          <p class="text-[#a6a6b0] text-sm mb-2">Mã vé</p>
          <p class="text-white font-mono text-2xl font-bold">{{ $booking->ticket_code ?? 'MV' . str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>

        <!-- Booking Info -->
        <div class="bg-[#1a1d24] rounded-lg p-6 mb-6 text-left">
          <h3 class="font-bold text-white mb-4">Chi tiết đặt vé</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-[#a6a6b0]">Phim:</span>
              <span class="text-white font-semibold">{{ $booking->suatChieu->phim->ten_phim }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[#a6a6b0]">Ngày chiếu:</span>
              <span class="text-white">{{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[#a6a6b0]">Ghế:</span>
              <span class="text-white">{{ $booking->chiTietDatVe->pluck('ghe.so_ghe')->join(', ') }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-[#a6a6b0]">Tổng tiền:</span>
              <span class="text-[#F53003] font-bold">{{ number_format($booking->tong_tien) }}đ</span>
            </div>
          </div>
        </div>

        <div class="flex gap-4 justify-center">
          <a href="{{ route('booking.tickets') }}" class="bg-[#F53003] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#ff4d4d] transition-colors">
            Xem vé của tôi
          </a>
          <a href="{{ route('home') }}" class="bg-[#262833] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#2a2d3a] transition-colors">
            Về trang chủ
          </a>
        </div>
      </div>
    @else
      <!-- Failed -->
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-8 text-center">
        <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-times text-white text-3xl"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-white mb-2">Thanh toán thất bại</h1>
        <p class="text-[#a6a6b0] mb-6">{{ $booking->trang_thai === 'CANCELLED' ? 'Bạn đã hủy thanh toán' : 'Có lỗi xảy ra trong quá trình thanh toán' }}</p>

        <div class="flex gap-4 justify-center">
          <a href="{{ route('booking.checkout', $booking->id) }}" class="bg-[#F53003] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#ff4d4d] transition-colors">
            Thử lại
          </a>
          <a href="{{ route('home') }}" class="bg-[#262833] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#2a2d3a] transition-colors">
            Về trang chủ
          </a>
        </div>
      </div>
    @endif
  </div>
</div>

@if($booking->trang_thai === 'PAID')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById("qrcode"), {
  text: "{{ route('booking.result', ['booking_id' => $booking->id]) }}",
  width: 256,
  height: 256
});
</script>
@endif
@endsection

