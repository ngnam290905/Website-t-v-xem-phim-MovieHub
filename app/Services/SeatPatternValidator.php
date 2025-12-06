<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SeatPatternValidator
{
    /**
     * Validate with the "No Single Seat Rule" only.
     * - Input là map các hàng -> mảng trạng thái 0 (trống), 1 (đã đặt), 2 (đang chọn)
     * - Không được để lại 1 ghế 0 lẻ theo các trường hợp a, b, c, d
     */
    public static function validateNoSingleSeatRule(array $rowStates): array
    {
        foreach ($rowStates as $row => $states) {
            $n = count($states);
            if ($n === 0) continue;
            // b) Trống 1 ghế sát biên
            if ($n >= 2) {
                if ($states[0] === 0 && $states[1] !== 0) {
                    return ['valid' => false, 'message' => 'Không được để ghế trống lẻ.'];
                }
                if ($states[$n-1] === 0 && $states[$n-2] !== 0) {
                    return ['valid' => false, 'message' => 'Không được để ghế trống lẻ.'];
                }
            }
            // a,c,d) Trống 1 ghế giữa hai ghế không-trống
            for ($i=1; $i<$n-1; $i++) {
                if ($states[$i] === 0 && $states[$i-1] !== 0 && $states[$i+1] !== 0) {
                    return ['valid' => false, 'message' => 'Không được để ghế trống lẻ.'];
                }
            }
        }
        return ['valid' => true];
    }

    // Backward compatible entry: accept rowStates if provided; otherwise just pass
    // through as valid (controller nên truyền rowStates đã dựng sẵn)
    public function validatePattern(array $seatCodes, array $rowStates = []): array
    {
        if (!empty($rowStates)) return self::validateNoSingleSeatRule($rowStates);
        return ['valid' => true];
    }

    /**
     * Check if seat selection is a 4-corner pattern (A1, J1, J15, A15)
     * 
     * @param array $seatsByRow Array of seats grouped by row
     * @return bool
     */
    private function isFourCornerPattern(array $seatsByRow): bool
    {
        // Must have exactly 2 rows
        if (count($seatsByRow) !== 2) {
            return false;
        }

        $rows = array_keys($seatsByRow);
        $minRow = $rows[0];
        $maxRow = $rows[1];
        $minRowCols = $seatsByRow[$minRow];
        $maxRowCols = $seatsByRow[$maxRow];

        // Each row must have exactly 2 seats
        if (count($minRowCols) !== 2 || count($maxRowCols) !== 2) {
            return false;
        }

        // Total must be 4 seats
        $totalSeats = count($minRowCols) + count($maxRowCols);
        if ($totalSeats !== 4) {
            return false;
        }

        // Get all columns
        $allCols = array_merge($minRowCols, $maxRowCols);
        $minCol = min($allCols);
        $maxCol = max($allCols);

        // Check if seats form exactly 4 corners:
        // (minRow, minCol), (minRow, maxCol), (maxRow, minCol), (maxRow, maxCol)
        $expectedCorners = [
            ['row' => $minRow, 'col' => $minCol],
            ['row' => $minRow, 'col' => $maxCol],
            ['row' => $maxRow, 'col' => $minCol],
            ['row' => $maxRow, 'col' => $maxCol],
        ];

        // Build actual seats array
        $actualSeats = [];
        foreach ($seatsByRow as $row => $cols) {
            foreach ($cols as $col) {
                $actualSeats[] = ['row' => $row, 'col' => $col];
            }
        }

        // Sort both arrays for comparison
        usort($actualSeats, function($a, $b) {
            if ($a['row'] !== $b['row']) {
                return strcmp($a['row'], $b['row']);
            }
            return $a['col'] - $b['col'];
        });

        usort($expectedCorners, function($a, $b) {
            if ($a['row'] !== $b['row']) {
                return strcmp($a['row'], $b['row']);
            }
            return $a['col'] - $b['col'];
        });

        // Check if actual seats match expected corners exactly
        if (count($actualSeats) !== count($expectedCorners)) {
            return false;
        }

        for ($i = 0; $i < count($actualSeats); $i++) {
            if ($actualSeats[$i]['row'] !== $expectedCorners[$i]['row'] || 
                $actualSeats[$i]['col'] !== $expectedCorners[$i]['col']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse seats into rows and columns
     * Chuyển đổi mã ghế thành cấu trúc theo hàng
     * 
     * @param array $seatCodes
     * @return array ['A' => [1, 2, 3], 'B' => [1, 2], ...]
     */
    private function parseSeatsByRow(array $seatCodes): array
    {
        $seatsByRow = [];

        foreach ($seatCodes as $code) {
            $code = trim(strtoupper($code));
            
            // Extract row and column (e.g., "A1" -> row="A", col=1)
            if (preg_match('/^([A-Z])(\d+)$/', $code, $matches)) {
                $row = $matches[1];
                $col = (int)$matches[2];
                
                if (!isset($seatsByRow[$row])) {
                    $seatsByRow[$row] = [];
                }
                $seatsByRow[$row][] = $col;
            }
        }

        // Sort columns in each row
        foreach ($seatsByRow as $row => $cols) {
            sort($seatsByRow[$row]);
        }

        // Sort rows alphabetically
        ksort($seatsByRow);

        return $seatsByRow;
    }

    /**
     * Check if pattern is triangle (số ghế tăng dần theo hàng)
     * Ví dụ: A1, B1 B2, C1 C2 C3
     */
    private function isTrianglePattern(array $seatsByRow): bool
    {
        if (count($seatsByRow) < 3) {
            return false; // Need at least 3 rows to form triangle
        }

        $rowCounts = array_map('count', $seatsByRow);
        $rows = array_keys($seatsByRow);
        
        // Check if seat counts are strictly increasing
        $isIncreasing = true;
        $prevCount = 0;
        foreach ($rowCounts as $count) {
            if ($count <= $prevCount) {
                $isIncreasing = false;
                break;
            }
            $prevCount = $count;
        }

        // Also check if it's strictly decreasing (inverted triangle)
        $isDecreasing = true;
        $prevCount = PHP_INT_MAX;
        foreach ($rowCounts as $count) {
            if ($count >= $prevCount) {
                $isDecreasing = false;
                break;
            }
            $prevCount = $count;
        }

        // If strictly increasing or decreasing with 3+ rows, it's a triangle pattern
        if (($isIncreasing || $isDecreasing) && count($seatsByRow) >= 3) {
            // Additional check: seats should not be in a block (should be scattered)
            // If seats form a block, it's not a triangle pattern
            if ($this->isBlockPattern($seatsByRow)) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Check if pattern is diamond (hình thoi)
     * Ví dụ: A2, B1 B2 B3, C2 (nhiều ở giữa, ít ở hai đầu)
     */
    private function isDiamondPattern(array $seatsByRow): bool
    {
        if (count($seatsByRow) < 3) {
            return false;
        }

        $rowCounts = array_map('count', $seatsByRow);
        $counts = array_values($rowCounts);
        
        // Find the middle index
        $middleIndex = (int)(count($counts) / 2);
        
        // Check if middle row has more seats than edges
        // Pattern: low -> high -> low
        $isDiamond = true;
        for ($i = 0; $i < $middleIndex; $i++) {
            if ($counts[$i] >= $counts[$i + 1]) {
                $isDiamond = false;
                break;
            }
        }
        
        if ($isDiamond) {
            for ($i = $middleIndex; $i < count($counts) - 1; $i++) {
                if ($counts[$i] <= $counts[$i + 1]) {
                    $isDiamond = false;
                    break;
                }
            }
        }

        // If it's a diamond pattern and not a block, return true
        if ($isDiamond && !$this->isBlockPattern($seatsByRow)) {
            return true;
        }

        return false;
    }

    /**
     * Check if pattern is zigzag
     * Ghế không liền nhau, tạo hình ziczac
     */
    private function isZigzagPattern(array $seatsByRow): bool
    {
        if (count($seatsByRow) < 2) {
            return false;
        }

        $rows = array_keys($seatsByRow);
        
        // Check if seats alternate positions between rows (zigzag)
        for ($i = 0; $i < count($rows) - 1; $i++) {
            $currentRow = $rows[$i];
            $nextRow = $rows[$i + 1];
            
            $currentCols = $seatsByRow[$currentRow];
            $nextCols = $seatsByRow[$nextRow];
            
            // Check if seats are not aligned (zigzag)
            // If all seats in next row are between seats in current row, it's zigzag
            $isZigzag = true;
            foreach ($nextCols as $nextCol) {
                $hasOverlap = false;
                foreach ($currentCols as $currentCol) {
                    if (abs($nextCol - $currentCol) <= 1) {
                        $hasOverlap = true;
                        break;
                    }
                }
                if (!$hasOverlap) {
                    $isZigzag = false;
                    break;
                }
            }
            
            // More strict: check if seats form a clear zigzag pattern
            // (alternating left-right positions)
            if (count($currentCols) > 0 && count($nextCols) > 0) {
                $currentMin = min($currentCols);
                $currentMax = max($currentCols);
                $nextMin = min($nextCols);
                $nextMax = max($nextCols);
                
                // If next row is completely outside current row range, it might be zigzag
                if (($nextMax < $currentMin - 1) || ($nextMin > $currentMax + 1)) {
                    // Check if it's intentional zigzag (alternating pattern)
                    if ($i > 0) {
                        $prevRow = $rows[$i - 1];
                        $prevCols = $seatsByRow[$prevRow];
                        $prevMin = min($prevCols);
                        $prevMax = max($prevCols);
                        
                        // If positions alternate: left-right-left or right-left-right
                        if (($nextMin < $prevMin && $currentMin > $prevMin) || 
                            ($nextMin > $prevMax && $currentMin < $prevMin)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if rows are skipped
     * Kiểm tra có bỏ qua hàng không
     * Cho phép skip hàng nếu là pattern 4 góc (A1, J1, J15, A15)
     */
    private function hasSkippedRows(array $seatsByRow): bool
    {
        if (count($seatsByRow) < 2) {
            return false;
        }

        $rows = array_keys($seatsByRow);
        $totalSeats = array_sum(array_map('count', $seatsByRow));
        
        // Check if this is a 4-corner pattern (allow skip rows for 4 corners)
        if ($totalSeats === 4 && count($rows) === 2) {
            $firstRow = $rows[0];
            $lastRow = $rows[1];
            $firstRowCols = $seatsByRow[$firstRow];
            $lastRowCols = $seatsByRow[$lastRow];
            
            // Check if each row has exactly 2 seats
            if (count($firstRowCols) === 2 && count($lastRowCols) === 2) {
                $allCols = array_merge($firstRowCols, $lastRowCols);
                $minCol = min($allCols);
                $maxCol = max($allCols);
                
                // Check if seats are at corners (min and max columns in each row)
                $isFourCorners = true;
                foreach ($firstRowCols as $col) {
                    if ($col != $minCol && $col != $maxCol) {
                        $isFourCorners = false;
                        break;
                    }
                }
                if ($isFourCorners) {
                    foreach ($lastRowCols as $col) {
                        if ($col != $minCol && $col != $maxCol) {
                            $isFourCorners = false;
                            break;
                        }
                    }
                }
                
                // If it's 4 corners pattern, allow skip rows
                if ($isFourCorners) {
                    return false; // Allow 4 corners even with skipped rows
                }
            }
        }
        
        // Check if there are gaps in row sequence
        for ($i = 0; $i < count($rows) - 1; $i++) {
            $currentRowNum = ord($rows[$i]) - ord('A');
            $nextRowNum = ord($rows[$i + 1]) - ord('A');
            
            // If there's a gap of more than 1 row, it's skipped
            if ($nextRowNum - $currentRowNum > 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for isolated gaps (bỏ trống 1 ghế đơn lẻ giữa 2 ghế đang chọn)
     * Ví dụ: A1, A3 (bỏ A2) - không hợp lệ
     * Ví dụ: A1, A2, A4 (bỏ A3) - không hợp lệ
     */
    private function hasIsolatedGaps(array $seatsByRow): bool
    {
        foreach ($seatsByRow as $row => $cols) {
            if (count($cols) < 2) {
                continue;
            }

            // Check for gaps of exactly 1 seat (gap = 2 means 1 seat is skipped)
            for ($i = 0; $i < count($cols) - 1; $i++) {
                $gap = $cols[$i + 1] - $cols[$i];
                
                // If gap is exactly 2 (meaning 1 seat is skipped), check if it's isolated
                if ($gap == 2) {
                    // Case 1: Only 2 seats with 1 gap between them (A1, A3)
                    if (count($cols) == 2) {
                        return true;
                    }
                    
                    // Case 2: A1, A3, A4 - gap between A1 and A3 is isolated
                    // (next seat A4 is adjacent to A3, so A3-A4 is a block, A1 is isolated)
                    if ($i + 2 < count($cols) && $cols[$i + 2] == $cols[$i + 1] + 1) {
                        return true;
                    }
                    
                    // Case 3: A1, A2, A4 - gap between A2 and A4 is isolated
                    // (previous seat A2 is adjacent to A1, so A1-A2 is a block, A4 is isolated)
                    if ($i > 0 && $cols[$i] == $cols[$i - 1] + 1) {
                        return true;
                    }
                    
                    // Case 4: A1, A3, A5 - gaps are consistent (every other seat)
                    // This is also not allowed (skip pattern)
                    if ($i + 2 < count($cols) && $cols[$i + 2] - $cols[$i + 1] == 2) {
                        // Check if all gaps are 2 (consistent skip pattern)
                        $allGapsAreTwo = true;
                        for ($j = 0; $j < count($cols) - 1; $j++) {
                            if ($cols[$j + 1] - $cols[$j] != 2) {
                                $allGapsAreTwo = false;
                                break;
                            }
                        }
                        if ($allGapsAreTwo) {
                            return true; // Consistent skip pattern (A1, A3, A5, A7...)
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check for too many corner seats
     * Kiểm tra có quá nhiều ghế ở góc không
     * Cho phép đặt 4 góc (A1, J1, J15, A15) hoặc số lượng hợp lý
     */
    private function hasTooManyCornerSeats(array $seatsByRow): bool
    {
        $cornerSeats = 0;
        $totalSeats = 0;
        $rows = array_keys($seatsByRow);
        
        // Get all seat positions to identify true corners
        $allSeats = [];
        $minRow = null;
        $maxRow = null;
        $minCol = PHP_INT_MAX;
        $maxCol = 0;
        
        foreach ($seatsByRow as $row => $cols) {
            $totalSeats += count($cols);
            
            if ($minRow === null || $row < $minRow) $minRow = $row;
            if ($maxRow === null || $row > $maxRow) $maxRow = $row;
            
            foreach ($cols as $col) {
                $allSeats[] = ['row' => $row, 'col' => $col];
                if ($col < $minCol) $minCol = $col;
                if ($col > $maxCol) $maxCol = $col;
            }
        }
        
        // Identify true corner seats (4 corners of the selection)
        // Corners are: (minRow, minCol), (minRow, maxCol), (maxRow, minCol), (maxRow, maxCol)
        $trueCorners = [
            ['row' => $minRow, 'col' => $minCol],
            ['row' => $minRow, 'col' => $maxCol],
            ['row' => $maxRow, 'col' => $minCol],
            ['row' => $maxRow, 'col' => $maxCol],
        ];
        
        // Count how many selected seats are true corners
        $trueCornerCount = 0;
        foreach ($allSeats as $seat) {
            foreach ($trueCorners as $corner) {
                if ($seat['row'] === $corner['row'] && $seat['col'] === $corner['col']) {
                    $trueCornerCount++;
                    break;
                }
            }
        }
        
        // Allow exactly 4 corners (A1, J1, J15, A15 pattern)
        if ($totalSeats === 4 && $trueCornerCount === 4) {
            return false; // This is a valid 4-corner selection
        }
        
        // Count corner seats (first and last in each row)
        foreach ($seatsByRow as $row => $cols) {
            $minColInRow = min($cols);
            $maxColInRow = max($cols);
            
            // Count corner seats (first and last in row)
            foreach ($cols as $col) {
                if ($col == $minColInRow || $col == $maxColInRow) {
                    $cornerSeats++;
                }
            }
        }
        
        // Allow up to 4 corner seats (reasonable limit)
        if ($cornerSeats <= 4) {
            return false; // Allow up to 4 corner seats
        }
        
        // If more than 4 corner seats and more than 50% are corners with multiple rows, it's suspicious
        if ($totalSeats > 4 && $cornerSeats > ($totalSeats * 0.5) && count($seatsByRow) >= 2) {
            return true;
        }

        return false;
    }

    /**
     * Check if seats form a block pattern (valid pattern)
     * Kiểm tra ghế có tạo thành block hợp lệ không
     */
    private function isBlockPattern(array $seatsByRow): bool
    {
        if (count($seatsByRow) == 1) {
            // Single row - check if seats are consecutive
            $cols = array_values($seatsByRow)[0];
            return $this->areConsecutive($cols);
        }

        // Multiple rows - check if they form a rectangular block
        $rows = array_keys($seatsByRow);
        $minRow = ord($rows[0]) - ord('A');
        $maxRow = ord($rows[count($rows) - 1]) - ord('A');
        
        // Check if all rows have the same columns (perfect rectangle)
        $firstRowCols = $seatsByRow[$rows[0]];
        $allSameCols = true;
        foreach ($rows as $row) {
            if ($seatsByRow[$row] !== $firstRowCols) {
                $allSameCols = false;
                break;
            }
        }
        
        if ($allSameCols) {
            return true; // Perfect rectangle
        }

        // Check if seats form a contiguous block (not necessarily perfect rectangle)
        // All seats should be adjacent to at least one other seat
        $allSeats = [];
        foreach ($seatsByRow as $row => $cols) {
            foreach ($cols as $col) {
                $allSeats[] = ['row' => $row, 'col' => $col];
            }
        }

        // Check if all seats are connected (adjacent to at least one other seat)
        $connected = true;
        foreach ($allSeats as $seat) {
            $hasNeighbor = false;
            foreach ($allSeats as $otherSeat) {
                if ($seat === $otherSeat) continue;
                
                $rowDiff = abs(ord($seat['row']) - ord($otherSeat['row']));
                $colDiff = abs($seat['col'] - $otherSeat['col']);
                
                // Adjacent if same row and col diff is 1, or same col and row diff is 1
                if (($rowDiff == 0 && $colDiff == 1) || ($rowDiff == 1 && $colDiff == 0)) {
                    $hasNeighbor = true;
                    break;
                }
            }
            if (!$hasNeighbor && count($allSeats) > 1) {
                $connected = false;
                break;
            }
        }

        return $connected;
    }

    /**
     * Check if numbers are consecutive
     */
    private function areConsecutive(array $numbers): bool
    {
        if (count($numbers) <= 1) {
            return true;
        }

        sort($numbers);
        for ($i = 0; $i < count($numbers) - 1; $i++) {
            if ($numbers[$i + 1] - $numbers[$i] != 1) {
                return false;
            }
        }

        return true;
    }
}

