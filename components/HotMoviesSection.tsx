import React, { useState } from 'react';
import Image from 'next/image';
import MovieCategorySection from './MovieCategorySection';

interface Movie {
  id: number;
  title: string;
  description: string;
  rating: number;
  language: string;
  viewCount: string;
  image: string;
  isHot: boolean;
}

const HotMoviesSection = () => {
  const [activeTab, setActiveTab] = useState('hot');

  const movies: Movie[] = [
    {
      id: 1,
      title: "Red Vengeance",
      description: "Một đặc vụ bí ẩn trở lại sau nhiều năm để phá một âm mưu đen tối đe doạ thành phố",
      rating: 8.5,
      language: "English",
      viewCount: "7.425",
      image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 128,
      price: 90000,
      genres: ["Hành động", "Kịch tính", "Tội phạm"]
    },
    {
      id: 2,
      title: "Haunted Mansion",
      description: "Một gia đình chuyển đến ngôi nhà cổ và khám phá những bí mật đáng sợ ẩn giấu bên trong",
      rating: 8.5,
      language: "English",
      viewCount: "5.698",
      image: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 105,
      price: 85000,
      genres: ["Kinh dị", "Bí ẩn", "Siêu nhiên"]
    },
    {
      id: 3,
      title: "The Mystery Detective",
      description: "Thám tử tài ba phải giải quyết vụ án phức tạp nhất trong sự nghiệp của mình",
      rating: 8.5,
      language: "English",
      viewCount: "8.106",
      image: "https://images.unsplash.com/photo-1489599808418-75c8b0b0b8a0?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 115,
      price: 88000,
      genres: ["Bí ẩn", "Tội phạm", "Kịch tính"]
    },
    {
      id: 4,
      title: "Love & Laughter",
      description: "Một câu chuyện tình yêu hài hước và cảm động giữa hai người hoàn toàn khác biệt",
      rating: 8.5,
      language: "English",
      viewCount: "3.193",
      image: "https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 95,
      price: 56000,
      genres: ["Hài", "Lãng mạn", "Tình cảm"]
    },
    {
      id: 5,
      title: "Action Hero",
      description: "Cuộc phiêu lưu đầy kịch tính của một anh hùng hành động trong thế giới tương lai",
      rating: 8.8,
      language: "English",
      viewCount: "9.234",
      image: "https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 135,
      price: 95000,
      genres: ["Hành động", "Khoa học viễn tưởng", "Phiêu lưu"]
    },
    {
      id: 6,
      title: "Sci-Fi Adventure",
      description: "Hành trình khám phá vũ trụ của một nhóm phi hành gia dũng cảm",
      rating: 8.7,
      language: "English",
      viewCount: "6.789",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=center",
      isHot: true,
      duration: 142,
      price: 92000,
      genres: ["Khoa học viễn tưởng", "Phiêu lưu", "Hành động"]
    }
  ];

  const tabs = [
    { id: 'hot', label: 'Phim Hot', icon: '🔥' },
    { id: 'now', label: 'Đang Chiếu', icon: '▶️' },
    { id: 'coming', label: 'Sắp Chiếu', icon: '🕐' }
  ];

  return (
    <div>
      {/* Navigation Tabs */}
      <div className="bg-gray-900 py-4 px-6">
        <div className="max-w-7xl mx-auto">
          <div className="flex gap-1 justify-center">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`flex items-center gap-2 px-6 py-3 rounded-lg font-medium transition-colors ${
                  activeTab === tab.id
                    ? 'bg-red-600 text-white'
                    : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700'
                }`}
              >
                <span>{tab.icon}</span>
                {tab.label}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Movie Section with Booking */}
      <MovieCategorySection
        title="Phim Hot"
        subtitle="Những bộ phim đang được yêu thích nhất hiện tại"
        icon="🔥"
        movies={movies}
        bgColor="bg-gray-900"
        showViewAll={true}
        viewAllCount={25}
        showBookingButton={true}
      />
    </div>
  );
};

export default HotMoviesSection;
