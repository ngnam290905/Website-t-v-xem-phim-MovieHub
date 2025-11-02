@extends('admin.layout')

@section('title', 'Chi tiết phim')
@section('page-title', 'Chi tiết phim')
@section('page-description', $movie->ten_phim)

@section('content')
  <!-- Breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
      <li class="inline-flex items-center">
        <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center text-sm font-medium text-[#a6a6b0] hover:text-white">
          <i class="fas fa-film mr-2"></i>
          Danh sách phim
        </a>
      </li>
      <li aria-current="page">
        <div class="flex items-center">
          <i class="fas fa-chevron-right text-[#a6a6b0] mx-2"></i>
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Chi tiết</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-6">
    <!-- Header actions -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">{{ $movie->ten_phim }}</h1>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]"><i class="fas fa-arrow-left mr-2"></i> Quay lại</a>
        @if(auth()->user()->vaiTro->ten === 'admin')
          <a href="{{ route('admin.movies.edit', $movie) }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-yellow-600/20 text-yellow-300 text-sm hover:bg-yellow-600/30"><i class="fas fa-edit mr-2"></i> Chỉnh sửa</a>
        @endif
      </div>
    </div>

    <!-- Main card -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Poster -->
        <div>
          <div class="relative w-full overflow-hidden rounded-xl border border-[#262833] bg-[#0f0f12]">
            @if($movie->poster)
              <img src="{{ $movie->poster_url }}" alt="{{ $movie->ten_phim }}" class="w-full object-cover" style="aspect-ratio: 2/3">
            @else
              <div class="flex items-center justify-center" style="aspect-ratio: 2/3">
                <div class="text-[#a6a6b0] text-sm flex flex-col items-center">
                  <i class="fas fa-image text-3xl mb-2"></i>
                  Chưa có poster
                </div>
              </div>
            @endif
            <span class="absolute top-3 left-3 text-[10px] uppercase px-2 py-1 rounded-full font-semibold {{ $movie->trang_thai==='dang_chieu' ? 'bg-green-500/20 text-green-300' : ($movie->trang_thai==='sap_chieu' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-gray-500/20 text-gray-300') }}">
              @switch($movie->trang_thai)
                @case('dang_chieu') Đang chiếu @break
                @case('sap_chieu') Sắp chiếu @break
                @case('ngung_chieu') Ngừng chiếu @break
                @default Khác
              @endswitch
            </span>
          </div>
        </div>

        <!-- Info -->
        <div class="lg:col-span-2 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Tên phim</div>
              <div class="text-white font-medium">{{ $movie->ten_phim }} @if($movie->ten_goc)<span class="text-[#a6a6b0]">({{ $movie->ten_goc }})</span>@endif</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Trạng thái</div>
              <div>
                <span class="text-[11px] uppercase px-2 py-1 rounded-full font-semibold {{ $movie->trang_thai==='dang_chieu' ? 'bg-green-500/20 text-green-300' : ($movie->trang_thai==='sap_chieu' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-gray-500/20 text-gray-300') }}">
                  @switch($movie->trang_thai)
                    @case('dang_chieu') Đang chiếu @break
                    @case('sap_chieu') Sắp chiếu @break
                    @case('ngung_chieu') Ngừng chiếu @break
                    @default Khác
                  @endswitch
                </span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Đạo diễn</div>
              <div class="text-white">{{ $movie->dao_dien }}</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Độ dài</div>
              <div class="text-white">{{ $movie->formatted_duration }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Thể loại</div>
              <div class="text-white">{{ $movie->the_loai ?: 'Chưa phân loại' }}</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Quốc gia</div>
              <div class="text-white">{{ $movie->quoc_gia ?: 'Chưa xác định' }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-[#a6a6b0]">Ngôn ngữ</div>
              <div class="text-white">{{ $movie->ngon_ngu ?: 'Chưa xác định' }}</div>
            </div>
            <div>
              <div class="text-xs text-[#a6a6b0]">Độ tuổi</div>
              <div class="text-white">{{ $movie->do_tuoi ?: 'Chưa xác định' }}</div>
            </div>
          </div>

          @if($movie->ngay_khoi_chieu || $movie->ngay_ket_thuc)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <div class="text-xs text-[#a6a6b0]">Ngày khởi chiếu</div>
                <div class="text-white">{{ $movie->ngay_khoi_chieu ? $movie->ngay_khoi_chieu->format('d/m/Y') : 'Chưa xác định' }}</div>
              </div>
              <div>
                <div class="text-xs text-[#a6a6b0]">Ngày kết thúc</div>
                <div class="text-white">{{ $movie->ngay_ket_thuc ? $movie->ngay_ket_thuc->format('d/m/Y') : 'Chưa xác định' }}</div>
              </div>
            </div>
          @endif

          @if($movie->diem_danh_gia > 0)
            <div>
              <div class="text-xs text-[#a6a6b0]">Đánh giá</div>
              <div class="text-white">{{ $movie->formatted_rating }} <span class="text-[#a6a6b0]">({{ $movie->so_luot_danh_gia }} lượt)</span></div>
            </div>
          @endif

          <div>
            <div class="text-xs text-[#a6a6b0]">Diễn viên</div>
            <div class="text-white">{{ $movie->dien_vien }}</div>
          </div>

          @if($movie->trailer)
            <div>
              <div class="text-xs text-[#a6a6b0]">Trailer</div>
              <a href="{{ $movie->trailer }}" target="_blank" class="inline-flex items-center px-4 py-2 rounded-lg bg-red-600/20 text-red-300 text-sm hover:bg-red-600/30">
                <i class="fab fa-youtube mr-2"></i> Xem trailer
              </a>
            </div>
          @endif

          <div>
            <div class="text-xs text-[#a6a6b0] mb-1">Mô tả</div>
            <div class="border border-[#262833] rounded-lg p-3 bg-[#0f0f12] text-[#d7d7df]">{{ $movie->mo_ta }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Showtimes -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-white">Lịch chiếu</h2>
      </div>

      @if($movie->suatChieu->count() > 0)
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-[#1a1d24]">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Phòng chiếu</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Bắt đầu</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Kết thúc</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#a6a6b0] uppercase">Trạng thái</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#262833]">
              @foreach($movie->suatChieu as $showtime)
                <tr class="hover:bg-[#1a1d24]">
                  <td class="px-4 py-3 text-white">{{ $showtime->phongChieu->ten_phong ?? 'N/A' }}</td>
                  <td class="px-4 py-3 text-white">{{ $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A' }}</td>
                  <td class="px-4 py-3 text-white">{{ $showtime->thoi_gian_ket_thuc ? $showtime->thoi_gian_ket_thuc->format('d/m/Y H:i') : 'N/A' }}</td>
                  <td class="px-4 py-3">
                    @if($showtime->trang_thai)
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">Hoạt động</span>
                    @else
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-300">Tạm dừng</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-[#a6a6b0] flex items-center gap-2"><i class="fas fa-info-circle"></i> Phim này chưa có lịch chiếu nào.</div>
      @endif
    </div>
  </div>
@endsection
