@extends('layouts.main')

@section('title', 'Đặt vé - MovieHub')

@section('content')
    @php
        $combos = App\Models\Combo::where('trang_thai', 1)->get();
        $khuyenmais = App\Models\KhuyenMai::where('trang_thai', 1)
            ->where('ngay_bat_dau', '<=', now())
            ->where('ngay_ket_thuc', '>=', now())
            ->get();
    @endphp

    <div class="min-h-screen bg-black text-white">
        <!-- Header -->
        <div class="bg-gray-900 border-b border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-xl font-semibold">Đặt vé</h1>
                </div>
                <div class="flex items-center gap-2">
                    @auth
                        <span class="text-sm text-gray-400">Xin chào, {{ Auth::user()->ho_ten }}</span>
                        <a href="{{ route('logout') }}" class="text-sm bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded">Đăng
                            xuất</a>
                    @else
                        <span class="text-sm text-gray-400">Đăng nhập để tích điểm</span>
                        <a href="{{ route('login.form') }}" class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1 rounded">Đăng
                            nhập</a>
                    @endauth
                </div>
            </div>
        </div>


        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Left Column - Movie Info and Seat Selection -->
                <div id="main-left" class="lg:col-span-2 space-y-6">
                    <!-- Movie Info -->
                    <div class="bg-gray-900 rounded-lg p-6">
                        <div class="flex gap-6">
                            <img src="{{ $movie->poster ?? 'https://image.tmdb.org/t/p/w342/2CAL2433ZeIihfX1Hb2139CX0pW.jpg' }}"
                                alt="{{ $movie->ten_phim ?? 'Movie' }}" class="w-32 h-48 object-cover rounded-lg">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold">{{ $movie->ten_phim ?? 'Movie Title' }}</h2>
                                <div class="mt-2 space-y-1">
                                    <p class="text-gray-400">{{ $movie->thoi_luong ?? '120' }} phút</p>
                                    <p class="text-gray-400">Lượt xem: 2.5M</p>
                                    <div class="flex items-center gap-2 mt-3">
                                        <span class="bg-yellow-600 text-xs px-2 py-1 rounded">T13</span>
                                        <span class="text-gray-400">Phim dành cho khán giả từ 13 tuổi trở lên</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date & Showtime Selection (Primary) -->
                        <div class="bg-gray-900 rounded-lg p-6 mt-6">
                            <h3 class="text-lg font-semibold mb-4">Chọn ngày</h3>
                            <div id="datePicker" class="flex gap-3 overflow-x-auto pb-2"></div>

                            <h3 class="text-lg font-semibold mt-6 mb-4">Chọn suất chiếu</h3>
                            <div id="showtimesContainer" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="col-span-full text-center py-8">
                                    <p class="text-gray-400">Vui lòng chọn ngày để xem suất chiếu</p>
                                </div>
                            </div>
                        </div>
                        <script>
                            (function() {
                                try {
                                    const dp = document.getElementById('datePicker');
                                    const stc = document.getElementById('showtimesContainer');
                                    const mid = {!! json_encode($movie->id ?? null) !!};
                                    if (!dp || !stc || !mid) return;
                                    stc.style.display = 'none';
                                    const dayVi = d => (
                                        d === 'Monday' ? 'Thứ 2' : d === 'Tuesday' ? 'Thứ 3' : d === 'Wednesday' ? 'Thứ 4' : d ===
                                        'Thursday' ? 'Thứ 5' : d === 'Friday' ? 'Thứ 6' : d === 'Saturday' ? 'Thứ 7' : 'Chủ nhật'
                                    );
                                    let sel = new Date().toISOString().slice(0, 10);

                                    function btnHtml(date) {
                                        return '<div class="text-center">\
                                                                                                                                                    <div class="text-[11px] opacity-75">' +
                                            dayVi(date.day_name) +
                                            '</div>\
                                                                                                                                                    <div class="font-semibold mt-1">' +
                                            (date
                                                .is_today ? 'Hôm nay' : (
                                                    date.is_tomorrow ?
                                                    'Ngày mai' : date.formatted)) +
                                            '</div>\
                                                                                                                                                  </div>';
                                    }

                                    function mkFallback() {
                                        const arr = [];
                                        const names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                        for (let i = 0; i < 7; i++) {
                                            const d = new Date();
                                            d.setDate(d.getDate() + i);
                                            const y = d.getFullYear(),
                                                m = ('0' + (d.getMonth() + 1)).slice(-2),
                                                dd = ('0' + d.getDate()).slice(-2);
                                            arr.push({
                                                date: `${y}-${m}-${dd}`,
                                                formatted: `${dd}/${m}/${y}`,
                                                day_name: names[d.getDay()],
                                                is_today: i === 0,
                                                is_tomorrow: i === 1
                                            });
                                        }
                                        return arr;
                                    }

                                    function renderDates(list) {
                                        dp.innerHTML = '';
                                        list.forEach(date => {
                                            const b = document.createElement('button');
                                            b.type = 'button';
                                            const active = date.date === sel;
                                            b.className =
                                                `flex-shrink-0 px-4 py-2 rounded-lg border text-sm transition ${active?'bg-red-600 border-red-600 text-white':'bg-gray-800 border-gray-700 text-gray-300 hover:border-red-600'}`;
                                            b.innerHTML = btnHtml(date);
                                            b.onclick = () => {
                                                sel = date.date;
                                                renderDates(list);
                                                stc.style.display = '';
                                                loadShowtimes(sel);
                                            };
                                            dp.appendChild(b);
                                        });
                                    }
                                    async function loadDates() {
                                        // First render fallback immediately for UX
                                        renderDates(mkFallback());
                                        try {
                                            const r = await fetch(`/api/booking/movie/${mid}/dates`);
                                            const j = await r.json().catch(() => ({
                                                success: false
                                            }));
                                            if (j && j.success && Array.isArray(j.data) && j.data.length) {
                                                renderDates(j.data);
                                            }
                                        } catch (e) {
                                            /* keep fallback */
                                        }
                                    }
                                    async function loadShowtimes(dateStr) {
                                        try {
                                            stc.innerHTML =
                                                '<div class="col-span-full text-center py-8"><p class="text-gray-400">Đang tải suất chiếu...</p></div>';
                                            const r = await fetch(
                                                `/api/booking/movie/${mid}/showtimes?date=${encodeURIComponent(dateStr)}`);
                                            const j = await r.json().catch(() => ({
                                                success: false,
                                                data: []
                                            }));
                                            if (!j.success || !Array.isArray(j.data) || j.data.length === 0) {
                                                stc.innerHTML =
                                                    '<div class="col-span-full text-center py-8"><p class="text-gray-400">Không có suất chiếu</p></div>';
                                                return;
                                            }
                                            stc.innerHTML = '';
                                            j.data.forEach(st => {
                                                const btn = document.createElement('button');
                                                btn.type = 'button';
                                                btn.className =
                                                    'border border-gray-700 rounded-lg p-3 text-center hover:border-red-600 hover:bg-red-600/20 transition';
                                                btn.innerHTML =
                                                    `<div class="font-semibold">${st.time}</div><div class="text-xs text-gray-400">${st.room_name||''}</div>`;
                                                btn.onclick = () => {
                                                    window.location.href = `/shows/${st.id}/seats`;
                                                };
                                                stc.appendChild(btn);
                                            });
                                        } catch (e) {
                                            stc.innerHTML =
                                                '<div class="col-span-full text-center py-8"><p class="text-gray-400">Không thể tải suất chiếu</p></div>';
                                        }
                                    }
                                    loadDates();
                                } catch (_) {}
                            })();
                        </script>

                        <!-- Right Column - Payment Summary -->
                        <div id="legacy-summary" class="space-y-6" style="display:none;">
                            <!-- Summary -->
                            <div class="bg-gray-900 rounded-lg p-6 sticky top-6">
                                <h3 class="text-lg font-semibold mb-4">Thông tin đặt vé</h3>

                                <div class="space-y-4">
                                    <!-- Movie Info -->
                                    <div>
                                        <p class="text-sm text-gray-400">Phim</p>
                                        <p class="font-medium">{{ $movie->ten_phim ?? 'Movie Title' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Thời lượng: {{ $movie->thoi_luong ?? '120' }}
                                            phút</p>
                                    </div>

                                    <!-- Showtime Info -->
                                    <div>
                                        <p class="text-sm text-gray-400">Suất chiếu</p>
                                        <p class="font-medium" id="summary-showtime">Chọn suất chiếu</p>
                                        <p class="text-xs text-gray-500 mt-1" id="summary-date">Chọn ngày chiếu</p>
                                        <p class="text-xs text-gray-500" id="summary-time">Chọn giờ chiếu</p>
                                    </div>

                                    <!-- Seats Info -->
                                    <div>
                                        <p class="text-sm text-gray-400">Ghế</p>
                                        <p class="font-medium" id="summary-seats">Chưa chọn ghế</p>
                                        <p class="text-xs text-gray-500 mt-1" id="summary-seat-types">Chưa chọn ghế</p>
                                    </div>

                                    <!-- Hold Timer Notification -->
                                    <div id="hold-notification" class="hidden border-t border-gray-800 pt-4">
                                        <div class="bg-yellow-600/20 border border-yellow-600/50 rounded-lg p-3">
                                            <div class="flex items-center gap-2 mb-2">
                                                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <p class="text-sm font-medium text-yellow-400">Ghế đã được giữ chỗ</p>
                                            </div>
                                            <p class="text-xs text-yellow-300" id="hold-timer-text">Thời gian còn lại: 5:00
                                            </p>
                                            <p class="text-xs text-yellow-400/80 mt-1">Vui lòng hoàn tất thanh toán trong
                                                thời gian này</p>
                                        </div>
                                    </div>

                                    <!-- Price Breakdown -->
                                    <div class="border-t border-gray-800 pt-4 space-y-2" id="price-breakdown">
                                        <div class="flex justify-between text-sm text-gray-500">
                                            <span>Chưa chọn ghế</span>
                                            <span>0đ</span>
                                        </div>
                                    </div>

                                    <!-- Combo Selection -->
                                    <div class="border-t border-gray-800 pt-4">
                                        <label class="block text-sm font-medium text-gray-400 mb-2">Chọn Combo (tuỳ
                                            chọn)</label>
                                        <div class="space-y-2">
                                            @forelse($combos as $c)
                                                <label
                                                    class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                                    <input type="radio" name="combo" value="{{ $c->id }}"
                                                        data-price="{{ (int) $c->gia }}" class="mr-3 text-red-600">
                                                    <div class="flex-1">
                                                        <div class="text-white font-medium">{{ $c->ten }}</div>
                                                        <div class="text-gray-400 text-sm">
                                                            {{ number_format((int) $c->gia, 0) }}đ</div>
                                                    </div>
                                                </label>
                                            @empty
                                                <div class="text-sm text-gray-500">Hiện chưa có combo khả dụng</div>
                                            @endforelse
                                            <label
                                                class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                                <input type="radio" name="combo" value=""
                                                    class="mr-3 text-red-600">
                                                <div class="flex-1 text-gray-400 text-sm">Không chọn combo</div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Promotion Selection -->
                                    <div class="border-t border-gray-800 pt-4">
                                        <label class="block text-sm font-medium text-gray-400 mb-2">Khuyến mãi</label>
                                        <select id="promotion"
                                            class="w-full bg-gray-800 text-white rounded-lg p-2 border border-gray-700">
                                            <option value="">Không áp dụng</option>
                                            @foreach ($khuyenmais as $km)
                                                @php $min = $km->dieu_kien ? (int)preg_replace('/\D+/', '', $km->dieu_kien) : 0; @endphp
                                                <option value="{{ $km->id }}" data-type="{{ $km->loai_giam }}"
                                                    data-value="{{ (float) $km->gia_tri_giam }}"
                                                    data-min="{{ $min }}"
                                                    data-max="{{ (float) $km->gia_tri_giam_toi_da ?? 0 }}">
                                                    {{ $km->ma_km }} - {{ $km->mo_ta }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div id="promotion-info" class="text-xs text-gray-400 mt-2 min-h-5"></div>
                                    </div>

                                    <!-- Payment Method Selection -->
                                    <div class="border-t border-gray-800 pt-4">
                                        <label class="block text-sm font-medium text-gray-400 mb-2">Phương thức thanh
                                            toán</label>
                                        <div class="space-y-2">
                                            <label
                                                class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                                <input type="radio" name="payment_method" value="online" checked
                                                    class="mr-3 text-red-600">
                                                <div class="flex-1">
                                                    <div class="text-white font-medium">Thanh toán online</div>
                                                    <div class="text-gray-400 text-sm">Chuyển khoản ngân hàng</div>

                                                </div>

                                                @if (false)
                                                    <!-- Date & Showtime Selection -->
                                                    <div class="bg-gray-900 rounded-lg p-6">
                                                        <h3 class="text-lg font-semibold mb-4">Chọn ngày</h3>
                                                        <div id="datePicker" class="flex gap-3 overflow-x-auto pb-2">
                                                        </div>

                                                        <h3 class="text-lg font-semibold mt-6 mb-4">Chọn suất chiếu</h3>
                                                        <div id="showtimesContainer"
                                                            class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                            <div class="col-span-full text-center py-8">
                                                                <p class="text-gray-400">Vui lòng chọn ngày để xem suất
                                                                    chiếu</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (false)
                                                    <!-- Screen -->
                                                    <div class="text-center py-4">
                                                        <div
                                                            class="bg-gradient-to-r from-gray-600 to-gray-800 rounded-lg py-4 px-8 mx-auto max-w-2xl relative">
                                                            <div class="text-white font-semibold text-lg">🎬 MÀN HÌNH</div>
                                                            <div
                                                                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent rounded-lg">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Seat Map -->
                                                    <div class="bg-gray-900 rounded-lg p-6">
                                                        @php
                                                            // Get room info from controller (using first showtime as default)
                                                            $defaultRoomInfo = null;
                                                            $defaultSeatsData = [];
                                                            if (isset($showtimes) && count($showtimes) > 0) {
                                                                $firstShowtime = $showtimes[0]['id'] ?? null;
                                                                if ($firstShowtime) {
                                                                    $suatChieu = App\Models\SuatChieu::find(
                                                                        $firstShowtime,
                                                                    );
                                                                    if ($suatChieu) {
                                                                        $defaultRoomInfo = $suatChieu->phongChieu;
                                                                        $defaultSeatsData = App\Models\Ghe::where(
                                                                            'id_phong',
                                                                            $suatChieu->id_phong,
                                                                        )
                                                                            ->with('loaiGhe')
                                                                            ->get()
                                                                            ->keyBy('so_ghe');
                                                                    }
                                                                }
                                                            }

                                                            // Use room info from database
                                                            $roomRows =
                                                                isset($roomInfo) && $roomInfo
                                                                    ? (int) ($roomInfo->so_hang ?? 10)
                                                                    : 10;
                                                            $roomCols =
                                                                isset($roomInfo) && $roomInfo
                                                                    ? (int) ($roomInfo->so_cot ?? 15)
                                                                    : 15;

                                                            // Generate row labels based on room rows
                                                            $rows = [];
                                                            for ($i = 1; $i <= $roomRows; $i++) {
                                                                $rows[] = chr(64 + $i); // A, B, C, etc.
                                                            }

                                                            $cols = range(1, $roomCols);
                                                        @endphp
                                                        <div id="seat-map" class="flex flex-col items-center gap-2">
                                                            @foreach ($rows as $r)
                                                                <div class="flex items-center gap-2">
                                                                    <div
                                                                        class="text-sm text-gray-400 font-medium w-6 text-center">
                                                                        {{ $r }}
                                                                    </div>
                                                                    <div class="flex gap-1">
                                                                        @foreach ($cols as $c)
                                                                            @php
                                                                                $code = $r . $c;
                                                                                $seat =
                                                                                    $defaultSeatsData[$code] ?? null;

                                                                                if ($seat) {
                                                                                    $isAvailable =
                                                                                        (int) ($seat->trang_thai ??
                                                                                            0) === 1;
                                                                                    $typeText = strtolower(
                                                                                        $seat->loaiGhe->ten_loai ??
                                                                                            'thuong',
                                                                                    );

                                                                                    if ($isAvailable) {
                                                                                        if (
                                                                                            str_contains(
                                                                                                $typeText,
                                                                                                'vip',
                                                                                            )
                                                                                        ) {
                                                                                            $btnClass =
                                                                                                'bg-yellow-600 hover:bg-yellow-700';
                                                                                            $price = 120000;
                                                                                        } elseif (
                                                                                            str_contains(
                                                                                                $typeText,
                                                                                                'đôi',
                                                                                            ) ||
                                                                                            str_contains(
                                                                                                $typeText,
                                                                                                'doi',
                                                                                            ) ||
                                                                                            str_contains(
                                                                                                $typeText,
                                                                                                'couple',
                                                                                            )
                                                                                        ) {
                                                                                            $btnClass =
                                                                                                'bg-pink-600 hover:bg-pink-700 w-12 h-8';
                                                                                            $price = 200000;
                                                                                        } else {
                                                                                            $btnClass =
                                                                                                'bg-gray-700 hover:bg-gray-600';
                                                                                            $price = 80000;
                                                                                        }
                                                                                    } else {
                                                                                        $btnClass =
                                                                                            'bg-gray-500 cursor-not-allowed';
                                                                                        $price = 0;
                                                                                    }
                                                                                } else {
                                                                                    $btnClass =
                                                                                        'bg-gray-800 hover:bg-gray-700';
                                                                                    $price = 80000;
                                                                                }
                                                                            @endphp

                                                                            <button type="button"
                                                                                class="seat w-8 h-8 rounded text-xs font-medium transition-all duration-200 {{ $btnClass }} {{ !$seat || !$isAvailable ? 'cursor-not-allowed' : '' }}"
                                                                                data-seat="{{ $code }}"
                                                                                data-price="{{ $price }}"
                                                                                data-type="{{ $seat->loaiGhe->ten_loai ?? 'Thường' }}"
                                                                                {{ !$seat || !$isAvailable ? 'disabled' : '' }}>
                                                                                {{ $c }}
                                                                            </button>
                                                                        @endforeach
                                                                    </div>
                                                                    <div
                                                                        class="text-sm text-gray-400 font-medium w-6 text-center">
                                                                        {{ $r }}
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <!-- Legend -->
                                                        <div class="mt-8 flex flex-wrap justify-center gap-6 text-sm">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 bg-gray-700 rounded"></div>
                                                                <span class="text-gray-400">Ghế thường (80.000đ)</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 bg-yellow-600 rounded"></div>
                                                                <span class="text-gray-400">Ghế VIP (120.000đ)</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-8 h-6 bg-pink-600 rounded"></div>
                                                                <span class="text-gray-400">Ghế đôi (200.000đ)</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 bg-red-600 rounded"></div>
                                                                <span class="text-gray-400">Đã đặt</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 bg-green-600 rounded"></div>
                                                                <span class="text-gray-400">Đang chọn</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Right Column - Payment Summary -->
                                                @if (false)
                                                    <div class="space-y-6">
                                                        <!-- Summary -->
                                                        <div class="bg-gray-900 rounded-lg p-6 sticky top-6">
                                                            <h3 class="text-lg font-semibold mb-4">Thông tin đặt vé</h3>

                                                            <div class="space-y-4">
                                                                <!-- Movie Info -->
                                                                <div>
                                                                    <p class="text-sm text-gray-400">Phim</p>
                                                                    <p class="font-medium">
                                                                        {{ $movie->ten_phim ?? 'Movie Title' }}</p>
                                                                    <p class="text-xs text-gray-500 mt-1">Thời lượng:
                                                                        {{ $movie->thoi_luong ?? '120' }} phút
                                                                    </p>
                                                                </div>

                                                                <!-- Showtime Info -->
                                                                <div>
                                                                    <p class="text-sm text-gray-400">Suất chiếu</p>
                                                                    <p class="font-medium" id="summary-showtime">Chọn suất
                                                                        chiếu</p>
                                                                    <p class="text-xs text-gray-500 mt-1"
                                                                        id="summary-date">Chọn ngày chiếu</p>
                                                                    <p class="text-xs text-gray-500" id="summary-time">
                                                                        Chọn giờ chiếu</p>
                                                                </div>

                                                                <!-- Seats Info -->
                                                                <div>
                                                                    <p class="text-sm text-gray-400">Ghế</p>
                                                                    <p class="font-medium" id="summary-seats">Chưa chọn
                                                                        ghế</p>
                                                                    <p class="text-xs text-gray-500 mt-1"
                                                                        id="summary-seat-types">Chưa chọn ghế</p>
                                                                </div>

                                                                <!-- Hold Timer Notification -->
                                                                <div id="hold-notification"
                                                                    class="hidden border-t border-gray-800 pt-4">
                                                                    <div
                                                                        class="bg-yellow-600/20 border border-yellow-600/50 rounded-lg p-3">
                                                                        <div class="flex items-center gap-2 mb-2">
                                                                            <svg class="w-5 h-5 text-yellow-400"
                                                                                fill="none" stroke="currentColor"
                                                                                viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                                </path>
                                                                            </svg>
                                                                            <p class="text-sm font-medium text-yellow-400">
                                                                                Ghế đã được giữ chỗ</p>
                                                                        </div>
                                                                        <p class="text-xs text-yellow-300"
                                                                            id="hold-timer-text">Thời gian còn lại: 5:00
                                                                        </p>
                                                                        <p class="text-xs text-yellow-400/80 mt-1">Vui lòng
                                                                            hoàn tất thanh toán trong thời gian
                                                                            này</p>
                                                                    </div>
                                                                </div>

                                                                <!-- Price Breakdown -->
                                                                <div class="border-t border-gray-800 pt-4 space-y-2"
                                                                    id="price-breakdown">
                                                                    <div
                                                                        class="flex justify-between text-sm text-gray-500">
                                                                        <span>Chưa chọn ghế</span>
                                                                        <span>0đ</span>
                                                                    </div>
                                                                </div>

                                                                <!-- Combo Selection -->
                                                                <div class="border-t border-gray-800 pt-4">
                                                                    <label
                                                                        class="block text-sm font-medium text-gray-400 mb-2">Chọn
                                                                        Combo (tuỳ chọn)</label>
                                                                    <div class="space-y-2">
                                                                        @forelse($combos as $c)
                                                                            <label
                                                                                class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                                                                <input type="radio" name="combo"
                                                                                    value="{{ $c->id }}"
                                                                                    data-price="{{ (int) $c->gia }}"
                                                                                    class="mr-3 text-red-600">
                                                                                <div class="flex-1">
                                                                                    <div class="text-white font-medium">
                                                                                        {{ $c->ten }}</div>
                                                                                    <div class="text-gray-400 text-sm">
                                                                                        {{ number_format((int) $c->gia, 0) }}đ
                                                                                    </div>
                                                                                </div>
                                                                            </label>
                                                                        @empty
                                                                            <div class="text-sm text-gray-500">Hiện chưa có
                                                                                combo khả dụng</div>
                                                                        @endforelse
                                                                        <label
                                                                            class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                                                            <input type="radio" name="combo"
                                                                                value="" class="mr-3 text-red-600">
                                                                            <div class="flex-1 text-gray-400 text-sm">Không
                                                                                chọn combo</div>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <!-- Promotion Selection -->
                                                                <div class="border-t border-gray-800 pt-4">
                                                                    <label
                                                                        class="block text-sm font-medium text-gray-400 mb-2">Khuyến
                                                                        mãi</label>
                                                                    <select id="promotion"
                                                                        class="w-full bg-gray-800 text-white rounded-lg p-2 border border-gray-700">
                                                                        <option value="">Không áp dụng</option>
                                                                        @foreach ($khuyenmais as $km)
                                                                            @php $min = $km->dieu_kien ? (int)preg_replace('/\D+/', '', $km->dieu_kien) : 0; @endphp
                                                                            <option value="{{ $km->id }}"
                                                                                data-type="{{ $km->loai_giam }}"
                                                                                data-value="{{ (float) $km->gia_tri_giam }}"
                                                                                data-min="{{ $min }}">
                                                                                {{ $km->ma_km }} - {{ $km->mo_ta }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <!-- Payment Method Selection -->
                                                                <div class="border-t border-gray-800 pt-4">
                                                                    <label
                                                                        class="block text-sm font-medium text-gray-400 mb-2">Phương
                                                                        thức thanh toán</label>
                                                                    <div class="space-y-2">
                                                                        <label
                                                                            class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                                                            <input type="radio" name="payment_method"
                                                                                value="online" checked
                                                                                class="mr-3 text-red-600">
                                                                            <div class="flex-1">
                                                                                <div class="text-white font-medium">Thanh
                                                                                    toán online</div>
                                                                                <div class="text-gray-400 text-sm">Chuyển
                                                                                    khoản ngân hàng</div>
                                                                            </div>
                                                                            <svg class="w-6 h-6 text-green-400"
                                                                                fill="none" stroke="currentColor"
                                                                                viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z">
                                                                                </path>
                                                                            </svg>
                                                                        </label>
                                                                        <label
                                                                            class="flex items-center p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700 transition">
                                                                            <input type="radio" name="payment_method"
                                                                                value="offline" class="mr-3 text-red-600">
                                                                            <div class="flex-1">
                                                                                <div class="text-white font-medium">Thanh
                                                                                    toán tại quầy</div>
                                                                                <div class="text-gray-400 text-sm">Thanh
                                                                                    toán khi đến rạp</div>
                                                                            </div>
                                                                            <svg class="w-6 h-6 text-blue-400"
                                                                                fill="none" stroke="currentColor"
                                                                                viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                                                </path>
                                                                            </svg>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <!-- Total -->
                                                                <div class="border-t border-gray-800 pt-4">
                                                                    <div class="flex justify-between">
                                                                        <span class="font-semibold">Tổng cộng</span>
                                                                        <span class="text-xl font-bold text-red-500"
                                                                            id="total-price">0đ</span>
                                                                    </div>
                                                                </div>

                                                                <!-- Action Buttons -->
                                                                <div class="space-y-3 pt-4">
                                                                    <button id="pay"
                                                                        class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-medium transition disabled:bg-gray-700 disabled:cursor-not-allowed"
                                                                        disabled>
                                                                        Thanh toán
                                                                    </button>
                                                                    <p class="text-xs text-gray-500 text-center">
                                                                        Bằng cách nhấp vào nút thanh toán, bạn đồng ý với
                                                                        điều khoản sử dụng của chúng tôi
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                        </div>
                                    </div>
                                </div>
                            </div>


                        @endsection

                        @section('scripts')
                            <script>
                                // Global variables
                                let currentBookingId = null;
                                let selectedShowtime = {{ $showtime ? $showtime->id : 'null' }};
                                let selectedCombo = null;
                                let selectedPromotion = null;
                                let holdExpiresAt = null;
                                let holdTimer = null;
                                let refreshInterval = null;
                                const selected = new Set();

                                // Helpers
                                const toNumber = (v) => {
                                    if (v === undefined || v === null) return 0;
                                    return parseInt(String(v).replace(/[^0-9.-]/g, '')) || 0;
                                };

                                const format = (n) => n.toLocaleString('vi-VN') + 'đ';

                                // --- LOGIC XỬ LÝ GHẾ ---

                                // Hàm tính giá
                                const priceFor = (seatButton) => {
                                    const type = (seatButton.dataset.seatType || '').toLowerCase();
                                    if (type.includes('vip')) return 120000;
                                    if (type.includes('đôi') || type.includes('doi') || type.includes('couple')) return 200000;
                                    return 80000;
                                };

                                // Hàm cập nhật giao diện nút ghế
                                function updateSeatVisual(btn, status, type) {
                                    // 1. Reset classes (Xóa sạch các class màu cũ)
                                    btn.className = 'seat-btn-enhanced relative group'; // Reset về class gốc

                                    // 2. Apply logic
                                    if (status === 'booked' || status === 'sold') {
                                        // ĐÃ BÁN -> MÀU ĐỎ
                                        btn.classList.add('seat-sold');
                                        btn.disabled = true;
                                    } else if (status === 'locked_by_other') {
                                        // ĐANG ĐƯỢC NGƯỜI KHÁC GIỮ -> MÀU XÁM
                                        btn.classList.add('seat-locked');
                                        btn.disabled = true;
                                    } else if (selected.has(btn)) {
                                        // ĐANG ĐƯỢC MÌNH CHỌN -> MÀU XANH
                                        btn.classList.add('seat-selected');
                                        btn.disabled = false;
                                    } else {
                                        // CÒN TRỐNG -> MÀU THEO LOẠI GHẾ
                                        btn.disabled = false;
                                        const lowerType = (type || '').toLowerCase();

                                        if (lowerType.includes('vip')) {
                                            btn.classList.add('seat-vip');
                                        } else if (lowerType.includes('đôi') || lowerType.includes('doi') || lowerType.includes('couple')) {
                                            btn.classList.add('seat-couple');
                                        } else {
                                            btn.classList.add('seat-available');
                                        }
                                    }
                                }

                                // Hàm xử lý khi click chọn ghế
                                async function handleSeatClick(btn) {
                                    if (btn.disabled) return;

                                    if (selected.has(btn)) {
                                        selected.delete(btn);
                                        // Logic bỏ chọn ghế đôi
                                        const type = (btn.dataset.seatType || '').toLowerCase();
                                        if (type.includes('đôi') || type.includes('doi') || type.includes('couple')) {
                                            const code = btn.dataset.seat; // SỬA: dùng dataset.seat
                                            const row = code.charAt(0);
                                            const num = parseInt(code.substring(1));
                                            const pairNum = (num % 2 === 1) ? num + 1 : num - 1;
                                            const pairCode = row + pairNum;
                                            const pairBtn = document.querySelector(`button[data-seat="${pairCode}"]`); // SỬA: data-seat
                                            if (pairBtn && selected.has(pairBtn)) {
                                                selected.delete(pairBtn);
                                                updateSeatVisual(pairBtn, 'available', pairBtn.dataset.seatType);
                                            }
                                        }
                                    } else {
                                        selected.add(btn);
                                        // Logic chọn ghế đôi
                                        const type = (btn.dataset.seatType || '').toLowerCase();
                                        if (type.includes('đôi') || type.includes('doi') || type.includes('couple')) {
                                            const code = btn.dataset.seat; // SỬA: dùng dataset.seat
                                            const row = code.charAt(0);
                                            const num = parseInt(code.substring(1));
                                            const pairNum = (num % 2 === 1) ? num + 1 : num - 1;
                                            const pairCode = row + pairNum;
                                            const pairBtn = document.querySelector(`button[data-seat="${pairCode}"]`); // SỬA: data-seat

                                            if (pairBtn && !pairBtn.disabled) {
                                                selected.add(pairBtn);
                                                updateSeatVisual(pairBtn, 'available', pairBtn.dataset.seatType);
                                            } else {
                                                alert('Ghế cặp không khả dụng, vui lòng chọn cặp khác!');
                                                selected.delete(btn);
                                                return;
                                            }
                                        }
                                    }

                                    updateUI(); // Cập nhật giao diện ngay lập tức
                                    await holdSelectedSeats(); // Gọi API giữ ghế
                                }

                                // Hàm tải trạng thái ghế từ Server
                                async function loadSeatStatus() {
                                    if (!selectedShowtime) return;

                                    try {
                                        const response = await fetch(`/showtime-seats/${selectedShowtime}`);
                                        const data = await response.json();

                                        if (data.seats) {
                                            // Sử dụng đúng class trong HTML của bạn để query
                                            const allButtons = document.querySelectorAll('.seat-btn-enhanced');

                                            allButtons.forEach(btn => {
                                                // QUAN TRỌNG: HTML dùng data-seat, JS phải dùng dataset.seat
                                                const code = btn.dataset.seat;
                                                const seatInfo = data.seats[code];

                                                if (seatInfo) {
                                                    let status = 'available';

                                                    // Logic xác định trạng thái
                                                    if (!seatInfo.available || seatInfo.status === 'sold' || seatInfo.status ===
                                                        'booked') {
                                                        status = 'sold';
                                                    } else if (seatInfo.status === 'hold') {
                                                        status = 'locked_by_other';
                                                    }

                                                    // Cập nhật lại giá và loại ghế vào DOM để dùng sau này
                                                    btn.dataset.seatType = seatInfo.type;
                                                    // Ghi đè giá cứng nếu cần
                                                    let fixedPrice = 80000;
                                                    const t = seatInfo.type.toLowerCase();
                                                    if (t.includes('vip')) fixedPrice = 120000;
                                                    if (t.includes('đôi') || t.includes('couple')) fixedPrice = 200000;
                                                    btn.dataset.price = fixedPrice;

                                                    // Nếu ghế đang được chọn bởi user hiện tại thì không đổi trạng thái visual
                                                    if (!selected.has(btn)) {
                                                        updateSeatVisual(btn, status, seatInfo.type);
                                                    }
                                                }
                                            });
                                        }
                                    } catch (e) {
                                        console.error('Lỗi tải trạng thái ghế:', e);
                                    }
                                }

                                // Hàm giữ ghế
                                async function holdSelectedSeats() {
                                    if (selected.size === 0) return;

                                    // SỬA: Dùng dataset.seatId thay vì seatId (HTML bạn dùng data-seat-id)
                                    const seatIds = Array.from(selected).map(btn => btn.dataset.seatId);
                                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                                    try {
                                        const res = await fetch(`/api/showtimes/${selectedShowtime}/select-seats`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': token
                                            },
                                            // SỬA: Dùng dataset.seat
                                            body: JSON.stringify({
                                                seats: Array.from(selected).map(b => b.dataset.seat)
                                            })
                                        });

                                        const data = await res.json();
                                        if (data.success) {
                                            currentBookingId = data.booking_id;
                                            holdExpiresAt = new Date(data.hold_expires_at);
                                            startTimer();
                                        } else {
                                            // Nếu giữ thất bại (do người khác vừa mua xong), báo lỗi và bỏ chọn
                                            alert(data.message || 'Không thể giữ ghế');
                                            selected.clear();
                                            updateUI();
                                            loadSeatStatus(); // Tải lại trạng thái mới nhất
                                        }
                                    } catch (e) {
                                        console.error(e);
                                    }
                                }

                                // Cập nhật UI tổng tiền & Sidebar
                                function updateUI() {
                                    const seatButtons = document.querySelectorAll('.seat-btn-enhanced');

                                    // Update visual cho tất cả ghế (để đảm bảo màu sắc đúng)
                                    seatButtons.forEach(btn => {
                                        // Chỉ update visual cho những ghế KHÔNG bị disable (không phải ghế đã bán)
                                        if (!btn.disabled || selected.has(btn)) {
                                            if (selected.has(btn)) {
                                                updateSeatVisual(btn, 'selected', btn.dataset.seatType);
                                            } else {
                                                updateSeatVisual(btn, 'available', btn.dataset.seatType);
                                            }
                                        }
                                    });

                                    // Tính toán tiền
                                    const selectedArr = Array.from(selected);
                                    const seatTotal = selectedArr.reduce((sum, btn) => sum + priceFor(btn), 0);

                                    let comboTotal = 0;
                                    const comboRadio = document.querySelector('input[name="combo"]:checked');
                                    if (comboRadio && comboRadio.value) {
                                        selectedCombo = {
                                            id: comboRadio.value,
                                            price: toNumber(comboRadio.dataset.price)
                                        };
                                        comboTotal = selectedCombo.price;
                                    } else {
                                        selectedCombo = null;
                                    }

                                    let discount = 0;
                                    const promoEl = document.getElementById('promotion');
                                    if (promoEl && promoEl.value) {
                                        const opt = promoEl.selectedOptions[0];
                                        const type = opt.dataset.type;
                                        const val = toNumber(opt.dataset.value);
                                        const max = toNumber(opt.dataset.max || 0);
                                        const subtotal = seatTotal + comboTotal;
                                        const min = toNumber(opt.dataset.min || 0);

                                        if (subtotal >= min) {
                                            if (type === 'phantram') {
                                                discount = Math.round(subtotal * (val / 100));
                                                if (max > 0 && discount > max) discount = max;
                                            } else {
                                                discount = val >= 1000 ? val : val * 1000;
                                            }
                                            if (discount > subtotal) discount = subtotal;
                                        }
                                    }

                                    // Render Text
                                    const summarySeats = document.getElementById('summary-seats');
                                    // SỬA: dùng dataset.seat
                                    const seatCodes = selectedArr.map(b => b.dataset.seat).join(', ');
                                    if (summarySeats) summarySeats.textContent = seatCodes || 'Chưa chọn ghế';

                                    const totalPriceEl = document.getElementById('total-price');
                                    if (totalPriceEl) totalPriceEl.textContent = format(Math.max(0, seatTotal + comboTotal - discount));

                                    const payBtn = document.getElementById('pay');
                                    if (payBtn) payBtn.disabled = selected.size === 0;

                                    // Update Breakdown logic (như cũ)...
                                    const breakdown = document.getElementById('price-breakdown');
                                    if (breakdown) {
                                        let html = '';
                                        if (seatTotal > 0) html +=
                                            `<div class="flex justify-between text-sm"><span class="text-gray-400">Ghế</span><span>${format(seatTotal)}</span></div>`;
                                        if (comboTotal > 0) html +=
                                            `<div class="flex justify-between text-sm"><span class="text-gray-400">Combo</span><span>${format(comboTotal)}</span></div>`;
                                        if (discount > 0) html +=
                                            `<div class="flex justify-between text-sm"><span class="text-green-500">Khuyến mãi</span><span class="text-green-500">-${format(discount)}</span></div>`;
                                        if (html === '') html =
                                            `<div class="flex justify-between text-sm text-gray-500"><span>Chưa chọn ghế</span><span>0đ</span></div>`;
                                        breakdown.innerHTML = html;
                                    }
                                }

                                function startTimer() {
                                    if (holdTimer) clearInterval(holdTimer);
                                    const timerEl = document.getElementById('timer');
                                    const timerDisplay = document.getElementById('timer-display');

                                    holdTimer = setInterval(() => {
                                        if (!holdExpiresAt) return;
                                        const now = new Date();
                                        const diff = Math.ceil((holdExpiresAt - now) / 1000);

                                        if (diff <= 0) {
                                            clearInterval(holdTimer);
                                            alert('Hết thời gian giữ ghế!');
                                            location.reload();
                                            return;
                                        }
                                        const m = Math.floor(diff / 60).toString().padStart(2, '0');
                                        const s = (diff % 60).toString().padStart(2, '0');
                                        const str = `${m}:${s}`;
                                        if (timerEl) timerEl.innerText = str;
                                        if (timerDisplay) timerDisplay.innerText = str;
                                    }, 1000);
                                }

                                // --- INIT EVENTS ---
                                document.addEventListener('DOMContentLoaded', () => {
                                    // 1. Gắn sự kiện click
                                    document.querySelectorAll('.seat-btn-enhanced').forEach(btn => {
                                        btn.addEventListener('click', (e) => {
                                            e.preventDefault();
                                            handleSeatClick(btn);
                                        });
                                    });

                                    // 2. Combo & Promo events
                                    document.querySelectorAll('input[name="combo"]').forEach(r => r.addEventListener('change', updateUI));
                                    const promo = document.getElementById('promotion');
                                    if (promo) promo.addEventListener('change', updateUI);

                                    // 3. Thanh toán event
                                    const payBtn = document.getElementById('pay');
                                    if (payBtn) {
                                        payBtn.addEventListener('click', async () => {
                                            if (selected.size === 0) return alert('Vui lòng chọn ghế');

                                            const method = document.querySelector('input[name="payment_method"]:checked')
                                                ?.value || 'offline';
                                            // SỬA: dùng dataset.seat
                                            const seats = Array.from(selected).map(b => b.dataset.seat);
                                            const comboRadio = document.querySelector('input[name="combo"]:checked');
                                            const combo = (comboRadio && comboRadio.value) ? {
                                                id: comboRadio.value
                                            } : null;
                                            const promoVal = document.getElementById('promotion')?.value || null;

                                            payBtn.disabled = true;
                                            payBtn.innerText = 'Đang xử lý...';

                                            try {
                                                const res = await fetch('/booking/store', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector(
                                                            'meta[name="csrf-token"]').content
                                                    },
                                                    body: JSON.stringify({
                                                        showtime: selectedShowtime,
                                                        seats: seats,
                                                        payment_method: method,
                                                        combo: combo,
                                                        promotion: promoVal,
                                                        booking_id: currentBookingId
                                                    })
                                                });

                                                const data = await res.json();
                                                if (data.success) {
                                                    if (data.is_redirect) {
                                                        // Lưu booking_id vào sessionStorage để có thể hủy nếu người dùng back lại
                                                        if (data.booking_id) {
                                                            sessionStorage.setItem('pending_booking_id', data.booking_id);
                                                        }
                                                        window.location.href = data.payment_url;
                                                    } else {
                                                        alert(data.message);
                                                        window.location.href = '/user/bookings';
                                                    }
                                                } else {
                                                    alert(data.message || 'Lỗi đặt vé');
                                                    payBtn.disabled = false;
                                                    payBtn.innerText = 'Thanh toán';
                                                    loadSeatStatus(); // Tải lại ghế để xem ghế nào bị trùng
                                                }
                                            } catch (e) {
                                                console.error(e);
                                                alert('Lỗi kết nối');
                                                payBtn.disabled = false;
                                                payBtn.innerText = 'Thanh toán';
                                            }
                                        });
                                    }

                                    // 4. CHẠY HÀM TẢI TRẠNG THÁI GHẾ NGAY LẬP TỨC
                                    loadSeatStatus();

                                    // 5. Refresh mỗi 5 giây
                                    refreshInterval = setInterval(loadSeatStatus, 5000);

                                    // 6. Tự động hủy booking chưa thanh toán khi người dùng quay lại
                                    const pendingBookingId = sessionStorage.getItem('pending_booking_id');
                                    if (pendingBookingId) {
                                        // Kiểm tra xem booking đã được thanh toán chưa
                                        fetch(`/booking/${pendingBookingId}/cancel`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                            }
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                console.log('Đã hủy booking chưa thanh toán:', pendingBookingId);
                                            }
                                            // Xóa booking_id khỏi sessionStorage
                                            sessionStorage.removeItem('pending_booking_id');
                                        })
                                        .catch(err => {
                                            console.error('Lỗi khi hủy booking:', err);
                                            sessionStorage.removeItem('pending_booking_id');
                                        });
                                    }
                                });
                            </script>
                        @endsection
