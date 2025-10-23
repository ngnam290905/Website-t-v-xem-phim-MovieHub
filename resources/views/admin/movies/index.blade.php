@extends('admin.layout')

@section('title', 'Quản lý phim')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <h3 class="card-title m-0">Danh sách phim</h3>
                        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 w-100 w-md-auto">
                            <!-- Status Filters -->
                            <div class="btn-group" role="group" aria-label="Bộ lọc trạng thái">
                                @php $activeStatus = request('status'); @endphp
                                <a href="{{ route('admin.movies.index') }}" class="btn btn-sm {{ $activeStatus ? 'btn-outline-secondary' : 'btn-secondary' }}">Tất cả</a>
                                <a href="{{ route('admin.movies.index', ['status' => 'dang_chieu']) }}" class="btn btn-sm {{ $activeStatus==='dang_chieu' ? 'btn-secondary' : 'btn-outline-secondary' }}">Đang chiếu</a>
                                <a href="{{ route('admin.movies.index', ['status' => 'sap_chieu']) }}" class="btn btn-sm {{ $activeStatus==='sap_chieu' ? 'btn-secondary' : 'btn-outline-secondary' }}">Sắp chiếu</a>
                                <a href="{{ route('admin.movies.index', ['status' => 'ngung_chieu']) }}" class="btn btn-sm {{ $activeStatus==='ngung_chieu' ? 'btn-secondary' : 'btn-outline-secondary' }}">Ngừng chiếu</a>
                            </div>
                            <!-- Search Form -->
                            <form action="{{ route('admin.movies.search') }}" method="GET" class="ms-md-2">
                                <div class="input-group" style="min-width: 280px;">
                                    <input type="text"
                                           class="form-control"
                                           name="search"
                                           placeholder="Tìm theo tên, đạo diễn..."
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit" title="Tìm kiếm">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('admin.movies.index') }}" class="btn btn-outline-danger" title="Xóa tìm kiếm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                            @if(auth()->user()->vaiTro->ten === 'admin')
                                <a href="{{ route('admin.movies.create') }}" class="btn btn-primary ms-md-2">
                                    <i class="fas fa-plus me-1"></i> Thêm phim
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(request('search'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-search"></i>
                            Kết quả tìm kiếm cho: <strong>"{{ request('search') }}"</strong>
                            <span class="badge bg-primary ms-2">{{ $movies->total() }} phim</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div>
                        @if($movies->count())
                            <div class="row g-3">
                                @foreach($movies as $movie)
                                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                        <div class="card h-100 shadow-sm border-0 overflow-hidden">
                                            <div class="ratio" style="--bs-aspect-ratio: 150%;">
                                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->ten_phim }}" class="w-100 h-100" style="object-fit: cover;">
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                    <h5 class="card-title mb-0" title="{{ $movie->ten_phim }}">{{ \Illuminate\Support\Str::limit($movie->ten_phim, 40) }}</h5>
                                                    <span class="badge text-uppercase {{ $movie->trang_thai==='dang_chieu' ? 'bg-success' : ($movie->trang_thai==='sap_chieu' ? 'bg-warning' : 'bg-secondary') }}">
                                                        @switch($movie->trang_thai)
                                                            @case('dang_chieu') Đang chiếu @break
                                                            @case('sap_chieu') Sắp chiếu @break
                                                            @case('ngung_chieu') Ngừng chiếu @break
                                                            @default Khác
                                                        @endswitch
                                                    </span>
                                                </div>
                                                <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($movie->mo_ta, 90) }}</p>
                                                <div class="d-flex flex-wrap gap-2 text-muted small mb-3">
                                                    <span><i class="far fa-clock me-1"></i>{{ $movie->formatted_duration }}</span>
                                                    <span><i class="fa fa-video me-1"></i>{{ $movie->the_loai ?: '—' }}</span>
                                                    <span><i class="fa fa-user-tie me-1"></i>{{ \Illuminate\Support\Str::limit($movie->dao_dien, 18) }}</span>
                                                </div>
                                                <div class="mt-auto d-flex gap-2">
                                                    <a href="{{ route('admin.movies.show', $movie) }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-eye me-1"></i> Xem</a>
                                                    @if(auth()->user()->vaiTro->ten === 'admin')
                                                        <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                        <form action="{{ route('admin.movies.toggle-status', $movie) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-outline-primary btn-sm" title="Đổi trạng thái" onclick="return confirm('Đổi trạng thái phim này?')"><i class="fas fa-sync"></i></button>
                                                        </form>
                                                        <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" onsubmit="return confirm('Xóa phim này?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                @if(request('search'))
                                    <i class="fas fa-search fa-2x mb-3"></i>
                                    <div>Không tìm thấy phim nào với từ khóa: <strong>"{{ request('search') }}"</strong></div>
                                    <a href="{{ route('admin.movies.index') }}" class="btn btn-outline-primary btn-sm mt-3">
                                        <i class="fas fa-list"></i> Xem tất cả phim
                                    </a>
                                @else
                                    <i class="fas fa-film fa-2x mb-3"></i>
                                    <div>Chưa có phim nào</div>
                                    @if(auth()->user()->vaiTro->ten === 'admin')
                                        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="fas fa-plus"></i> Thêm phim đầu tiên
                                        </a>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <nav class="cinema-pagination w-100 d-flex justify-content-center">
                            {{ $movies->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
<style>
    /* Compact, single-line, cinema-themed pagination */
    .cinema-pagination .pagination {
        --bs-pagination-padding-x: .45rem;
        --bs-pagination-padding-y: .15rem;
        --bs-pagination-font-size: .72rem;
        --bs-pagination-border-radius: .35rem;
        --bs-pagination-color: #a6a6b0;
        --bs-pagination-bg: #0f0f12;
        --bs-pagination-hover-color: #ffffff;
        --bs-pagination-hover-bg: #1b1d24;
        --bs-pagination-focus-color: #ffffff;
        --bs-pagination-focus-bg: #1b1d24;
        --bs-pagination-active-color: #ffffff;
        --bs-pagination-active-bg: #F53003; /* accent */
        --bs-pagination-active-border-color: #F53003;
        --bs-pagination-border-color: #2f3240;
        gap: .25rem;
        margin-bottom: 0;
        white-space: nowrap;
        background: transparent;
    }
    .cinema-pagination .page-link {
        display: inline-flex;
        align-items: center;
        height: 26px;
        line-height: 1;
        background-color: var(--bs-pagination-bg);
        border-color: var(--bs-pagination-border-color);
    }
    .cinema-pagination .page-item.disabled .page-link {
        opacity: .45;
        background-color: #0f0f12;
        color: #6c757d;
    }
    .cinema-pagination .page-item:first-child .page-link,
    .cinema-pagination .page-item:last-child .page-link {
        border-radius: .35rem;
    }
    /* Ensure it's always one line and centered */
    .cinema-pagination { overflow-x: auto; padding: .25rem 0; }
</style>
@endpush
@endsection
