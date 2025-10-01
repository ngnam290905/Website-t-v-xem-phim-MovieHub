import React from 'react';
import MovieCategorySection from './MovieCategorySection';

const HorrorMoviesSection = () => {
  const horrorMovies = [
    {
      id: 1,
      title: "The Conjuring 4",
      description: "Những câu chuyện ma quái và kinh dị từ các vụ án có thật",
      rating: 8.7,
      language: "English",
      viewCount: "16.4K",
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 135,
      price: 85000,
      genres: ["Kinh dị", "Ma quái", "Tâm linh"]
    },
    {
      id: 2,
      title: "Insidious 5",
      description: "Hành trình vào thế giới tâm linh đầy bí ẩn và đáng sợ",
      rating: 8.4,
      language: "English",
      viewCount: "12.8K",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 120,
      price: 80000,
      genres: ["Kinh dị", "Tâm linh", "Bí ẩn"]
    },
    {
      id: 3,
      title: "The Exorcist: Reborn",
      description: "Câu chuyện ma quỷ ám ảnh và kinh dị nhất mọi thời đại",
      rating: 9.0,
      language: "English",
      viewCount: "19.2K",
      image: "https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 150,
      price: 90000,
      genres: ["Kinh dị", "Ma quỷ", "Tôn giáo"]
    },
    {
      id: 4,
      title: "Hereditary 2",
      description: "Gia đình bị ám ảnh bởi những bí mật đen tối và đáng sợ",
      rating: 8.6,
      language: "English",
      viewCount: "14.7K",
      image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 125,
      price: 82000,
      genres: ["Kinh dị", "Tâm lý", "Gia đình"]
    },
    {
      id: 5,
      title: "A Quiet Place 3",
      description: "Thế giới im lặng đầy nguy hiểm và những sinh vật đáng sợ",
      rating: 8.8,
      language: "English",
      viewCount: "17.3K",
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 110,
      price: 87000,
      genres: ["Kinh dị", "Khoa học viễn tưởng", "Gia đình"]
    },
    {
      id: 6,
      title: "Midsommar 2",
      description: "Lễ hội mùa hè đầy bí ẩn và những nghi lễ đáng sợ",
      rating: 8.3,
      language: "English",
      viewCount: "11.9K",
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 140,
      price: 83000,
      genres: ["Kinh dị", "Tâm lý", "Nghi lễ"]
    }
  ];

  return (
    <MovieCategorySection
      title="Phim Kinh Dị"
      subtitle="Những bộ phim kinh dị và ma quái đầy hồi hộp và đáng sợ"
      icon="👻"
      movies={horrorMovies}
      bgColor="bg-gray-800"
      viewAllCount={22}
      showBookingButton={true}
    />
  );
};

export default HorrorMoviesSection;
