@extends('admin.layout')

@section('title', 'Bán vé tại quầy')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Bán vé tại quầy</h1>
        <div class="text-sm text-gray-400">
            Nhân viên: <span class="text-white font-semibold">{{ Auth::user()->ho_ten }}</span>
        </div>
    </div>

    <!-- Bước 1-2: Chọn phim -->
    <div class="bg-[#151822] border border-[#262833] rounded-2xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">
            <i class="fas fa-film text-blue-500 mr-2"></i>
            Bước 1: Chọn phim
        </h2>
        
        <div class="mb-4">
            <label class="block text-sm text-gray-300 mb-2">Loại phim</label>
            <select id="movie-status" class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500">
                <option value="showing">Phim đang chiếu</option>
                <option value="upcoming">Phim sắp chiếu</option>
            </select>
        </div>

        <div id="movies-container" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <p class="text-gray-400 col-span-full text-center py-8">Đang tải danh sách phim...</p>
        </div>
    </div>

    <!-- Bước 3: Chọn suất chiếu -->
    <div id="showtimes-section" class="bg-[#151822] border border-[#262833] rounded-2xl p-6" style="display: none;">
        <h2 class="text-xl font-semibold text-white mb-4">
            <i class="fas fa-clock text-blue-500 mr-2"></i>
            Bước 2: Chọn suất chiếu
        </h2>
        
        <div id="selected-movie-info" class="mb-4 p-4 bg-[#1b1e28] rounded-lg border border-[#262833]">
            <p class="text-gray-400">Vui lòng chọn phim trước</p>
        </div>

        <div id="showtimes-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        </div>
    </div>

    <!-- Bước 4: Chọn ghế -->
    <div id="seats-section" class="bg-[#151822] border border-[#262833] rounded-2xl p-6" style="display: none;">
        <h2 class="text-xl font-semibold text-white mb-4">
            <i class="fas fa-chair text-blue-500 mr-2"></i>
            Bước 3: Chọn ghế
        </h2>
        
        <div id="seat-map-container" class="mb-4">
            <p class="text-gray-400 text-center py-8">Đang tải sơ đồ ghế...</p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-gray-300 bg-[#10121a] p-4 rounded-lg border border-[#262833]">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-green-600 border-2 border-green-500"></span>
                <span>Trống</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-red-600 border-2 border-red-500"></span>
                <span>Đã bán</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-yellow-600 border-2 border-yellow-500"></span>
                <span>Đang giữ</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-blue-600 border-2 border-blue-500"></span>
                <span>Đã chọn</span>
            </div>
        </div>

        <div id="selected-seats-display" class="mt-4 p-3 bg-[#1b1e28] rounded-lg border border-[#262833]">
            <p class="text-gray-400">Chưa chọn ghế nào</p>
        </div>
    </div>

    <!-- Bước 5: Chọn đồ ăn -->
    <div id="foods-section" class="bg-[#151822] border border-[#262833] rounded-2xl p-6" style="display: none;">
        <h2 class="text-xl font-semibold text-white mb-4">
            <i class="fas fa-utensils text-blue-500 mr-2"></i>
            Bước 4: Chọn đồ ăn (tùy chọn)
        </h2>
        
        <div id="foods-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <p class="text-gray-400 col-span-full text-center py-4">Đang tải danh sách đồ ăn...</p>
        </div>
    </div>

    <!-- Bước 6: Thông tin khách hàng và thanh toán -->
    <div id="payment-section" class="bg-[#151822] border border-[#262833] rounded-2xl p-6" style="display: none;">
        <h2 class="text-xl font-semibold text-white mb-4">
            <i class="fas fa-money-bill-wave text-blue-500 mr-2"></i>
            Bước 5: Thông tin khách hàng & Thanh toán
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm text-gray-300 mb-2">Số điện thoại khách hàng</label>
                <input type="text" id="customer-phone" placeholder="0909123456" 
                    class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm text-gray-300 mb-2">Phương thức thanh toán <span class="text-red-500">*</span></label>
                <select id="payment-method" class="w-full bg-[#10121a] border border-[#262833] rounded-lg px-3 py-2 text-sm text-gray-200 outline-none focus:border-blue-500">
                    <option value="cash">Tiền mặt</option>
                    <option value="transfer">Chuyển khoản</option>
                    <option value="e_wallet">Ví điện tử</option>
                    <option value="pos">POS</option>
                </select>
            </div>
        </div>

        <div class="mt-6 p-4 bg-gradient-to-r from-blue-900/30 to-purple-900/30 border border-blue-500/30 rounded-xl">
            <div class="flex items-center justify-between mb-2">
                <span class="text-lg font-semibold text-white">Tổng tiền:</span>
                <span id="total-amount" class="text-2xl font-bold text-green-400">0 đ</span>
            </div>
            <div id="price-breakdown" class="text-sm text-gray-300 space-y-1"></div>
        </div>

        <div class="mt-6 flex justify-end gap-4">
            <button id="cancel-btn" class="px-6 py-2 bg-[#262833] text-gray-300 rounded-lg hover:bg-[#374151] transition">
                Hủy
            </button>
            <button id="confirm-btn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-check mr-2"></i>
                Xác nhận đặt vé
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedMovie = null;
let selectedShowtime = null;
let selectedSeats = [];
let selectedFoods = [];
let seatMap = {};
const BASE_PRICE = 100000;

// Load movies
document.getElementById('movie-status').addEventListener('change', loadMovies);
loadMovies();

function loadMovies() {
    const status = document.getElementById('movie-status').value;
    const container = document.getElementById('movies-container');
    container.innerHTML = '<p class="text-gray-400 col-span-full text-center py-8">Đang tải...</p>';
    
    fetch(`/admin/box-office/movies?status=${status}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                container.innerHTML = data.data.map(movie => `
                    <div class="movie-card cursor-pointer bg-[#1b1e28] rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition" 
                         onclick="selectMovie(${movie.id}, '${movie.ten_phim}', '${movie.poster || ''}')">
                        <img src="${movie.poster || '/images/no-poster.svg'}" alt="${movie.ten_phim}" 
                             class="w-full h-48 object-cover">
                        <div class="p-3">
                            <h3 class="text-white font-semibold text-sm mb-1">${movie.ten_phim}</h3>
                            <p class="text-gray-400 text-xs">${movie.thoi_luong} phút | ${movie.do_tuoi}+</p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-gray-400 col-span-full text-center py-8">Không có phim</p>';
            }
        })
        .catch(err => {
            container.innerHTML = '<p class="text-red-400 col-span-full text-center py-8">Lỗi khi tải danh sách phim</p>';
        });
}

function selectMovie(movieId, movieName, poster) {
    selectedMovie = { id: movieId, name: movieName, poster };
    document.getElementById('selected-movie-info').innerHTML = `
        <div class="flex items-center gap-3">
            <img src="${poster || '/images/no-poster.svg'}" alt="${movieName}" class="w-16 h-20 object-cover rounded">
            <div>
                <h3 class="text-white font-semibold">${movieName}</h3>
                <p class="text-gray-400 text-sm">Đã chọn phim</p>
            </div>
        </div>
    `;
    
    document.getElementById('showtimes-section').style.display = 'block';
    loadShowtimes(movieId);
}

function loadShowtimes(movieId) {
    const container = document.getElementById('showtimes-container');
    container.innerHTML = '<p class="text-gray-400 col-span-full text-center py-4">Đang tải suất chiếu...</p>';
    
    fetch(`/admin/box-office/showtimes?movie_id=${movieId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                container.innerHTML = data.data.map(st => `
                    <div class="showtime-card cursor-pointer p-4 bg-[#1b1e28] rounded-lg border-2 border-transparent hover:border-blue-500 transition"
                         onclick="selectShowtime(${st.id})">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-semibold">${st.phong_chieu}</span>
                            <span class="text-blue-400 text-sm">${st.gia_ve_co_ban.toLocaleString('vi-VN')} đ</span>
                        </div>
                        <div class="text-gray-400 text-sm">
                            <div>${st.thoi_gian_bat_dau} - ${st.thoi_gian_ket_thuc}</div>
                            <div>${st.ngay_chieu}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-gray-400 col-span-full text-center py-4">Không có suất chiếu</p>';
            }
        })
        .catch(err => {
            container.innerHTML = '<p class="text-red-400 col-span-full text-center py-4">Lỗi khi tải suất chiếu</p>';
        });
}

function selectShowtime(showtimeId) {
    selectedShowtime = showtimeId;
    document.getElementById('seats-section').style.display = 'block';
    loadSeats(showtimeId);
    loadFoods();
}

function loadSeats(showtimeId) {
    const container = document.getElementById('seat-map-container');
    container.innerHTML = '<p class="text-gray-400 text-center py-8">Đang tải sơ đồ ghế...</p>';
    
    fetch(`/admin/box-office/showtimes/${showtimeId}/seats`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderSeatMap(data.data.seats);
                document.getElementById('foods-section').style.display = 'block';
                document.getElementById('payment-section').style.display = 'block';
            }
        })
        .catch(err => {
            container.innerHTML = '<p class="text-red-400 text-center py-8">Lỗi khi tải sơ đồ ghế</p>';
        });
}

function renderSeatMap(seats) {
    const container = document.getElementById('seat-map-container');
    
    // Group by row
    const seatsByRow = {};
    seats.forEach(seat => {
        const row = seat.code.charAt(0);
        if (!seatsByRow[row]) seatsByRow[row] = [];
        seatsByRow[row].push(seat);
        seatMap[seat.id] = seat;
    });
    
    let html = '<div class="screen-display text-center mb-8 p-4 bg-gradient-to-r from-gray-700 to-gray-800 rounded-lg border-2 border-gray-600"><div class="text-white font-bold">MÀN HÌNH</div></div>';
    html += '<div class="flex flex-col items-center gap-2">';
    
    Object.keys(seatsByRow).sort().forEach(row => {
        html += '<div class="flex items-center gap-2">';
        html += `<div class="w-8 text-center text-gray-400 font-bold">${row}</div>`;
        seatsByRow[row].sort((a, b) => parseInt(a.code.slice(1)) - parseInt(b.code.slice(1))).forEach(seat => {
            let bgColor = 'bg-green-600';
            let borderColor = 'border-green-500';
            let cursor = 'cursor-pointer';
            
            if (seat.status === 'sold') {
                bgColor = 'bg-red-600';
                borderColor = 'border-red-500';
                cursor = 'cursor-not-allowed';
            } else if (seat.status === 'holding') {
                bgColor = 'bg-yellow-600';
                borderColor = 'border-yellow-500';
            } else if (selectedSeats.includes(seat.id)) {
                bgColor = 'bg-blue-600';
                borderColor = 'border-blue-500';
            }
            
            html += `<button type="button" onclick="toggleSeat(${seat.id})" 
                     class="w-10 h-10 rounded ${bgColor} border-2 ${borderColor} ${cursor} text-white text-xs font-bold"
                     ${seat.status === 'sold' ? 'disabled' : ''}>
                     ${seat.code.slice(1)}
                     </button>`;
        });
        html += '</div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function toggleSeat(seatId) {
    const index = selectedSeats.indexOf(seatId);
    if (index > -1) {
        selectedSeats.splice(index, 1);
    } else {
        selectedSeats.push(seatId);
    }
    updateSelectedSeats();
    loadSeats(selectedShowtime);
}

function updateSelectedSeats() {
    const display = document.getElementById('selected-seats-display');
    if (selectedSeats.length > 0) {
        const seatCodes = selectedSeats.map(id => seatMap[id]?.code || `ID:${id}`).join(', ');
        display.innerHTML = `<p class="text-white"><span class="font-semibold">Đã chọn ${selectedSeats.length} ghế:</span> ${seatCodes}</p>`;
        calculateTotal();
    } else {
        display.innerHTML = '<p class="text-gray-400">Chưa chọn ghế nào</p>';
    }
    
    document.getElementById('confirm-btn').disabled = selectedSeats.length === 0;
}

function loadFoods() {
    fetch('/admin/box-office/foods')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderFoods(data.data);
            }
        });
}

function renderFoods(data) {
    const container = document.getElementById('foods-container');
    let html = '';
    
    if (data.combos && data.combos.length > 0) {
        html += '<div class="col-span-full"><h3 class="text-white font-semibold mb-2">Combo</h3></div>';
        html += data.combos.map(combo => `
            <div class="p-3 bg-[#1b1e28] rounded-lg border border-[#262833]">
                <h4 class="text-white text-sm font-semibold mb-1">${combo.ten}</h4>
                <p class="text-gray-400 text-xs mb-2">${parseInt(combo.gia).toLocaleString('vi-VN')} đ</p>
                <div class="flex items-center gap-2">
                    <button onclick="updateFoodQuantity('combo', ${combo.id}, -1)" class="w-8 h-8 bg-[#262833] text-white rounded">-</button>
                    <input type="number" id="food-combo-${combo.id}" value="0" min="0" readonly 
                           class="w-12 text-center bg-[#10121a] border border-[#262833] rounded text-white text-sm">
                    <button onclick="updateFoodQuantity('combo', ${combo.id}, 1)" class="w-8 h-8 bg-[#262833] text-white rounded">+</button>
                </div>
            </div>
        `).join('');
    }
    
    if (data.foods && data.foods.length > 0) {
        html += '<div class="col-span-full mt-4"><h3 class="text-white font-semibold mb-2">Đồ ăn</h3></div>';
        html += data.foods.map(food => `
            <div class="p-3 bg-[#1b1e28] rounded-lg border border-[#262833]">
                <h4 class="text-white text-sm font-semibold mb-1">${food.name}</h4>
                <p class="text-gray-400 text-xs mb-2">${parseInt(food.price).toLocaleString('vi-VN')} đ</p>
                <div class="flex items-center gap-2">
                    <button onclick="updateFoodQuantity('food', ${food.id}, -1)" class="w-8 h-8 bg-[#262833] text-white rounded">-</button>
                    <input type="number" id="food-food-${food.id}" value="0" min="0" max="${food.stock}" readonly 
                           class="w-12 text-center bg-[#10121a] border border-[#262833] rounded text-white text-sm">
                    <button onclick="updateFoodQuantity('food', ${food.id}, 1)" class="w-8 h-8 bg-[#262833] text-white rounded">+</button>
                </div>
            </div>
        `).join('');
    }
    
    container.innerHTML = html || '<p class="text-gray-400 col-span-full text-center py-4">Không có đồ ăn</p>';
}

function updateFoodQuantity(type, id, change) {
    const input = document.getElementById(`food-${type}-${id}`);
    const current = parseInt(input.value) || 0;
    const newValue = Math.max(0, current + change);
    input.value = newValue;
    calculateTotal();
}

function calculateTotal() {
    let seatTotal = 0;
    selectedSeats.forEach(seatId => {
        const seat = seatMap[seatId];
        if (seat) {
            seatTotal += seat.price || BASE_PRICE;
        }
    });
    
    let foodTotal = 0;
    document.querySelectorAll('[id^="food-"]').forEach(input => {
        const qty = parseInt(input.value) || 0;
        if (qty > 0) {
            // TODO: Calculate food total
        }
    });
    
    const total = seatTotal + foodTotal;
    document.getElementById('total-amount').textContent = total.toLocaleString('vi-VN') + ' đ';
    document.getElementById('price-breakdown').innerHTML = `
        <div>Ghế: ${seatTotal.toLocaleString('vi-VN')} đ</div>
        ${foodTotal > 0 ? `<div>Đồ ăn: ${foodTotal.toLocaleString('vi-VN')} đ</div>` : ''}
    `;
}

document.getElementById('confirm-btn').addEventListener('click', function() {
    if (selectedSeats.length === 0) {
        alert('Vui lòng chọn ít nhất một ghế!');
        return;
    }
    
    const customerPhone = document.getElementById('customer-phone').value;
    const paymentMethod = document.getElementById('payment-method').value;
    
    if (!customerPhone) {
        alert('Vui lòng nhập số điện thoại khách hàng!');
        return;
    }
    
    // Collect food data
    const foods = [];
    document.querySelectorAll('[id^="food-"]').forEach(input => {
        const qty = parseInt(input.value) || 0;
        if (qty > 0) {
            const parts = input.id.split('-');
            const type = parts[1];
            const id = parseInt(parts[2]);
            foods.push({ id, qty });
        }
    });
    
    // Get seat codes
    const seatCodes = selectedSeats.map(id => seatMap[id]?.code || '').filter(Boolean);
    
    const orderData = {
        showtime_id: selectedShowtime,
        seats: seatCodes,
        foods: foods,
        payment_method: paymentMethod,
        customer_phone: customerPhone,
        type: 'OFFLINE'
    };
    
    fetch('/admin/box-office/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(orderData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Nếu là QR payment, redirect đến trang QR
            if (data.data.qr_payment && data.data.qr_url) {
                window.location.href = data.data.qr_url;
            } else {
                // Các phương thức thanh toán khác, redirect đến trang in vé
                alert('Đặt vé thành công!');
                window.location.href = `/admin/box-office/tickets/${data.data.booking_id}/print`;
            }
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(err => {
        alert('Lỗi khi đặt vé: ' + err.message);
    });
});
</script>
@endpush
@endsection
