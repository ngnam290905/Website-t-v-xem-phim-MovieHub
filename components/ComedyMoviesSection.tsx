import React from 'react';
import MovieCategorySection from './MovieCategorySection';

const ComedyMoviesSection = () => {
  const comedyMovies = [
    {
      id: 1,
      title: "The Hangover 4",
      description: "Cuộc phiêu lưu hài hước nhất của nhóm bạn thân trong chuyến du lịch",
      rating: 8.2,
      language: "English",
      viewCount: "11.3K",
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 110,
      price: 75000,
      genres: ["Hài", "Phiêu lưu", "Tình bạn"]
    },
    {
      id: 2,
      title: "Superbad 2",
      description: "Những tình huống dở khóc dở cười của tuổi thanh xuân",
      rating: 8.5,
      language: "English",
      viewCount: "9.8K",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 95,
      price: 70000,
      genres: ["Hài", "Tuổi teen", "Tình bạn"]
    },
    {
      id: 3,
      title: "Dumb and Dumber 3",
      description: "Hai chàng trai ngốc nghếch trong những tình huống hài hước",
      rating: 7.9,
      language: "English",
      viewCount: "7.2K",
      image: "https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 100,
      price: 65000,
      genres: ["Hài", "Ngốc nghếch", "Phiêu lưu"]
    },
    {
      id: 4,
      title: "Meet the Parents 4",
      description: "Cuộc gặp gỡ gia đình đầy bất ngờ và hài hước",
      rating: 8.1,
      language: "English",
      viewCount: "13.5K",
      image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 105,
      price: 72000,
      genres: ["Hài", "Gia đình", "Lãng mạn"]
    },
    {
      id: 5,
      title: "Anchorman 3",
      description: "Những phóng viên hài hước trong thế giới truyền hình",
      rating: 8.3,
      language: "English",
      viewCount: "10.7K",
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      isNew: true,
      duration: 115,
      price: 78000,
      genres: ["Hài", "Truyền thông", "Nghề nghiệp"]
    },
    {
      id: 6,
      title: "Step Brothers 2",
      description: "Hai anh em kế trong những tình huống dở khóc dở cười",
      rating: 7.8,
      language: "English",
      viewCount: "8.9K",
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      isTrending: true,
      duration: 90,
      price: 68000,
      genres: ["Hài", "Gia đình", "Anh em"]
    }
  ];

  return (
    <MovieCategorySection
      title="Phim Hài"
      subtitle="Những bộ phim hài hước giúp bạn thư giãn và cười vỡ bụng"
      icon="😂"
      movies={comedyMovies}
      bgColor="bg-gray-900"
      viewAllCount={28}
      showBookingButton={true}
    />
  );
};

export default ComedyMoviesSection;
