import React from 'react';
import { useRouter } from 'next/router';
import MovieCard from './MovieCard';

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
  originalPrice?: number;
  discount?: number;
  genres?: string[];
  showtimes?: string[];
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
  onWatchTrailer?: (movieId: number) => void;
}

const MovieCategorySection: React.FC<MovieCategorySectionProps> = ({
  title,
  subtitle,
  icon,
  movies,
  bgColor = "bg-gray-900",
  showViewAll = true,
  viewAllCount = 25,
  showBookingButton = false,
  onWatchTrailer
}) => {
  const router = useRouter();

  const handleBookTicket = (movieId: number) => {
    router.push(`/booking?movieId=${movieId}`);
  };

  const handleWatchTrailer = (movieId: number) => {
    if (onWatchTrailer) {
      onWatchTrailer(movieId);
    } else {
      // Default behavior - could open a modal or redirect to trailer page
      console.log(`Watch trailer for movie ${movieId}`);
    }
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
        <div className="overflow-x-auto movie-scroll">
          <div className="flex gap-6 pb-4">
            {movies.map((movie) => (
              <MovieCard
                key={movie.id}
                movie={movie}
                showBookingButton={showBookingButton}
                onBookTicket={handleBookTicket}
                onWatchTrailer={handleWatchTrailer}
              />
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export default MovieCategorySection;
