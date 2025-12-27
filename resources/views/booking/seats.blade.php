@extends('layouts.app')

@section('title', 'Chọn ghế - ' . $showtime->phim->ten_phim)

@section('content')
<div class="min-h-screen bg-[#0F1117]">
  <div class="max-w-7xl mx-auto px-4">
    <style>
      /* Seat map visual refinements */
      #seat-map-container .seat-btn-enhanced{width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:#1a1d24;border:1px solid #2a2f3a;transition:transform .15s ease, box-shadow .15s ease}
      #seat-map-container .seat-btn-enhanced.seat-available:hover{transform:translateY(-2px);box-shadow:0 8px 18px rgba(255,120,78,.18)}
      #seat-map-container .seat-btn-enhanced.seat-vip{background:linear-gradient(180deg,#3a2a00,#251a00);border-color:#6b4e00}
      #seat-map-container .seat-btn-enhanced.seat-selected{background:linear-gradient(180deg,#FF784E,#FFB25E);color:#fff;border-color:#FF8A5E}
      #seat-map-container .seat-btn-enhanced.seat-sold{background:#dc2626;border-color:#b91c1c;color:#fee2e2;opacity:1; cursor: not-allowed;}
      #seat-map-container .seat-btn-enhanced.seat-locked{background:#334155;border-color:#475569;color:#cbd5e1}
      #seat-map-container .seat-btn-enhanced.seat-disabled{background:#1f2937;border-color:#374151;opacity:.5}
      #seat-map-container .seat-btn-enhanced.seat-couple{background:linear-gradient(180deg,#ec4899,#f43f5e);border-color:#f472b6;color:#fff;width:40px}
      #seat-map-container .seat-number{font-size:12px;font-weight:700;color:#E6E7EB}
      /* Row/column labels */
      #seat-map-container .col-label{width:40px;height:40px;border-radius:10px}
      #seat-map-container .col-label-text{font-size:13px;font-weight:700}
      #seat-map-container .row-label{width:40px;height:40px;border-radius:12px}
      /* Grid spacing */
      #seat-map-container .seat-row-gap{gap:4px}
      @media (min-width: 1024px){
        #seat-map-container .seat-row-gap{gap:6px}
      }
    </style>
    
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
            <x-image 
              src="{{ $showtime->phim->poster_url ?? $showtime->phim->poster ?? asset('images/no-poster.svg') }}" 
              alt="{{ $showtime->phim->ten_phim }}"
              aspectRatio="2/3"
              class="w-32 md:w-40 rounded-xl shadow-lg"
              quality="high"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10"></div>
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
                  <span id="timer-minutes">10</span>:<span id="timer-seconds">00</span>
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

          <!-- Enhanced Screen Visualization -->
          <div class="text-center mb-8 relative">
            <!-- Screen 3D Effect -->
            <div class="relative mx-auto max-w-4xl">
              <!-- Screen Shadow -->
              <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-transparent rounded-t-full blur-2xl transform translate-y-8"></div>
              
              <!-- Main Screen -->
              <div class="relative bg-gradient-to-b from-[#1a1a1a] via-[#0a0a0a] to-[#1a1a1a] rounded-t-[40px] border-2 border-[#FF784E]/40 shadow-[0_20px_60px_rgba(255,120,78,0.3)] overflow-hidden">
                <!-- Screen Reflection -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/5 via-transparent to-transparent"></div>
                
                <!-- Screen Content -->
                <div class="relative px-12 py-6">
                  <div class="flex items-center justify-center gap-3 mb-2">
                    <div class="w-2 h-2 rounded-full bg-[#FF784E] animate-pulse"></div>
                    <span class="text-[#FF784E] font-bold text-lg tracking-wider">MÀN HÌNH</span>
                    <div class="w-2 h-2 rounded-full bg-[#FF784E] animate-pulse"></div>
                  </div>
                  <div class="h-1 bg-gradient-to-r from-transparent via-[#FF784E]/60 to-transparent rounded-full"></div>
                </div>
                
                <!-- Screen Bottom Edge -->
                <div class="absolute bottom-0 left-0 right-0 h-2 bg-gradient-to-b from-[#FF784E]/20 to-transparent"></div>
              </div>
              
              <!-- Screen Stand -->
              <div class="mx-auto mt-2 w-32 h-4 bg-gradient-to-b from-[#2a2a2a] to-[#1a1a1a] rounded-b-lg border border-[#FF784E]/20"></div>
            </div>
            
            
          </div>

          <!-- Seat Map Container -->
          <div class="relative mb-6" id="seat-map-container">
            <!-- Column Numbers (Top) - Enhanced -->
            @php
              // Group seats by row và sắp xếp lại
              $rows = $seats->groupBy('so_hang')->sortKeys();
              
              // Tính toán số cột tối đa và tạo ma trận
              $maxCols = 0;
              $seatMatrix = [];
              
              foreach($rows as $rowLabel => $rowSeats) {
                // Sắp xếp ghế trong hàng theo số
                $sortedSeats = $rowSeats->sortBy(function($seat) {
                  // Extract number from seat code (A1 -> 1, B12 -> 12)
                  preg_match('/(\d+)/', $seat->so_ghe, $matches);
                  return (int)($matches[1] ?? 0);
                });
                
                $seatMatrix[$rowLabel] = $sortedSeats->values();
                $maxCols = max($maxCols, $sortedSeats->count());
              }
              
              // Detect VIP rows
              $vipRows = [];
              foreach($seatMatrix as $rowLabel => $rowSeats) {
                $firstSeat = $rowSeats->first();
                if($firstSeat && $firstSeat->seatType && strpos(strtolower($firstSeat->seatType->ten_loai ?? ''), 'vip') !== false) {
                  $vipRows[] = $rowLabel;
                }
              }
              
              // Tạo danh sách số cột (với spacing cho lối đi)
              $colNumbers = [];
              $aislePositions = []; // Không tạo lối đi, khoảng cách các cột bằng nhau
              for ($i = 1; $i <= $maxCols; $i++) {
                $colNumbers[] = $i;
              }
            @endphp
            
            
            
            <!-- Seat Grid - Ma trận cải tiến -->
            <div id="seat-map-scale" class="inline-block space-y-4" style="transform-origin: top center; display:block; margin: 0 auto;">
              @foreach($seatMatrix as $rowLabel => $rowSeats)
                @php
                  $rowIndex = array_search($rowLabel, array_keys($seatMatrix));
                  
                  $isVipRow = in_array($rowLabel, $vipRows);
                  $rowIndex = array_search($rowLabel, array_keys($seatMatrix));
                  
                  // Tạo map ghế theo vị trí để align đúng cột
                  // Gom nhóm ghế đôi theo pair_id
                  $seatMap = [];
                  $processedSeats = [];
                  foreach($rowSeats as $seat) {
                    // Skip nếu ghế này đã được xử lý như một phần của cặp
                    if (in_array($seat->id, $processedSeats)) {
                      continue;
                    }
                    
                    preg_match('/(\d+)/', $seat->so_ghe, $matches);
                    $colNum = (int)($matches[1] ?? 0);
                    
                    // Kiểm tra nếu là ghế đôi
                    $isDouble = ($seat->is_double ?? false) || ($seat->id_loai == 3);
                    $pairId = $seat->pair_id ?? null;
                    
                    if ($isDouble && $pairId) {
                      // Tìm ghế cặp
                      $pairSeat = $rowSeats->firstWhere('id', $pairId);
                      if ($pairSeat) {
                        // Gom nhóm 2 ghế thành 1 entry
                        $seatMap[$colNum] = [
                          'type' => 'couple',
                          'seat1' => $seat,
                          'seat2' => $pairSeat,
                          'col' => $colNum
                        ];
                        $processedSeats[] = $seat->id;
                        $processedSeats[] = $pairSeat->id;
                      } else {
                        // Không tìm thấy ghế cặp, hiển thị như ghế thường
                        $seatMap[$colNum] = $seat;
                      }
                    } else {
                      // Ghế thường
                      $seatMap[$colNum] = $seat;
                    }
                  }
                @endphp
                
                <!-- Row Container -->
                <div class="relative seat-row-container" data-row="{{ $rowLabel }}">
                  <!-- VIP Row Badge -->
                  @if($isVipRow)
                    <div class="absolute -left-20 top-1/2 transform -translate-y-1/2 flex items-center gap-2 z-10">
                      <div class="px-3 py-1 bg-gradient-to-r from-yellow-600/20 to-yellow-700/20 border border-yellow-500/50 rounded-full">
                        <span class="text-yellow-400 text-xs font-bold flex items-center gap-1">
                          <i class="fas fa-crown text-[10px]"></i>
                          <span>VIP</span>
                        </span>
                      </div>
                    </div>
                  @endif
                  
                  <!-- Row Separator (for every 3 rows) -->
                  @if($rowIndex > 0 && $rowIndex % 3 == 0)
                    <div class="absolute -left-4 -right-4 top-0 h-px bg-gradient-to-r from-transparent via-[#FF784E]/30 to-transparent mb-3"></div>
                  @endif
                  
                  <div class="flex items-center gap-4">
                    <!-- Row Label -->
                    <div class="row-label flex items-center justify-center text-base font-bold text-[#E6E7EB] bg-gradient-to-br from-[#2a2d3a] to-[#1a1d24] rounded-lg border border-[#3a3d4a] shadow-lg shrink-0 {{ $isVipRow ? 'ring-2 ring-yellow-500/50' : '' }}">
                      {{ $rowLabel }}
                    </div>
                    
                    <!-- Seats Row - Ma trận với spacing -->
                    <div class="flex seat-row-gap items-center">
                      @for($col = 1; $col <= $maxCols; $col++)
                        
                        @if(isset($seatMap[$col]))
                        @php
                            $seatData = $seatMap[$col];
                            $isCouplePair = is_array($seatData) && isset($seatData['type']) && $seatData['type'] === 'couple';
                            
                            if ($isCouplePair) {
                              $seat1 = $seatData['seat1'];
                              $seat2 = $seatData['seat2'];
                              $seatType1 = $seat1->seatType ?? $seat1->loaiGhe ?? null;
                              $seatType2 = $seat2->seatType ?? $seat2->loaiGhe ?? null;
                              $basePrice = 100000;
                              $heso1 = (float)($seatType1->he_so_gia ?? 1);
                              $heso2 = (float)($seatType2->he_so_gia ?? 1);
                              $price1 = $basePrice * $heso1;
                              $price2 = $basePrice * $heso2;
                              $totalPrice = $price1 + $price2;
                              $status1 = $seat1->booking_status ?? 'available';
                              $status2 = $seat2->booking_status ?? 'available';
                              $status = ($status1 === 'booked' || $status2 === 'booked') ? 'booked' : 
                                        (($status1 === 'locked_by_other' || $status2 === 'locked_by_other') ? 'locked_by_other' : 
                                        (($status1 === 'selected' || $status2 === 'selected' || $status1 === 'locked_by_me' || $status2 === 'locked_by_me') ? 'selected' : 'available'));
                              $seatNumber1 = preg_replace('/^[A-Z]/', '', $seat1->so_ghe);
                              $seatNumber2 = preg_replace('/^[A-Z]/', '', $seat2->so_ghe);
                              $seatCodes = $seat1->so_ghe . '-' . $seat2->so_ghe;
                            } else {
                              $seat = $seatData;
                              $status = $seat->booking_status ?? 'available';
                              $seatType = $seat->seatType ?? $seat->loaiGhe ?? null;
                              
                              $isVipSeat = ($seat->id_loai == 2) || ($seatType && strpos(strtolower($seatType->ten_loai ?? ''), 'vip') !== false);
                              $isCoupleSeat = ($seat->id_loai == 3) || ($seat->is_double ?? false) || ($seatType && (
                                strpos(strtolower($seatType->ten_loai ?? ''), 'đôi') !== false ||
                                strpos(strtolower($seatType->ten_loai ?? ''), 'doi') !== false ||
                                strpos(strtolower($seatType->ten_loai ?? ''), 'couple') !== false
                              ));
                              $basePrice = 100000;
                              $heso = (float)($seatType->he_so_gia ?? 1);
                              $price = $basePrice * $heso;
                              $seatNumber = preg_replace('/^[A-Z]/', '', $seat->so_ghe);
                            }
                        @endphp
                        
                        @if($isCouplePair)
                          {{-- Couple Seat Pair (render compact like single) --}}
                          <button 
                            type="button"
                            class="seat-btn-enhanced seat-couple relative group
                            @if($status === 'booked') seat-sold
                            @elseif($status === 'locked_by_other') seat-locked
                            @elseif($status === 'selected') seat-selected
                            @elseif($status === 'disabled') seat-disabled
                            @endif"
                            data-seat-id="{{ $seat1->id }}"
                            data-seat-id-2="{{ $seat2->id }}"
                            data-seat-code="{{ $seat1->so_ghe }}"
                            data-seat-code-2="{{ $seat2->so_ghe }}"
                            data-seat-price="{{ $totalPrice }}"
                            data-seat-price-1="{{ $price1 }}"
                            data-seat-price-2="{{ $price2 }}"
                            data-seat-type="Ghế Đôi"
                            data-is-couple="true"
                            @if(in_array($status, ['booked', 'locked_by_other', 'disabled'])) disabled
                            @endif
                            onclick="toggleCoupleSeat({{ $seat1->id }}, '{{ $seat1->so_ghe }}', {{ $price1 }}, {{ $seat2->id }}, '{{ $seat2->so_ghe }}', {{ $price2 }})"
                            aria-label="Ghế đôi {{ $seatCodes }}"
                            tabindex="{{ in_array($status, ['booked', 'locked_by_other', 'disabled']) ? '-1' : '0' }}">
                            
                            <!-- Seat Glow Effect -->
                            <div class="absolute inset-0 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-sm
                              @if($status === 'selected') bg-[#FF784E]/30
                              @else bg-pink-500/20
                              @endif"></div>
                            
                            <!-- Seat Content (compact) -->
                            <div class="relative z-10 flex items-center justify-center h-full w-full">
                              <i class="fas fa-heart text-[10px] text-pink-200"></i>
                            </div>
                            
                            <!-- Seat Border Glow -->
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300
                              @if($status === 'selected') border-[#FF784E]/50
                              @else border-pink-400/50
                              @endif"></div>
                            
                            <!-- Enhanced Tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-4 px-4 py-3 bg-gradient-to-br from-[#1a1d24] via-[#2a2d3a] to-[#1a1d24] text-white text-xs rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-2xl border border-pink-500/50 pointer-events-none backdrop-blur-sm">
                              <div class="flex items-center gap-2 mb-2">
                                <div class="w-2 h-2 rounded-full bg-pink-500"></div>
                                <div class="font-bold text-pink-400">Ghế đôi {{ $seatCodes }}</div>
                              </div>
                              <div class="text-[#E6E7EB] font-semibold mb-1">{{ number_format($totalPrice) }}đ</div>
                              <div class="text-[#A0A6B1] text-[10px] flex items-center gap-1">
                                <i class="fas fa-heart text-[8px]"></i>
                                <span>Ghế Đôi</span>
                              </div>
                              <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-[#2a2d3a]"></div>
                            </div>
                          </button>
                        @else
                          {{-- Single Seat --}}
                          <button 
                            type="button"
                            class="seat-btn-enhanced relative group
                            @if($status === 'booked') seat-sold
                            @elseif($status === 'locked_by_other') seat-locked
                            @elseif($status === 'locked_by_me' || $status === 'selected') seat-selected
                            @elseif($status === 'disabled') seat-disabled
                            @elseif($isVipSeat) seat-vip
                            @elseif($isCoupleSeat) seat-couple
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
                            
                            <!-- Seat Glow Effect -->
                            <div class="absolute inset-0 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-sm
                              @if($isVipSeat) bg-yellow-500/30
                              @elseif($status === 'selected') bg-[#FF784E]/30
                              @else bg-[#FF784E]/20
                              @endif"></div>
                            
                            <!-- Seat Content -->
                            <div class="relative z-10 flex flex-col items-center justify-center h-full w-full">
                              <span class="seat-number block leading-none">{{ $seatNumber }}</span>
                              @if($isVipSeat && $status !== 'booked' && $status !== 'locked_by_other')
                                <i class="fas fa-crown text-[9px] text-yellow-300 mt-0.5 drop-shadow-lg"></i>
                              @endif
                            </div>
                            
                            <!-- Seat Border Glow -->
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300
                              @if($isVipSeat) border-yellow-400/50
                              @elseif($status === 'selected') border-[#FF784E]/50
                              @else border-[#FF784E]/30
                              @endif"></div>
                            
                            <!-- Enhanced Tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-4 px-4 py-3 bg-gradient-to-br from-[#1a1d24] via-[#2a2d3a] to-[#1a1d24] text-white text-xs rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap z-50 shadow-2xl border border-[#FF784E]/50 pointer-events-none backdrop-blur-sm">
                              <div class="flex items-center gap-2 mb-2">
                                <div class="w-2 h-2 rounded-full @if($isVipSeat) bg-yellow-500 @else bg-[#FF784E] @endif"></div>
                                <div class="font-bold @if($isVipSeat) text-yellow-400 @else text-[#FF784E] @endif">Ghế {{ $seat->so_ghe }}</div>
                              </div>
                              <div class="text-[#E6E7EB] font-semibold mb-1">{{ number_format($price) }}đ</div>
                              <div class="text-[#A0A6B1] text-[10px] flex items-center gap-1">
                                <i class="fas fa-tag text-[8px]"></i>
                                <span>{{ $seatType->ten_loai ?? 'Thường' }}</span>
                              </div>
                              <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-[#2a2d3a]"></div>
                            </div>
                          </button>
                        @endif
                        @else
                          {{-- Empty cell để giữ alignment --}}
                          <div class="w-[40px] h-[40px]"></div>
                        @endif
                      @endfor
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            
            
          </div>

          <!-- Legend (5 types only) -->
          <div class="bg-gradient-to-r from-[#151822] via-[#1a1d24] to-[#151822] rounded-xl p-6 border border-[#2A2F3A]">
            <h4 class="text-sm font-bold text-[#E6E7EB] mb-4 text-center flex items-center justify-center gap-2">
              <i class="fas fa-info-circle text-[#FF784E]"></i>
              <span>Chú thích</span>
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
              <!-- Ghế đã đặt -->
              <div class="flex flex-col items-center gap-2 p-3 rounded-lg bg-[#1a1d24] border border-[#2a2d3a]">
                <div class="w-12 h-12 rounded-lg bg-red-600/80 border-2 border-red-700 flex items-center justify-center shadow-lg">
                  <i class="fas fa-times text-white text-sm"></i>
                </div>
                <span class="text-xs text-[#a6a6b0] font-medium">Ghế đã đặt</span>
              </div>

              <!-- Ghế bạn chọn -->
              <div class="flex flex-col items-center gap-2 p-3 rounded-lg bg-[#1a1d24] border border-[#2a2d3a]">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#FF784E] to-[#FFB25E] border-2 border-[#FF784E] flex items-center justify-center shadow-lg">
                  <i class="fas fa-check text-white text-sm"></i>
                </div>
                <span class="text-xs text-[#a6a6b0] font-medium">Ghế bạn chọn</span>
              </div>

              <!-- Ghế VIP -->
              <div class="flex flex-col items-center gap-2 p-3 rounded-lg bg-[#1a1d24] border border-[#2a2d3a]">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-600 to-yellow-700 border-2 border-yellow-500 flex items-center justify-center shadow-lg">
                  <i class="fas fa-crown text-yellow-200 text-sm"></i>
                </div>
                <span class="text-xs text-[#a6a6b0] font-medium">Ghế VIP</span>
              </div>

              <!-- Ghế đôi -->
              <div class="flex flex-col items-center gap-2 p-3 rounded-lg bg-[#1a1d24] border border-[#2a2d3a]">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 border-2 border-pink-500 flex items-center justify-center shadow-lg">
                  <i class="fas fa-heart text-white text-sm"></i>
                </div>
                <span class="text-xs text-[#a6a6b0] font-medium">Ghế đôi</span>
              </div>

              <!-- Ghế thường -->
              <div class="flex flex-col items-center gap-2 p-3 rounded-lg bg-[#1a1d24] border border-[#2a2d3a]">
                <div class="w-12 h-12 rounded-lg bg-[#2a2d3a] border-2 border-[#3a3d4a] flex items-center justify-center">
                  <div class="w-8 h-8 rounded bg-[#2a2d3a] border border-[#3a3d4a]"></div>
                </div>
                <span class="text-xs text-[#a6a6b0] font-medium">Ghế thường</span>
              </div>
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
                <span id="timer-minutes">10</span>:<span id="timer-seconds">00</span>
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
  lockExpiresAt = {{ $existingBooking->created_at->addMinutes(10)->timestamp }};
  startTimer();
@endif

function toggleSeat(seatId, seatCode, price, type) {
  if (selectedSeats.has(seatId)) {
    removeSeat(seatId);
  } else {
    addSeat(seatId, seatCode, price, type);
  }
}

// Build row states snapshot with a draft selection: 0=empty, 1=booked, 2=selected
function buildRowStatesWithDraft(draft){
  const rows = {};
  document.querySelectorAll('.seat-row-container').forEach(rowEl => {
    const rowLabel = rowEl.getAttribute('data-row');
    const states = [];
    rowEl.querySelectorAll('[data-seat-id]').forEach(btn => {
      const id = parseInt(btn.getAttribute('data-seat-id'),10);
      const isSold = btn.classList.contains('seat-sold');
      if (draft.has(id)) {
        states.push(2);
      } else if (isSold) {
        states.push(1);
      } else {
        states.push(0);
      }
    });
    if (states.length) rows[rowLabel] = states;
  });
  return rows;
}

// Validate No Single Seat Rule on snapshot
function validateNoSingleSeatRule(rowStates){
  const result = { valid: true };
  const keys = Object.keys(rowStates);
  for (const k of keys){
    const arr = rowStates[k];
    const n = arr.length;
    if (n === 0) continue;
    // b) edge single empty next to non-empty
    if (n >= 2) {
      if (arr[0] === 0 && arr[1] !== 0) return { valid: false, message: 'Không được để ghế trống lẻ.' };
      if (arr[n-1] === 0 && arr[n-2] !== 0) return { valid: false, message: 'Không được để ghế trống lẻ.' };
    }
    // a,c,d) single empty between two non-empty
    for (let i=1;i<n-1;i++){
      if (arr[i] === 0 && arr[i-1] !== 0 && arr[i+1] !== 0){
        return { valid: false, message: 'Không được để ghế trống lẻ.' };
      }
    }
  }
  return result;
}

function toggleCoupleSeat(seatId1, seatCode1, price1, seatId2, seatCode2, price2) {
  const isSeat1Selected = selectedSeats.has(seatId1);
  const isSeat2Selected = selectedSeats.has(seatId2);
  
  if (isSeat1Selected || isSeat2Selected) {
    // Remove both seats if either is selected
    if (isSeat1Selected) removeSeat(seatId1);
    if (isSeat2Selected) removeSeat(seatId2);
  } else {
    // Add both seats
    const draft = new Map(selectedSeats);
    draft.set(seatId1, { code: seatCode1, price: price1, type: 'Ghế Đôi' });
    draft.set(seatId2, { code: seatCode2, price: price2, type: 'Ghế Đôi' });
    
    // Validate No Single Seat Rule
    const snapshot = buildRowStatesWithDraft(draft);
    const nsr = validateNoSingleSeatRule(snapshot);
    if (!nsr.valid) {
      alert(nsr.message || 'Không được để ghế trống lẻ.');
      return;
    }
    
    selectedSeats = draft;
    updateUI();
    lockSeats();
  }
}

// Helpers for contiguity check per row
function seatRowFromCode(code){
  return (code || '').trim().charAt(0);
}
function seatNumFromCode(code){
  const m = String(code || '').match(/(\d+)/);
  return m ? parseInt(m[1], 10) : NaN;
}
// (ĐÃ GỠ) Logic liền kề cũ đã bị loại bỏ theo yêu cầu.

// Couple-seat helpers
function isCoupleTypeText(t){
  const s = String(t || '').toLowerCase();
  return s.includes('đôi') || s.includes('doi') || s.includes('couple');
}
function pairedSeatCodeOf(code){
  const row = seatRowFromCode(code);
  const n = seatNumFromCode(code);
  if (!row || isNaN(n)) return null;
  const pairNum = (n % 2 === 0) ? (n - 1) : (n + 1);
  return row + String(pairNum);
}
function getSeatBtnByCode(code){
  return document.querySelector(`[data-seat-code="${code}"]`);
}

function addSeat(seatId, seatCode, price, type) {
  const draft = new Map(selectedSeats);

  // If selecting a couple seat, auto-include its pair
  if (isCoupleTypeText(type)){
    const pairCode = pairedSeatCodeOf(seatCode);
    const pairBtn = pairCode ? getSeatBtnByCode(pairCode) : null;
    if (!pairBtn) {
      alert('Không thể chọn ghế đôi vì ghế cặp không khả dụng.');
      return;
    }
    const pairDisabled = pairBtn.hasAttribute('disabled');
    const pairType = pairBtn.getAttribute('data-seat-type');
    const pairPrice = parseInt(pairBtn.getAttribute('data-seat-price') || '0', 10);
    const pairId = parseInt(pairBtn.getAttribute('data-seat-id') || '0', 10);
    if (pairDisabled || !isCoupleTypeText(pairType)){
      alert('Ghế cặp của ghế đôi đang không khả dụng. Vui lòng chọn cặp khác.');
      return;
    }
    // Add both to draft
    draft.set(seatId, { code: seatCode, price: price, type: type });
    draft.set(pairId, { code: pairCode, price: pairPrice, type: pairType });
  } else {
    draft.set(seatId, { code: seatCode, price: price, type: type });
  }

  // Validate No Single Seat Rule
  const snapshot = buildRowStatesWithDraft(draft);
  const nsr = validateNoSingleSeatRule(snapshot);
  if (!nsr.valid) {
    alert(nsr.message || 'Không được để ghế trống lẻ.');
    return;
  }

  selectedSeats = draft;
  updateUI();
  lockSeats();
}

function removeSeat(seatId) {
  const current = selectedSeats.get(seatId);
  const toUnlock = [seatId];
  if (current && isCoupleTypeText(current.type)){
    const pairCode = pairedSeatCodeOf(current.code);
    if (pairCode){
      // find the selected seatId that matches this code
      for (const [id, data] of selectedSeats.entries()){
        if (data.code === pairCode){
          selectedSeats.delete(id);
          toUnlock.push(id);
          break;
        }
      }
    }
  }
  selectedSeats.delete(seatId);
  updateUI();
  unlockSeats(toUnlock);
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
  document.querySelectorAll('.seat-btn, .seat-btn-enhanced').forEach(btn => {
    const id = parseInt(btn.dataset.seatId);
    const typeText = String(btn.dataset.seatType || '').toLowerCase();
    const isVip = typeText.includes('vip');
    const isCouple = typeText.includes('đôi') || typeText.includes('doi') || typeText.includes('couple');

    if (selectedSeats.has(id)) {
      // Selected: ensure only seat-selected remains
      btn.classList.remove('seat-available', 'seat-vip', 'seat-couple');
      btn.classList.add('seat-selected');
    } else if (!btn.disabled) {
      // Not selected: remove selected state and restore base class
      btn.classList.remove('seat-selected', 'seat-available', 'seat-vip', 'seat-couple');
      if (isCouple) {
        btn.classList.add('seat-couple');
      } else if (isVip) {
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
  
  const seatIds = Array.from(selectedSeats.keys()).map(id => Number(id));
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  if (!token) {
    console.error('CSRF token not found');
    alert('Có lỗi xảy ra. Vui lòng tải lại trang.');
    return;
  }

  try {
    // NEW API: Hold multiple seats at once
    const response = await fetch(`/shows/${showId}/seats/hold`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ seat_ids: seatIds })
    });

    // Try to parse JSON body even on non-2xx
    let data = null;
    try { data = await response.json(); } catch (_) { data = null; }
    
    if (!response.ok) {
      if (response.status === 419) {
        alert('Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.');
        window.location.reload();
        return;
      }
      // Show backend message if available
      const msg = data && data.message;
      if (msg) {
        alert(msg);
        // Remove failed seats
        if (data.failed_seats && Array.isArray(data.failed_seats)) {
          data.failed_seats.forEach(failed => {
            if (failed.seat_id) removeSeat(failed.seat_id);
          });
        }
        return;
      }
      console.error('Lock seats failed with HTTP', response.status, data);
      alert('Có lỗi xảy ra khi giữ ghế. Vui lòng thử lại.');
      return;
    }

    // Success (2xx)
    if (!data) {
      alert('Có lỗi xảy ra khi giữ ghế. Vui lòng thử lại.');
      return;
    }
    
    if (data.success) {
      // NEW: expires_at is now in timestamp format
      if (data.expires_at) {
        lockExpiresAt = data.expires_at;
        startTimer();
      }
      startRefresh();
    } else {
      alert(data.message || 'Không thể giữ ghế');
      // Remove failed seats
      if (data.failed_seats && Array.isArray(data.failed_seats)) {
        data.failed_seats.forEach(failed => {
          if (failed.seat_id) removeSeat(failed.seat_id);
        });
      }
    }
  } catch (error) {
    console.error('Error locking seats:', error);
    alert('Có lỗi xảy ra khi giữ ghế. Vui lòng thử lại.');
  }
}

async function unlockSeats(seatIds) {
  if (!seatIds || seatIds.length === 0) return;
  
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  if (!token) {
    console.error('CSRF token not found');
    return;
  }

  try {
    // NEW API: Release multiple seats
    const response = await fetch(`/shows/${showId}/seats/release`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ seat_ids: seatIds })
    });

    if (!response.ok && response.status === 419) {
      console.warn('CSRF token expired during unlock');
    }
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
        // Update seat statuses (NEW LOGIC: status can be 'booked' or 'locked_by_other')
        Object.entries(data.seats).forEach(([seatId, status]) => {
          const btn = document.querySelector(`[data-seat-id="${seatId}"]`);
          if (!btn) return;

          const id = parseInt(seatId);

          // Helper: reset to normal available visual when re-enabled
          const resetToAvailable = () => {
            btn.classList.remove('seat-selected', 'seat-locked', 'seat-sold', 'opacity-50');
            btn.disabled = false;
            // restore base style classes
            const typeText = String(btn.dataset.seatType || '').toLowerCase();
            const isVip = typeText.includes('vip');
            const isCouple = typeText.includes('đôi') || typeText.includes('doi') || typeText.includes('couple');
            btn.classList.remove('seat-available', 'seat-vip', 'seat-couple');
            if (isCouple) {
              btn.classList.add('seat-couple');
            } else if (isVip) {
              btn.classList.add('seat-vip');
            } else {
              btn.classList.add('seat-available');
            }
          };

          if (status === 'booked') {
            // Sold: mark as sold and disable
            btn.disabled = true;
            btn.classList.remove('seat-selected', 'seat-available', 'seat-vip', 'seat-couple', 'seat-locked');
            btn.classList.add('seat-sold', 'opacity-50');
            return;
          }

          if (status === 'locked_by_other') {
            // Held by another user: show as locked but disabled
            if (!selectedSeats.has(id)) {
              btn.disabled = true;
              btn.classList.remove('seat-sold', 'seat-available', 'seat-vip', 'seat-couple', 'seat-selected');
              btn.classList.add('seat-locked');
            }
            return;
          }

          // Otherwise: available (if not selected by me)
          if (!selectedSeats.has(id)) {
            resetToAvailable();
          }
        });
      }
    } catch (error) {
      console.error('Error refreshing seats:', error);
    }
  }, 5000); // Refresh every 5 seconds
}

async function continueToAddons() {
  if (selectedSeats.size === 0) {
    alert('Vui lòng chọn ít nhất một ghế');
    return;
  }

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) {
    alert('Không tìm thấy CSRF token. Vui lòng tải lại trang.');
    return;
  }

  const seatsArray = Array.from(selectedSeats.values()).map(s => s.code);
  const bookingHoldId = window.bookingId || null;

  try {
    const combosPayload = Array.from(selectedCombos.entries()).map(([id, info]) => ({
      id_combo: id,
      so_luong: info.quantity,
      gia: info.price
    }));

    const res = await fetch('/booking/continue', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        showtime_id: showId,
        seats: seatsArray,
        booking_hold_id: bookingHoldId,
        combos: combosPayload
      })
    });

    if (!res.ok) {
      let data = null;
      try { data = await res.json(); } catch (_) { /* ignore */ }
      if (res.status === 410) {
        alert((data && data.message) || 'Phiên giữ ghế đã hết hạn. Vui lòng chọn lại.');
        window.location.reload();
        return;
      }
      if (res.status === 422) {
        alert((data && data.message) || 'Lựa chọn ghế không hợp lệ.');
        return;
      }
      alert((data && data.message) || ('Có lỗi xảy ra (' + res.status + '). Vui lòng thử lại.'));
      return;
    }

    // Redirect to payment step
    window.location.href = '/booking/payment';
  } catch (e) {
    console.error('continueToPayment error', e);
    alert('Có lỗi xảy ra. Vui lòng thử lại.');
  }
}

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN').format(price);
}

// Zoom functions
let currentZoom = 1;
const minZoom = 0.7;
const maxZoom = 1.5;
const zoomStep = 0.1;
let baseScale = 1;

function zoomIn() {
  const scaleEl = document.getElementById('seat-map-scale');
  if (!scaleEl) return;
  currentZoom = Math.min(currentZoom + zoomStep, maxZoom);
  scaleEl.style.transform = `scale(${(baseScale * currentZoom).toFixed(3)})`;
}

function zoomOut() {
  const scaleEl = document.getElementById('seat-map-scale');
  if (!scaleEl) return;
  currentZoom = Math.max(currentZoom - zoomStep, minZoom);
  scaleEl.style.transform = `scale(${(baseScale * currentZoom).toFixed(3)})`;
}

// Keyboard navigation for seat map
let currentFocusedSeat = null;
const seatButtons = [];

// Auto-fit seat map to container width
function fitSeatMap() {
  const container = document.getElementById('seat-map-container');
  const scaleEl = document.getElementById('seat-map-scale');
  if (!container || !scaleEl) return;

  // Temporarily reset transform to measure natural width
  const prev = scaleEl.style.transform;
  scaleEl.style.transform = 'none';

  const containerWidth = container.clientWidth;
  const gridWidth = scaleEl.scrollWidth; // natural width
  // leave some breathing room
  const target = Math.max(0, containerWidth - 24);
  let scale = 1;
  if (gridWidth > 0) {
    scale = Math.min(1, target / gridWidth);
  }

  baseScale = scale;
  currentZoom = 1; // reset incremental zoom when auto-fit
  scaleEl.style.transform = `scale(${scale.toFixed(3)})`;
  scaleEl.style.transformOrigin = 'top center';
  // Restore if needed
  // not restoring prev because we set the new scale
}

window.addEventListener('resize', () => {
  // Debounce
  clearTimeout(window.__fitSeatMapTimer);
  window.__fitSeatMapTimer = setTimeout(fitSeatMap, 100);
});

document.addEventListener('DOMContentLoaded', () => {
  // Run after DOM ready and a tick for fonts/images
  setTimeout(fitSeatMap, 0);
});

function initKeyboardNavigation() {
    const seats = document.querySelectorAll('.seat-btn:not([disabled]), .seat-btn-enhanced:not([disabled])');
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
            const nextSeat = nextRow.querySelector('.seat-btn:not([disabled]), .seat-btn-enhanced:not([disabled])');
            if (nextSeat) {
              nextIndex = seatButtons.indexOf(nextSeat);
            }
          }
          e.preventDefault();
          break;
        case 'ArrowUp':
          const prevRow = seat.closest('.flex.items-center')?.previousElementSibling;
          if (prevRow) {
            const prevSeat = prevRow.querySelector('.seat-btn:not([disabled]), .seat-btn-enhanced:not([disabled])');
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

