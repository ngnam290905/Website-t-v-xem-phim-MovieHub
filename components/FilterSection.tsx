import React, { useState } from 'react';

interface FilterOption {
  id: string;
  label: string;
  icon: string;
  count?: number;
}

interface FilterSectionProps {
  onFilterChange?: (filters: string[]) => void;
  onSortChange?: (sortBy: string) => void;
}

const FilterSection: React.FC<FilterSectionProps> = ({
  onFilterChange,
  onSortChange
}) => {
  const [activeFilters, setActiveFilters] = useState<string[]>([]);
  const [sortBy, setSortBy] = useState('popularity');

  const filterOptions: FilterOption[] = [
    { id: 'action', label: 'Hành động', icon: '⚔️', count: 12 },
    { id: 'comedy', label: 'Hài', icon: '😂', count: 8 },
    { id: 'horror', label: 'Kinh dị', icon: '👻', count: 6 },
    { id: 'romance', label: 'Lãng mạn', icon: '💕', count: 10 },
    { id: 'sci-fi', label: 'Khoa học viễn tưởng', icon: '🚀', count: 7 },
    { id: 'drama', label: 'Kịch', icon: '🎭', count: 15 },
    { id: 'thriller', label: 'Kịch tính', icon: '🔍', count: 9 },
    { id: 'animation', label: 'Hoạt hình', icon: '🎨', count: 5 }
  ];

  const sortOptions = [
    { id: 'popularity', label: 'Phổ biến' },
    { id: 'rating', label: 'Đánh giá cao' },
    { id: 'newest', label: 'Mới nhất' },
    { id: 'price-low', label: 'Giá thấp' },
    { id: 'price-high', label: 'Giá cao' },
    { id: 'duration', label: 'Thời lượng' }
  ];

  const handleFilterToggle = (filterId: string) => {
    const newFilters = activeFilters.includes(filterId)
      ? activeFilters.filter(id => id !== filterId)
      : [...activeFilters, filterId];
    
    setActiveFilters(newFilters);
    onFilterChange?.(newFilters);
  };

  const handleSortChange = (newSortBy: string) => {
    setSortBy(newSortBy);
    onSortChange?.(newSortBy);
  };

  const clearAllFilters = () => {
    setActiveFilters([]);
    onFilterChange?.([]);
  };

  return (
    <div className="bg-gray-800 py-6 px-6">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-3">
            <span className="text-2xl">🔍</span>
            <h3 className="text-2xl font-bold text-white">Lọc & Sắp xếp phim</h3>
          </div>
          {activeFilters.length > 0 && (
            <button
              onClick={clearAllFilters}
              className="text-gray-400 hover:text-white text-sm underline"
            >
              Xóa tất cả bộ lọc
            </button>
          )}
        </div>

        {/* Filter Options */}
        <div className="mb-6">
          <h4 className="text-lg font-semibold text-white mb-4">Thể loại phim</h4>
          <div className="flex flex-wrap gap-3">
            {filterOptions.map((option) => (
              <button
                key={option.id}
                onClick={() => handleFilterToggle(option.id)}
                className={`flex items-center gap-2 px-4 py-2 rounded-full transition-all duration-200 ${
                  activeFilters.includes(option.id)
                    ? 'bg-orange-500 text-white shadow-lg'
                    : 'bg-gray-700 text-gray-300 hover:bg-gray-600 hover:text-white'
                }`}
              >
                <span>{option.icon}</span>
                <span className="font-medium">{option.label}</span>
                {option.count && (
                  <span className="text-xs bg-white/20 px-2 py-0.5 rounded-full">
                    {option.count}
                  </span>
                )}
              </button>
            ))}
          </div>
        </div>

        {/* Sort Options */}
        <div className="flex items-center gap-4">
          <h4 className="text-lg font-semibold text-white">Sắp xếp theo:</h4>
          <div className="flex gap-2">
            {sortOptions.map((option) => (
              <button
                key={option.id}
                onClick={() => handleSortChange(option.id)}
                className={`px-4 py-2 rounded-lg transition-all duration-200 ${
                  sortBy === option.id
                    ? 'bg-red-500 text-white'
                    : 'bg-gray-700 text-gray-300 hover:bg-gray-600 hover:text-white'
                }`}
              >
                {option.label}
              </button>
            ))}
          </div>
        </div>

        {/* Active Filters Display */}
        {activeFilters.length > 0 && (
          <div className="mt-4 flex items-center gap-2">
            <span className="text-gray-400 text-sm">Đang lọc:</span>
            <div className="flex gap-2">
              {activeFilters.map((filterId) => {
                const option = filterOptions.find(opt => opt.id === filterId);
                return (
                  <span
                    key={filterId}
                    className="flex items-center gap-1 bg-orange-500 text-white px-3 py-1 rounded-full text-sm"
                  >
                    <span>{option?.icon}</span>
                    <span>{option?.label}</span>
                    <button
                      onClick={() => handleFilterToggle(filterId)}
                      className="ml-1 hover:bg-orange-600 rounded-full p-0.5"
                    >
                      ✕
                    </button>
                  </span>
                );
              })}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default FilterSection;
