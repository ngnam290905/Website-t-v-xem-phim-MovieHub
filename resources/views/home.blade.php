@extends('layouts.app')

@section('title', 'MovieHub - Đặt vé xem phim')

@section('content')
  <section class="flex flex-col gap-8">
    

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
@endsection


