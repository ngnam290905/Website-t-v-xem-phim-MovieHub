import React from 'react'

const HeroSection = () => {
  return (
    <div className="relative min-h-screen flex items-center justify-center overflow-hidden">
      {/* Background with Gradient and Blur Effect */}
      <div className="absolute inset-0 z-0">
        {/* Gradient Background */}
        <div className="absolute inset-0 bg-gradient-to-br from-orange-600 via-red-600 to-pink-600" />
        
        {/* Blurred Pattern Overlay */}
        <div className="absolute inset-0 opacity-30">
          <div className="w-full h-full bg-gradient-to-r from-orange-400 via-red-500 to-pink-500 transform scale-110 blur-3xl" />
        </div>
        
        {/* Additional Pattern Elements */}
        <div className="absolute top-20 left-20 w-96 h-96 bg-orange-400 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse" />
        <div className="absolute top-40 right-20 w-80 h-80 bg-red-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse" style={{ animationDelay: '2s' }} />
        <div className="absolute bottom-20 left-1/3 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse" style={{ animationDelay: '4s' }} />
        
        {/* Dark Overlay - Increased opacity for better contrast */}
        <div className="absolute inset-0 bg-black bg-opacity-70" />
      </div>

      {/* Content */}
      <div className="relative z-10 text-center px-6 max-w-4xl mx-auto">
        {/* Logo */}
        <div className="mb-8 flex justify-center">
          <div className="w-20 h-20 flex items-center justify-center">
            <img 
              src="/logo.svg" 
              alt="MovieHub Logo" 
              className="w-full h-full object-contain drop-shadow-2xl"
            />
          </div>
        </div>
        {/* Main Headline - IMPROVED VERSION WITH BETTER CONTRAST */}
        <h1 className="text-5xl md:text-7xl font-bold mb-8 leading-tight">
          <span className="text-white drop-shadow-lg">Đặt vé xem </span>
          <span className="bg-gradient-to-r from-red-400 to-orange-400 bg-clip-text text-transparent tracking-tight drop-shadow-lg">phim</span>
          <br />
          <span className="text-red-400 tracking-tight drop-shadow-lg">nhanh chóng</span>
          <br />
          <span className="text-white drop-shadow-lg">chọn ghế dễ dàng</span>
        </h1>

        {/* Tagline - IMPROVED CONTRAST */}
        <p className="text-xl md:text-2xl text-gray-100 mb-10 font-light tracking-wide leading-relaxed drop-shadow-md">
          Trải nghiệm điện ảnh ngay trong tay bạn
        </p>

        {/* Description - IMPROVED CONTRAST */}
        <p className="text-lg text-gray-200 mb-16 max-w-3xl mx-auto leading-relaxed drop-shadow-md">
          Tìm phim hot, chọn suất chiếu, thanh toán trực tuyến chỉ trong vài bước. 
          Giao diện hiện đại, mượt mà với công nghệ Next.js tiên tiến.
        </p>

        {/* Search Bar - IMPROVED VERSION */}
        <div className="mb-12">
          <div className="flex flex-col sm:flex-row gap-4 max-w-2xl mx-auto">
            <div className="flex-1 relative">
              <input
                type="text"
                placeholder="Tìm kiếm phim, diễn viên, thể loại..."
                className="w-full px-6 py-4 bg-gray-800/95 backdrop-blur-sm text-white placeholder-gray-200 placeholder:text-base placeholder:font-normal rounded-lg border border-gray-500 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 shadow-lg"
              />
            </div>
            <button className="bg-gray-700 hover:bg-gray-600 text-white px-8 py-4 rounded-lg transition-all duration-200 font-medium h-14 shadow-lg hover:shadow-xl">
              Tìm kiếm
            </button>
          </div>
        </div>

        {/* Action Buttons - IMPROVED VERSION */}
        <div className="flex flex-col sm:flex-row gap-6 justify-center">
          <button className="bg-red-500 hover:bg-red-600 text-white px-8 py-4 rounded-lg text-lg font-semibold flex items-center justify-center space-x-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
            <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clipRule="evenodd" />
            </svg>
            <span>Tìm phim ngay</span>
          </button>
          <button className="bg-gray-700 hover:bg-gray-600 text-white px-8 py-4 rounded-lg text-lg font-semibold flex items-center justify-center space-x-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
            <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span>Cách hoạt động</span>
          </button>
        </div>
      </div>
    </div>
  )
}

export default HeroSection
