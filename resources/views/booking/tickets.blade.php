@extends('layouts.main')

@section('title', 'Vé của tôi')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#0f0f1a] via-[#151822] to-[#1a1d24] py-6 px-4">
  <div class="max-w-6xl mx-auto">
    
    <h1 class="text-2xl md:text-3xl font-bold text-white mb-6">Vé của tôi</h1>

    <!-- Filter -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4 mb-6">
      <form method="GET" class="flex flex-col sm:flex-row gap-4">
        <input type="date" name="date" value="{{ request('date') }}" 
               class="flex-1 px-4 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white focus:outline-none focus:border-[#F53003]">
        <button type="submit" class="bg-[#F53003] text-white px-6 py-2 rounded-lg font-semibold hover:bg-[#ff4d4d] transition-colors">
          Lọc
        </button>
        @if(request('date'))
          <a href="{{ route('booking.tickets') }}" class="bg-[#262833] text-white px-6 py-2 rounded-lg font-semibold hover:bg-[#2a2d3a] transition-colors">
            Xóa lọc
          </a>
        @endif
      </form>
    </div>

    <!-- Tickets List -->
    @if($bookings->count() > 0)
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($bookings as $booking)
          <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 hover:border-[#F53003] transition-all">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h3 class="text-xl font-bold text-white mb-1">{{ $booking->suatChieu->phim->ten_phim }}</h3>
                <p class="text-sm text-[#a6a6b0]">{{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y H:i') }}</p>
              </div>
              <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-semibold">
                Đã thanh toán
              </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
              <div>
                <p class="text-[#a6a6b0]">Phòng:</p>
                <p class="text-white font-semibold">{{ $booking->suatChieu->phongChieu->name ?? 'Phòng chiếu' }}</p>
              </div>
              <div>
                <p class="text-[#a6a6b0]">Ghế:</p>
                <p class="text-white font-semibold">{{ $booking->chiTietDatVe->pluck('ghe.so_ghe')->join(', ') }}</p>
              </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[#262833]">
              <div>
                <p class="text-xs text-[#a6a6b0]">Mã đặt vé</p>
                <p class="text-white font-bold">#{{ $booking->id }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-[#a6a6b0]">Tổng tiền</p>
                <p class="text-[#F53003] font-bold">{{ number_format($booking->tong_tien) }}đ</p>
              </div>
            </div>

            <button onclick="showTicketDetail({{ $booking->id }})" 
                    class="w-full mt-4 bg-[#262833] hover:bg-[#2a2d3a] text-white py-2 rounded-lg font-semibold transition-colors">
              Xem chi tiết
            </button>
          </div>
        @endforeach
      </div>

      <!-- Pagination -->
      <div class="mt-6">
        {{ $bookings->links('pagination.custom') }}
      </div>
    @else
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-12 text-center">
        <i class="fas fa-ticket-alt text-6xl text-[#a6a6b0] mb-4"></i>
        <p class="text-[#a6a6b0] text-lg">Bạn chưa có vé nào</p>
        <a href="{{ route('home') }}" class="inline-block mt-4 bg-[#F53003] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#ff4d4d] transition-colors">
          Đặt vé ngay
        </a>
      </div>
    @endif
  </div>
</div>

<!-- Ticket Detail Modal -->
<div id="ticket-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-6 max-w-md w-full">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold text-white">Chi tiết vé</h3>
      <button onclick="closeTicketModal()" class="text-[#a6a6b0] hover:text-white">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div id="ticket-content">
      <!-- Content will be loaded here -->
    </div>
  </div>
</div>

<script>
function showTicketDetail(bookingId) {
  // Load ticket detail via AJAX
  fetch(`/result?booking_id=${bookingId}`)
    .then(response => response.text())
    .then(html => {
      document.getElementById('ticket-content').innerHTML = html;
      document.getElementById('ticket-modal').classList.remove('hidden');
      document.getElementById('ticket-modal').classList.add('flex');
    });
}

function closeTicketModal() {
  document.getElementById('ticket-modal').classList.add('hidden');
  document.getElementById('ticket-modal').classList.remove('flex');
}
</script>
@endsection

