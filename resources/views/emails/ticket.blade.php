<x-mail::message>
# Vé xem phim của bạn

Xin chào {{ $booking->ten_khach_hang ?? ($booking->nguoiDung->ho_ten ?? 'Quý khách') }},

Cảm ơn bạn đã đặt vé tại rạp của chúng tôi! Dưới đây là thông tin chi tiết về vé của bạn.

---

## 🎬 Thông tin phim

**{{ $booking->suatChieu->phim->ten_phim ?? 'N/A' }}**

@if($booking->suatChieu->phim->do_dai)
Thời lượng: {{ $booking->suatChieu->phim->do_dai }} phút
@endif

---

## 🎭 Thông tin suất chiếu

**Ngày chiếu:** {{ $booking->suatChieu->thoi_gian_bat_dau->format('d/m/Y') }}

**Giờ chiếu:** {{ $booking->suatChieu->thoi_gian_bat_dau->format('H:i') }}

**Rạp - Phòng chiếu:** {{ $booking->suatChieu->phongChieu->ten_phong ?? 'N/A' }}

---

## 🪑 Thông tin đặt ghế

**Danh sách ghế:**
@foreach($booking->chiTietDatVe as $detail)
- {{ $detail->ghe->so_ghe ?? 'N/A' }} 
@if($detail->ghe && $detail->ghe->loaiGhe)
({{ $detail->ghe->loaiGhe->ten_loai ?? 'Standard' }})
@endif
@endforeach

---

## 🎫 Mã vé

**Mã vé:** `{{ $booking->ticket_code ?? 'N/A' }}`

---

## 💳 Thông tin thanh toán

**Tổng tiền:** {{ number_format($booking->tong_tien ?? 0, 0, ',', '.') }} VNĐ

@if($booking->thanhToan)
**Phương thức thanh toán:** {{ $booking->thanhToan->phuong_thuc ?? 'N/A' }}

@if($booking->thanhToan->ma_giao_dich)
**Mã giao dịch:** {{ $booking->thanhToan->ma_giao_dich }}
@endif

**Thời gian thanh toán:** {{ $booking->thanhToan->thoi_gian ? \Carbon\Carbon::parse($booking->thanhToan->thoi_gian)->format('d/m/Y H:i') : 'N/A' }}
@endif

---

## 📞 Hỗ trợ

Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ:

**Hotline:** 1900 1234 (Miễn phí)

**Email hỗ trợ:** support@cinema.com

**Thời gian hỗ trợ:** 8:00 - 22:00 hàng ngày

---

## ⚠️ Lưu ý quan trọng

- Vui lòng đến rạp trước **15 phút** so với giờ chiếu
- Đến quầy chỉ cần đưa **mã vé** để nhân viên kiểm tra
- Vé không được hoàn tiền sau khi thanh toán
- Vui lòng giữ vé cẩn thận cho đến khi vào phòng chiếu

---

Chúc bạn xem phim vui vẻ! 🎉

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
