@extends('admin.layout')

@section('title', 'Quản lý phim')
@section('page-title', 'Quản lý Phim')
@section('page-description', 'Danh sách và quản lý phim trong hệ thống')

@section('content')
  <!-- Breadcrumb -->

  <div class="space-y-6">
    <!-- Quick Stats -->

    <!-- Header + Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-white">Danh sách Phim</h1>
        <p class="text-[#a6a6b0] mt-1">Quản lý tất cả phim trong hệ thống</p>
      </div>
      @php $roleName = optional(auth()->user()->vaiTro)->ten; @endphp
      @if(auth()->user() && in_array($roleName, ['admin','staff','Nhân viên','nhan vien','NV','nv','Nhan vien']))
        <a href="{{ route('admin.movies.create') }}" class="bg-[#F53003] hover:bg-[#e02a00] text-white px-5 py-2.5 rounded-lg font-semibold transition-colors inline-flex items-center">
          <i class="fas fa-plus mr-2"></i> Thêm phim
        </a>
      @endif
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Tổng phim</div>
        <div class="text-2xl font-bold text-white mt-1">{{ $totalMovies ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Đang chiếu</div>
        <div class="text-2xl font-bold text-green-400 mt-1">{{ $nowShowing ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Sắp chiếu</div>
        <div class="text-2xl font-bold text-yellow-400 mt-1">{{ $upcoming ?? 0 }}</div>
      </div>
      <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
        <div class="text-sm text-[#a6a6b0]">Ngừng chiếu</div>
        <div class="text-2xl font-bold text-gray-400 mt-1">{{ $ended ?? 0 }}</div>
      </div>
    </div>

    @if(session('success'))
      <div class="bg-green-600/10 border border-green-600/30 text-green-400 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="bg-red-600/10 border border-red-600/30 text-red-400 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
      <form action="{{ route('admin.movies.index') }}" method="GET" class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
          <div>
            <label class="block text-xs text-[#a6a6b0] mb-1">Trạng thái</label>
            <select name="status" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-sm text-white">
              <option value="">Tất cả</option>
              <option value="dang_chieu" {{ request('status')==='dang_chieu' ? 'selected' : '' }}>Đang chiếu</option>
              <option value="sap_chieu" {{ request('status')==='sap_chieu' ? 'selected' : '' }}>Sắp chiếu</option>
              <option value="ngung_chieu" {{ request('status')==='ngung_chieu' ? 'selected' : '' }}>Ngừng chiếu</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-[#a6a6b0] mb-1">Diễn viên</label>
            <input type="text" name="dien_vien" value="{{ request('dien_vien') }}" placeholder="Tên diễn viên" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-sm text-white">
          </div>
          <div>
            <label class="block text-xs text-[#a6a6b0] mb-1">Thể loại</label>
            <input type="text" name="the_loai" value="{{ request('the_loai') }}" placeholder="Thể loại" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-sm text-white">
          </div>
          <div>
            <label class="block text-xs text-[#a6a6b0] mb-1">Quốc gia</label>
            <input type="text" name="quoc_gia" value="{{ request('quoc_gia') }}" placeholder="Quốc gia" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-sm text-white">
          </div>
          <div class="self-end flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-[#F53003] hover:bg-[#e02a00] text-white rounded-lg text-sm">Tìm kiếm</button>
            <a href="{{ route('admin.movies.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm">Xóa bộ lọc</a>
          </div>
        </div>
        @if(request()->hasAny(['status','dien_vien','the_loai','quoc_gia','search']))
          <div class="text-xs text-[#a6a6b0]">Đang áp dụng {{ collect(request()->only(['status','dien_vien','the_loai','quoc_gia','search']))->filter()->count() }} bộ lọc</div>
        @endif
      </form>
    </div>

    <!-- Movies grid -->
    @if($movies->count())
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($movies as $movie)
          <div class="bg-[#151822] border border-[#262833] rounded-xl overflow-hidden flex flex-col">
            <div class="relative">
              <img src="{{ $movie->poster_url }}" alt="{{ $movie->ten_phim }}" class="w-full aspect-[2/3] object-cover">
              <span class="absolute top-3 left-3 text-[10px] uppercase px-2 py-1 rounded-full font-semibold {{ $movie->trang_thai==='dang_chieu' ? 'bg-green-500/20 text-green-300' : ($movie->trang_thai==='sap_chieu' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-gray-500/20 text-gray-300') }}">
                @switch($movie->trang_thai)
                  @case('dang_chieu') Đang chiếu @break
                  @case('sap_chieu') Sắp chiếu @break
                  @case('ngung_chieu') Ngừng chiếu @break
                  @default Khác
                @endswitch
              </span>
            </div>
            <div class="p-4 flex-1 flex flex-col gap-3">
              <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold leading-tight" title="{{ $movie->ten_phim }}">{{ \Illuminate\Support\Str::limit($movie->ten_phim, 48) }}</h3>
              </div>
              <p class="text-xs text-[#a6a6b0]">{{ \Illuminate\Support\Str::limit($movie->mo_ta, 120) }}</p>
              <div class="flex flex-wrap gap-3 text-xs text-[#a6a6b0]">
                <span><i class="far fa-clock mr-1"></i>{{ $movie->formatted_duration }}</span>
                <span><i class="fa fa-video mr-1"></i>{{ $movie->the_loai ?: '—' }}</span>
                <span><i class="fa fa-user-tie mr-1"></i>{{ \Illuminate\Support\Str::limit($movie->dao_dien, 20) }}</span>
              </div>
              <div class="mt-auto flex items-center gap-2">
                <a href="{{ route('admin.movies.show', $movie) }}" class="btn-table-action btn-table-view" title="Xem chi tiết">
                  <i class="fas fa-eye text-xs"></i>
                </a>
                @php $roleName = optional(auth()->user()->vaiTro)->ten; @endphp
                @if(auth()->user() && in_array($roleName, ['admin','staff','Nhân viên','nhan vien','NV','nv','Nhan vien']))
                  <a href="{{ route('admin.movies.edit', $movie) }}" class="btn-table-action btn-table-edit" title="Chỉnh sửa">
                    <i class="fas fa-edit text-xs"></i>
                  </a>
                  <form action="{{ route('admin.movies.toggle-status', $movie) }}" method="POST" onsubmit="return confirm('Đổi trạng thái phim này?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-table-action bg-purple-600 hover:bg-purple-700" title="Đổi trạng thái">
                      <i class="fas fa-sync text-xs"></i>
                    </button>
                  </form>
                  <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phim này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-table-action btn-table-delete" title="Xóa">
                      <i class="fas fa-trash text-xs"></i>
                    </button>
                  </form>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-16">
        @if(request('search'))
          <i class="fas fa-search text-3xl text-[#a6a6b0] mb-3"></i>
          <div class="text-[#a6a6b0]">Không tìm thấy phim nào với từ khóa: <strong class="text-white">"{{ request('search') }}"</strong></div>
          <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center px-4 py-2 mt-4 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]">
            <i class="fas fa-list mr-2"></i> Xem tất cả phim
          </a>
        @else
          <i class="fas fa-film text-3xl text-[#a6a6b0] mb-3"></i>
          <div class="text-[#a6a6b0]">Chưa có phim nào</div>
          @php $roleName = optional(auth()->user()->vaiTro)->ten; @endphp
          @if(auth()->user() && in_array($roleName, ['admin','staff','Nhân viên','nhan vien','NV','nv','Nhan vien']))
            <a href="{{ route('admin.movies.create') }}" class="inline-flex items-center px-4 py-2 mt-4 bg-[#F53003] hover:bg-[#e02a00] text-white rounded-lg text-sm">
              <i class="fas fa-plus mr-2"></i> Thêm phim đầu tiên
            </a>
          @endif
        @endif
      </div>
    @endif

    <!-- Pagination -->
    @if($movies->hasPages())
      <div class="flex items-center justify-between">
        <div class="text-sm text-[#a6a6b0]">
          Trang {{ $movies->currentPage() }}/{{ $movies->lastPage() }} • {{ $movies->total() }} phim
        </div>
        <div class="flex items-center gap-2">{{ $movies->appends(request()->except('page'))->links() }}</div>
      </div>
    @endif
  </div>
@endsection
