@extends('admin.layout')

@section('title', 'Chi tiết phim')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết phim: {{ $movie->ten_phim }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        @if(auth()->user()->vaiTro->ten === 'admin')
                            <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-warning ml-2">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($movie->poster)
                                <div class="text-center mb-4">
                                    <img src="{{ asset('storage/' . $movie->poster) }}" 
                                         alt="{{ $movie->ten_phim }}" 
                                         class="img-fluid rounded shadow" 
                                         style="max-height: 400px;">
                                </div>
                            @else
                                <div class="text-center mb-4">
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded shadow" 
                                         style="height: 400px;">
                                        <div class="text-muted">
                                            <i class="fas fa-image fa-3x mb-2"></i>
                                            <br>
                                            <span>Chưa có poster</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5><strong>Tên phim:</strong></h5>
                                    <p class="text-muted">{{ $movie->ten_phim }}</p>
                                </div>
                                
                                <div class="col-sm-6">
                                    <h5><strong>Trạng thái:</strong></h5>
                                    @if($movie->trang_thai)
                                        <span class="badge bg-success fs-6">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Tạm dừng</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5><strong>Đạo diễn:</strong></h5>
                                    <p class="text-muted">{{ $movie->dao_dien }}</p>
                                </div>
                                
                                <div class="col-sm-6">
                                    <h5><strong>Độ dài:</strong></h5>
                                    <p class="text-muted">{{ $movie->do_dai }} phút</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <h5><strong>Diễn viên:</strong></h5>
                                    <p class="text-muted">{{ $movie->dien_vien }}</p>
                                </div>
                            </div>
                            
                            @if($movie->trailer)
                                <div class="row">
                                    <div class="col-12">
                                        <h5><strong>Trailer:</strong></h5>
                                        <a href="{{ $movie->trailer }}" target="_blank" class="btn btn-danger">
                                            <i class="fab fa-youtube"></i> Xem trailer
                                        </a>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5><strong>Mô tả:</strong></h5>
                                    <div class="border rounded p-3 bg-light">
                                        <p class="mb-0">{{ $movie->mo_ta }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($movie->suatChieu->count() > 0)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5><strong>Lịch chiếu:</strong></h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Phòng chiếu</th>
                                                <th>Thời gian bắt đầu</th>
                                                <th>Thời gian kết thúc</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($movie->suatChieu as $showtime)
                                                <tr>
                                                    <td>{{ $showtime->phongChieu->ten_phong ?? 'N/A' }}</td>
                                                    <td>{{ $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y H:i') : 'N/A' }}</td>
                                                    <td>{{ $showtime->thoi_gian_ket_thuc ? $showtime->thoi_gian_ket_thuc->format('d/m/Y H:i') : 'N/A' }}</td>
                                                    <td>
                                                        @if($showtime->trang_thai)
                                                            <span class="badge bg-success">Hoạt động</span>
                                                        @else
                                                            <span class="badge bg-secondary">Tạm dừng</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Phim này chưa có lịch chiếu nào.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
