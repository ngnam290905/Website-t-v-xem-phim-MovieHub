import React from 'react';
import MovieCategorySection from './MovieCategorySection';

const SciFiMoviesSection = () => {
  const sciFiMovies = [
    {
      id: 1,
      title: "Dune: Part 3",
      description: "Cuộc phiêu lưu vũ trụ đầy kịch tính trong thế giới tương lai",
      rating: 9.3,
      language: "English",
      viewCount: "28.4K",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 165,
      price: 100000,
      genres: ["Khoa học viễn tưởng", "Phiêu lưu", "Drama"]
    },
    {
      id: 2,
      title: "Blade Runner 2049: Sequel",
      description: "Thế giới cyberpunk đầy bí ẩn và công nghệ tương lai",
      rating: 8.9,
      language: "English",
      viewCount: "21.7K",
      image: "https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 155,
      price: 95000,
      genres: ["Khoa học viễn tưởng", "Cyberpunk", "Noir"]
    },
    {
      id: 3,
      title: "Interstellar 2",
      description: "Hành trình khám phá vũ trụ và du hành thời gian",
      rating: 9.4,
      language: "English",
      viewCount: "33.1K",
      image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 170,
      price: 105000,
      genres: ["Khoa học viễn tưởng", "Drama", "Du hành thời gian"]
    },
    {
      id: 4,
      title: "The Matrix: Resurrections 2",
      description: "Trở lại thế giới ma trận với những bí mật mới",
      rating: 8.7,
      language: "English",
      viewCount: "25.6K",
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 145,
      price: 92000,
      genres: ["Khoa học viễn tưởng", "Hành động", "Triết học"]
    },
    {
      id: 5,
      title: "Avatar: The Way of Water 2",
      description: "Hành trình khám phá hành tinh Pandora và những bí mật",
      rating: 9.1,
      language: "English",
      viewCount: "29.8K",
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 190,
      price: 110000,
      genres: ["Khoa học viễn tưởng", "Phiêu lưu", "Hành động"]
    },
    {
      id: 6,
      title: "Arrival 2",
      description: "Cuộc gặp gỡ với người ngoài hành tinh và ngôn ngữ vũ trụ",
      rating: 8.8,
      language: "English",
      viewCount: "19.3K",
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 130,
      price: 88000,
      genres: ["Khoa học viễn tưởng", "Drama", "Ngôn ngữ"]
    }
  ];

  return (
    <MovieCategorySection
      title="Phim Khoa Học Viễn Tưởng"
      subtitle="Những bộ phim sci-fi đầy tưởng tượng và công nghệ tương lai"
      icon="🚀"
      movies={sciFiMovies}
      bgColor="bg-gray-800"
      viewAllCount={26}
      showBookingButton={true}
    />
  );
};

export default SciFiMoviesSection;
