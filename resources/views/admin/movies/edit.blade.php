@extends('admin.layout')

@section('title', 'Chỉnh sửa phim')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chỉnh sửa phim: {{ $movie->ten_phim }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.movies.update', $movie) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="ten_phim">Tên phim <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('ten_phim') is-invalid @enderror" 
                                           id="ten_phim" 
                                           name="ten_phim" 
                                           value="{{ old('ten_phim', $movie->ten_phim) }}" 
                                           required>
                                    @error('ten_phim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="mo_ta">Mô tả <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('mo_ta') is-invalid @enderror" 
                                              id="mo_ta" 
                                              name="mo_ta" 
                                              rows="4" 
                                              required>{{ old('mo_ta', $movie->mo_ta) }}</textarea>
                                    @error('mo_ta')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="dao_dien">Đạo diễn <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control @error('dao_dien') is-invalid @enderror" 
                                                   id="dao_dien" 
                                                   name="dao_dien" 
                                                   value="{{ old('dao_dien', $movie->dao_dien) }}" 
                                                   required>
                                            @error('dao_dien')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="do_dai">Độ dài (phút) <span class="text-danger">*</span></label>
                                            <input type="number" 
                                                   class="form-control @error('do_dai') is-invalid @enderror" 
                                                   id="do_dai" 
                                                   name="do_dai" 
                                                   value="{{ old('do_dai', $movie->do_dai) }}" 
                                                   min="1" 
                                                   required>
                                            @error('do_dai')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="dien_vien">Diễn viên <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('dien_vien') is-invalid @enderror" 
                                              id="dien_vien" 
                                              name="dien_vien" 
                                              rows="2" 
                                              placeholder="Nhập tên các diễn viên, cách nhau bởi dấu phẩy" 
                                              required>{{ old('dien_vien', $movie->dien_vien) }}</textarea>
                                    @error('dien_vien')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="trailer">Link trailer</label>
                                    <input type="url" 
                                           class="form-control @error('trailer') is-invalid @enderror" 
                                           id="trailer" 
                                           name="trailer" 
                                           value="{{ old('trailer', $movie->trailer) }}" 
                                           placeholder="https://youtube.com/watch?v=...">
                                    @error('trailer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="trang_thai" 
                                               name="trang_thai" 
                                               value="1" 
                                               {{ old('trang_thai', $movie->trang_thai) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="trang_thai">
                                            Kích hoạt phim
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="poster">Poster phim</label>
                                    <input type="file" 
                                           class="form-control @error('poster') is-invalid @enderror" 
                                           id="poster" 
                                           name="poster" 
                                           accept="image/*">
                                    @error('poster')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Để trống nếu không muốn thay đổi poster hiện tại. Định dạng: JPEG, PNG, JPG, GIF, WEBP. Kích thước tối đa: 5MB
                                    </small>
                                </div>

                                @if($movie->poster)
                                    <div class="current-poster mb-3">
                                        <label class="form-label">Poster hiện tại:</label>
                                        <img src="{{ asset('storage/' . $movie->poster) }}" 
                                             alt="{{ $movie->ten_phim }}" 
                                             class="img-fluid rounded" 
                                             style="max-height: 300px;">
                                    </div>
                                @endif

                                <div id="poster-preview" class="mt-3" style="display: none;">
                                    <label class="form-label">Poster mới:</label>
                                    <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật phim
                            </button>
                            <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview poster image
    const posterInput = document.getElementById('poster');
    const previewDiv = document.getElementById('poster-preview');
    const previewImg = document.getElementById('preview-img');
    
    posterInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewDiv.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewDiv.style.display = 'none';
        }
    });
    
    // Update file input label (for Bootstrap 5)
    posterInput.addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : '';
        // Bootstrap 5 doesn't need custom file label update
    });
});
</script>
@endpush
@endsection
