@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-[#a6a6b0] bg-[#1a1d24] border border-[#262833] cursor-default leading-5 rounded-lg">
                    <i class="fas fa-chevron-left mr-2"></i> Trước
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#151822] border border-[#262833] leading-5 rounded-lg hover:bg-[#1a1d24] focus:outline-none focus:ring ring-[#F53003] focus:border-[#F53003] active:bg-[#1a1d24] transition ease-in-out duration-150">
                    <i class="fas fa-chevron-left mr-2"></i> Trước
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-white bg-[#151822] border border-[#262833] leading-5 rounded-lg hover:bg-[#1a1d24] focus:outline-none focus:ring ring-[#F53003] focus:border-[#F53003] active:bg-[#1a1d24] transition ease-in-out duration-150">
                    Sau <i class="fas fa-chevron-right ml-2"></i>
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-[#a6a6b0] bg-[#1a1d24] border border-[#262833] cursor-default leading-5 rounded-lg">
                    Sau <i class="fas fa-chevron-right ml-2"></i>
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#a6a6b0] leading-5">
                    Hiển thị
                    @if ($paginator->firstItem())
                        <span class="font-medium text-white">{{ $paginator->firstItem() }}</span>
                        đến
                        <span class="font-medium text-white">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    trong
                    <span class="font-medium text-white">{{ $paginator->total() }}</span>
                    kết quả
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-lg" role="group">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-[#a6a6b0] bg-[#1a1d24] border border-[#262833] cursor-default rounded-l-lg" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-[#151822] border border-[#262833] rounded-l-lg hover:bg-[#1a1d24] focus:z-10 focus:outline-none focus:ring ring-[#F53003] focus:border-[#F53003] active:bg-[#1a1d24] transition ease-in-out duration-150" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-[#a6a6b0] bg-[#1a1d24] border border-[#262833] cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#F53003] border border-[#F53003] cursor-default z-10">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#151822] border border-[#262833] hover:bg-[#1a1d24] focus:z-10 focus:outline-none focus:ring ring-[#F53003] focus:border-[#F53003] active:bg-[#1a1d24] transition ease-in-out duration-150" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-[#151822] border border-[#262833] rounded-r-lg hover:bg-[#1a1d24] focus:z-10 focus:outline-none focus:ring ring-[#F53003] focus:border-[#F53003] active:bg-[#1a1d24] transition ease-in-out duration-150" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-[#a6a6b0] bg-[#1a1d24] border border-[#262833] cursor-default rounded-r-lg" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif

