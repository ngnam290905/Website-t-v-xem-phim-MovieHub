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
                                <td class="px-4 py-3 text-center flex gap-2 justify-center">
                                    {{-- Xem --}}
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                        class="px-3 py-1 bg-blue-600/80 hover:bg-blue-600 rounded text-xs">
                                        Xem
                                    </a>

                                    {{-- Hủy --}}
                                    @auth
                                        @if (optional(auth()->user()->vaiTro)->ten === 'admin' && $booking->trang_thai == 0)
                                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST"
                                                onsubmit="return confirm('Xác nhận hủy vé này?')">
                                                @csrf
                                                <button type="submit"
                                                    class="px-3 py-1 bg-red-600/80 hover:bg-red-600 rounded text-xs">
                                                    Hủy
                                                </button>
                                            </form>
                                        @endif
                                    @endauth

                                    {{-- Sửa --}}
                                    <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                        class="px-3 py-1 bg-yellow-500/80 hover:bg-yellow-500 rounded text-xs text-black">
                                        Sửa
                                    </a>
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
