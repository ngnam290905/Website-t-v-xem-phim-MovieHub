import React from 'react';
import Image from 'next/image';
import { useRouter } from 'next/router';

interface Movie {
  id: number;
  title: string;
  description: string;
  rating: number;
  language: string;
  viewCount: string;
  image: string;
  isHot?: boolean;
  isNew?: boolean;
  isTrending?: boolean;
  duration?: number;
  price?: number;
  genres?: string[];
}

interface MovieCategorySectionProps {
  title: string;
  subtitle: string;
  icon: string;
  movies: Movie[];
  bgColor?: string;
  showViewAll?: boolean;
  viewAllCount?: number;
  showBookingButton?: boolean;
}

const MovieCategorySection: React.FC<MovieCategorySectionProps> = ({
  title,
  subtitle,
  icon,
  movies,
  bgColor = "bg-gray-900",
  showViewAll = true,
  viewAllCount = 25,
  showBookingButton = false
}) => {
  const router = useRouter();

  const handleBookTicket = (movieId: number) => {
    router.push(`/booking?movieId=${movieId}`);
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price);
  };
  return (
    <section className={`${bgColor} py-12 px-6`}>
      <div className="max-w-7xl mx-auto">
        {/* Header Section */}
        <div className="flex justify-between items-start mb-8">
          <div>
            <div className="flex items-center gap-3 mb-2">
              <span className="text-orange-500 text-2xl">{icon}</span>
              <h2 className="text-4xl font-bold text-white">{title}</h2>
            </div>
            <p className="text-gray-300 text-lg">
              {subtitle}
            </p>
          </div>
          
          {showViewAll && (
            <button className="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium">
              Xem tất cả {viewAllCount} phim →
            </button>
          )}
        </div>

        {/* Movie Cards */}
        <div className="overflow-x-auto">
          <div className="flex gap-6 pb-4">
            {movies.map((movie) => (
              <div key={movie.id} className="flex-shrink-0 w-72">
                <div className="bg-gray-800 rounded-xl overflow-hidden hover:transform hover:scale-105 transition-transform duration-300 cursor-pointer">
                  {/* Movie Image */}
                  <div className="relative">
                    <Image
                      src={movie.image}
                      alt={movie.title}
                      width={300}
                      height={400}
                      className="w-full h-80 object-cover"
                    />
                    
                    {/* Rating */}
                    <div className="absolute top-3 left-3 flex items-center gap-1 bg-black bg-opacity-70 px-2 py-1 rounded-full">
                      <span className="text-yellow-400">⭐</span>
                      <span className="text-white text-sm font-medium">{movie.rating}</span>
                    </div>

                    {/* Language Tag */}
                    <div className="absolute top-12 left-3">
                      <div className="flex items-center gap-1 bg-blue-600 px-2 py-1 rounded-full">
                        <span className="text-white text-xs">🌐</span>
                        <span className="text-white text-xs">{movie.language}</span>
                      </div>
                    </div>

                    {/* Special Tags */}
                    <div className="absolute top-3 right-3 flex flex-col gap-1">
                      {movie.isHot && (
                        <div className="flex items-center gap-1 bg-orange-500 px-2 py-1 rounded-full">
                          <span className="text-white text-xs">🔥</span>
                          <span className="text-white text-xs">Hot</span>
                        </div>
                      )}
                      {movie.isNew && (
                        <div className="flex items-center gap-1 bg-green-500 px-2 py-1 rounded-full">
                          <span className="text-white text-xs">🆕</span>
                          <span className="text-white text-xs">Mới</span>
                        </div>
                      )}
                      {movie.isTrending && (
                        <div className="flex items-center gap-1 bg-purple-500 px-2 py-1 rounded-full">
                          <span className="text-white text-xs">📈</span>
                          <span className="text-white text-xs">Trending</span>
                        </div>
                      )}
                    </div>

                    {/* View Count */}
                    <div className="absolute bottom-3 right-3 flex items-center gap-1 bg-black bg-opacity-70 px-2 py-1 rounded-full">
                      <span className="text-white text-xs">👥</span>
                      <span className="text-white text-xs">{movie.viewCount}</span>
                    </div>
                  </div>

                  {/* Movie Info */}
                  <div className="p-4">
                    <h3 className="text-white text-xl font-bold mb-2">{movie.title}</h3>
                    <p className="text-gray-300 text-sm leading-relaxed line-clamp-3 mb-3">{movie.description}</p>
                    
                    {/* Movie Details */}
                    {showBookingButton && movie.duration && movie.price && (
                      <div className="space-y-2 mb-4 text-sm">
                        <div className="flex justify-between">
                          <span className="text-gray-400">Thời lượng:</span>
                          <span className="text-white">{movie.duration} phút</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-400">Giá vé:</span>
                          <span className="text-orange-500 font-bold">{formatPrice(movie.price)}</span>
                        </div>
                        {movie.genres && (
                          <div className="flex flex-wrap gap-1">
                            {movie.genres.map((genre, index) => (
                              <span
                                key={index}
                                className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded"
                              >
                                {genre}
                              </span>
                            ))}
                          </div>
                        )}
                      </div>
                    )}

                    {/* Book Ticket Button */}
                    {showBookingButton && (
                      <button
                        onClick={() => handleBookTicket(movie.id)}
                        className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                      >
                        Đặt vé ngay
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export default MovieCategorySection;
