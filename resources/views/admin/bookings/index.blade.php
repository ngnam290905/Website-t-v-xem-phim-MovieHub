@extends('admin.layout')

@section('title', 'Quản lý đặt vé')

@section('content')
    @if (session('error'))
        <div class="text-red-400 text-sm bg-red-900/30 px-3 py-2 rounded mb-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">🎟️ Quản lý Đặt Vé</h2>
            {{-- Bộ lọc --}}
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
                {{-- Lọc theo trạng thái --}}
                <select name="status"
                    class="bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Chờ xác nhận</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đã hủy</option>
                </select>

                {{-- Lọc theo phim --}}
                <input type="text" name="phim" value="{{ request('phim') }}" placeholder="Tên phim..."
                    class="bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300 w-48 placeholder-gray-500">

                {{-- Lọc theo người dùng --}}
                <input type="text" name="nguoi_dung" value="{{ request('nguoi_dung') }}" placeholder="Tên người dùng..."
                    class="bg-[#1b1e28] border border-[#262833] rounded-lg text-sm px-3 py-2 text-gray-300 w-48 placeholder-gray-500">

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm text-white transition">
                    Lọc
                </button>

                @if (request()->hasAny(['status', 'phim', 'nguoi_dung']))
                    <a href="{{ route('admin.bookings.index') }}"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm text-white transition">
                        Đặt lại
                    </a>
                @endif
            </form>


            @if (session('success'))
                <div class="text-green-400 text-sm bg-green-900/30 px-3 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif
        </div>

        @if ($bookings->isEmpty())
            <div class="text-center text-gray-400 py-10 border border-dashed border-[#262833] rounded-xl">
                <p>Chưa có dữ liệu đặt vé. Kiểm tra database hoặc chạy seeder.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-[#262833] rounded-xl">
                    <thead class="bg-[#1b1e28] text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Người dùng</th>
                            <th class="px-4 py-3">Phim</th>
                            <th class="px-4 py-3">Suất chiếu</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3">Thanh toán</th>
                            <th class="px-4 py-3">Tổng tiền</th>
                            <th class="px-4 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262833]">
                        @foreach ($bookings as $booking)
                            <tr class="hover:bg-[#1b1e28]/70 transition">
                                <td class="px-4 py-3">{{ $booking->id }}</td>
                                <td class="px-4 py-3">{{ $booking->nguoiDung->ho_ten ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $booking->suatChieu?->phim?->ten_phim ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    {{ $booking->suatChieu?->thoi_gian_bat_dau?->format('d/m/Y H:i') ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    @switch($booking->trang_thai)
                                        @case(0)
                                            <span class="px-2 py-1 text-yellow-400 bg-yellow-900/30 rounded-full text-xs">Chờ xác
                                                nhận</span>
                                        @break

                                        @case(1)
                                            <span class="px-2 py-1 text-green-400 bg-green-900/30 rounded-full text-xs">Đã xác
                                                nhận</span>
                                        @break

                                        @case(2)
                                            <span class="px-2 py-1 text-red-400 bg-red-900/30 rounded-full text-xs">Đã hủy</span>
                                        @break

                                        @default
                                            <span class="px-2 py-1 text-gray-400 bg-gray-800 rounded-full text-xs">Không xác
                                                định</span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3">{{ $booking->thanhToan?->phuong_thuc ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ number_format($booking->tong_tien ?? 0) }} VNĐ</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1.5">

                                        {{-- Xem --}}
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                            class="p-1.5 rounded-md bg-blue-600/80 hover:bg-blue-600 transition"
                                            title="Xem vé">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        {{-- Xác nhận (admin + trạng thái chờ) --}}
                                        @auth
                                            @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $booking->trang_thai == 0)
                                                <form action="{{ route('admin.bookings.confirm', $booking->id) }}"
                                                    method="POST" onsubmit="return confirm('Xác nhận vé này?')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-1.5 rounded-md bg-green-600/80 hover:bg-green-600 transition"
                                                        title="Xác nhận vé">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth

                                        {{-- Hủy (admin + trạng thái chờ) --}}
                                        @auth
                                            @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $booking->trang_thai == 0)
                                                <form action="{{ route('admin.bookings.cancel', $booking->id) }}"
                                                    method="POST" onsubmit="return confirm('Xác nhận hủy vé này?')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-1.5 rounded-md bg-red-600/80 hover:bg-red-600 transition"
                                                        title="Hủy vé">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth

                                        {{-- Sửa (admin + chưa hủy) --}}
                                        @auth
                                            @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $booking->trang_thai != 2)
                                                <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                    class="p-1.5 rounded-md bg-yellow-500/80 hover:bg-yellow-500 transition"
                                                    title="Sửa vé">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-.828.5L7 15l1.172-4a2 2 0 01.5-.828z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        @endauth

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $bookings->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
@endsection
