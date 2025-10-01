import React, { useState } from 'react';
import Image from 'next/image';

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
  originalPrice?: number;
  discount?: number;
  showtimes?: string[];
}

interface MovieCardProps {
  movie: Movie;
  showBookingButton?: boolean;
  onBookTicket?: (movieId: number) => void;
  onWatchTrailer?: (movieId: number) => void;
}

const MovieCard: React.FC<MovieCardProps> = ({
  movie,
  showBookingButton = false,
  onBookTicket,
  onWatchTrailer
}) => {
  const [imageError, setImageError] = useState(false);
  const [showTooltip, setShowTooltip] = useState(false);

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price);
  };

  const renderStars = (rating: number) => {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;

    for (let i = 0; i < fullStars; i++) {
      stars.push(
        <span key={i} className="text-yellow-400 text-sm">⭐</span>
      );
    }

    if (hasHalfStar) {
      stars.push(
        <span key="half" className="text-yellow-400 text-sm">✨</span>
      );
    }

    const emptyStars = 5 - Math.ceil(rating);
    for (let i = 0; i < emptyStars; i++) {
      stars.push(
        <span key={`empty-${i}`} className="text-gray-400 text-sm">☆</span>
      );
    }

    return stars;
  };

  const getCTAText = () => {
    if (movie.isHot) return "Phim Hot 🔥";
    if (movie.isNew) return "Phim Mới 🆕";
    if (movie.isTrending) return "Đang Hot 📈";
    if (movie.discount && movie.discount > 0) return "Ưu đãi 💰";
    return "Đặt vé ngay";
  };

  const getCTAColor = () => {
    if (movie.isHot) return "bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600";
    if (movie.isNew) return "bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600";
    if (movie.isTrending) return "bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600";
    if (movie.discount && movie.discount > 0) return "bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600";
    return "bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700";
  };

  return (
    <div className="flex-shrink-0 w-72 group">
      <div className="movie-card bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl overflow-hidden hover:shadow-2xl cursor-pointer border border-gray-700 hover:border-gray-600">
        {/* Movie Image */}
        <div className="relative overflow-hidden">
          {imageError ? (
            <div className="w-full h-80 bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center">
              <Image
                src="/images/fallback-movie-poster.svg"
                alt="Fallback movie poster"
                width={300}
                height={400}
                className="w-full h-full object-cover opacity-50"
              />
            </div>
          ) : (
            <Image
              src={movie.image}
              alt={movie.title}
              width={300}
              height={400}
              className="w-full h-80 object-cover group-hover:scale-110 transition-transform duration-500"
              onError={() => setImageError(true)}
            />
          )}
          
          {/* Gradient Overlay */}
          <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          
          {/* Rating with Stars */}
          <div className="absolute top-3 left-3 flex items-center gap-1 bg-black/80 backdrop-blur-sm px-3 py-1.5 rounded-full">
            <div className="flex items-center gap-0.5">
              {renderStars(movie.rating)}
            </div>
            <span className="text-white text-sm font-semibold ml-1">{movie.rating}</span>
          </div>

          {/* Language Tag */}
          <div className="absolute top-3 right-3">
            <div className="flex items-center gap-1 bg-blue-600/90 backdrop-blur-sm px-2 py-1 rounded-full">
              <span className="text-white text-xs">🌐</span>
              <span className="text-white text-xs font-medium">{movie.language}</span>
            </div>
          </div>

          {/* Special Tags */}
          <div className="absolute top-12 right-3 flex flex-col gap-1">
            {movie.isHot && (
              <div className="flex items-center gap-1 bg-gradient-to-r from-orange-500 to-red-500 px-3 py-1 rounded-full shadow-lg">
                <span className="text-white text-xs">🔥</span>
                <span className="text-white text-xs font-bold">HOT</span>
              </div>
            )}
            {movie.isNew && (
              <div className="flex items-center gap-1 bg-gradient-to-r from-green-500 to-emerald-500 px-3 py-1 rounded-full shadow-lg">
                <span className="text-white text-xs">🆕</span>
                <span className="text-white text-xs font-bold">MỚI</span>
              </div>
            )}
            {movie.isTrending && (
              <div className="flex items-center gap-1 bg-gradient-to-r from-purple-500 to-pink-500 px-3 py-1 rounded-full shadow-lg">
                <span className="text-white text-xs">📈</span>
                <span className="text-white text-xs font-bold">TRENDING</span>
              </div>
            )}
          </div>

          {/* View Count */}
          <div className="absolute bottom-3 right-3 flex items-center gap-1 bg-black/80 backdrop-blur-sm px-2 py-1 rounded-full">
            <span className="text-white text-xs">👥</span>
            <span className="text-white text-xs font-medium">{movie.viewCount}</span>
          </div>

          {/* Hover Actions */}
          <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div className="flex gap-2">
              {onWatchTrailer && (
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    onWatchTrailer(movie.id);
                  }}
                  className="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors font-medium flex items-center gap-2"
                >
                  <span>▶️</span>
                  Trailer
                </button>
              )}
            </div>
          </div>
        </div>

        {/* Movie Info */}
        <div className="p-5">
          <h3 className="text-white text-xl font-bold mb-2 line-clamp-1">{movie.title}</h3>
          
          {/* Description with Tooltip */}
          <div className="relative">
            <p 
              className="text-gray-300 text-sm leading-relaxed line-clamp-2 mb-3 cursor-help"
              onMouseEnter={() => setShowTooltip(true)}
              onMouseLeave={() => setShowTooltip(false)}
            >
              {movie.description}
            </p>
            
            {showTooltip && (
              <div className="absolute z-10 bottom-full left-0 right-0 mb-2 p-3 bg-gray-900 text-white text-sm rounded-lg shadow-xl border border-gray-700">
                {movie.description}
                <div className="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
              </div>
            )}
          </div>
          
          {/* Movie Details */}
          {showBookingButton && (
            <div className="space-y-3 mb-4">
              {/* Duration and Genres */}
              <div className="flex items-center justify-between text-sm">
                <div className="flex items-center gap-1 text-gray-400">
                  <span>⏱️</span>
                  <span>{movie.duration} phút</span>
                </div>
                {movie.genres && movie.genres.length > 0 && (
                  <div className="flex items-center gap-1">
                    <span className="text-gray-400">🎭</span>
                    <span className="text-gray-300">{movie.genres[0]}</span>
                  </div>
                )}
              </div>

              {/* Pricing */}
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <span className="text-gray-400 text-sm">💰</span>
                  <div className="flex items-center gap-2">
                    {movie.originalPrice && movie.discount && movie.discount > 0 ? (
                      <>
                        <span className="text-orange-500 font-bold text-lg">
                          {formatPrice(movie.price!)}
                        </span>
                        <span className="text-gray-500 line-through text-sm">
                          {formatPrice(movie.originalPrice)}
                        </span>
                        <span className="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                          -{movie.discount}%
                        </span>
                      </>
                    ) : (
                      <span className="text-orange-500 font-bold text-lg">
                        {formatPrice(movie.price!)}
                      </span>
                    )}
                  </div>
                </div>
                
                {/* Urgency Indicators */}
                {movie.isHot && (
                  <span className="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold animate-pulse">
                    Chỉ hôm nay!
                  </span>
                )}
                {movie.discount && movie.discount > 0 && (
                  <span className="bg-yellow-500 text-black text-xs px-2 py-1 rounded-full font-bold">
                    Còn 2 suất cuối!
                  </span>
                )}
              </div>

              {/* Showtimes */}
              {movie.showtimes && movie.showtimes.length > 0 && (
                <div className="flex items-center gap-1 text-sm">
                  <span className="text-gray-400">🎟️</span>
                  <div className="flex gap-1">
                    {movie.showtimes.slice(0, 3).map((time, index) => (
                      <span key={index} className="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs">
                        {time}
                      </span>
                    ))}
                    {movie.showtimes.length > 3 && (
                      <span className="text-gray-400 text-xs">+{movie.showtimes.length - 3}</span>
                    )}
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Action Buttons */}
          {showBookingButton && onBookTicket && (
            <div className="space-y-2">
              <button
                onClick={() => onBookTicket(movie.id)}
                className={`w-full ${getCTAColor()} text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl`}
              >
                {getCTAText()}
              </button>
              
              <button className="w-full bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                <span>ℹ️</span>
                Chi tiết
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default MovieCard;
