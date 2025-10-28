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
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Vui lòng kiểm tra lại các lỗi sau:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
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
                                    <label for="ten_goc">Tên gốc</label>
                                    <input type="text" 
                                           class="form-control @error('ten_goc') is-invalid @enderror" 
                                           id="ten_goc" 
                                           name="ten_goc" 
                                           value="{{ old('ten_goc', $movie->ten_goc) }}" 
                                           placeholder="Tên phim bằng ngôn ngữ gốc">
                                    @error('ten_goc')
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
                                                   max="600"
                                                   required>
                                            @error('do_dai')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="the_loai">Thể loại</label>
                                            <input type="text" 
                                                   class="form-control @error('the_loai') is-invalid @enderror" 
                                                   id="the_loai" 
                                                   name="the_loai" 
                                                   value="{{ old('the_loai', $movie->the_loai) }}" 
                                                   placeholder="Hành động, Tình cảm, Hài hước...">
                                            @error('the_loai')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quoc_gia">Quốc gia</label>
                                            <input type="text" 
                                                   class="form-control @error('quoc_gia') is-invalid @enderror" 
                                                   id="quoc_gia" 
                                                   name="quoc_gia" 
                                                   value="{{ old('quoc_gia', $movie->quoc_gia) }}" 
                                                   placeholder="Việt Nam, Mỹ, Hàn Quốc...">
                                            @error('quoc_gia')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ngon_ngu">Ngôn ngữ</label>
                                            <input type="text" 
                                                   class="form-control @error('ngon_ngu') is-invalid @enderror" 
                                                   id="ngon_ngu" 
                                                   name="ngon_ngu" 
                                                   value="{{ old('ngon_ngu', $movie->ngon_ngu) }}" 
                                                   placeholder="Tiếng Việt, English, 한국어...">
                                            @error('ngon_ngu')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="do_tuoi">Độ tuổi</label>
                                            <select class="form-control @error('do_tuoi') is-invalid @enderror" 
                                                    id="do_tuoi" 
                                                    name="do_tuoi">
                                                <option value="">Chọn độ tuổi</option>
                                                <option value="P" {{ old('do_tuoi', $movie->do_tuoi) == 'P' ? 'selected' : '' }}>P - Phổ biến</option>
                                                <option value="C13" {{ old('do_tuoi', $movie->do_tuoi) == 'C13' ? 'selected' : '' }}>C13 - Trên 13 tuổi</option>
                                                <option value="C16" {{ old('do_tuoi', $movie->do_tuoi) == 'C16' ? 'selected' : '' }}>C16 - Trên 16 tuổi</option>
                                                <option value="C18" {{ old('do_tuoi', $movie->do_tuoi) == 'C18' ? 'selected' : '' }}>C18 - Trên 18 tuổi</option>
                                            </select>
                                            @error('do_tuoi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ngay_khoi_chieu">Ngày khởi chiếu</label>
                                            <input type="date" 
                                                   class="form-control @error('ngay_khoi_chieu') is-invalid @enderror" 
                                                   id="ngay_khoi_chieu" 
                                                   name="ngay_khoi_chieu" 
                                                   value="{{ old('ngay_khoi_chieu', $movie->ngay_khoi_chieu ? $movie->ngay_khoi_chieu->format('Y-m-d') : '') }}">
                                            @error('ngay_khoi_chieu')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ngay_ket_thuc">Ngày kết thúc</label>
                                            <input type="date" 
                                                   class="form-control @error('ngay_ket_thuc') is-invalid @enderror" 
                                                   id="ngay_ket_thuc" 
                                                   name="ngay_ket_thuc" 
                                                   value="{{ old('ngay_ket_thuc', $movie->ngay_ket_thuc ? $movie->ngay_ket_thuc->format('Y-m-d') : '') }}">
                                            @error('ngay_ket_thuc')
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
                                    <label for="trang_thai">Trạng thái phim <span class="text-danger">*</span></label>
                                    <select class="form-control @error('trang_thai') is-invalid @enderror" 
                                            id="trang_thai" 
                                            name="trang_thai" 
                                            required>
                                        <option value="">Chọn trạng thái</option>
                                        <option value="sap_chieu" {{ old('trang_thai', $movie->trang_thai) == 'sap_chieu' ? 'selected' : '' }}>Sắp chiếu</option>
                                        <option value="dang_chieu" {{ old('trang_thai', $movie->trang_thai) == 'dang_chieu' ? 'selected' : '' }}>Đang chiếu</option>
                                        <option value="ngung_chieu" {{ old('trang_thai', $movie->trang_thai) == 'ngung_chieu' ? 'selected' : '' }}>Ngừng chiếu</option>
                                    </select>
                                    @error('trang_thai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                        Định dạng: JPEG, PNG, JPG, GIF, WEBP. Kích thước tối đa: 5MB
                                    </small>
                                </div>

                                @if($movie->poster)
                                    <div class="current-poster mb-3">
                                        <label>Poster hiện tại:</label>
                                        <img src="{{ $movie->poster_url }}" 
                                             alt="{{ $movie->ten_phim }}" 
                                             class="img-fluid rounded" 
                                             style="max-height: 300px;">
                                    </div>
                                @endif

                                <div id="poster-preview" class="mt-3" style="display: none;">
                                    <label>Poster mới:</label>
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
});
</script>
@endpush
@endsection