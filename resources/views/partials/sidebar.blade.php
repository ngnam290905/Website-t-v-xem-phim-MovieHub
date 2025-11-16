<aside class="hidden lg:block lg:sticky lg:top-6 w-64 shrink-0">
  <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-4">
    <h3 class="font-semibold mb-3">Danh mục</h3>
    <nav class="flex flex-col gap-1 text-sm">
      <a href="{{ route('movies.now-showing') }}" class="px-3 py-2 rounded hover:bg-[#222533]">Đang chiếu</a>
      <a href="{{ route('movies.coming-soon') }}" class="px-3 py-2 rounded hover:bg-[#222533]">Sắp chiếu</a>
      <a href="{{ route('movies.hot') }}" class="px-3 py-2 rounded hover:bg-[#222533]">Phim hot</a>
    </nav>
  </div>
</aside>
