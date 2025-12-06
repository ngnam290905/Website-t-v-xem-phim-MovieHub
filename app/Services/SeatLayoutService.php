<?php

namespace App\Services;

use App\Models\PhongChieu;
use App\Models\Ghe;
use Illuminate\Support\Facades\Log;

class SeatLayoutService
{
    /**
     * Get seat layout matrix for a room
     * Returns matrix with null values for empty positions
     * 
     * @param PhongChieu $room
     * @return array Matrix with [row][col] => seat data or null
     */
    public function getSeatMatrix(PhongChieu $room): array
    {
        // Get layout from room metadata if exists
        $layoutType = $room->layout_type ?? 'rectangle'; // rectangle, triangle, diamond, corners_cut
        $layoutConfig = $room->layout_config ?? null;
        
        // Get all seats for this room
        $seats = Ghe::where('id_phong', $room->id)
            ->with('loaiGhe')
            ->get()
            ->keyBy('so_ghe');
        
        // Get room dimensions
        $maxRows = $room->so_hang ?? $room->rows ?? 10;
        $maxCols = $room->so_cot ?? $room->cols ?? 15;
        
        // Initialize matrix with null
        $matrix = [];
        
        // Generate row labels (A, B, C, ...)
        $rowLabels = [];
        for ($i = 1; $i <= $maxRows; $i++) {
            $rowLabels[] = chr(64 + $i); // A, B, C, ...
        }
        
        // Build matrix based on layout type
        switch ($layoutType) {
            case 'triangle':
                $matrix = $this->buildTriangleMatrix($rowLabels, $maxCols, $seats);
                break;
            case 'diamond':
                $matrix = $this->buildDiamondMatrix($rowLabels, $maxCols, $seats);
                break;
            case 'corners_cut':
                $matrix = $this->buildCornersCutMatrix($rowLabels, $maxCols, $seats);
                break;
            case 'rectangle':
            default:
                $matrix = $this->buildRectangleMatrix($rowLabels, $maxCols, $seats);
                break;
        }
        
        return $matrix;
    }
    
    /**
     * Build rectangle matrix (standard layout)
     * Uses row/col from database (so_hang, so_ghe) instead of hardcoded values
     */
    private function buildRectangleMatrix(array $rowLabels, int $maxCols, $seats): array
    {
        $matrix = [];
        
        // Group seats by row from database
        $seatsByRow = $seats->groupBy(function($seat) {
            return $seat->so_hang ?? substr($seat->so_ghe, 0, 1);
        });
        
        foreach ($rowLabels as $rowIndex => $rowLabel) {
            $matrix[$rowLabel] = [];
            
            // Get seats for this row from database
            $rowSeats = $seatsByRow->get($rowLabel, collect());
            
            // Create a map of column number to seat
            $seatMap = [];
            foreach ($rowSeats as $seat) {
                // Extract column number from seat code (A1 -> 1, B12 -> 12)
                preg_match('/(\d+)/', $seat->so_ghe, $matches);
                $colNum = (int)($matches[1] ?? 0);
                if ($colNum > 0) {
                    $seatMap[$colNum] = $seat;
                }
            }
            
            // Fill matrix columns
            for ($col = 1; $col <= $maxCols; $col++) {
                if (isset($seatMap[$col])) {
                    $matrix[$rowLabel][$col] = $this->formatSeatData($seatMap[$col]);
                } else {
                    $matrix[$rowLabel][$col] = null;
                }
            }
        }
        return $matrix;
    }
    
    /**
     * Build triangle matrix
     * Example:
     *     A1
     *   B1 B2
     * C1 C2 C3
     */
    private function buildTriangleMatrix(array $rowLabels, int $maxCols, $seats): array
    {
        $matrix = [];
        $totalRows = count($rowLabels);
        
        foreach ($rowLabels as $rowIndex => $rowLabel) {
            $matrix[$rowLabel] = [];
            
            // Calculate how many seats in this row (starts from 1, increases by 1)
            $seatsInRow = $rowIndex + 1;
            
            // Calculate padding (empty spaces) on left
            $padding = $maxCols - $seatsInRow;
            $leftPadding = (int)floor($padding / 2);
            
            // Fill left padding with null
            for ($col = 1; $col <= $leftPadding; $col++) {
                $matrix[$rowLabel][$col] = null;
            }
            
            // Fill seats
            for ($col = 1; $col <= $seatsInRow; $col++) {
                $actualCol = $leftPadding + $col;
                $seatCode = $rowLabel . $col;
                $seat = $seats->get($seatCode);
                $matrix[$rowLabel][$actualCol] = $seat ? $this->formatSeatData($seat) : null;
            }
            
            // Fill right padding with null
            for ($col = $leftPadding + $seatsInRow + 1; $col <= $maxCols; $col++) {
                $matrix[$rowLabel][$col] = null;
            }
        }
        
        return $matrix;
    }
    
    /**
     * Build diamond matrix
     * Example:
     *     A1
     *   B1 B2
     * C1 C2 C3
     * D1 D2 D3 D4
     *   C4 C5 C6
     *     B3 B4
     *       A2
     */
    private function buildDiamondMatrix(array $rowLabels, int $maxCols, $seats): array
    {
        $matrix = [];
        $totalRows = count($rowLabels);
        $midRow = (int)ceil($totalRows / 2);
        
        foreach ($rowLabels as $rowIndex => $rowLabel) {
            $matrix[$rowLabel] = [];
            
            // Calculate seats in row (increases to mid, then decreases)
            if ($rowIndex < $midRow) {
                // Growing phase
                $seatsInRow = $rowIndex + 1;
            } else {
                // Shrinking phase
                $seatsInRow = $totalRows - $rowIndex;
            }
            
            // Calculate padding
            $padding = $maxCols - $seatsInRow;
            $leftPadding = (int)floor($padding / 2);
            
            // Fill left padding
            for ($col = 1; $col <= $leftPadding; $col++) {
                $matrix[$rowLabel][$col] = null;
            }
            
            // Fill seats
            for ($col = 1; $col <= $seatsInRow; $col++) {
                $actualCol = $leftPadding + $col;
                $seatCode = $rowLabel . $col;
                $seat = $seats->get($seatCode);
                $matrix[$rowLabel][$actualCol] = $seat ? $this->formatSeatData($seat) : null;
            }
            
            // Fill right padding
            for ($col = $leftPadding + $seatsInRow + 1; $col <= $maxCols; $col++) {
                $matrix[$rowLabel][$col] = null;
            }
        }
        
        return $matrix;
    }
    
    /**
     * Build matrix with 4 corners cut
     * Example:
     * X A1 A2 A3 X
     * B1 B2 B3 B4
     * C1 C2 C3 C4
     * X D1 D2 D3 X
     */
    private function buildCornersCutMatrix(array $rowLabels, int $maxCols, $seats): array
    {
        $matrix = [];
        $totalRows = count($rowLabels);
        
        foreach ($rowLabels as $rowIndex => $rowLabel) {
            $matrix[$rowLabel] = [];
            
            // First and last row: cut corners (first and last column = null)
            if ($rowIndex === 0 || $rowIndex === $totalRows - 1) {
                // First column = null
                $matrix[$rowLabel][1] = null;
                
                // Middle columns = seats
                for ($col = 2; $col < $maxCols; $col++) {
                    $seatCode = $rowLabel . ($col - 1); // Adjust seat numbering
                    $seat = $seats->get($seatCode);
                    $matrix[$rowLabel][$col] = $seat ? $this->formatSeatData($seat) : null;
                }
                
                // Last column = null
                $matrix[$rowLabel][$maxCols] = null;
            } else {
                // Middle rows: all columns are seats
                for ($col = 1; $col <= $maxCols; $col++) {
                    $seatCode = $rowLabel . $col;
                    $seat = $seats->get($seatCode);
                    $matrix[$rowLabel][$col] = $seat ? $this->formatSeatData($seat) : null;
                }
            }
        }
        
        return $matrix;
    }
    
    /**
     * Format seat data for frontend
     */
    private function formatSeatData(Ghe $seat): array
    {
        return [
            'id' => $seat->id,
            'code' => $seat->so_ghe,
            'type' => $seat->loaiGhe->ten_loai ?? 'Thường',
            'available' => $seat->trang_thai == 1,
            'price' => $this->calculateSeatPrice($seat),
            'pos_x' => $seat->pos_x,
            'pos_y' => $seat->pos_y,
            'zone' => $seat->zone
        ];
    }
    
    /**
     * Calculate seat price based on type
     */
    private function calculateSeatPrice(Ghe $seat): int
    {
        $typeText = strtolower($seat->loaiGhe->ten_loai ?? 'thường');
        
        if (str_contains($typeText, 'vip')) {
            return 120000;
        } elseif (str_contains($typeText, 'đôi') || str_contains($typeText, 'doi') || str_contains($typeText, 'couple')) {
            return 200000;
        }
        
        return 80000;
    }
    
    /**
     * Convert matrix to flat array for API response
     * Includes null positions for empty seats
     * 
     * @param array $matrix Seat matrix from getSeatMatrix()
     * @param int|null $showtimeId For status checking
     * @param object|null $seatStatusService SeatHoldService instance
     * @param object|null $showtimeSeats Collection of ShowtimeSeat for sold/reserved check
     * @return array Flat array with seat codes as keys, null for empty positions
     */
    public function matrixToFlatArray(array $matrix, ?int $showtimeId = null, $seatStatusService = null, $showtimeSeats = null): array
    {
        $result = [];
        
        foreach ($matrix as $rowLabel => $columns) {
            foreach ($columns as $col => $seatData) {
                if ($seatData === null) {
                    // Empty position - mark as null with position key
                    $positionKey = $rowLabel . $col;
                    $result[$positionKey] = null;
                } else {
                    // Real seat - add status from service if provided
                    $seatCode = $seatData['code'];
                    if ($seatStatusService && $showtimeId) {
                        // Get status from seat status service
                        $status = $seatStatusService->getSeatStatus(
                            $showtimeId,
                            $seatData['id'],
                            auth()->id()
                        );
                        $seatData['status'] = $status;
                        $seatData['available'] = ($status === 'available');
                        
                        // Check showtimeSeats for sold/reserved
                        if ($showtimeSeats) {
                            $showtimeSeat = $showtimeSeats->get($seatCode);
                            if ($showtimeSeat) {
                                if ($showtimeSeat->isBooked() || $showtimeSeat->status === 'booked') {
                                    $seatData['status'] = 'sold';
                                    $seatData['available'] = false;
                                } elseif ($showtimeSeat->status === 'reserved') {
                                    $seatData['status'] = 'reserved';
                                    $seatData['available'] = false;
                                }
                            }
                        }
                    }
                    $result[$seatCode] = $seatData;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Check if a position is a valid seat (not null)
     */
    public function isValidSeat(array $matrix, string $rowLabel, int $col): bool
    {
        if (!isset($matrix[$rowLabel])) {
            return false;
        }
        
        if (!isset($matrix[$rowLabel][$col])) {
            return false;
        }
        
        return $matrix[$rowLabel][$col] !== null;
    }
}

