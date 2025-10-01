import React from 'react';
import MovieCategorySection from './MovieCategorySection';

const DramaMoviesSection = () => {
  const dramaMovies = [
    {
      id: 1,
      title: "The Godfather: Legacy",
      description: "Câu chuyện gia đình mafia đầy kịch tính và bi kịch",
      rating: 9.6,
      language: "English",
      viewCount: "35.2K",
      image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 175,
      price: 90000,
      genres: ["Drama", "Tội phạm", "Gia đình"]
    },
    {
      id: 2,
      title: "Schindler's List: The Return",
      description: "Câu chuyện cảm động về lòng nhân ái trong thời kỳ đen tối",
      rating: 9.7,
      language: "English",
      viewCount: "42.8K",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 195,
      price: 85000,
      genres: ["Drama", "Lịch sử", "Chiến tranh"]
    },
    {
      id: 3,
      title: "Forrest Gump 2",
      description: "Hành trình cuộc đời đầy cảm động và ý nghĩa",
      rating: 9.3,
      language: "English",
      viewCount: "38.6K",
      image: "https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 140,
      price: 80000,
      genres: ["Drama", "Hài", "Lịch sử"]
    },
    {
      id: 4,
      title: "The Shawshank Redemption 2",
      description: "Câu chuyện về hy vọng và sự tự do trong tù ngục",
      rating: 9.5,
      language: "English",
      viewCount: "41.3K",
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 150,
      price: 82000,
      genres: ["Drama", "Tội phạm", "Hy vọng"]
    },
    {
      id: 5,
      title: "Good Will Hunting 2",
      description: "Câu chuyện về tài năng, tình bạn và sự trưởng thành",
      rating: 9.0,
      language: "English",
      viewCount: "27.9K",
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 125,
      price: 78000,
      genres: ["Drama", "Tâm lý", "Tình bạn"]
    },
    {
      id: 6,
      title: "The Pursuit of Happyness 2",
      description: "Hành trình vượt qua khó khăn để đạt được ước mơ",
      rating: 8.9,
      language: "English",
      viewCount: "33.7K",
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 115,
      price: 75000,
      genres: ["Drama", "Gia đình", "Cảm động"]
    }
  ];

  return (
    <MovieCategorySection
      title="Phim Chính Kịch"
      subtitle="Những bộ phim chính kịch đầy cảm động và ý nghĩa sâu sắc"
      icon="🎭"
      movies={dramaMovies}
      bgColor="bg-gray-900"
      viewAllCount={30}
      showBookingButton={true}
    />
  );
};

export default DramaMoviesSection;
