@extends('admin.layout')

@section('title', 'Chỉnh sửa phim')
@section('page-title', 'Chỉnh sửa phim')
@section('page-description', 'Cập nhật thông tin phim: ' . $movie->ten_phim)

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
          <span class="ml-1 text-sm font-medium text-white md:ml-2">Chỉnh sửa</span>
        </div>
      </li>
    </ol>
  </nav>

  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <h1 class="text-xl font-semibold">{{ $movie->ten_phim }}</h1>
      <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]"><i class="fas fa-arrow-left mr-2"></i> Quay lại</a>
    </div>

    @if ($errors->any())
      <div class="bg-red-600/10 border border-red-600/30 text-red-400 px-4 py-3 rounded-lg">
        <div class="font-semibold mb-1">Vui lòng kiểm tra lại các lỗi sau:</div>
        <ul class="list-disc ml-5 space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    @if (session('error'))
      <div class="bg-red-600/10 border border-red-600/30 text-red-400 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif
    @if (session('success'))
      <div class="bg-green-600/10 border border-green-600/30 text-green-400 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-[#151822] border border-[#262833] rounded-xl p-5">
      <form action="{{ route('admin.movies.update', $movie) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-4">
            <div>
              <label for="ten_phim" class="block text-sm text-[#a6a6b0] mb-1">Tên phim <span class="text-red-500">*</span></label>
              <input id="ten_phim" name="ten_phim" type="text" value="{{ old('ten_phim', $movie->ten_phim) }}" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('ten_phim') ring-2 ring-red-500 @enderror">
              @error('ten_phim')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="ten_goc" class="block text-sm text-[#a6a6b0] mb-1">Tên gốc</label>
              <input id="ten_goc" name="ten_goc" type="text" value="{{ old('ten_goc', $movie->ten_goc) }}" placeholder="Tên phim bằng ngôn ngữ gốc" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('ten_goc') ring-2 ring-red-500 @enderror">
              @error('ten_goc')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="mo_ta" class="block text-sm text-[#a6a6b0] mb-1">Mô tả <span class="text-red-500">*</span></label>
              <textarea id="mo_ta" name="mo_ta" rows="4" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('mo_ta') ring-2 ring-red-500 @enderror">{{ old('mo_ta', $movie->mo_ta) }}</textarea>
              @error('mo_ta')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="dao_dien" class="block text-sm text-[#a6a6b0] mb-1">Đạo diễn <span class="text-red-500">*</span></label>
                <input id="dao_dien" name="dao_dien" type="text" value="{{ old('dao_dien', $movie->dao_dien) }}" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('dao_dien') ring-2 ring-red-500 @enderror">
                @error('dao_dien')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
              <div>
                <label for="do_dai" class="block text-sm text-[#a6a6b0] mb-1">Độ dài (phút) <span class="text-red-500">*</span></label>
                <input id="do_dai" name="do_dai" type="number" value="{{ old('do_dai', $movie->do_dai) }}" min="1" max="600" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('do_dai') ring-2 ring-red-500 @enderror">
                @error('do_dai')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="the_loai" class="block text-sm text-[#a6a6b0] mb-1">Thể loại</label>
                <input id="the_loai" name="the_loai" type="text" value="{{ old('the_loai', $movie->the_loai) }}" placeholder="Hành động, Tình cảm, Hài hước..." class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('the_loai') ring-2 ring-red-500 @enderror">
                @error('the_loai')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
              <div>
                <label for="quoc_gia" class="block text-sm text-[#a6a6b0] mb-1">Quốc gia</label>
                <input id="quoc_gia" name="quoc_gia" type="text" value="{{ old('quoc_gia', $movie->quoc_gia) }}" placeholder="Việt Nam, Mỹ, Hàn Quốc..." class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('quoc_gia') ring-2 ring-red-500 @enderror">
                @error('quoc_gia')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="ngon_ngu" class="block text-sm text-[#a6a6b0] mb-1">Ngôn ngữ</label>
                <input id="ngon_ngu" name="ngon_ngu" type="text" value="{{ old('ngon_ngu', $movie->ngon_ngu) }}" placeholder="Tiếng Việt, English, 한국어..." class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('ngon_ngu') ring-2 ring-red-500 @enderror">
                @error('ngon_ngu')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
              <div>
                <label for="do_tuoi" class="block text-sm text-[#a6a6b0] mb-1">Độ tuổi</label>
                <select id="do_tuoi" name="do_tuoi" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('do_tuoi') ring-2 ring-red-500 @enderror">
                  <option value="">Chọn độ tuổi</option>
                  <option value="P" {{ old('do_tuoi', $movie->do_tuoi) == 'P' ? 'selected' : '' }}>P - Phổ biến</option>
                  <option value="C13" {{ old('do_tuoi', $movie->do_tuoi) == 'C13' ? 'selected' : '' }}>C13 - Trên 13 tuổi</option>
                  <option value="C16" {{ old('do_tuoi', $movie->do_tuoi) == 'C16' ? 'selected' : '' }}>C16 - Trên 16 tuổi</option>
                  <option value="C18" {{ old('do_tuoi', $movie->do_tuoi) == 'C18' ? 'selected' : '' }}>C18 - Trên 18 tuổi</option>
                </select>
                @error('do_tuoi')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="ngay_khoi_chieu" class="block text-sm text-[#a6a6b0] mb-1">Ngày khởi chiếu</label>
                <input id="ngay_khoi_chieu" name="ngay_khoi_chieu" type="date" value="{{ old('ngay_khoi_chieu', $movie->ngay_khoi_chieu ? $movie->ngay_khoi_chieu->format('Y-m-d') : '') }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('ngay_khoi_chieu') ring-2 ring-red-500 @enderror">
                @error('ngay_khoi_chieu')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
              <div>
                <label for="ngay_ket_thuc" class="block text-sm text-[#a6a6b0] mb-1">Ngày kết thúc</label>
                <input id="ngay_ket_thuc" name="ngay_ket_thuc" type="date" value="{{ old('ngay_ket_thuc', $movie->ngay_ket_thuc ? $movie->ngay_ket_thuc->format('Y-m-d') : '') }}" class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('ngay_ket_thuc') ring-2 ring-red-500 @enderror">
                @error('ngay_ket_thuc')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              </div>
            </div>

            <div>
              <label for="dien_vien" class="block text-sm text-[#a6a6b0] mb-1">Diễn viên <span class="text-red-500">*</span></label>
              <textarea id="dien_vien" name="dien_vien" rows="2" placeholder="Nhập tên các diễn viên, cách nhau bởi dấu phẩy" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('dien_vien') ring-2 ring-red-500 @enderror">{{ old('dien_vien', $movie->dien_vien) }}</textarea>
              @error('dien_vien')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="trailer" class="block text-sm text-[#a6a6b0] mb-1">Link trailer</label>
              <input id="trailer" name="trailer" type="url" value="{{ old('trailer', $movie->trailer) }}" placeholder="https://youtube.com/watch?v=..." class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('trailer') ring-2 ring-red-500 @enderror">
              @error('trailer')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
            </div>

            <div>
              <label for="trang_thai" class="block text-sm text-[#a6a6b0] mb-1">Trạng thái phim <span class="text-red-500">*</span></label>
              <select id="trang_thai" name="trang_thai" required class="w-full px-3 py-2 bg-[#1a1d24] border border-[#262833] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] @error('trang_thai') ring-2 ring-red-500 @enderror">
                <option value="">Chọn trạng thái</option>
                <option value="sap_chieu" {{ old('trang_thai', $movie->trang_thai) == 'sap_chieu' ? 'selected' : '' }}>Sắp chiếu</option>
                <option value="dang_chieu" {{ old('trang_thai', $movie->trang_thai) == 'dang_chieu' ? 'selected' : '' }}>Đang chiếu</option>
                <option value="ngung_chieu" {{ old('trang_thai', $movie->trang_thai) == 'ngung_chieu' ? 'selected' : '' }}>Ngừng chiếu</option>
              </select>
              @error('trang_thai')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="space-y-3">
            <div>
              <label for="poster" class="block text-sm text-[#a6a6b0] mb-1">Poster phim</label>
              <input id="poster" name="poster" type="file" accept="image/*" class="block w-full text-sm text-[#a6a6b0] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#222533] file:text-white hover:file:bg-[#2b2e3b] @error('poster') ring-2 ring-red-500 @enderror">
              @error('poster')<div class="mt-1 text-xs text-red-400">{{ $message }}</div>@enderror
              <p class="text-xs text-[#7c7d86] mt-1">Định dạng: JPEG, PNG, JPG, GIF, WEBP. Kích thước tối đa: 5MB</p>
            </div>

            @if($movie->poster)
              <div class="space-y-2">
                <div class="text-xs text-[#a6a6b0]">Poster hiện tại:</div>
                <img src="{{ $movie->poster_url }}" alt="{{ $movie->ten_phim }}" class="w-full max-h-96 object-cover rounded-lg border border-[#262833]">
              </div>
            @endif

            <div id="poster-preview" class="hidden">
              <div class="text-xs text-[#a6a6b0]">Poster mới:</div>
              <img id="preview-img" alt="Preview" class="w-full max-h-96 object-cover rounded-lg border border-[#262833]">
            </div>
          </div>
        </div>

        <div class="pt-2 flex items-center gap-2">
          <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-[#F53003] hover:bg-[#e02a00] text-white text-sm font-semibold"><i class="fas fa-save mr-2"></i> Cập nhật phim</button>
          <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-[#2f3240] text-sm text-[#a6a6b0] hover:bg-[#222533]"><i class="fas fa-times mr-2"></i> Hủy</a>
        </div>
      </form>
    </div>
  </div>

  @push('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const posterInput = document.getElementById('poster');
    const previewDiv = document.getElementById('poster-preview');
    const previewImg = document.getElementById('preview-img');
    posterInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
          previewImg.src = ev.target.result;
          previewDiv.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
      } else {
        previewDiv.classList.add('hidden');
      }
    });
  });
  </script>
  @endpush
@endsection