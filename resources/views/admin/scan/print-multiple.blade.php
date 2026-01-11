@extends('layouts.admin')

@section('content')
<style>
.ticket-per-seat { width: 300px; border: 1px solid #000; padding: 12px; margin: 8px; display: inline-block; vertical-align: top; }
@media print { body * { visibility: hidden; } .print-area, .print-area * { visibility: visible; } .print-area { position: absolute; left: 0; top: 0; } }
</style>

<div class="container">
    <h3>In nhiều vé (mỗi ghế 1 vé)</h3>

    <div style="margin-bottom:12px">
        <button id="printBtn" class="btn btn-primary">In</button>
        <button id="markPrintedBtn" class="btn btn-success">Đánh dấu đã in</button>
    </div>

    <div class="print-area">
        @foreach($printItems as $item)
            <div class="ticket-per-seat">
                <div><strong>Phim:</strong> {{ $item['booking']->suatChieu->phim->ten_phim ?? 'N/A' }}</div>
                <div><strong>Suất chiếu:</strong> {{ $item['booking']->suatChieu->thoi_gian ?? '' }}</div>
                <div><strong>Phòng:</strong> {{ $item['booking']->suatChieu->phongChieu->ten_phong ?? '' }}</div>
                <div><strong>Ghế:</strong> {{ $item['seat'] }}</div>
                <div><strong>Giá:</strong> {{ number_format($item['price']) }} đ</div>
                <div><strong>Mã đặt:</strong> {{ $item['booking']->ma_dat ?? $item['booking']->id }}</div>
            </div>
        @endforeach
    </div>
</div>

<script>
(function(){
    const printBtn = document.getElementById('printBtn');
    const markBtn = document.getElementById('markPrintedBtn');
    const bookingIds = [...new Set(@json(array_map(function($i){ return $i['booking']->id; }, $printItems)))];

    printBtn.addEventListener('click', function(){
        window.print();
    });

    markBtn.addEventListener('click', function(){
        if (!confirm('Bạn có chắc muốn đánh dấu các vé này là đã in không?')) return;
        fetch('{{ url('/admin/scan/print-multiple/mark-printed') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: bookingIds })
        }).then(r=>r.json()).then(data=>{
            if (data.success) {
                alert('Đã đánh dấu ' + (data.count||bookingIds.length) + ' vé là đã in');
                location.reload();
            } else {
                alert(data.message || 'Lỗi');
            }
        }).catch(()=>alert('Lỗi mạng'));
    });
})();
</script>
@endsection
