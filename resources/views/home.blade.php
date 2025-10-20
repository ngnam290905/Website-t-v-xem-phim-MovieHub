@extends('layouts.app')

@section('title', 'MovieHub - Đặt vé xem phim')
@section('meta')
<meta name="description" content="Đặt vé xem phim trực tuyến, chọn rạp, giờ chiếu, ghế nhanh chóng tại MovieHub.">
<meta name="keywords" content="đặt vé phim, moviehub, rạp chiếu phim, phim mới, phim hot">
@endsection

@section('content')

  <!-- Welcome Section -->
  <section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
          Chào mừng đến với <span class="text-[#F53003]">MovieHub</span>
        </h1>
        <p class="text-xl text-[#a6a6b0] mb-8 max-w-2xl mx-auto">
          Đặt vé xem phim trực tuyến, chọn rạp, giờ chiếu, ghế nhanh chóng và tiện lợi
        </p>
      </div>
    </div>
  </section>

  <!-- Bộ lọc phim và tìm kiếm -->
  <section class="flex flex-col gap-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
      <form class="flex gap-2 flex-wrap" id="filterForm" onsubmit="showFilterLoading(event)">
        <div class="relative flex items-center">
          <span class="absolute left-2 text-sm">🏢</span>
          <select class="pl-6 pr-2 py-1.5 rounded border border-[#262833] bg-[#222533] text-white text-xs">
            <option>Chọn rạp</option>
            <option>CGV</option>
            <option>BHD</option>
            <option>Lotte</option>
          </select>
        </div>
        <div class="relative flex items-center">
          <span class="absolute left-2 text-sm">🎬</span>
          <select class="pl-6 pr-2 py-1.5 rounded border border-[#262833] bg-[#222533] text-white text-xs">
            <option>Thể loại</option>
            <option>Hành động</option>
            <option>Tình cảm</option>
            <option>Kinh dị</option>
          </select>
        </div>
        <div class="relative flex items-center">
          <span class="absolute left-2 text-sm">🕒</span>
          <select class="pl-6 pr-2 py-1.5 rounded border border-[#262833] bg-[#222533] text-white text-xs">
            <option>Thời gian</option>
            <option>Hôm nay</option>
            <option>Cuối tuần</option>
          </select>
        </div>
        <button type="submit" class="px-3 py-1.5 rounded bg-gradient-to-r from-[#F53003] to-orange-400 text-white font-semibold transition-all duration-300 hover:scale-105 flex items-center gap-1 text-xs">
          <span>Lọc</span>
          <span id="filterSpinner" class="hidden animate-spin ml-1 w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
        </button>
      </form>
      <form class="flex items-center gap-2" id="searchForm" onsubmit="showSearchLoading(event)">
        <input type="text" placeholder="Tìm phim..." class="px-3 py-1.5 rounded border border-[#262833] bg-[#222533] text-white w-40 text-xs">
        <button type="submit" class="px-3 py-1.5 rounded bg-gradient-to-r from-[#F53003] to-orange-400 text-white font-semibold transition-all duration-300 hover:scale-105 flex items-center gap-1 text-xs">
          <span>Tìm</span>
          <span id="searchSpinner" class="hidden animate-spin ml-1 w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
        </button>
      </form>
      <script>
        function showFilterLoading(e) {
          e.preventDefault();
          document.getElementById('filterSpinner').classList.remove('hidden');
          setTimeout(function(){
            document.getElementById('filterSpinner').classList.add('hidden');
            document.getElementById('filterForm').submit();
          }, 1200);
        }
        function showSearchLoading(e) {
          e.preventDefault();
          document.getElementById('searchSpinner').classList.remove('hidden');
          setTimeout(function(){
            document.getElementById('searchSpinner').classList.add('hidden');
            document.getElementById('searchForm').submit();
          }, 1200);
        }
      </script>
    </div>
    

    <div id="now" class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Phim đang chiếu</h2>
      <a href="#coming" class="text-sm text-[#F53003] hover:underline">Xem phim sắp chiếu</a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @php
        $movies = [
          ['id'=>1,'title'=>'Hành Tinh Bí Ẩn','poster'=>'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg','duration'=>128,'rating'=>'T13'],
          ['id'=>2,'title'=>'Săn Lùng Siêu Trộm','poster'=>'https://image.tmdb.org/t/p/w342/62HCnUTziyWcpDaBO2i1DX17ljH.jpg','duration'=>115,'rating'=>'T16'],
          ['id'=>3,'title'=>'Vùng Đất Linh Hồn','poster'=>'https://image.tmdb.org/t/p/w342/e1mjopzAS2KNsvpbpahQ1a6SkSn.jpg','duration'=>102,'rating'=>'P']
        ];
      @endphp

      @foreach ($movies as $movie)
        <div class="bg-[#1b1d24] border border-[#262833] rounded-xl overflow-hidden flex flex-col">
          <img src="{{ $movie['poster'] }}" alt="{{ $movie['title'] }}" class="w-full aspect-[2/3] object-cover">
          <div class="p-4 flex-1 flex flex-col gap-3">
            <div>
              <h3 class="font-semibold">{{ $movie['title'] }}</h3>
              <p class="text-xs text-[#a6a6b0]">{{ $movie['duration'] }} phút • {{ $movie['rating'] }}</p>
            </div>
            <div class="mt-auto flex gap-2">
              <a href="{{ route('booking', ['id'=>$movie['id']]) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#F53003] hover:opacity-90 transition text-white text-sm">Đặt vé</a>
              <a href="#" class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-[#2f3240] text-sm hover:bg-[#222533]">Chi tiết</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>


  <!-- Popup đặt vé -->
  <div id="booking-popup" class="fixed inset-0 bg-black/40 items-center justify-center z-50 hidden">
    <div class="bg-[#1b1d24] rounded-xl p-6 w-full max-w-md relative">
      <button onclick="closeBookingPopup()" class="absolute top-2 right-2 text-white text-xl">&times;</button>
      <h3 class="font-semibold text-lg mb-4">Chọn rạp, giờ chiếu, ghế</h3>
      <form>
        <div class="mb-3">
          <label class="block mb-1">Rạp</label>
          <select class="w-full px-3 py-2 rounded border border-[#262833] bg-[#222533] text-white">
            <option>CGV</option>
            <option>BHD</option>
            <option>Lotte</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block mb-1">Giờ chiếu</label>
          <select class="w-full px-3 py-2 rounded border border-[#262833] bg-[#222533] text-white">
            <option>17:00</option>
            <option>19:00</option>
            <option>21:00</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block mb-1">Ghế</label>
          <input type="text" class="w-full px-3 py-2 rounded border border-[#262833] bg-[#222533] text-white" placeholder="A1, A2...">
        </div>
        <button type="submit" class="w-full px-4 py-2 rounded bg-[#F53003] text-white mt-2">Xác nhận đặt vé</button>
      </form>
    </div>
  </div>

  <script>
    function openBookingPopup(id) {
      const popup = document.getElementById('booking-popup');
      popup.classList.remove('hidden');
      popup.classList.add('flex');
    }
    
    function closeBookingPopup() {
      const popup = document.getElementById('booking-popup');
      popup.classList.add('hidden');
      popup.classList.remove('flex');
    }
  </script>
@endsection


