import React from 'react';
import MovieCategorySection from './MovieCategorySection';

const ActionMoviesSection = () => {
  const actionMovies = [
    {
      id: 1,
      title: "Fast & Furious 10",
      description: "Cuộc phiêu lưu tốc độ cao nhất với những pha đuổi xe ngoạn mục và kịch tính",
      rating: 8.9,
      language: "English",
      viewCount: "12.5K",
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 140,
      price: 95000,
      genres: ["Hành động", "Tốc độ", "Phiêu lưu"]
    },
    {
      id: 2,
      title: "Mission Impossible 8",
      description: "Ethan Hunt trở lại với nhiệm vụ bất khả thi nhất trong sự nghiệp của anh",
      rating: 9.1,
      language: "English",
      viewCount: "15.2K",
      image: "https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 150,
      price: 98000,
      genres: ["Hành động", "Gián điệp", "Kịch tính"]
    },
    {
      id: 3,
      title: "John Wick 5",
      description: "Sát thủ huyền thoại trở lại với cuộc trả thù cuối cùng",
      rating: 8.7,
      language: "English",
      viewCount: "18.9K",
      image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 130,
      price: 92000,
      genres: ["Hành động", "Tội phạm", "Bạo lực"]
    },
    {
      id: 4,
      title: "The Expendables 4",
      description: "Đội quân hưu trí trở lại với nhiệm vụ nguy hiểm nhất",
      rating: 8.3,
      language: "English",
      viewCount: "9.7K",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 125,
      price: 88000,
      genres: ["Hành động", "Chiến tranh", "Phiêu lưu"]
    },
    {
      id: 5,
      title: "Top Gun: Maverick 2",
      description: "Pete Mitchell trở lại với những chuyến bay mạo hiểm mới",
      rating: 9.3,
      language: "English",
      viewCount: "22.1K",
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 160,
      price: 100000,
      genres: ["Hành động", "Chiến tranh", "Drama"]
    },
    {
      id: 6,
      title: "Mad Max: Fury Road 2",
      description: "Hành trình qua sa mạc đầy kịch tính và nguy hiểm",
      rating: 8.8,
      language: "English",
      viewCount: "14.6K",
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 120,
      price: 90000,
      genres: ["Hành động", "Khoa học viễn tưởng", "Phiêu lưu"]
    }
  ];

  return (
    <MovieCategorySection
      title="Phim Hành Động"
      subtitle="Những bộ phim hành động đầy kịch tính và hấp dẫn"
      icon="💥"
      movies={actionMovies}
      bgColor="bg-gray-800"
      viewAllCount={35}
      showBookingButton={true}
    />
  );
};

export default ActionMoviesSection;
