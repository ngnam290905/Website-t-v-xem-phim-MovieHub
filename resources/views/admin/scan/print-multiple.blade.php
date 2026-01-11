@extends('layouts.admin')

@section('content')
<style>
    :root {
        --ticket-border: #0f172a;
        --muted: #475569;
        --bg: #ffffff;
        --surface: #ffffff;
        --accent: #0ea5e9;
    }

    .print-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 16px;
    }

    body.print-multiple-mode #sidebar,
    body.print-multiple-mode #mobile-menu-button,
    body.print-multiple-mode #mobile-overlay,
    body.print-multiple-mode header {
        display: none !important;
    }

    body.print-multiple-mode .flex.h-screen > .flex-1 {
        width: 100% !important;
    }

    body.print-multiple-mode main > div {
        max-width: none !important;
        padding: 0 !important;
    }

    .manage-area {
        display: block;
    }

    .print-only {
        display: none;
    }

    .print-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 12px 0 16px;
    }

    .action-bar {
        position: sticky;
        top: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        border: 1px solid rgba(15, 23, 42, 0.14);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        box-shadow: 0 12px 28px rgba(2, 6, 23, 0.10);
        margin: 12px 0 16px;
    }

    .action-left {
        display: flex;
        align-items: baseline;
        gap: 10px;
        min-width: 0;
    }

    .action-title {
        font-weight: 900;
        font-size: 14px;
        color: #0b1220;
        margin: 0;
    }

    .action-sub {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
        white-space: nowrap;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-print {
        border: 0;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 900;
        font-size: 13px;
        color: white;
        background: #F53003;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-print:hover { filter: brightness(1.05); }

    .btn-mark {
        border: 0;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 900;
        font-size: 13px;
        color: white;
        background: #16a34a;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-mark:hover { filter: brightness(1.05); }

    .btn-secondary {
        border: 1px solid rgba(15, 23, 42, 0.18);
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 900;
        font-size: 13px;
        color: #0b1220;
        background: rgba(15, 23, 42, 0.04);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-secondary:hover { background: rgba(15, 23, 42, 0.06); }

    .tickets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 14px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .tickets-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
    }

    @media (max-width: 768px) {
        .tickets-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 520px) {
        .tickets-grid { grid-template-columns: 1fr; }
        .print-actions { flex-wrap: wrap; }
    }

    .booking-card {
        background: var(--surface);
        border: 1px solid rgba(15, 23, 42, 0.16);
        border-radius: 16px;
        box-shadow: 0 10px 20px rgba(2, 6, 23, 0.06);
        overflow: hidden;
    }

    .booking-header {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 14px;
        background: linear-gradient(135deg, rgba(14,165,233,0.10), rgba(99,102,241,0.06));
        border-bottom: 1px solid rgba(15, 23, 42, 0.10);
    }

    .booking-meta {
        display: grid;
        gap: 4px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .meta-line {
        font-size: 12px;
        color: var(--muted);
    }

    .meta-line strong {
        color: #0b1220;
        font-weight: 800;
    }

    .badges {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: flex-start;
    }

    .badge {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(15, 23, 42, 0.04);
        color: #0b1220;
        white-space: nowrap;
    }

    .badge-paid { background: rgba(34, 197, 94, 0.12); border-color: rgba(34, 197, 94, 0.22); }
    .badge-cancel { background: rgba(239, 68, 68, 0.12); border-color: rgba(239, 68, 68, 0.22); }
    .badge-printed { background: rgba(59, 130, 246, 0.12); border-color: rgba(59, 130, 246, 0.22); }
    .badge-checked { background: rgba(168, 85, 247, 0.12); border-color: rgba(168, 85, 247, 0.22); }
    .badge-processing { background: rgba(234, 179, 8, 0.14); border-color: rgba(234, 179, 8, 0.24); }

    .booking-body {
        padding: 14px;
        display: grid;
        gap: 12px;
    }

    .chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(15, 23, 42, 0.04);
        font-size: 12px;
        color: #0b1220;
        white-space: nowrap;
    }

    .chip strong {
        font-weight: 900;
    }

    .booking-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px dashed rgba(15, 23, 42, 0.18);
        font-size: 12px;
        color: var(--muted);
    }

    .booking-total .value {
        font-weight: 900;
        color: #0b1220;
        font-size: 14px;
        white-space: nowrap;
    }

    .ticket-per-seat {
        background: var(--surface);
        border: 1px solid rgba(15, 23, 42, 0.25);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(2, 6, 23, 0.08);
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .ticket-header {
        display: flex;
        gap: 12px;
        padding: 14px;
        border-bottom: 1px dashed rgba(15, 23, 42, 0.25);
        background: linear-gradient(135deg, rgba(14,165,233,0.10), rgba(99,102,241,0.08));
    }

    .ticket-poster {
        width: 78px;
        height: 110px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.15);
        flex: 0 0 auto;
        background: #f1f5f9;
    }

    .ticket-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .ticket-title {
        font-weight: 800;
        font-size: 16px;
        line-height: 1.2;
        margin: 0 0 6px 0;
        color: #0b1220;
    }

    .ticket-sub {
        color: var(--muted);
        font-size: 12px;
        margin: 0;
    }

    .ticket-body {
        padding: 14px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 14px;
    }

    .ticket-field {
        font-size: 12px;
        color: #0b1220;
    }

    .ticket-field strong {
        color: var(--muted);
        font-weight: 700;
        display: block;
        font-size: 11px;
        margin-bottom: 2px;
    }

    .ticket-emphasis {
        font-size: 18px;
        font-weight: 900;
        letter-spacing: 0.3px;
    }

    .ticket-footer {
        padding: 12px 14px;
        border-top: 1px dashed rgba(15, 23, 42, 0.25);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .ticket-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 12px;
        color: #0b1220;
        background: rgba(15, 23, 42, 0.06);
        padding: 6px 10px;
        border-radius: 10px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        white-space: nowrap;
    }

    .ticket-price {
        font-weight: 900;
        color: #0b1220;
        white-space: nowrap;
    }

    .combo-voucher {
        background: var(--surface);
        border: 1px solid rgba(15, 23, 42, 0.25);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(2, 6, 23, 0.08);
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .combo-header {
        display: flex;
        gap: 12px;
        padding: 14px;
        border-bottom: 1px dashed rgba(15, 23, 42, 0.25);
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.12), rgba(14,165,233,0.06));
    }

    .combo-image {
        width: 78px;
        height: 78px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.15);
        flex: 0 0 auto;
        background: #f1f5f9;
    }

    .combo-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .food-header {
        display: flex;
        gap: 12px;
        padding: 14px;
        border-bottom: 1px dashed rgba(15, 23, 42, 0.25);
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(14,165,233,0.06));
    }

    .section-title {
        margin: 16px 0 10px;
        font-weight: 900;
        font-size: 14px;
        color: #0b1220;
    }

    @media print {
        @page { size: A4; margin: 10mm; }

        body * { visibility: hidden; }
        .print-only, .print-only * { visibility: visible; }

        .print-actions { display: none !important; }
        .action-bar { display: none !important; }
        .print-page { padding: 0 !important; max-width: none !important; }
        .manage-area { display: none !important; }
        .print-only { display: block !important; position: absolute; left: 0; top: 0; width: 100%; }

        .ticket-per-seat { box-shadow: none !important; }
    }
</style>

<div class="print-page">
    <h3 style="margin: 0; font-weight: 800;">In vé (mỗi ghế 1 vé)</h3>

    <div class="action-bar">
        <div class="action-left">
            <p class="action-title">Hành động</p>
            <p class="action-sub">In từng ghế = 1 vé • Combo = phiếu riêng</p>
        </div>
        <div class="action-buttons">
            <a class="btn-secondary" href="{{ route('admin.scan.index') }}">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <button id="printBtn" class="btn-print" type="button">
                <i class="fas fa-print"></i>
                In vé
            </button>
        </div>
    </div>

    <div class="manage-area">
        <div class="tickets-grid">
            @foreach(($bookings ?? collect()) as $booking)
                @php
                    $showtime = $booking->suatChieu;
                    $movie = $showtime->phim ?? null;
                    $room = $showtime->phongChieu ?? null;
                    $poster = $movie ? ($movie->poster_url ?? null) : null;
                    $ticketCode = $booking->ticket_code ?: sprintf('MV%06d', $booking->id);

                    $seats = $booking->chiTietDatVe ?? collect();
                    $combos = $booking->chiTietCombo ?? collect();
                    $foods = $booking->chiTietFood ?? collect();

                    $statusLabel = 'Đang xử lý';
                    $statusClass = 'badge-processing';
                    if ((int)($booking->trang_thai ?? 0) === 1) { $statusLabel = 'Đã thanh toán'; $statusClass = 'badge-paid'; }
                    if ((int)($booking->trang_thai ?? 0) === 2) { $statusLabel = 'Đã huỷ'; $statusClass = 'badge-cancel'; }

                    $isPrinted = (bool)($booking->da_in ?? false);
                    $isChecked = (bool)($booking->checked_in ?? false);
                    $total = method_exists($booking, 'getTongTienHienThiAttribute') ? ($booking->tong_tien_hien_thi ?? ($booking->tong_tien ?? 0)) : ($booking->tong_tien ?? 0);
                @endphp
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="ticket-poster">
                            <img src="{{ $poster ?: asset('images/no-poster.svg') }}" alt="{{ $movie->ten_phim ?? 'Poster' }}">
                        </div>
                        <div class="booking-meta">
                            <div class="ticket-title">{{ $movie->ten_phim ?? 'N/A' }}</div>
                            <div class="meta-line">⏰ <strong>{{ $showtime && $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y – H:i') : '' }}</strong> @if($room && ($room->ten_phong ?? null)) | Phòng {{ $room->ten_phong }} @endif</div>
                            <div class="meta-line">🧾 Mã đặt vé: <strong>#{{ $booking->id }}</strong> <span style="margin-left:6px;" class="ticket-code">{{ $ticketCode }}</span></div>
                        </div>
                        <div class="badges">
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($isPrinted)
                                <span class="badge badge-printed">Đã in</span>
                            @endif
                            @if($isChecked)
                                <span class="badge badge-checked">Đã soát</span>
                            @endif
                        </div>
                    </div>

                    <div class="booking-body">
                        <div>
                            <div class="meta-line" style="margin-bottom:8px;"><strong>Ghế</strong></div>
                            <div class="chip-list">
                                @foreach($seats as $detail)
                                    @php
                                        $seatNo = $detail->ghe->so_ghe ?? 'N/A';
                                        $seatPrice = $detail->gia ?? ($detail->gia_ve ?? 0);
                                    @endphp
                                    <div class="chip"><strong>{{ $seatNo }}</strong><span>{{ number_format((float)$seatPrice) }} đ/ghế</span></div>
                                @endforeach
                            </div>
                        </div>

                        @if($combos && $combos->isNotEmpty())
                            <div>
                                <div class="meta-line" style="margin-bottom:8px;"><strong>Combo</strong></div>
                                <div class="chip-list">
                                    @foreach($combos as $comboDetail)
                                        @php
                                            $combo = $comboDetail->combo;
                                            $comboName = $combo->ten ?? $combo->ten_combo ?? 'Combo';
                                            $qty = max(1, (int)($comboDetail->so_luong ?? 1));
                                        @endphp
                                        <div class="chip"><strong>{{ $comboName }}</strong><span>x{{ $qty }}</span></div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($foods && $foods->isNotEmpty())
                            <div>
                                <div class="meta-line" style="margin-bottom:8px;"><strong>Đồ ăn</strong></div>
                                <div class="chip-list">
                                    @foreach($foods as $foodDetail)
                                        @php
                                            $food = $foodDetail->food;
                                            $foodName = $food->name ?? 'Đồ ăn';
                                            $qty = max(1, (int)($foodDetail->quantity ?? 1));
                                        @endphp
                                        <div class="chip"><strong>{{ $foodName }}</strong><span>x{{ $qty }}</span></div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="booking-total">
                            <div>Tổng tiền booking</div>
                            <div class="value">{{ number_format((float)$total) }} đ</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="print-only">
        <div class="section-title">Vé ghế</div>
        <div class="tickets-grid">
            @foreach($printItems as $item)
                @php
                    $booking = $item['booking'];
                    $showtime = $booking->suatChieu;
                    $movie = $showtime->phim ?? null;
                    $room = $showtime->phongChieu ?? null;
                    $poster = $movie ? ($movie->poster_url ?? null) : null;
                    $ticketCode = $booking->ticket_code ?: sprintf('MV%06d', $booking->id);
                @endphp
                <div class="ticket-per-seat">
                    <div class="ticket-header">
                        <div class="ticket-poster">
                            <img src="{{ $poster ?: asset('images/no-poster.svg') }}" alt="{{ $movie->ten_phim ?? 'Poster' }}">
                        </div>
                        <div style="min-width:0;">
                            <div class="ticket-title">{{ $movie->ten_phim ?? 'N/A' }}</div>
                            <p class="ticket-sub">{{ $showtime && $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y H:i') : '' }}</p>
                            <p class="ticket-sub">{{ $room->ten_phong ?? '' }}</p>
                        </div>
                    </div>

                    <div class="ticket-body">
                        <div class="ticket-field">
                            <strong>Ghế</strong>
                            <div class="ticket-emphasis">{{ $item['seat'] }}</div>
                        </div>
                        <div class="ticket-field">
                            <strong>Mã vé</strong>
                            <div style="font-weight: 800;">{{ $ticketCode }}</div>
                        </div>
                        <div class="ticket-field">
                            <strong>Giá</strong>
                            <div>{{ number_format((float)$item['price']) }} đ/ghế</div>
                        </div>
                        <div class="ticket-field">
                            <strong>ID đặt vé</strong>
                            <div>#{{ $booking->id }}</div>
                        </div>
                    </div>

                    <div class="ticket-footer">
                        <div class="ticket-code">{{ $ticketCode }} • {{ $item['seat'] }}</div>
                        <div class="ticket-price">{{ number_format((float)$item['price']) }} đ/ghế</div>
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $hasAnyFnb = ($bookings ?? collect())->contains(function($b){
                return (($b->chiTietCombo ?? collect())->isNotEmpty()) || (($b->chiTietFood ?? collect())->isNotEmpty());
            });
        @endphp
        @if($hasAnyFnb)
            <div class="section-title">Phiếu Combo &amp; Đồ ăn</div>
            <div class="tickets-grid">
                @foreach(($bookings ?? collect()) as $booking)
                    @php
                        $combos = $booking->chiTietCombo ?? collect();
                        $foods = $booking->chiTietFood ?? collect();
                        if ($combos->isEmpty() && $foods->isEmpty()) continue;

                        $showtime = $booking->suatChieu;
                        $movie = $showtime->phim ?? null;
                        $ticketCode = $booking->ticket_code ?: sprintf('MV%06d', $booking->id);

                        $comboTotal = (float) $combos->sum(function($c){
                            $qty = max(1, (int)($c->so_luong ?? 1));
                            return (float)($c->gia_ap_dung ?? 0) * $qty;
                        });
                        $foodTotal = (float) $foods->sum(function($f){
                            $qty = max(1, (int)($f->quantity ?? 1));
                            return (float)($f->price ?? 0) * $qty;
                        });
                        $fnbTotal = $comboTotal + $foodTotal;

                        $firstCombo = $combos->first();
                        $firstFood = $foods->first();
                        $img = null;
                        if ($firstCombo && $firstCombo->combo) { $img = $firstCombo->combo->image_url ?? null; }
                        if (!$img && $firstFood && $firstFood->food) { $img = $firstFood->food->image_url ?? null; }
                    @endphp
                    <div class="combo-voucher">
                        <div class="combo-header">
                            <div class="combo-image">
                                <img src="{{ $img ?: asset('images/no-poster.svg') }}" alt="F&B">
                            </div>
                            <div style="min-width:0;">
                                <div class="ticket-title">Phiếu F&amp;B</div>
                                <p class="ticket-sub">{{ $movie->ten_phim ?? '' }}</p>
                                <p class="ticket-sub">{{ $showtime && $showtime->thoi_gian_bat_dau ? $showtime->thoi_gian_bat_dau->format('d/m/Y H:i') : '' }}</p>
                            </div>
                        </div>

                        <div class="ticket-body">
                            <div class="ticket-field">
                                <strong>Mã vé</strong>
                                <div style="font-weight: 800;">{{ $ticketCode }}</div>
                            </div>
                            <div class="ticket-field">
                                <strong>ID đặt vé</strong>
                                <div>#{{ $booking->id }}</div>
                            </div>
                            <div class="ticket-field" style="grid-column: 1 / -1;">
                                <strong>Nội dung</strong>
                                <div>
                                    @foreach($combos as $comboDetail)
                                        @php
                                            $combo = $comboDetail->combo;
                                            $comboName = $combo->ten ?? $combo->ten_combo ?? 'Combo';
                                            $qty = max(1, (int)($comboDetail->so_luong ?? 1));
                                        @endphp
                                        <div>{{ $comboName }} x{{ $qty }}</div>
                                    @endforeach
                                    @foreach($foods as $foodDetail)
                                        @php
                                            $food = $foodDetail->food;
                                            $foodName = $food->name ?? 'Đồ ăn';
                                            $qty = max(1, (int)($foodDetail->quantity ?? 1));
                                        @endphp
                                        <div>{{ $foodName }} x{{ $qty }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="ticket-footer">
                            <div class="ticket-code">FNB • {{ $ticketCode }}</div>
                            <div class="ticket-price">{{ number_format((float)$fnbTotal) }} đ</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
(function(){
    document.body.classList.add('print-multiple-mode');
    const printBtn = document.getElementById('printBtn');
    @php
        $bookingIds = ($bookings ?? collect())->map(function ($b) { return (int) $b->id; })->values()->all();
    @endphp
    const bookingIds = @json($bookingIds);
    const autoPrint = @json((bool) request()->boolean('auto_print'));

    let hasMarkedPrinted = false;
    function markPrintedAfterPrint(){
        if (hasMarkedPrinted) return;
        hasMarkedPrinted = true;

        fetch('{{ route('admin.scan.mark-multiple-printed') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: bookingIds })
        }).then(r=>r.json()).then(data=>{
            if (data.success) {
                if (!autoPrint) {
                    alert('Đã đánh dấu ' + (data.count||bookingIds.length) + ' vé là đã in');
                    location.reload();
                } else {
                    location.reload();
                }
            } else {
                if (!autoPrint) {
                    alert(data.message || 'Lỗi');
                } else {
                    console.warn('mark-printed failed:', data);
                }
            }
        }).catch((err)=>{
            if (!autoPrint) {
                alert('Lỗi mạng');
            } else {
                console.warn('mark-printed network error:', err);
            }
        });
    }

    printBtn.addEventListener('click', function(){
        if (!autoPrint) {
            if (!confirm('Sau khi in xong, vé sẽ được đánh dấu là đã in và không thể in lại. Tiếp tục?')) return;
        }
        window.print();
    });

    // Auto mark printed after closing print dialog
    window.addEventListener('afterprint', markPrintedAfterPrint);

    // Auto open print dialog after coming from box-office payment
    if (autoPrint) {
        setTimeout(function(){
            try { window.print(); } catch (e) { /* ignore */ }
        }, 350);
    }
})();
</script>
@endsection
