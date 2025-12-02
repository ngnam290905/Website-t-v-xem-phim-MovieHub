<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kết quả thanh toán</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:#0f1218; color:#e6e7eb; margin:0; display:flex; align-items:center; justify-content:center; min-height:100vh; }
    .card { background:#161a23; border:1px solid #2a2f3a; border-radius:16px; padding:24px; width: 520px; }
    .title { font-size: 20px; font-weight: 700; margin: 0 0 12px; }
    .ok { color:#34d399; }
    .err { color:#f87171; }
    .row { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #2a2f3a; }
    .row:last-child { border-bottom:none; }
    .muted { color:#a0a6b1; }
    .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; border-radius:12px; border:none; cursor:pointer; color:#fff; background: linear-gradient(90deg, #FF784E, #FFB25E); font-weight:700; text-decoration:none; }
  </style>
</head>
<body>
  <div class="card">
    <h1 class="title {{ $success ? 'ok' : 'err' }}">{{ $success ? 'Thanh toán thành công' : 'Thanh toán thất bại' }}</h1>
    <div class="row"><span class="muted">Mã giao dịch</span><span>{{ $txnRef ?? '-' }}</span></div>
    <div class="row"><span class="muted">Số tiền</span><span>{{ number_format($amount ?? 0, 0, ',', '.') }}đ</span></div>
    <p class="muted" style="margin:12px 0 16px;">{{ $message ?? '' }}</p>
    <div style="display:flex; gap:8px;">
      <a href="/" class="btn">Về trang chủ</a>
      <a href="/user/booking-history" class="btn" style="background:#2a2f3a; color:#e6e7eb;">Lịch sử vé</a>
    </div>
  </div>
</body>
</html>
