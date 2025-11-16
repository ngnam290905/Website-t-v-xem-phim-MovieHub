<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AiTraining;

class AiTrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiTraining::create(['question' => 'Xin chào', 'answer' => 'Chào bạn! Tôi là trợ lý AI của rạp chiếu phim. Tôi có thể giúp bạn tìm phim, suất chiếu hoặc đặt vé.', 'intent' => 'greeting']);
        AiTraining::create(['question' => 'Chào', 'answer' => 'Chào bạn! Bạn cần hỗ trợ gì về phim hay đặt vé không?', 'intent' => 'greeting']);
        AiTraining::create(['question' => 'Cảm ơn', 'answer' => 'Không có gì! Rất vui được giúp đỡ bạn.', 'intent' => 'thanks']);
        AiTraining::create(['question' => 'Tạm biệt', 'answer' => 'Tạm biệt! Hẹn gặp lại bạn lần sau.', 'intent' => 'goodbye']);
        AiTraining::create(['question' => 'Bạn là ai', 'answer' => 'Tôi là AI Chatbot của hệ thống đặt vé rạp chiếu phim MovieHub. Tôi có thể trả lời câu hỏi và hỗ trợ đặt vé.', 'intent' => 'about']);
        AiTraining::create(['question' => 'Giờ mở cửa', 'answer' => 'Rạp chiếu phim mở cửa từ 8:00 sáng đến 12:00 đêm hàng ngày.', 'intent' => 'info']);
        AiTraining::create(['question' => 'Cách đặt vé', 'answer' => 'Bạn có thể đặt vé trực tuyến qua website hoặc đến quầy tại rạp. Tôi cũng có thể giúp bạn tìm suất chiếu phù hợp.', 'intent' => 'info']);
        AiTraining::create(['question' => 'Phim đang chiếu', 'answer' => 'Hiện tại có nhiều phim đang chiếu. Bạn muốn tìm phim theo thể loại nào?', 'intent' => 'movie_info']);
        AiTraining::create(['question' => 'Giá vé', 'answer' => 'Giá vé phụ thuộc vào loại ghế và suất chiếu. Vé thường từ 50.000 đến 150.000 VND.', 'intent' => 'price']);
        AiTraining::create(['question' => 'Combo', 'answer' => 'Chúng tôi có combo bắp nước với giá ưu đãi. Bạn có thể xem chi tiết trên website.', 'intent' => 'combo']);
        AiTraining::create(['question' => 'Đổi vé', 'answer' => 'Bạn có thể đổi vé trước giờ chiếu 2 tiếng. Vui lòng liên hệ quầy hoặc gọi hotline.', 'intent' => 'policy']);
        AiTraining::create(['question' => 'Hoàn tiền', 'answer' => 'Chính sách hoàn tiền áp dụng trong vòng 24h sau khi đặt vé online.', 'intent' => 'policy']);
        AiTraining::create(['question' => 'Độ tuổi', 'answer' => 'Mỗi phim có độ tuổi quy định. Vui lòng kiểm tra thông tin phim trước khi mua vé.', 'intent' => 'info']);
        AiTraining::create(['question' => 'Phim 3D', 'answer' => 'Chúng tôi có nhiều phim chiếu 3D. Bạn có thể tìm kiếm trên website.', 'intent' => 'movie_info']);
        AiTraining::create(['question' => 'Khuyến mãi', 'answer' => 'Hiện tại có khuyến mãi cho sinh viên và thành viên. Kiểm tra chi tiết trên app.', 'intent' => 'promo']);
        AiTraining::create(['question' => 'Đặt chỗ', 'answer' => 'Bạn có thể đặt chỗ online hoặc tại rạp. Ghế sẽ được giữ trong 15 phút.', 'intent' => 'booking']);
        AiTraining::create(['question' => 'Thanh toán', 'answer' => 'Chúng tôi chấp nhận thanh toán bằng tiền mặt, thẻ tín dụng và ví điện tử.', 'intent' => 'payment']);
        AiTraining::create(['question' => 'Liên hệ', 'answer' => 'Bạn có thể liên hệ hotline 1900-xxxx hoặc email support@moviehub.com', 'intent' => 'contact']);
        AiTraining::create(['question' => 'Địa chỉ', 'answer' => 'Rạp chiếu phim tại địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM', 'intent' => 'location']);
        AiTraining::create(['question' => 'Bãi đậu xe', 'answer' => 'Rạp có bãi đậu xe miễn phí cho khách hàng.', 'intent' => 'facility']);
    }
}
