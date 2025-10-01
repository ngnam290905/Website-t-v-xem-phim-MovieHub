import React from 'react';
import MovieCategorySection from './MovieCategorySection';

const RomanceMoviesSection = () => {
  const romanceMovies = [
    {
      id: 1,
      title: "The Notebook 2",
      description: "Câu chuyện tình yêu cảm động và lãng mạn nhất mọi thời đại",
      rating: 9.2,
      language: "English",
      viewCount: "24.6K",
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 130,
      price: 80000,
      genres: ["Lãng mạn", "Drama", "Tình yêu"]
    },
    {
      id: 2,
      title: "Titanic: The Return",
      description: "Tình yêu vượt qua mọi khó khăn và thử thách của cuộc sống",
      rating: 9.5,
      language: "English",
      viewCount: "31.2K",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 180,
      price: 95000,
      genres: ["Lãng mạn", "Drama", "Lịch sử"]
    },
    {
      id: 3,
      title: "La La Land 2",
      description: "Cuộc tình lãng mạn giữa hai nghệ sĩ trong thành phố ánh sáng",
      rating: 8.9,
      language: "English",
      viewCount: "18.7K",
      image: "https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 140,
      price: 85000,
      genres: ["Lãng mạn", "Nhạc kịch", "Drama"]
    },
    {
      id: 4,
      title: "Crazy Rich Asians 2",
      description: "Tình yêu trong thế giới của những người siêu giàu",
      rating: 8.6,
      language: "English",
      viewCount: "16.3K",
      image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 120,
      price: 78000,
      genres: ["Lãng mạn", "Hài", "Gia đình"]
    },
    {
      id: 5,
      title: "The Fault in Our Stars 2",
      description: "Tình yêu đẹp và cảm động giữa hai người trẻ tuổi",
      rating: 9.1,
      language: "English",
      viewCount: "22.8K",
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 125,
      price: 82000,
      genres: ["Lãng mạn", "Drama", "Tuổi teen"]
    },
    {
      id: 6,
      title: "Before Sunrise 4",
      description: "Cuộc gặp gỡ tình cờ và tình yêu trong một đêm",
      rating: 8.8,
      language: "English",
      viewCount: "14.5K",
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 100,
      price: 75000,
      genres: ["Lãng mạn", "Drama", "Đối thoại"]
    }
  ];

  return (
    <MovieCategorySection
      title="Phim Tình Cảm"
      subtitle="Những bộ phim tình cảm lãng mạn và cảm động nhất"
      icon="💕"
      movies={romanceMovies}
      bgColor="bg-gray-900"
      viewAllCount={32}
      showBookingButton={true}
    />
  );
};

export default RomanceMoviesSection;
