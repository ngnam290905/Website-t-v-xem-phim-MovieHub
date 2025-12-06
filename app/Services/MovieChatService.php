<?php

namespace App\Services;

use App\Models\Phim;
use App\Models\SuatChieu;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MovieChatService
{
    public function processMessage($message)
    {
        $message = trim($message);
        if (empty($message)) {
            return 'Xin chào! Tôi có thể giúp bạn tìm thông tin về phim, lịch chiếu và đặt vé. Bạn muốn biết gì?';
        }

        $lowerMessage = Str::lower($message);

        // Tìm phim theo tên
        if ($this->isMovieNameQuery($lowerMessage)) {
            return $this->searchMovieByName($message);
        }

        // Tìm phim theo thể loại
        if ($this->isGenreQuery($lowerMessage)) {
            return $this->searchMovieByGenre($message);
        }

        // Tìm phim đang chiếu
        if ($this->isNowShowingQuery($lowerMessage)) {
            return $this->getNowShowingMovies();
        }

        // Tìm phim sắp chiếu
        if ($this->isComingSoonQuery($lowerMessage)) {
            return $this->getComingSoonMovies();
        }

        // Tìm phim hot
        if ($this->isHotMoviesQuery($lowerMessage)) {
            return $this->getHotMovies();
        }

        // Tìm lịch chiếu
        if ($this->isShowtimeQuery($lowerMessage)) {
            return $this->getShowtimes($message);
        }

        // Hướng dẫn đặt vé
        if ($this->isBookingQuery($lowerMessage)) {
            return $this->getBookingGuide();
        }

        // Câu hỏi chung về rạp
        if ($this->isGeneralQuery($lowerMessage)) {
            return $this->getGeneralAnswer($message);
        }

        // Tìm kiếm tổng quát
        return $this->generalSearch($message);
    }

    private function isMovieNameQuery($message)
    {
        $patterns = [
            'phim nào tên',
            'phim tên là',
            'phim có tên',
            'tìm phim',
            'phim',
            'movie',
        ];
        
        foreach ($patterns as $pattern) {
            if (Str::contains($message, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function isGenreQuery($message)
    {
        return Str::contains($message, ['thể loại', 'genre', 'hành động', 'tình cảm', 'kinh dị', 'hài', 'viễn tưởng', 'hoạt hình']);
    }

    private function isNowShowingQuery($message)
    {
        return Str::contains($message, ['đang chiếu', 'phim đang chiếu', 'now showing', 'hiện tại']);
    }

    private function isComingSoonQuery($message)
    {
        return Str::contains($message, ['sắp chiếu', 'phim sắp chiếu', 'coming soon', 'sắp ra mắt']);
    }

    private function isHotMoviesQuery($message)
    {
        return Str::contains($message, ['phim hot', 'phim nổi bật', 'phim hay', 'hot movies', 'featured']);
    }

    private function isShowtimeQuery($message)
    {
        return Str::contains($message, ['lịch chiếu', 'suất chiếu', 'giờ chiếu', 'showtime', 'schedule']);
    }

    private function isBookingQuery($message)
    {
        return Str::contains($message, ['đặt vé', 'mua vé', 'booking', 'ticket']);
    }

    private function isGeneralQuery($message)
    {
        $patterns = ['giá vé', 'giá', 'price', 'rạp', 'cinema', 'địa chỉ', 'address'];
        foreach ($patterns as $pattern) {
            if (Str::contains($message, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function searchMovieByName($message)
    {
        // Extract movie name from message
        $movieName = $this->extractMovieName($message);
        
        if (empty($movieName)) {
            $movies = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
                ->orderBy('ngay_khoi_chieu', 'desc')
                ->limit(5)
                ->get(['id', 'ten_phim', 'the_loai', 'poster', 'diem_danh_gia']);
            
            if ($movies->isEmpty()) {
                return 'Hiện tại không có phim nào đang hoặc sắp chiếu.';
            }
            
            $list = $movies->map(function($m) {
                $rating = $m->diem_danh_gia ? "⭐ {$m->diem_danh_gia}/10" : '';
                return "• {$m->ten_phim} ({$m->the_loai}) {$rating}";
            })->implode("\n");
            
            return "Đây là một số phim đang và sắp chiếu:\n\n{$list}\n\nBạn có thể hỏi chi tiết về bất kỳ phim nào!";
        }

        $movies = Phim::where(function($q) use ($movieName) {
                $q->where('ten_phim', 'like', "%{$movieName}%")
                  ->orWhere('ten_goc', 'like', "%{$movieName}%");
            })
            ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->limit(5)
            ->get();

        if ($movies->isEmpty()) {
            return "Xin lỗi, tôi không tìm thấy phim nào có tên chứa '{$movieName}'. Bạn có thể thử tìm với tên khác hoặc xem danh sách phim đang chiếu.";
        }

        if ($movies->count() === 1) {
            $movie = $movies->first();
            return $this->formatMovieDetail($movie);
        }

        $list = $movies->map(function($m) {
            $status = $m->trang_thai === 'dang_chieu' ? 'Đang chiếu' : 'Sắp chiếu';
            return "• {$m->ten_phim} ({$status})";
        })->implode("\n");

        return "Tôi tìm thấy {$movies->count()} phim:\n\n{$list}\n\nBạn muốn xem chi tiết phim nào?";
    }

    private function searchMovieByGenre($message)
    {
        $genres = ['hành động', 'tình cảm', 'kinh dị', 'hài', 'viễn tưởng', 'hoạt hình', 'phiêu lưu', 'giật gân'];
        $foundGenre = null;
        
        foreach ($genres as $genre) {
            if (Str::contains($message, $genre)) {
                $foundGenre = $genre;
                break;
            }
        }

        if (!$foundGenre) {
            return 'Bạn muốn tìm phim thể loại gì? Ví dụ: hành động, tình cảm, kinh dị, hài, viễn tưởng...';
        }

        $movies = Phim::where('the_loai', 'like', "%{$foundGenre}%")
            ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->limit(10)
            ->get(['id', 'ten_phim', 'the_loai', 'diem_danh_gia']);

        if ($movies->isEmpty()) {
            return "Hiện không có phim thể loại {$foundGenre} nào đang hoặc sắp chiếu.";
        }

        $list = $movies->map(function($m) {
            $rating = $m->diem_danh_gia ? "⭐ {$m->diem_danh_gia}/10" : '';
            return "• {$m->ten_phim} {$rating}";
        })->implode("\n");

        return "Các phim thể loại {$foundGenre}:\n\n{$list}";
    }

    private function getNowShowingMovies()
    {
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->limit(10)
            ->get(['id', 'ten_phim', 'the_loai', 'diem_danh_gia']);

        if ($movies->isEmpty()) {
            return 'Hiện không có phim nào đang chiếu.';
        }

        $list = $movies->map(function($m) {
            $rating = $m->diem_danh_gia ? "⭐ {$m->diem_danh_gia}/10" : '';
            return "• {$m->ten_phim} ({$m->the_loai}) {$rating}";
        })->implode("\n");

        return "Các phim đang chiếu:\n\n{$list}";
    }

    private function getComingSoonMovies()
    {
        $movies = Phim::where('trang_thai', 'sap_chieu')
            ->orderBy('ngay_khoi_chieu', 'asc')
            ->limit(10)
            ->get(['id', 'ten_phim', 'the_loai', 'ngay_khoi_chieu']);

        if ($movies->isEmpty()) {
            return 'Hiện không có phim nào sắp chiếu.';
        }

        $list = $movies->map(function($m) {
            $date = $m->ngay_khoi_chieu ? $m->ngay_khoi_chieu->format('d/m/Y') : '';
            return "• {$m->ten_phim} ({$m->the_loai}) - Khởi chiếu: {$date}";
        })->implode("\n");

        return "Các phim sắp chiếu:\n\n{$list}";
    }

    private function getHotMovies()
    {
        $movies = Phim::where('hot', true)
            ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orderBy('diem_danh_gia', 'desc')
            ->limit(10)
            ->get(['id', 'ten_phim', 'the_loai', 'diem_danh_gia']);

        if ($movies->isEmpty()) {
            return 'Hiện không có phim hot nào.';
        }

        $list = $movies->map(function($m) {
            $rating = $m->diem_danh_gia ? "⭐ {$m->diem_danh_gia}/10" : '';
            return "• {$m->ten_phim} ({$m->the_loai}) {$rating}";
        })->implode("\n");

        return "Các phim hot đang được yêu thích:\n\n{$list}";
    }

    private function getShowtimes($message)
    {
        // Extract movie name if mentioned
        $movieName = $this->extractMovieName($message);
        
        if ($movieName) {
            $movie = Phim::where(function($q) use ($movieName) {
                    $q->where('ten_phim', 'like', "%{$movieName}%")
                      ->orWhere('ten_goc', 'like', "%{$movieName}%");
                })
                ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
                ->first();

            if (!$movie) {
                return "Không tìm thấy phim '{$movieName}'. Bạn có thể xem danh sách phim đang chiếu.";
            }

            $showtimes = SuatChieu::where('id_phim', $movie->id)
                ->where('thoi_gian_bat_dau', '>=', now())
                ->where('trang_thai', 1)
                ->with(['phongChieu'])
                ->orderBy('thoi_gian_bat_dau')
                ->limit(10)
                ->get();

            if ($showtimes->isEmpty()) {
                return "Phim '{$movie->ten_phim}' hiện chưa có suất chiếu nào. Vui lòng kiểm tra lại sau.";
            }

            $list = $showtimes->map(function($s) {
                $time = $s->thoi_gian_bat_dau->format('d/m/Y H:i');
                $room = $s->phongChieu->ten_phong ?? 'N/A';
                return "• {$time} - Phòng {$room}";
            })->implode("\n");

            return "Lịch chiếu phim '{$movie->ten_phim}':\n\n{$list}";
        }

        return 'Bạn muốn xem lịch chiếu của phim nào? Hãy cho tôi biết tên phim.';
    }

    private function getBookingGuide()
    {
        return "Để đặt vé, bạn có thể:\n\n" .
               "1. Chọn phim bạn muốn xem\n" .
               "2. Chọn suất chiếu phù hợp\n" .
               "3. Chọn ghế ngồi\n" .
               "4. Chọn combo (nếu muốn)\n" .
               "5. Thanh toán\n\n" .
               "Bạn có thể đặt vé trực tiếp trên website hoặc đến quầy vé tại rạp.";
    }

    private function getGeneralAnswer($message)
    {
        if (Str::contains($message, ['giá', 'price'])) {
            return "Giá vé tại rạp:\n\n" .
                   "• Ghế thường: 80,000 VNĐ\n" .
                   "• Ghế VIP: 120,000 VNĐ\n" .
                   "• Ghế đôi: 200,000 VNĐ\n\n" .
                   "Giá có thể thay đổi tùy theo phim và suất chiếu.";
        }

        if (Str::contains($message, ['địa chỉ', 'address', 'rạp', 'cinema'])) {
            return "Rạp chiếu phim MovieHub\n\n" .
                   "Địa chỉ: Vui lòng liên hệ admin để biết địa chỉ cụ thể.\n" .
                   "Hotline: Vui lòng xem thông tin trên website.";
        }

        return 'Tôi có thể giúp bạn tìm phim, xem lịch chiếu và đặt vé. Bạn muốn biết gì?';
    }

    private function generalSearch($message)
    {
        // Tìm kiếm tổng quát trong database
        $movies = Phim::where(function($q) use ($message) {
                $q->where('ten_phim', 'like', "%{$message}%")
                  ->orWhere('ten_goc', 'like', "%{$message}%")
                  ->orWhere('dao_dien', 'like', "%{$message}%")
                  ->orWhere('dien_vien', 'like', "%{$message}%")
                  ->orWhere('the_loai', 'like', "%{$message}%")
                  ->orWhere('mo_ta', 'like', "%{$message}%");
            })
            ->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->limit(5)
            ->get();

        if ($movies->isNotEmpty()) {
            if ($movies->count() === 1) {
                return $this->formatMovieDetail($movies->first());
            }

            $list = $movies->map(function($m) {
                $rating = $m->diem_danh_gia ? "⭐ {$m->diem_danh_gia}/10" : '';
                return "• {$m->ten_phim} ({$m->the_loai}) {$rating}";
            })->implode("\n");

            return "Tôi tìm thấy {$movies->count()} kết quả:\n\n{$list}\n\nBạn muốn xem chi tiết phim nào?";
        }

        return "Xin lỗi, tôi không tìm thấy thông tin liên quan đến '{$message}'. " .
               "Bạn có thể thử:\n" .
               "• Tìm phim theo tên\n" .
               "• Xem phim đang chiếu\n" .
               "• Xem phim sắp chiếu\n" .
               "• Tìm phim theo thể loại";
    }

    private function extractMovieName($message)
    {
        // Simple extraction - có thể cải thiện sau
        $patterns = [
            '/phim\s+([^\s]+(?:\s+[^\s]+)*)/i',
            '/tên\s+phim\s+([^\s]+(?:\s+[^\s]+)*)/i',
            '/phim\s+nào\s+tên\s+([^\s]+(?:\s+[^\s]+)*)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return trim($matches[1]);
            }
        }

        // Nếu không match pattern, thử lấy từ sau "phim"
        if (preg_match('/phim\s+(.+)/i', $message, $matches)) {
            $name = trim($matches[1]);
            // Loại bỏ các từ không cần thiết
            $name = preg_replace('/\b(tên|nào|là|gì|có)\b/i', '', $name);
            return trim($name);
        }

        return '';
    }

    private function formatMovieDetail($movie)
    {
        $detail = "📽️ {$movie->ten_phim}\n\n";
        
        if ($movie->ten_goc) {
            $detail .= "Tên gốc: {$movie->ten_goc}\n";
        }
        
        if ($movie->the_loai) {
            $detail .= "Thể loại: {$movie->the_loai}\n";
        }
        
        if ($movie->do_dai) {
            $detail .= "Thời lượng: {$movie->do_dai} phút\n";
        }
        
        if ($movie->dao_dien) {
            $detail .= "Đạo diễn: {$movie->dao_dien}\n";
        }
        
        if ($movie->dien_vien) {
            $detail .= "Diễn viên: {$movie->dien_vien}\n";
        }
        
        if ($movie->diem_danh_gia) {
            $detail .= "Đánh giá: ⭐ {$movie->diem_danh_gia}/10\n";
        }
        
        if ($movie->mo_ta_ngan) {
            $detail .= "\n{$movie->mo_ta_ngan}\n";
        }
        
        $detail .= "\nTrạng thái: " . ($movie->trang_thai === 'dang_chieu' ? 'Đang chiếu' : 'Sắp chiếu');
        
        if ($movie->trang_thai === 'dang_chieu') {
            $detail .= "\n\nBạn có thể hỏi tôi về lịch chiếu của phim này!";
        }

        return $detail;
    }
}

