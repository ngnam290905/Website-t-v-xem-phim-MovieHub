@extends('admin.layout')

@section('title', 'Quản lý phim')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Danh sách phim</h3>
                    @if(auth()->user()->vaiTro->ten === 'admin')
                        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm phim mới
                        </a>
                    @endif
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Poster</th>
                                    <th>Tên phim</th>
                                    <th>Đạo diễn</th>
                                    <th>Độ dài (phút)</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movies as $movie)
                                    <tr>
                                        <td>{{ $movie->id }}</td>
                                        <td>
                                            @if($movie->poster)
                                                <img src="{{ asset('storage/' . $movie->poster) }}" 
                                                     alt="{{ $movie->ten_phim }}" 
                                                     style="width: 60px; height: 80px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 80px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $movie->ten_phim }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($movie->mo_ta, 50) }}</small>
                                        </td>
                                        <td>{{ $movie->dao_dien }}</td>
                                        <td>{{ $movie->do_dai }}</td>
                                        <td>
                                            @if($movie->trang_thai)
                                                <span class="badge badge-success">Hoạt động</span>
                                            @else
                                                <span class="badge badge-secondary">Tạm dừng</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.movies.show', $movie) }}" 
                                                   class="btn btn-info btn-sm" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if(auth()->user()->vaiTro->ten === 'admin')
                                                    <a href="{{ route('admin.movies.edit', $movie) }}" 
                                                       class="btn btn-warning btn-sm" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form action="{{ route('admin.movies.toggle-status', $movie) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-{{ $movie->trang_thai ? 'secondary' : 'success' }} btn-sm"
                                                                title="{{ $movie->trang_thai ? 'Tạm dừng' : 'Kích hoạt' }}"
                                                                onclick="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái phim này?')">
                                                            <i class="fas fa-{{ $movie->trang_thai ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('admin.movies.destroy', $movie) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa phim này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-film fa-2x mb-2"></i>
                                            <br>
                                            Chưa có phim nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $movies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
