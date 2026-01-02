@extends('admin.layout')

@section('title', 'Đặt vé tại quầy')

@section('content')
<div class="container-fluid p-0" data-bs-theme="dark">
    {{-- Thông báo lỗi/thành công --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="staff-booking-form" action="{{ route('admin.bookings.store') }}" method="POST">
        @csrf
        
        <div class="row g-4">
            {{-- CỘT TRÁI: NỘI DUNG CHÍNH --}}
            <div class="col-lg-9">
                
                {{-- 1. Thông tin suất chiếu --}}
                <div class="card bg-dark text-white border-secondary mb-4 shadow-sm">
                    <div class="card-header border-secondary bg-transparent">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-film me-2"></i> 1. Chọn phim & Suất chiếu
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-secondary small">Phim</label>
                                <select id="movie_id" name="movie_id" required class="form-select bg-dark text-white border-secondary">
                                    <option value="">-- Chọn phim --</option>
                                    @foreach($movies as $movie)
                                        <option value="{{ $movie->id }}">{{ $movie->ten_phim }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary small">Ngày chiếu (Cố định)</label>
                                <input type="date" id="show_date" name="show_date" value="{{ date('Y-m-d') }}" readonly 
                                    class="form-control bg-dark text-white border-secondary cursor-not-allowed opacity-75">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary small">Suất chiếu</label>
                                <select id="showtime_id" name="showtime_id" required class="form-select bg-dark text-white border-secondary" disabled>
                                    <option value="">-- Vui lòng chọn phim --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Sơ đồ ghế --}}
                <div class="card bg-dark text-white border-secondary mb-4 shadow-sm d-none" id="seat-section">
                    <div class="card-header border-secondary bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-couch me-2"></i> 2. Chọn ghế
                        </h5>
                        <small class="text-muted"><i class="fas fa-tv me-1"></i> Màn hình phía trước</small>
                    </div>
                    <div class="card-body text-center">
                        <div class="mx-auto mb-5 position-relative" style="height: 4px; width: 60%; background: linear-gradient(90deg, transparent, #FF784E, transparent); border-radius: 50%; box-shadow: 0 0 15px rgba(255, 120, 78, 0.5);">
                            <span class="position-absolute top-100 start-50 translate-middle-x text-muted small mt-2">SCREEN</span>
                        </div>

                        <div class="overflow-auto pb-3">
                            <div id="seat-map" class="d-inline-block" style="min-width: 600px;"></div>
                        </div>

                        <div class="d-flex justify-content-center flex-wrap gap-3 mt-4 pt-3 border-top border-secondary">
                            <div class="d-flex align-items-center"><span class="badge border border-secondary me-2 bg-transparent" style="width:20px; height:20px;"> </span> <small class="text-muted">Thường</small></div>
                            <div class="d-flex align-items-center"><span class="badge bg-warning me-2" style="width:20px; height:20px;"> </span> <small class="text-muted">VIP</small></div>
                            <div class="d-flex align-items-center"><span class="badge bg-danger me-2" style="width:20px; height:20px;"> </span> <small class="text-muted">Đôi</small></div>
                            <div class="d-flex align-items-center"><span class="badge bg-secondary me-2 opacity-50" style="width:20px; height:20px;"> </span> <small class="text-muted">Đã bán</small></div>
                            <div class="d-flex align-items-center"><span class="badge bg-success me-2" style="width:20px; height:20px;"> </span> <small class="text-white">Đang chọn</small></div>
                        </div>
                        
                        <div id="seat-ids-container"></div>
                    </div>
                </div>

                {{-- 3. Combo --}}
                <div class="card bg-dark text-white border-secondary shadow-sm">
                    <div class="card-header border-secondary bg-transparent">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-popcorn me-2"></i> 3. Bắp nước & Combo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($combos as $combo)
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 bg-dark border border-secondary">
                                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-white small mb-1">{{ $combo->ten }}</div>
                                                <div class="text-warning fw-bold small">{{ number_format($combo->gia) }} đ</div>
                                            </div>
                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                <button type="button" class="btn btn-outline-secondary border-secondary text-white decrease-combo" data-id="{{ $combo->id }}">-</button>
                                                <input type="text" name="combo_quantities[{{ $combo->id }}]" value="0" readonly 
                                                    class="form-control text-center bg-transparent text-white border-secondary combo-qty p-0" 
                                                    data-id="{{ $combo->id }}" data-price="{{ $combo->gia }}">
                                                <button type="button" class="btn btn-outline-primary increase-combo" data-id="{{ $combo->id }}">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: THANH TOÁN (Sticky) --}}
            <div class="col-lg-3">
                <div class="card bg-dark text-white border-secondary shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-wallet me-2"></i>THANH TOÁN</h5>
                    </div>
                    <div class="card-body">
                        {{-- Nhân viên --}}
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom border-secondary">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-user-tie text-white"></i>
                            </div>
                            <div>
                                <div class="text-muted small" style="font-size: 0.75rem;">Nhân viên</div>
                                <div class="fw-bold text-white">{{ $user->ho_ten ?? 'Admin' }}</div>
                            </div>
                        </div>

                        {{-- Chi tiết --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Ghế (<span id="summary-seat-count">0</span>):</span>
                                <span class="text-white fw-bold" id="summary-seat-price">0 đ</span>
                            </div>
                            <div class="text-muted small fst-italic mb-3 ps-2 border-start border-secondary" id="summary-seats-list" style="font-size: 0.75rem; min-height: 18px;">
                                (Chưa chọn ghế)
                            </div>
                            
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Combo:</span>
                                <span class="text-white fw-bold" id="summary-combo-price">0 đ</span>
                            </div>
                        </div>

                        {{-- Tổng tiền --}}
                        <div class="alert alert-dark border border-secondary d-flex justify-content-between align-items-center mb-4 py-3">
                            <span class="fw-bold text-white">TỔNG CỘNG</span>
                            <span class="h4 mb-0 text-warning fw-bold" id="total-price">0 đ</span>
                        </div>

                        {{-- Form Payment --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Phương thức thanh toán</label>
                            <select name="payment_method" id="payment_method" class="form-select form-select-sm bg-dark text-white border-secondary">
                                <option value="cash">💵 Tiền mặt (Tại quầy)</option>
                                <option value="transfer">🏦 Chuyển khoản (QR Code)</option>
                                <option value="card">💳 Thẻ ngân hàng</option>
                            </select>
                        </div>

                        {{-- KHU VỰC HIỂN THỊ QR CODE --}}
                        <div id="qr-code-section" class="mb-4 text-center d-none">
                            <div class="p-2 bg-white rounded">
                                {{-- Ảnh QR sẽ được gen vào đây --}}
                                <img id="qr-image" src="" class="img-fluid" alt="QR Code">
                            </div>
                            <div class="mt-2 text-warning small fw-bold">
                                <i class="fas fa-info-circle me-1"></i> Quét mã để thanh toán
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                                Chờ khách chuyển khoản xong rồi ấn "Xuất vé"
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted">Ghi chú</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Ghi chú đơn hàng..."></textarea>
                        </div>

                        {{-- Actions --}}
                        <div class="d-grid gap-2">
                            <button type="submit" id="btn-submit" disabled class="btn btn-primary fw-bold py-2 text-uppercase">
                                <i class="fas fa-print me-2"></i> Xuất vé ngay
                            </button>
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary btn-sm">Hủy bỏ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // ===========================================
    // CẤU HÌNH NGÂN HÀNG (SỬA Ở ĐÂY)
    // ===========================================
    const BANK_ID = 'MB'; // Mã ngân hàng (VD: MB, VCB, TPB, VPB...)
    const ACCOUNT_NO = '0334997858'; // Số tài khoản nhận tiền
    const ACCOUNT_NAME = 'RAP CHIEU PHIM'; // Tên chủ tài khoản
    const TEMPLATE = 'compact2'; // Mẫu QR (compact, compact2, print)

    const BASE_PRICE = 100000;
    let selectedSeats = [];
    let currentTotal = 0;
    
    // Elements
    const elMovie = document.getElementById('movie_id');
    const elDate = document.getElementById('show_date');
    const elShowtime = document.getElementById('showtime_id');
    const elSeatSection = document.getElementById('seat-section');
    const elSeatMap = document.getElementById('seat-map');
    const btnSubmit = document.getElementById('btn-submit');
    const elPaymentMethod = document.getElementById('payment_method');
    const elQrSection = document.getElementById('qr-code-section');
    const elQrImage = document.getElementById('qr-image');

    // 1. Load Showtime
    function loadShowtimes() {
        const movieId = elMovie.value;
        const date = elDate.value;

        if (!movieId || !date) {
            elShowtime.innerHTML = '<option value="">-- Vui lòng chọn phim --</option>';
            elShowtime.disabled = true;
            return;
        }

        elShowtime.innerHTML = '<option>Đang tải...</option>';
        elShowtime.disabled = true;

        fetch(`/admin/bookings/movie/${movieId}/showtimes?date=${date}`)
            .then(res => res.json())
            .then(res => {
                elShowtime.innerHTML = '<option value="">-- Chọn suất chiếu --</option>';
                if(res.success && res.data.length > 0) {
                    res.data.forEach(st => {
                        const opt = document.createElement('option');
                        opt.value = st.id;
                        opt.textContent = `${st.time} - ${st.room_name} (${st.available_seats} ghế trống)`;
                        elShowtime.appendChild(opt);
                    });
                } else {
                    elShowtime.innerHTML = '<option value="">Không có suất chiếu</option>';
                }
                elShowtime.disabled = false;
            });
    }

    elMovie.addEventListener('change', loadShowtimes);
    elDate.addEventListener('change', loadShowtimes);

    // 2. Load Seats
    elShowtime.addEventListener('change', function() {
        const showtimeId = this.value;
        if(!showtimeId) {
            elSeatSection.classList.add('d-none');
            return;
        }
        
        // Reset
        selectedSeats = [];
        updateSummary();
        
        elSeatSection.classList.remove('d-none');
        elSeatMap.innerHTML = '<div class="py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary"></div> Đang tải dữ liệu ghế...</div>';

        fetch(`/admin/showtimes/${showtimeId}/seats`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    elSeatMap.innerHTML = `<div class="text-danger">${data.error}</div>`;
                    return;
                }
                renderSeatMap(data.seats);
            })
            .catch(err => {
                elSeatMap.innerHTML = `<div class="text-danger">Lỗi tải ghế. Vui lòng thử lại.</div>`;
            });
    });

    // 3. Render Seat Map
    function renderSeatMap(seats) {
        elSeatMap.innerHTML = '';
        const rows = {};

        seats.forEach(s => {
            const r = s.row || s.label.charAt(0);
            if(!rows[r]) rows[r] = [];
            rows[r].push(s);
        });

        Object.keys(rows).sort().forEach(rLabel => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'd-flex justify-content-center mb-1 gap-1';

            const labelDiv = document.createElement('div');
            labelDiv.className = 'd-flex align-items-center justify-content-center fw-bold text-secondary';
            labelDiv.style.width = '30px';
            labelDiv.textContent = rLabel;
            rowDiv.appendChild(labelDiv);

            rows[rLabel].sort((a,b) => a.col - b.col).forEach(seat => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm p-0 d-flex align-items-center justify-content-center fw-bold shadow-sm transition-all';
                btn.style.width = '36px';
                btn.style.height = '36px';
                btn.style.fontSize = '11px';
                btn.textContent = seat.label.substring(1);

                if (seat.booked) {
                    btn.classList.add('btn-secondary', 'disabled', 'opacity-25');
                    btn.style.cursor = 'not-allowed';
                } else {
                    const type = seat.type || 1; 
                    if(type == 2) btn.classList.add('btn-warning', 'text-dark');
                    else if(type == 3) btn.classList.add('btn-danger');
                    else btn.classList.add('btn-outline-secondary', 'text-light');

                    btn.onclick = () => toggleSeat(btn, seat, type);
                }
                rowDiv.appendChild(btn);
            });
            elSeatMap.appendChild(rowDiv);
        });
    }

    // 4. Toggle Seat
    function toggleSeat(btn, seat, type) {
        const idx = selectedSeats.findIndex(s => s.id === seat.id);
        
        if(idx > -1) {
            selectedSeats.splice(idx, 1);
            btn.classList.remove('btn-success', 'text-white');
            if(type == 2) btn.classList.add('btn-warning', 'text-dark');
            else if(type == 3) btn.classList.add('btn-danger');
            else btn.classList.add('btn-outline-secondary', 'text-light');
        } else {
            selectedSeats.push({...seat, type: type});
            btn.classList.remove('btn-outline-secondary', 'btn-warning', 'btn-danger', 'text-dark', 'text-light');
            btn.classList.add('btn-success', 'text-white');
        }
        updateSummary();
    }

    // 5. Combo Logic
    document.querySelectorAll('.increase-combo').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            const input = document.querySelector(`.combo-qty[data-id="${id}"]`);
            input.value = parseInt(input.value) + 1;
            updateSummary();
        }
    });
    document.querySelectorAll('.decrease-combo').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            const input = document.querySelector(`.combo-qty[data-id="${id}"]`);
            if(parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
                updateSummary();
            }
        }
    });

    // 6. Payment Method Change Listener (VIETQR LOGIC)
    elPaymentMethod.addEventListener('change', function() {
        updateQRVisibility();
    });

    function updateQRVisibility() {
        const method = elPaymentMethod.value;
        // Chỉ hiện QR nếu chọn Transfer VÀ có tiền
        if (method === 'transfer' && currentTotal > 0) {
            elQrSection.classList.remove('d-none');
            generateQR(currentTotal);
        } else {
            elQrSection.classList.add('d-none');
        }
    }

    function generateQR(amount) {
        // Tạo nội dung chuyển khoản ngẫu nhiên hoặc theo quy tắc
        // Ở đây dùng timestamp để Unique mỗi lần tạo đơn
        const content = 'THANHTOAN VE ' + Math.floor(Date.now() / 1000); 
        const qrUrl = `https://img.vietqr.io/image/${BANK_ID}-${ACCOUNT_NO}-${TEMPLATE}.png?amount=${amount}&addInfo=${content}&accountName=${encodeURIComponent(ACCOUNT_NAME)}`;
        elQrImage.src = qrUrl;
    }

    // 7. Update Summary
    function updateSummary() {
        // Seat Total
        let seatTotal = 0;
        const seatNames = selectedSeats.map(s => s.label).join(', ');
        
        selectedSeats.forEach(s => {
            let price = BASE_PRICE;
            if(s.type == 2) price = 150000;
            if(s.type == 3) price = 200000;
            seatTotal += price;
        });

        // Combo Total
        let comboTotal = 0;
        document.querySelectorAll('.combo-qty').forEach(inp => {
            const qty = parseInt(inp.value);
            if(qty > 0) {
                comboTotal += qty * parseFloat(inp.dataset.price);
            }
        });

        currentTotal = seatTotal + comboTotal;

        // Display
        document.getElementById('summary-seat-count').textContent = selectedSeats.length;
        document.getElementById('summary-seats-list').textContent = seatNames || '(Chưa chọn ghế)';
        document.getElementById('summary-seat-price').textContent = new Intl.NumberFormat('vi-VN').format(seatTotal) + ' đ';
        document.getElementById('summary-combo-price').textContent = new Intl.NumberFormat('vi-VN').format(comboTotal) + ' đ';
        document.getElementById('total-price').textContent = new Intl.NumberFormat('vi-VN').format(currentTotal) + ' đ';

        // Update Hidden Inputs
        const container = document.getElementById('seat-ids-container');
        container.innerHTML = '';
        selectedSeats.forEach(s => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'seat_ids[]';
            inp.value = s.id;
            container.appendChild(inp);
        });

        // Enable Submit Button
        btnSubmit.disabled = (selectedSeats.length === 0);

        // Update QR if needed
        updateQRVisibility();
    }

    // Submit Handler
    document.getElementById('staff-booking-form').addEventListener('submit', function() {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang xử lý...';
    });
    
</script>
@endpush
@endsection