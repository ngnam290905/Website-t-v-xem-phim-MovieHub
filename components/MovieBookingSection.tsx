import React, { useState } from 'react';
import Image from 'next/image';
import { useRouter } from 'next/router';

interface BookingMovie {
  id: number;
  title: string;
  description: string;
  genres: string[];
  origin: string;
  duration: number; // in minutes
  showtimes: number;
  price: number;
  originalPrice?: number;
  image: string;
  language: string;
  viewCount: string;
}

const MovieBookingSection = () => {
  const router = useRouter();
  const [selectedMovie, setSelectedMovie] = useState<number | null>(null);

  const bookingMovies: BookingMovie[] = [
    {
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
    },
    {
      id: 2,
      title: "Haunted Mansion",
      description: "Một gia đình chuyển đến ngôi nhà cổ và khám phá những bí mật đáng sợ ẩn giấu bên trong",
      genres: ["Kinh dị", "Bí ẩn", "Siêu nhiên"],
      origin: "USA",
      duration: 105,
      showtimes: 3,
      price: 85000,
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      language: "English",
      viewCount: "1.170"
    },
    {
      id: 3,
      title: "The Mystery Detective",
      description: "Thám tử tài ba phải giải quyết vụ án phức tạp nhất trong sự nghiệp của mình",
      genres: ["Bí ẩn", "Tội phạm", "Kịch tính"],
      origin: "USA",
      duration: 115,
      showtimes: 3,
      price: 88000,
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      language: "English",
      viewCount: "1.461"
    },
    {
      id: 4,
      title: "Love & Laughter",
      description: "Một câu chuyện tình yêu hài hước và cảm động giữa hai người hoàn toàn khác biệt",
      genres: ["Hài", "Lãng mạn", "Tình cảm"],
      origin: "USA",
      duration: 95,
      showtimes: 3,
      price: 56000,
      originalPrice: 70000,
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      language: "English",
      viewCount: "9.205"
    }
  ];

  const handleBookTicket = (movieId: number) => {
    setSelectedMovie(movieId);
    // Navigate to booking page with movie ID
    router.push(`/booking?movieId=${movieId}`);
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price);
  };

  return (
    <section className="bg-gray-900 py-12 px-6">
      <div className="max-w-7xl mx-auto">
        {/* Header Section */}
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold text-white mb-4">Đặt Vé Xem Phim</h2>
          <p className="text-gray-300 text-lg">
            Chọn phim yêu thích và đặt vé ngay để có trải nghiệm xem phim tuyệt vời
          </p>
        </div>

        {/* Movie Booking Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {bookingMovies.map((movie) => (
            <div key={movie.id} className="bg-gray-800 rounded-xl overflow-hidden hover:transform hover:scale-105 transition-transform duration-300">
              {/* Movie Image */}
              <div className="relative">
                <Image
                  src={movie.image}
                  alt={movie.title}
                  width={300}
                  height={400}
                  className="w-full h-80 object-cover"
                />
                
                {/* Language Tag */}
                <div className="absolute top-3 left-3">
                  <div className="flex items-center gap-1 bg-blue-600 px-2 py-1 rounded-full">
                    <span className="text-white text-xs">🌐</span>
                    <span className="text-white text-xs">{movie.language}</span>
                  </div>
                </div>

                {/* View Count */}
                <div className="absolute top-3 right-3">
                  <div className="flex items-center gap-1 bg-black bg-opacity-70 px-2 py-1 rounded-full">
                    <span className="text-white text-xs">👥</span>
                    <span className="text-white text-xs">{movie.viewCount}</span>
                  </div>
                </div>
              </div>

              {/* Movie Info */}
              <div className="p-4">
                <h3 className="text-white text-xl font-bold mb-2">{movie.title}</h3>
                <p className="text-gray-300 text-sm leading-relaxed mb-3 line-clamp-2">
                  {movie.description}
                </p>
                
                {/* Genres */}
                <div className="flex flex-wrap gap-1 mb-3">
                  {movie.genres.map((genre, index) => (
                    <span
                      key={index}
                      className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded"
                    >
                      {genre}
                    </span>
                  ))}
                </div>

                {/* Origin */}
                <p className="text-gray-400 text-sm mb-3">{movie.origin}</p>

                {/* Booking Details */}
                <div className="space-y-2 mb-4">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-400">Thời lượng:</span>
                    <span className="text-white">{movie.duration} phút</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-400">Suất chiếu:</span>
                    <span className="text-white">{movie.showtimes} suất</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-400">Giá vé:</span>
                    <div className="text-right">
                      {movie.originalPrice && (
                        <span className="text-gray-500 line-through text-xs">
                          {formatPrice(movie.originalPrice)}
                        </span>
                      )}
                      <span className="text-orange-500 font-bold ml-2">
                        {formatPrice(movie.price)}
                      </span>
                    </div>
                  </div>
                </div>

                {/* Book Ticket Button */}
                <button
                  onClick={() => handleBookTicket(movie.id)}
                  className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200"
                >
                  Đặt vé ngay
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* View All Movies Button */}
        <div className="text-center mt-8">
          <button className="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg transition-colors duration-200">
            Xem tất cả phim đang chiếu
          </button>
        </div>
      </div>
    </section>
  );
};

export default MovieBookingSection;
