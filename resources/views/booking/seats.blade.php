@extends('layouts.main')

@section('title', 'Chọn ghế - ' . $showtime->phim->ten_phim)

@section('content')
<div class="min-h-screen bg-[#0F1117]">
  <div class="max-w-7xl mx-auto px-4">
    
    <!-- Movie Info Header -->
    <div class="pt-4 pb-6">
      <a href="{{ route('movie-detail', $showtime->phim->id) }}" class="inline-flex items-center text-[#a6a6b0] hover:text-white mb-4 transition-colors text-sm group">
        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
        Quay lại
      </a>
      
      <div class="bg-[#161A23] border border-[#2A2F3A] rounded-[20px] p-5 flex flex-col md:flex-row gap-5">
        <!-- Movie Poster -->
        <div class="shrink-0">
          <div class="relative group">
            <img 
              src="{{ $showtime->phim->poster ?? asset('images/no-poster.svg') }}" 
              alt="{{ $showtime->phim->ten_phim }}" 
              class="w-32 h-48 md:w-40 md:h-60 object-cover rounded-xl shadow-lg group-hover:scale-105 transition-transform duration-300"
              loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
          </div>
        </div>
        
        <!-- Movie Info -->
        <div class="flex-1 flex flex-col justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">{{ $showtime->phim->ten_phim }}</h1>
            
            <div class="flex flex-wrap items-center gap-4 text-sm text-[#a6a6b0] mb-4">
              <span class="flex items-center gap-2">
                <i class="far fa-calendar text-[#FF784E]"></i>
                <span>{{ $showtime->thoi_gian_bat_dau->format('d/m/Y') }}</span>
              </span>
              <span class="flex items-center gap-2">
                <i class="far fa-clock text-[#FF784E]"></i>
                <span>{{ $showtime->thoi_gian_bat_dau->format('H:i') }}</span>
              </span>
              <span class="flex items-center gap-2">
                <i class="fas fa-door-open text-[#FF784E]"></i>
                <span>{{ $showtime->phongChieu->name ?? $showtime->phongChieu->ten_phong ?? 'Phòng chiếu' }}</span>
              </span>
              @if($showtime->phim->thoi_luong)
                <span class="flex items-center gap-2">
                  <i class="fas fa-film text-[#FF784E]"></i>
                  <span>{{ $showtime->phim->thoi_luong }} phút</span>
                </span>
              @endif
            </div>
            
            @if($showtime->phim->the_loai)
              <div class="flex flex-wrap items-center gap-2 mb-4">
                <i class="fas fa-tags text-[#FF784E] text-sm"></i>
                <div class="flex flex-wrap gap-2">
                  @php
                    $genres = is_string($showtime->phim->the_loai) ? explode(',', $showtime->phim->the_loai) : [];
                  @endphp
                  @foreach(array_slice($genres, 0, 3) as $genre)
                    <span class="px-3 py-1 bg-[#2A2F3A] text-[#E6E7EB] text-xs rounded-full border border-[#3A3F4A]">
                      {{ trim($genre) }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-12 gap-6 pb-6">
      
      <!-- Seat Map Section -->
      <div class="col-span-12 lg:col-span-9">
        <div class="bg-[#161A23] border border-[#2A2F3A] rounded-[20px] p-5">
          
          <!-- Timer & Info Bar -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 p-4 bg-[#151822] rounded-[16px] border border-[#2A2F3A]">
            <div>
              <p class="text-sm text-[#a6a6b0] mb-1">Thời gian giữ ghế</p>
              <div class="flex items-center gap-2">
                <div id="timer" class="text-2xl font-bold text-[#F53003]">
                  <span id="timer-minutes">5</span>:<span id="timer-seconds">00</span>
                </div>
                <span class="text-sm text-[#a6a6b0]">phút</span>
              </div>
            </div>
            @if($existingBooking)
              <div class="text-sm text-yellow-400">
                <i class="fas fa-info-circle mr-1"></i>
                Bạn đang có booking chưa hoàn tất
              </div>
            @endif
          </div>

          <!-- Screen Bar -->
          <div class="text-center mb-8 -mt-2 relative">
            <div class="inline-block bg-gradient-to-r from-[#FF784E]/20 via-[#FFB25E]/30 to-[#FF784E]/20 text-[#E6E7EB] px-12 py-3 rounded-full text-sm font-bold shadow-2xl border border-[#FF784E]/30 relative overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer"></div>
              <span class="relative z-10 flex items-center gap-2">
                <i class="fas fa-film"></i>
                <span>MÀN HÌNH</span>
              </span>
            </div>
            <div class="absolute top-1/2 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[#FF784E]/50 to-transparent -z-10"></div>
          </div>

          <!-- Seat Map -->
          <div class="relative overflow-x-auto mb-4" id="seat-map-container">
            <!-- Column Numbers (Top) -->
            @php
              $rows = $seats->groupBy('so_hang');
              $maxCols = $rows->map(fn($row) => $row->count())->max();
              $colNumbers = range(1, $maxCols);
            @endphp
            <div class="flex items-center gap-1 mb-2 pl-10">
              @foreach($colNumbers as $colNum)
                <div class="w-9 text-center text-xs font-bold text-[#a6a6b0]">{{ $colNum }}</div>
              @endforeach
            </div>
            
            <div class="inline-block min-w-full">
              @foreach($rows as $rowLabel => $rowSeats)
                <div class="flex items-center gap-2 mb-2">
                  <!-- Row Label -->
                  <div class="w-8 text-center text-sm font-bold text-[#a6a6b0] shrink-0">
                    {{ $rowLabel }}
                  </div>
                  
                  <!-- Seats -->
                  <div class="flex gap-1 flex-wrap">
                    @foreach($rowSeats as $seat)
                      @php
                        $status = $seat->booking_status ?? 'available';
                        $seatType = $seat->seatType ?? null;
                        $seatPrice = $seatType->he_so_gia ?? 1;
                        $basePrice = 50000;
                        $price = $basePrice * $seatPrice;
                        $seatNumber = preg_replace('/^[A-Z]/', '', $seat->so_ghe);
                      @endphp
                      
                      <button 
                        type="button"
                        class="seat-btn relative group
                        @if($status === 'booked') seat-sold
                        @elseif($status === 'locked_by_other') seat-locked
                        @elseif($status === 'locked_by_me' || $status === 'selected') seat-selected
                        @elseif($status === 'disabled') seat-disabled
                        @elseif($seatType && strpos(strtolower($seatType->ten_loai), 'vip') !== false) seat-vip
                        @else seat-available
                        @endif"
                        data-seat-id="{{ $seat->id }}"
                        data-seat-code="{{ $seat->so_ghe }}"
                        data-seat-price="{{ $price }}"
                        data-seat-type="{{ $seatType->ten_loai ?? 'Thường' }}"
                        @if(in_array($status, ['booked', 'locked_by_other', 'disabled'])) disabled
                        @endif
                        onclick="toggleSeat({{ $seat->id }}, '{{ $seat->so_ghe }}', {{ $price }}, '{{ $seatType->ten_loai ?? 'Thường' }}')"
                        aria-label="Ghế {{ $seat->so_ghe }} - {{ $status === 'available' ? 'trống' : ($status === 'selected' ? 'đã chọn' : ($status === 'booked' ? 'đã bán' : ($status === 'locked_by_other' ? 'đang được chọn' : 'vô hiệu'))) }}"
                        tabindex="{{ in_array($status, ['booked', 'locked_by_other', 'disabled']) ? '-1' : '0' }}">
                        <span class="seat-number">{{ $seatNumber }}</span>
                        <!-- Enhanced Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 px-4 py-2 bg-gradient-to-r from-[#1a1d24] to-[#2a2d3a] text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-30 shadow-2xl border border-[#FF784E]/30 pointer-events-none">
                          <div class="font-semibold mb-1 text-[#FF784E]">Ghế {{ $seat->so_ghe }}</div>
                          <div class="text-[#E6E7EB]">{{ number_format($price) }}đ</div>
                          <div class="text-[#A0A6B1] text-[10px] mt-1">{{ $seatType->ten_loai ?? 'Thường' }}</div>
                          <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-[#2a2d3a]"></div>
                        </div>
                      </button>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
            
            <!-- Zoom Controls -->
            <div class="absolute bottom-4 right-4 flex flex-col gap-2 z-10">
              <button onclick="zoomIn()" class="w-8 h-8 bg-[#1a1d24] border border-[#262833] rounded text-white hover:bg-[#2a2d3a] transition-colors flex items-center justify-center">
                <i class="fas fa-plus text-xs"></i>
              </button>
              <button onclick="zoomOut()" class="w-8 h-8 bg-[#1a1d24] border border-[#262833] rounded text-white hover:bg-[#2a2d3a] transition-colors flex items-center justify-center">
                <i class="fas fa-minus text-xs"></i>
              </button>
            </div>
          </div>

          <!-- Legend -->
          <div class="flex flex-wrap gap-3 justify-center text-xs pt-4 border-t border-[#2A2F3A]">
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full bg-[#2a2d3a] border border-[#3a3d4a]"></div>
              <span class="text-[#a6a6b0]">Trống</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full bg-gradient-to-br from-yellow-600 to-yellow-700 border border-yellow-500"></div>
              <span class="text-[#a6a6b0]">VIP</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full bg-gradient-to-br from-[#F53003] to-orange-500 border border-[#F53003]"></div>
              <span class="text-[#a6a6b0]">Đã chọn</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full bg-gray-700 opacity-50 border border-gray-600 relative">
                <div class="absolute inset-0 flex items-center justify-center">
                  <i class="fas fa-clock text-[8px] text-gray-400"></i>
                </div>
              </div>
              <span class="text-[#a6a6b0]">Đang chọn</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full bg-red-600 border border-red-700 relative">
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-[8px] text-white font-bold">✕</span>
                </div>
              </div>
              <span class="text-[#a6a6b0]">Đã bán</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full bg-gray-800 opacity-30 border border-gray-700 border-dashed"></div>
              <span class="text-[#a6a6b0]">Vô hiệu</span>
            </div>
          </div>
        </div>

        <!-- Combo & Đồ ăn Section -->
        <div class="mt-6 bg-[#161A23] border border-[#2A2F3A] rounded-[16px] p-5">
          <div class="mb-4">
            <h3 class="text-[16px] font-bold text-[#E6E7EB] mb-1">Combo & Đồ ăn</h3>
            <p class="text-[12px] text-[#A0A6B1]">Chọn thêm để trải nghiệm trọn vẹn</p>
          </div>

          @if($combos && $combos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 overflow-x-auto md:overflow-visible" id="combo-carousel">
              @foreach($combos as $combo)
                @php
                  $isSelected = $selectedCombos->where('id_combo', $combo->id)->first();
                  $quantity = $isSelected ? $isSelected->so_luong : 0;
                @endphp
                <div class="combo-card bg-[#151822] border {{ $isSelected ? 'border-[#FF784E]' : 'border-[#2A2F3A]' }} rounded-[16px] p-4 transition-all duration-300 hover:border-[#FF784E]/50 hover:shadow-xl hover:scale-[1.02] min-w-[280px] md:min-w-0 relative overflow-hidden">
                  @if($isSelected)
                    <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-gradient-to-r from-[#FF784E] to-[#FFB25E] flex items-center justify-center z-10 shadow-lg">
                      <i class="fas fa-check text-white text-xs"></i>
                    </div>
                  @endif
                  
                  <div class="flex items-start gap-4 mb-4">
                    <!-- Combo Image -->
                    <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-[#FF784E] to-[#FFB25E] flex items-center justify-center shrink-0 overflow-hidden shadow-lg">
                      @if($combo->hinh_anh ?? false)
                        <img src="{{ $combo->hinh_anh }}" alt="{{ $combo->ten }}" class="w-full h-full object-cover">
                      @else
                        <div class="flex flex-col items-center justify-center text-white">
                          <i class="fas fa-box text-2xl mb-1"></i>
                          <i class="fas fa-utensils text-sm"></i>
                        </div>
                      @endif
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <h4 class="text-[16px] font-bold text-[#E6E7EB] mb-1">{{ $combo->ten }}</h4>
                      <p class="text-[12px] text-[#A0A6B1] line-clamp-2 mb-2">{{ $combo->mo_ta ?? 'Combo hấp dẫn với bắp và nước' }}</p>
                      
                      <div class="flex items-center gap-2">
                        <span class="text-[18px] font-bold text-[#FF784E]">{{ number_format($combo->gia) }}đ</span>
                        @if($combo->gia_goc && $combo->gia_goc > $combo->gia)
                          <span class="text-[12px] text-[#A0A6B1] line-through">{{ number_format($combo->gia_goc) }}đ</span>
                          <span class="px-2 py-0.5 bg-[#FF784E]/20 text-[#FF784E] text-[10px] font-semibold rounded">-{{ round((1 - $combo->gia / $combo->gia_goc) * 100) }}%</span>
                        @endif
                      </div>
                    </div>
                  </div>
                  
                  <!-- Quantity Selector -->
                  <div class="flex items-center gap-3">
                    @if($quantity > 0)
                      <div class="flex items-center gap-2 bg-[#2A2F3A] rounded-lg p-1">
                        <button 
                          onclick="updateComboQuantity({{ $combo->id }}, {{ $quantity - 1 }}, {{ $combo->gia }}, '{{ $combo->ten }}')"
                          class="w-8 h-8 rounded-md bg-[#1a1d24] hover:bg-[#FF784E] text-[#E6E7EB] hover:text-white transition-all duration-200 flex items-center justify-center font-bold">
                          <i class="fas fa-minus text-xs"></i>
                        </button>
                        <input 
                          type="number" 
                          value="{{ $quantity }}" 
                          min="0" 
                          max="10"
                          onchange="updateComboQuantity({{ $combo->id }}, parseInt(this.value), {{ $combo->gia }}, '{{ $combo->ten }}')"
                          class="w-12 text-center bg-transparent text-[#E6E7EB] font-bold text-[14px] border-none focus:outline-none">
                        <button 
                          onclick="updateComboQuantity({{ $combo->id }}, {{ $quantity + 1 }}, {{ $combo->gia }}, '{{ $combo->ten }}')"
                          class="w-8 h-8 rounded-md bg-[#1a1d24] hover:bg-[#FF784E] text-[#E6E7EB] hover:text-white transition-all duration-200 flex items-center justify-center font-bold">
                          <i class="fas fa-plus text-xs"></i>
                        </button>
                      </div>
                    @else
                      <button 
                        onclick="updateComboQuantity({{ $combo->id }}, 1, {{ $combo->gia }}, '{{ $combo->ten }}')"
                        class="flex-1 py-2.5 rounded-[12px] font-semibold text-[14px] transition-all duration-300 bg-gradient-to-r from-[#FF784E] to-[#FFB25E] hover:from-[#FF6B3D] hover:to-[#FFA54E] text-white shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Thêm vào đơn</span>
                      </button>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-8">
              <i class="fas fa-box-open text-4xl text-[#A0A6B1] mb-3"></i>
              <p class="text-[14px] text-[#A0A6B1]">Hiện tại không có combo nào</p>
            </div>
          @endif
        </div>
      </div>

      <!-- Mini Cart & Summary -->
      <div class="col-span-12 lg:col-span-3">
        <div class="summary-sidebar bg-[#161A23] border border-[#2A2F3A] rounded-[20px] p-5 sticky top-[96px]">
          <!-- Stepper -->
          <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
              <div class="flex-1 flex items-center">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[#FF784E] to-[#FFB25E] flex items-center justify-center text-white text-[14px] font-bold shadow-lg shadow-[#FF784E]/30 relative z-10">
                  <i class="fas fa-check text-xs"></i>
                </div>
                <div class="flex-1 h-1 bg-gradient-to-r from-[#FF784E] to-[#2A2F3A] mx-2 rounded-full"></div>
              </div>
              <div class="flex-1 flex items-center">
                <div class="w-10 h-10 rounded-full bg-[#2A2F3A] border-2 border-[#3A3F4A] flex items-center justify-center text-[#A0A6B1] text-[14px] font-bold relative z-10">
                  2
                </div>
              </div>
            </div>
            <div class="flex items-center gap-4 text-[13px] px-1">
              <span class="text-[#E6E7EB] font-semibold flex items-center gap-2">
                <i class="fas fa-circle text-[4px] text-[#FF784E]"></i>
                <span>Chọn ghế</span>
              </span>
              <span class="text-[#A0A6B1] flex items-center gap-2">
                <i class="fas fa-circle text-[4px] text-[#3A3F4A]"></i>
                <span>Thanh toán</span>
              </span>
            </div>
          </div>

          <!-- Showtime Info -->
          <div class="mb-6 pb-6 border-b border-[#2A2F3A]">
            <div class="flex items-center gap-2 text-[14px] text-[#A0A6B1]">
              <i class="fas fa-calendar-alt text-[#FF784E]"></i>
              <span>
                @php
                  $isToday = $showtime->thoi_gian_bat_dau->isToday();
                  $dateStr = $isToday ? 'Hôm nay' : $showtime->thoi_gian_bat_dau->format('d/m/Y');
                @endphp
                {{ $dateStr }} • 
                <i class="far fa-clock text-[#FF784E] ml-1"></i>
                {{ $showtime->thoi_gian_bat_dau->format('H:i') }} • 
                <i class="fas fa-door-open text-[#FF784E] ml-1"></i>
                {{ $showtime->phongChieu->name ?? $showtime->phongChieu->ten_phong ?? 'Phòng chiếu' }}
              </span>
            </div>
          </div>

          <!-- Selected Seats -->
          <div class="mb-6">
            <h4 class="text-[14px] font-semibold text-[#E6E7EB] mb-3 flex items-center gap-2">
              <i class="fas fa-chair text-[#FF784E]"></i>
              <span>Ghế</span>
            </h4>
            <div id="selected-seats" class="space-y-2 min-h-[60px]">
              @if($existingBooking && $existingBooking->chiTietDatVe->count() > 0)
                @foreach($existingBooking->chiTietDatVe as $detail)
                  @php
                    $seat = $detail->ghe;
                    $price = $detail->gia;
                  @endphp
                  <div class="flex items-center justify-between text-[14px] animate-fade-in" data-seat-id="{{ $seat->id }}">
                    <span class="text-[#E6E7EB] flex items-center gap-2">
                      <i class="fas fa-circle text-[6px] text-[#FF784E]"></i>
                      {{ $seat->so_ghe }}
                    </span>
                    <span class="text-[#A0A6B1] price-value">{{ number_format($price) }}đ</span>
                  </div>
                @endforeach
              @else
                <p class="text-[14px] text-[#A0A6B1]">–</p>
              @endif
            </div>
          </div>

          <!-- Combo Summary -->
          <div class="mb-6 pb-6 border-b border-[#2A2F3A]">
            <h4 class="text-[14px] font-semibold text-[#E6E7EB] mb-3 flex items-center gap-2">
              <i class="fas fa-box text-[#FF784E]"></i>
              <span>Combo</span>
            </h4>
            <div id="selected-combos-summary" class="space-y-2">
              @if($selectedCombos && $selectedCombos->count() > 0)
                @foreach($selectedCombos as $selected)
                  <div class="flex items-center justify-between text-[14px] animate-fade-in">
                    <span class="text-[#E6E7EB] flex items-center gap-2">
                      <i class="fas fa-circle text-[6px] text-[#FF784E]"></i>
                      {{ $selected->combo->ten }} x{{ $selected->so_luong }}
                    </span>
                    <span class="text-[#A0A6B1] price-value">{{ number_format($selected->gia_ap_dung * $selected->so_luong) }}đ</span>
                  </div>
                @endforeach
              @else
                <p class="text-[14px] text-[#A0A6B1]">–</p>
              @endif
            </div>
          </div>

          <!-- Price Summary -->
          <div class="mb-6 space-y-3">
            <div class="flex justify-between text-[14px] items-center">
              <span class="text-[#A0A6B1] flex items-center gap-2">
                <i class="fas fa-ticket-alt text-[#FF784E] text-xs"></i>
                <span>Giá vé</span>
              </span>
              <span class="text-[#E6E7EB] font-semibold price-value" id="seat-total-price">
                {{ $existingBooking ? number_format($existingBooking->chiTietDatVe->sum('gia')) : 0 }}đ
              </span>
            </div>
            <div class="flex justify-between text-[14px] items-center">
              <span class="text-[#A0A6B1] flex items-center gap-2">
                <i class="fas fa-utensils text-[#FF784E] text-xs"></i>
                <span>Combo</span>
              </span>
              <span class="text-[#E6E7EB] font-semibold price-value" id="combo-total-price">
                {{ $selectedCombos ? number_format($selectedCombos->sum(function($item) { return $item->gia_ap_dung * $item->so_luong; })) : 0 }}đ
              </span>
            </div>
          </div>

          <!-- Total -->
          <div class="mb-6 pt-6 border-t border-[#2A2F3A]">
            <div class="flex justify-between items-center">
              <span class="text-[20px] font-bold text-[#E6E7EB] flex items-center gap-2">
                <i class="fas fa-receipt text-[#FF784E]"></i>
                <span>Tổng</span>
              </span>
              <span id="total-price" class="text-[22px] font-bold text-[#FF784E] price-value">
                {{ $existingBooking ? number_format($existingBooking->tong_tien + ($selectedCombos ? $selectedCombos->sum(function($item) { return $item->gia_ap_dung * $item->so_luong; }) : 0)) : 0 }}đ
              </span>
            </div>
          </div>

          <!-- Lock Timer -->
          <div id="lock-timer" class="mb-4 {{ $existingBooking ? '' : 'hidden' }}">
            <div class="flex items-center gap-2 text-[12px] px-3 py-2 rounded-lg bg-[#2A2F3A]/50">
              <i class="fas fa-clock text-[#FF784E]"></i>
              <span class="text-[#A0A6B1]">Thời gian giữ ghế:</span>
              <span id="timer-display" class="font-semibold text-[#E6E7EB]">
                <span id="timer-minutes">5</span>:<span id="timer-seconds">00</span>
              </span>
            </div>
          </div>

          <!-- CTA Button -->
          <button 
            id="continue-btn"
            onclick="continueToAddons()"
            class="w-full h-[50px] bg-gradient-to-r from-[#FF784E] to-[#FFB25E] text-white rounded-[12px] font-bold text-[15px] hover:shadow-2xl hover:shadow-[#FF784E]/50 transition-all duration-300 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-3 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-r from-[#FF6B3D] to-[#FFA54E] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <span class="relative z-10 flex items-center gap-2">
              <i class="fas fa-credit-card"></i>
              <span>Tiếp tục thanh toán</span>
              <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </span>
          </button>

          <!-- Note -->
          <p class="text-[12px] text-[#A0A6B1] text-center mt-4">
            Chỉ hiển thị khi bạn chọn ghế…
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
  <div class="bg-[#151822] border border-[#262833] rounded-xl p-6">
    <div class="flex items-center space-x-3">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#F53003]"></div>
      <span class="text-white">Đang xử lý...</span>
    </div>
  </div>
</div>

<script>
let selectedSeats = new Map();
let lockExpiresAt = null;
let timerInterval = null;
let refreshInterval = null;
const showId = {{ $showtime->id }};
const bookingId = {{ $existingBooking ? $existingBooking->id : 'null' }};

@if($existingBooking)
  @foreach($existingBooking->chiTietDatVe as $detail)
    selectedSeats.set({{ $detail->ghe->id }}, {
      code: '{{ $detail->ghe->so_ghe }}',
      price: {{ $detail->gia }},
      type: '{{ $detail->ghe->seatType->ten_loai ?? 'Thường' }}'
    });
  @endforeach
  lockExpiresAt = {{ $existingBooking->created_at->addMinutes(5)->timestamp }};
  startTimer();
@endif

function toggleSeat(seatId, seatCode, price, type) {
  if (selectedSeats.has(seatId)) {
    removeSeat(seatId);
  } else {
    addSeat(seatId, seatCode, price, type);
  }
}

function addSeat(seatId, seatCode, price, type) {
  selectedSeats.set(seatId, { code: seatCode, price: price, type: type });
  updateUI();
  lockSeats();
}

function removeSeat(seatId) {
  selectedSeats.delete(seatId);
  updateUI();
  unlockSeats([seatId]);
}

let selectedCombos = new Map();
@if($selectedCombos)
  @foreach($selectedCombos as $selected)
    selectedCombos.set({{ $selected->id_combo }}, {
      name: '{{ $selected->combo->ten }}',
      price: {{ $selected->gia_ap_dung }},
      quantity: {{ $selected->so_luong }}
    });
  @endforeach
@endif

function animatePriceUpdate(element, newValue) {
  element.style.transform = 'scale(1.2)';
  element.style.color = '#FF784E';
  setTimeout(() => {
    element.textContent = newValue;
    element.style.transform = 'scale(1)';
    setTimeout(() => {
      element.style.color = '';
    }, 300);
  }, 150);
}

function updateUI() {
  // Update seat buttons
  document.querySelectorAll('.seat-btn').forEach(btn => {
    const id = parseInt(btn.dataset.seatId);
    if (selectedSeats.has(id)) {
      btn.classList.remove('seat-available', 'seat-vip');
      btn.classList.add('seat-selected');
    } else if (!btn.disabled) {
      btn.classList.remove('seat-selected');
      if (btn.classList.contains('seat-vip')) {
        btn.classList.add('seat-vip');
      } else {
        btn.classList.add('seat-available');
      }
    }
  });

  // Update seats summary
  const seatsSummary = document.getElementById('selected-seats');
  const totalSeats = selectedSeats.size;
  const seatTotalPrice = Array.from(selectedSeats.values()).reduce((sum, seat) => sum + seat.price, 0);

  if (totalSeats === 0) {
    seatsSummary.innerHTML = '<p class="text-[14px] text-[#A0A6B1]">–</p>';
  } else {
    seatsSummary.innerHTML = Array.from(selectedSeats.entries()).map(([id, seat]) => `
      <div class="flex items-center justify-between text-[14px] animate-fade-in" data-seat-id="${id}">
        <span class="text-[#E6E7EB] flex items-center gap-2">
          <i class="fas fa-circle text-[6px] text-[#FF784E]"></i>
          ${seat.code}
        </span>
        <span class="text-[#A0A6B1] price-value">${formatPrice(seat.price)}đ</span>
      </div>
    `).join('');
  }

  // Update combo summary
  updateComboSummary();
  
  // Update prices with animation
  const comboTotalPrice = Array.from(selectedCombos.values()).reduce((sum, combo) => sum + (combo.price * combo.quantity), 0);
  const seatPriceEl = document.getElementById('seat-total-price');
  const comboPriceEl = document.getElementById('combo-total-price');
  const totalPriceEl = document.getElementById('total-price');
  
  animatePriceUpdate(seatPriceEl, formatPrice(seatTotalPrice) + 'đ');
  animatePriceUpdate(comboPriceEl, formatPrice(comboTotalPrice) + 'đ');
  animatePriceUpdate(totalPriceEl, formatPrice(seatTotalPrice + comboTotalPrice) + 'đ');
  
  // Update lock timer visibility
  const lockTimer = document.getElementById('lock-timer');
  if (totalSeats > 0) {
    lockTimer.classList.remove('hidden');
  } else {
    lockTimer.classList.add('hidden');
  }
  
  const continueBtn = document.getElementById('continue-btn');
  if (totalSeats > 0) {
    continueBtn.disabled = false;
  } else {
    continueBtn.disabled = true;
  }
  
  // Auto scroll to summary on first seat selection
  if (totalSeats === 1) {
    setTimeout(() => {
      const summarySidebar = document.querySelector('.summary-sidebar');
      if (summarySidebar) {
        summarySidebar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }, 300);
  }
}

function updateComboSummary() {
  const comboSummary = document.getElementById('selected-combos-summary');
  if (selectedCombos.size === 0) {
    comboSummary.innerHTML = '<p class="text-[14px] text-[#A0A6B1]">–</p>';
  } else {
    comboSummary.innerHTML = Array.from(selectedCombos.values()).map(combo => `
      <div class="flex items-center justify-between text-[14px] animate-fade-in">
        <span class="text-[#E6E7EB] flex items-center gap-2">
          <i class="fas fa-circle text-[6px] text-[#FF784E]"></i>
          ${combo.name} x${combo.quantity}
        </span>
        <span class="text-[#A0A6B1] price-value">${formatPrice(combo.price * combo.quantity)}đ</span>
      </div>
    `).join('');
  }
}

function updateComboQuantity(comboId, quantity, price, name) {
  quantity = Math.max(0, Math.min(10, parseInt(quantity) || 0));
  
  if (quantity === 0) {
    selectedCombos.delete(comboId);
  } else {
    selectedCombos.set(comboId, {
      name: name,
      price: price,
      quantity: quantity
    });
  }
  
  // Update combo card UI
  const comboCard = document.querySelector(`[onclick*="updateComboQuantity(${comboId}"]`)?.closest('.combo-card');
  if (comboCard) {
    const quantitySelector = comboCard.querySelector('.flex.items-center.gap-2.bg-\\[\\#2A2F3A\\]');
    const addButton = comboCard.querySelector('button[onclick*="updateComboQuantity"]');
    
    if (quantity > 0) {
      comboCard.classList.add('border-[#FF784E]');
      comboCard.classList.remove('border-[#2A2F3A]');
      
      // Show quantity selector
      if (!quantitySelector) {
        const selectorHTML = `
          <div class="flex items-center gap-2 bg-[#2A2F3A] rounded-lg p-1">
            <button onclick="updateComboQuantity(${comboId}, ${quantity - 1}, ${price}, '${name}')" class="w-8 h-8 rounded-md bg-[#1a1d24] hover:bg-[#FF784E] text-[#E6E7EB] hover:text-white transition-all duration-200 flex items-center justify-center font-bold">
              <i class="fas fa-minus text-xs"></i>
            </button>
            <input type="number" value="${quantity}" min="0" max="10" onchange="updateComboQuantity(${comboId}, parseInt(this.value), ${price}, '${name}')" class="w-12 text-center bg-transparent text-[#E6E7EB] font-bold text-[14px] border-none focus:outline-none">
            <button onclick="updateComboQuantity(${comboId}, ${quantity + 1}, ${price}, '${name}')" class="w-8 h-8 rounded-md bg-[#1a1d24] hover:bg-[#FF784E] text-[#E6E7EB] hover:text-white transition-all duration-200 flex items-center justify-center font-bold">
              <i class="fas fa-plus text-xs"></i>
            </button>
          </div>
        `;
        if (addButton) {
          addButton.outerHTML = selectorHTML;
        }
      } else {
        const input = quantitySelector.querySelector('input');
        if (input) input.value = quantity;
      }
      
      // Show check icon
      if (!comboCard.querySelector('.absolute.top-2.right-2')) {
        const checkIcon = document.createElement('div');
        checkIcon.className = 'absolute top-2 right-2 w-6 h-6 rounded-full bg-gradient-to-r from-[#FF784E] to-[#FFB25E] flex items-center justify-center z-10 shadow-lg';
        checkIcon.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
        comboCard.appendChild(checkIcon);
      }
    } else {
      comboCard.classList.remove('border-[#FF784E]');
      comboCard.classList.add('border-[#2A2F3A]');
      
      // Show add button
      if (quantitySelector) {
        quantitySelector.outerHTML = `
          <button onclick="updateComboQuantity(${comboId}, 1, ${price}, '${name}')" class="flex-1 py-2.5 rounded-[12px] font-semibold text-[14px] transition-all duration-300 bg-gradient-to-r from-[#FF784E] to-[#FFB25E] hover:from-[#FF6B3D] hover:to-[#FFA54E] text-white shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Thêm vào đơn</span>
          </button>
        `;
      }
      
      // Remove check icon
      const checkIcon = comboCard.querySelector('.absolute.top-2.right-2');
      if (checkIcon) checkIcon.remove();
    }
  }
  
  updateUI();
}

function toggleCombo(comboId, price, name) {
  const current = selectedCombos.get(comboId);
  updateComboQuantity(comboId, current ? 0 : 1, price, name);
}

async function lockSeats() {
  if (selectedSeats.size === 0) return;
  
  const seatIds = Array.from(selectedSeats.keys());
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  try {
    const response = await fetch(`/shows/${showId}/seats/lock`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ seat_ids: seatIds })
    });

    const data = await response.json();
    
    if (data.success) {
      if (data.booking_id) {
        window.bookingId = data.booking_id;
      }
      if (data.expires_at) {
        lockExpiresAt = data.expires_at;
        startTimer();
      }
      startRefresh();
    } else {
      alert(data.message || 'Không thể giữ ghế');
      // Remove conflicting seats
      if (data.conflicts) {
        data.conflicts.forEach(conflict => {
          removeSeat(conflict.seat_id);
        });
      }
    }
  } catch (error) {
    console.error('Error locking seats:', error);
  }
}

async function unlockSeats(seatIds) {
  if (!seatIds || seatIds.length === 0) return;
  
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  try {
    await fetch(`/shows/${showId}/seats/unlock`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ seat_ids: seatIds })
    });
  } catch (error) {
    console.error('Error unlocking seats:', error);
  }
}

function startTimer() {
  if (timerInterval) clearInterval(timerInterval);
  
  timerInterval = setInterval(() => {
    if (!lockExpiresAt) return;
    
    const now = Math.floor(Date.now() / 1000);
    const remaining = lockExpiresAt - now;
    
    if (remaining <= 0) {
      clearInterval(timerInterval);
      alert('Thời gian giữ ghế đã hết hạn. Vui lòng chọn lại.');
      window.location.reload();
      return;
    }
    
    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;
    
    const minutesEl = document.getElementById('timer-minutes');
    const secondsEl = document.getElementById('timer-seconds');
    const timerDisplay = document.getElementById('timer-display');
    
    if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
    if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
    
    // Change color when < 30 seconds
    if (remaining < 30 && timerDisplay) {
      timerDisplay.classList.add('text-[#FF784E]');
      timerDisplay.classList.remove('text-[#E6E7EB]');
    } else if (timerDisplay) {
      timerDisplay.classList.remove('text-[#FF784E]');
      timerDisplay.classList.add('text-[#E6E7EB]');
    }
  }, 1000);
}

function startRefresh() {
  if (refreshInterval) clearInterval(refreshInterval);
  
  refreshInterval = setInterval(async () => {
    try {
      const response = await fetch(`/shows/${showId}/seats/refresh`);
      const data = await response.json();
      
      if (data.success && data.seats) {
        // Update seat statuses
        Object.entries(data.seats).forEach(([seatId, status]) => {
          const btn = document.querySelector(`[data-seat-id="${seatId}"]`);
          if (!btn) return;
          
          const id = parseInt(seatId);
          if (status === 'booked' || (status === 'locked_by_other' && !selectedSeats.has(id))) {
            btn.disabled = true;
            btn.classList.remove('bg-green-600', 'bg-yellow-500', 'hover:bg-green-700', 'hover:bg-yellow-600');
            btn.classList.add(status === 'booked' ? 'bg-red-600' : 'bg-gray-700', 'opacity-50');
          }
        });
      }
    } catch (error) {
      console.error('Error refreshing seats:', error);
    }
  }, 5000); // Refresh every 5 seconds
}

function continueToAddons() {
  if (selectedSeats.size === 0) {
    alert('Vui lòng chọn ít nhất một ghế');
    return;
  }
  
  const currentBookingId = window.bookingId || bookingId;
  if (!currentBookingId) {
    alert('Có lỗi xảy ra. Vui lòng thử lại.');
    return;
  }
  
  window.location.href = `/bookings/${currentBookingId}/addons`;
}

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN').format(price);
}

// Zoom functions
let currentZoom = 1;
const minZoom = 0.7;
const maxZoom = 1.5;
const zoomStep = 0.1;

function zoomIn() {
  const container = document.getElementById('seat-map-container');
  if (!container) return;
  
  currentZoom = Math.min(currentZoom + zoomStep, maxZoom);
  container.style.transform = `scale(${currentZoom})`;
  container.style.transformOrigin = 'top center';
}

function zoomOut() {
  const container = document.getElementById('seat-map-container');
  if (!container) return;
  
  currentZoom = Math.max(currentZoom - zoomStep, minZoom);
  container.style.transform = `scale(${currentZoom})`;
  container.style.transformOrigin = 'top center';
}

// Keyboard navigation for seat map
let currentFocusedSeat = null;
const seatButtons = [];

function initKeyboardNavigation() {
  const seats = document.querySelectorAll('.seat-btn:not([disabled])');
  seats.forEach((seat, index) => {
    seatButtons.push(seat);
    seat.addEventListener('keydown', (e) => {
      const currentIndex = seatButtons.indexOf(seat);
      let nextIndex = currentIndex;
      
      switch(e.key) {
        case 'ArrowRight':
          nextIndex = Math.min(currentIndex + 1, seatButtons.length - 1);
          e.preventDefault();
          break;
        case 'ArrowLeft':
          nextIndex = Math.max(currentIndex - 1, 0);
          e.preventDefault();
          break;
        case 'ArrowDown':
          // Find next seat in same column (approximate)
          const currentRow = seat.closest('.flex.items-center');
          const nextRow = currentRow?.nextElementSibling;
          if (nextRow) {
            const nextSeat = nextRow.querySelector('.seat-btn:not([disabled])');
            if (nextSeat) {
              nextIndex = seatButtons.indexOf(nextSeat);
            }
          }
          e.preventDefault();
          break;
        case 'ArrowUp':
          const prevRow = seat.closest('.flex.items-center')?.previousElementSibling;
          if (prevRow) {
            const prevSeat = prevRow.querySelector('.seat-btn:not([disabled])');
            if (prevSeat) {
              nextIndex = seatButtons.indexOf(prevSeat);
            }
          }
          e.preventDefault();
          break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          seat.click();
          return;
      }
      
      if (nextIndex !== currentIndex && seatButtons[nextIndex]) {
        seatButtons[nextIndex].focus();
      }
    });
  });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  updateUI();
  startRefresh();
  initKeyboardNavigation();
  
  // Mobile pinch zoom support
  const seatMapContainer = document.getElementById('seat-map-container');
  if (seatMapContainer && 'ontouchstart' in window) {
    let lastDistance = 0;
    
    seatMapContainer.addEventListener('touchstart', (e) => {
      if (e.touches.length === 2) {
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        lastDistance = Math.hypot(
          touch2.clientX - touch1.clientX,
          touch2.clientY - touch1.clientY
        );
      }
    });
    
    seatMapContainer.addEventListener('touchmove', (e) => {
      if (e.touches.length === 2) {
        e.preventDefault();
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        const distance = Math.hypot(
          touch2.clientX - touch1.clientX,
          touch2.clientY - touch1.clientY
        );
        
        if (lastDistance > 0) {
          const scale = distance / lastDistance;
          currentZoom = Math.max(minZoom, Math.min(maxZoom, currentZoom * scale));
          seatMapContainer.style.transform = `scale(${currentZoom})`;
          seatMapContainer.style.transformOrigin = 'top center';
        }
        lastDistance = distance;
      }
    });
  }
  
  // Mobile combo carousel
  const comboCarousel = document.getElementById('combo-carousel');
  if (comboCarousel && window.innerWidth < 768) {
    let isDown = false;
    let startX;
    let scrollLeft;
    
    comboCarousel.addEventListener('mousedown', (e) => {
      isDown = true;
      comboCarousel.style.cursor = 'grabbing';
      startX = e.pageX - comboCarousel.offsetLeft;
      scrollLeft = comboCarousel.scrollLeft;
    });
    
    comboCarousel.addEventListener('mouseleave', () => {
      isDown = false;
      comboCarousel.style.cursor = 'grab';
    });
    
    comboCarousel.addEventListener('mouseup', () => {
      isDown = false;
      comboCarousel.style.cursor = 'grab';
    });
    
    comboCarousel.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - comboCarousel.offsetLeft;
      const walk = (x - startX) * 2;
      comboCarousel.scrollLeft = scrollLeft - walk;
    });
  }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
  if (refreshInterval) clearInterval(refreshInterval);
  if (timerInterval) clearInterval(timerInterval);
});
</script>
@endsection

