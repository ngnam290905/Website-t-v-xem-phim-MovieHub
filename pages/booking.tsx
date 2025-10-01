import React, { useState } from 'react';
import { useRouter } from 'next/router';
import Image from 'next/image';

interface Movie {
  id: number;
  title: string;
  description: string;
  genres: string[];
  origin: string;
  duration: number;
  showtimes: number;
  price: number;
  originalPrice?: number;
  image: string;
  language: string;
  viewCount: string;
}

const BookingPage = () => {
  const router = useRouter();
  const [selectedSeats, setSelectedSeats] = useState<string[]>([]);
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [customerInfo, setCustomerInfo] = useState({
    name: '',
    email: '',
    phone: ''
  });

  // Mock movie data - in real app, this would come from API
  const movie: Movie = {
    id: 1,
    title: "Red Vengeance",
    description: "Một đặc vụ bí ẩn trở lại sau nhiều năm để phá một âm mưu đen tối đe doạ thành phố",
    genres: ["Hành động", "Kịch tính", "Tội phạm"],
    origin: "USA",
    duration: 128,
    showtimes: 3,
    price: 90000,
    image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
    language: "English",
    viewCount: "5.013"
  };

  const timeSlots = [
    { time: '14:00', available: true },
    { time: '16:30', available: true },
    { time: '19:00', available: false },
    { time: '21:30', available: true }
  ];

  const seatRows = ['A', 'B', 'C', 'D', 'E', 'F'];
  const seatNumbers = Array.from({ length: 12 }, (_, i) => i + 1);

  const handleSeatSelect = (seat: string) => {
    if (selectedSeats.includes(seat)) {
      setSelectedSeats(selectedSeats.filter(s => s !== seat));
    } else {
      setSelectedSeats([...selectedSeats, seat]);
    }
  };

  const handleBooking = () => {
    if (selectedSeats.length === 0 || !selectedTime || !customerInfo.name || !customerInfo.email || !customerInfo.phone) {
      alert('Vui lòng điền đầy đủ thông tin và chọn ghế ngồi!');
      return;
    }
    
    // Here you would typically send data to your backend
    console.log('Booking data:', {
      movie,
      selectedSeats,
      selectedTime,
      customerInfo,
      totalPrice: movie.price * selectedSeats.length
    });
    
    alert('Đặt vé thành công! Bạn sẽ nhận được email xác nhận.');
    router.push('/');
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price);
  };

  return (
    <div className="min-h-screen bg-black text-white">
      {/* Header */}
      <div className="bg-gray-900 py-4 px-6">
        <div className="max-w-7xl mx-auto flex items-center justify-between">
          <button
            onClick={() => router.back()}
            className="text-orange-500 hover:text-orange-400 flex items-center gap-2"
          >
            ← Quay lại
          </button>
          <h1 className="text-2xl font-bold">Đặt Vé Xem Phim</h1>
          <div></div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto py-8 px-6">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Movie Info */}
          <div className="lg:col-span-1">
            <div className="bg-gray-800 rounded-xl overflow-hidden">
              <Image
                src={movie.image}
                alt={movie.title}
                width={300}
                height={400}
                className="w-full h-80 object-cover"
              />
              <div className="p-6">
                <h2 className="text-2xl font-bold mb-3">{movie.title}</h2>
                <p className="text-gray-300 mb-4">{movie.description}</p>
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-gray-400">Thể loại:</span>
                    <span className="text-white">{movie.genres.join(', ')}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-400">Thời lượng:</span>
                    <span className="text-white">{movie.duration} phút</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-400">Ngôn ngữ:</span>
                    <span className="text-white">{movie.language}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-400">Giá vé:</span>
                    <span className="text-orange-500 font-bold">{formatPrice(movie.price)}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Booking Form */}
          <div className="lg:col-span-2">
            <div className="bg-gray-800 rounded-xl p-6">
              <h3 className="text-xl font-bold mb-6">Chọn Suất Chiếu</h3>
              
              {/* Time Selection */}
              <div className="mb-6">
                <h4 className="text-lg font-semibold mb-3">Thời gian chiếu</h4>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                  {timeSlots.map((slot) => (
                    <button
                      key={slot.time}
                      onClick={() => setSelectedTime(slot.time)}
                      disabled={!slot.available}
                      className={`p-3 rounded-lg border-2 transition-colors ${
                        selectedTime === slot.time
                          ? 'border-orange-500 bg-orange-500 text-white'
                          : slot.available
                          ? 'border-gray-600 hover:border-orange-500 text-white'
                          : 'border-gray-700 bg-gray-700 text-gray-500 cursor-not-allowed'
                      }`}
                    >
                      {slot.time}
                    </button>
                  ))}
                </div>
              </div>

              {/* Seat Selection */}
              <div className="mb-6">
                <h4 className="text-lg font-semibold mb-3">Chọn Ghế Ngồi</h4>
                <div className="bg-gray-700 p-4 rounded-lg">
                  <div className="text-center mb-4">
                    <div className="w-16 h-8 bg-gray-600 rounded mx-auto mb-2"></div>
                    <span className="text-sm text-gray-400">Màn hình</span>
                  </div>
                  
                  <div className="grid grid-cols-12 gap-1 mb-4">
                    {seatRows.map((row) => (
                      <React.Fragment key={row}>
                        <div className="text-center text-sm font-bold text-gray-400 py-2">
                          {row}
                        </div>
                        {seatNumbers.map((num) => {
                          const seat = `${row}${num}`;
                          const isSelected = selectedSeats.includes(seat);
                          return (
                            <button
                              key={seat}
                              onClick={() => handleSeatSelect(seat)}
                              className={`w-8 h-8 rounded text-xs font-bold transition-colors ${
                                isSelected
                                  ? 'bg-orange-500 text-white'
                                  : 'bg-gray-600 hover:bg-gray-500 text-white'
                              }`}
                            >
                              {num}
                            </button>
                          );
                        })}
                      </React.Fragment>
                    ))}
                  </div>
                  
                  <div className="flex justify-center gap-4 text-sm">
                    <div className="flex items-center gap-2">
                      <div className="w-4 h-4 bg-gray-600 rounded"></div>
                      <span>Trống</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-4 h-4 bg-orange-500 rounded"></div>
                      <span>Đã chọn</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Customer Info */}
              <div className="mb-6">
                <h4 className="text-lg font-semibold mb-3">Thông Tin Khách Hàng</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <input
                    type="text"
                    placeholder="Họ và tên"
                    value={customerInfo.name}
                    onChange={(e) => setCustomerInfo({...customerInfo, name: e.target.value})}
                    className="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-orange-500 focus:outline-none"
                  />
                  <input
                    type="email"
                    placeholder="Email"
                    value={customerInfo.email}
                    onChange={(e) => setCustomerInfo({...customerInfo, email: e.target.value})}
                    className="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-orange-500 focus:outline-none"
                  />
                  <input
                    type="tel"
                    placeholder="Số điện thoại"
                    value={customerInfo.phone}
                    onChange={(e) => setCustomerInfo({...customerInfo, phone: e.target.value})}
                    className="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-orange-500 focus:outline-none"
                  />
                </div>
              </div>

              {/* Booking Summary */}
              <div className="bg-gray-700 p-4 rounded-lg mb-6">
                <h4 className="text-lg font-semibold mb-3">Tóm Tắt Đặt Vé</h4>
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span>Phim:</span>
                    <span>{movie.title}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Suất chiếu:</span>
                    <span>{selectedTime || 'Chưa chọn'}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Ghế ngồi:</span>
                    <span>{selectedSeats.length > 0 ? selectedSeats.join(', ') : 'Chưa chọn'}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Số lượng vé:</span>
                    <span>{selectedSeats.length}</span>
                  </div>
                  <div className="flex justify-between font-bold text-lg border-t border-gray-600 pt-2">
                    <span>Tổng cộng:</span>
                    <span className="text-orange-500">
                      {formatPrice(movie.price * selectedSeats.length)}
                    </span>
                  </div>
                </div>
              </div>

              {/* Book Button */}
              <button
                onClick={handleBooking}
                className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg transition-colors duration-200"
              >
                Xác Nhận Đặt Vé
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default BookingPage;
