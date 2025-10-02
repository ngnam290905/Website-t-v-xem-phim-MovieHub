import React from 'react'

const HeroSection = () => {
  return (
    <div className="relative min-h-screen flex items-center justify-center overflow-hidden">
      {/* Enhanced Background with Cinema Theme */}
      <div className="absolute inset-0 z-0">
        {/* Base Gradient Background */}
        <div className="absolute inset-0 bg-gradient-to-br from-gray-900 via-black to-gray-800" />
        
        {/* Cinema Light Effects */}
        <div className="absolute inset-0">
          {/* Projector light beam effect */}
          <div className="absolute top-0 left-1/4 w-2 h-full bg-gradient-to-b from-yellow-400 via-yellow-300 to-transparent opacity-60 transform -skew-x-12" />
          <div className="absolute top-0 left-1/3 w-1 h-full bg-gradient-to-b from-yellow-300 via-yellow-200 to-transparent opacity-40 transform -skew-x-12" />
          <div className="absolute top-0 right-1/4 w-2 h-full bg-gradient-to-b from-yellow-400 via-yellow-300 to-transparent opacity-60 transform skew-x-12" />
          <div className="absolute top-0 right-1/3 w-1 h-full bg-gradient-to-b from-yellow-300 via-yellow-200 to-transparent opacity-40 transform skew-x-12" />
        </div>
        
        {/* Movie poster silhouettes */}
        <div className="absolute inset-0 opacity-10">
          <div className="absolute top-20 left-10 w-32 h-48 bg-gradient-to-b from-red-600 to-red-800 rounded-lg transform rotate-12" />
          <div className="absolute top-32 right-16 w-28 h-40 bg-gradient-to-b from-blue-600 to-blue-800 rounded-lg transform -rotate-12" />
          <div className="absolute bottom-32 left-20 w-36 h-52 bg-gradient-to-b from-purple-600 to-purple-800 rounded-lg transform rotate-6" />
          <div className="absolute bottom-20 right-24 w-30 h-44 bg-gradient-to-b from-green-600 to-green-800 rounded-lg transform -rotate-6" />
        </div>
        
        {/* Neon glow effects */}
        <div className="absolute top-1/4 left-1/4 w-64 h-64 bg-cyan-400 rounded-full mix-blend-screen filter blur-3xl opacity-20 animate-pulse" />
        <div className="absolute top-1/3 right-1/4 w-48 h-48 bg-yellow-400 rounded-full mix-blend-screen filter blur-2xl opacity-25 animate-pulse" style={{ animationDelay: '1s' }} />
        <div className="absolute bottom-1/4 left-1/3 w-56 h-56 bg-pink-400 rounded-full mix-blend-screen filter blur-3xl opacity-15 animate-pulse" style={{ animationDelay: '2s' }} />
        
        {/* Cinema seat pattern overlay */}
        <div className="absolute inset-0 opacity-5">
          <div className="w-full h-full" style={{
            backgroundImage: `repeating-linear-gradient(
              45deg,
              transparent,
              transparent 10px,
              rgba(255,255,255,0.1) 10px,
              rgba(255,255,255,0.1) 20px
            )`
          }} />
        </div>
        
        {/* Dark overlay for better text contrast */}
        <div className="absolute inset-0 bg-black bg-opacity-60" />
      </div>

      {/* Content */}
      <div className="relative z-10 text-center px-4 sm:px-6 max-w-6xl mx-auto">
        {/* Enhanced Logo Section - Mobile Optimized */}
        <div className="mb-8 sm:mb-10 md:mb-12 flex flex-col items-center">
          <div className="w-20 h-20 sm:w-24 sm:h-24 md:w-32 md:h-32 lg:w-40 lg:h-40 flex items-center justify-center mb-4 sm:mb-6">
            <img 
              src="/logo.svg" 
              alt="MovieHub Logo" 
              className="w-full h-full object-contain drop-shadow-2xl filter brightness-110"
            />
          </div>
          <div className="text-center">
            <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-1 sm:mb-2 drop-shadow-lg">
              MovieHub
            </h2>
            <p className="text-sm sm:text-base md:text-lg lg:text-xl text-cyan-300 font-medium drop-shadow-md">
              Rạp chiếu phim số 1 Việt Nam
            </p>
          </div>
        </div>

        {/* Main Headline - Mobile Optimized */}
        <h1 className="text-2xl xs:text-3xl sm:text-4xl md:text-6xl lg:text-7xl font-bold mb-4 sm:mb-6 md:mb-8 leading-tight px-2">
          <span className="text-white drop-shadow-lg">Đặt vé xem </span>
          <span className="bg-gradient-to-r from-cyan-400 via-yellow-400 to-orange-400 bg-clip-text text-transparent tracking-tight drop-shadow-lg">phim</span>
          <br className="hidden xs:block" />
          <span className="text-cyan-400 tracking-tight drop-shadow-lg">nhanh chóng</span>
          <br className="hidden xs:block" />
          <span className="text-white drop-shadow-lg">chọn ghế dễ dàng</span>
        </h1>

        {/* Enhanced Tagline - Mobile Optimized */}
        <p className="text-base sm:text-lg md:text-xl lg:text-2xl text-gray-100 mb-3 sm:mb-4 md:mb-6 font-light tracking-wide leading-relaxed drop-shadow-md px-2">
          Trải nghiệm điện ảnh đỉnh cao ngay trong tay bạn
        </p>

        {/* Trust Indicators - Mobile Optimized */}
        <div className="flex flex-col xs:flex-row flex-wrap justify-center gap-3 xs:gap-4 sm:gap-6 md:gap-8 mb-6 sm:mb-8 md:mb-12 text-xs xs:text-sm sm:text-base md:text-base px-2">
          <div className="flex items-center justify-center space-x-1 xs:space-x-2 text-cyan-300">
            <span className="text-lg xs:text-xl sm:text-2xl">🎬</span>
            <span className="text-xs xs:text-sm">+100.000 vé đã bán</span>
          </div>
          <div className="flex items-center justify-center space-x-1 xs:space-x-2 text-yellow-300">
            <span className="text-lg xs:text-xl sm:text-2xl">🏆</span>
            <span className="text-xs xs:text-sm">Đối tác CGV, Galaxy</span>
          </div>
          <div className="flex items-center justify-center space-x-1 xs:space-x-2 text-green-300">
            <span className="text-lg xs:text-xl sm:text-2xl">⚡</span>
            <span className="text-xs xs:text-sm">Đặt vé trong 30s</span>
          </div>
        </div>

        {/* Enhanced Description - Mobile Optimized */}
        <p className="text-sm sm:text-base md:text-lg text-gray-200 mb-8 sm:mb-12 md:mb-16 max-w-4xl mx-auto leading-relaxed drop-shadow-md px-2">
          Tìm phim hot, chọn suất chiếu, thanh toán trực tuyến chỉ trong vài bước. 
          Giao diện hiện đại, mượt mà với công nghệ Next.js tiên tiến.
        </p>

        {/* Enhanced Search Bar - Mobile Optimized */}
        <div className="mb-8 sm:mb-12 md:mb-16 px-2">
          <div className="flex flex-col xs:flex-row gap-2 xs:gap-3 sm:gap-4 max-w-3xl mx-auto">
            <div className="flex-1 relative">
              <div className="absolute inset-y-0 left-0 pl-2 xs:pl-3 sm:pl-4 flex items-center pointer-events-none">
                <svg className="h-3 w-3 xs:h-4 xs:w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                type="text"
                placeholder="Tìm kiếm phim, diễn viên..."
                className="w-full pl-8 xs:pl-10 sm:pl-12 pr-3 xs:pr-4 sm:pr-6 py-2.5 xs:py-3 sm:py-4 md:py-5 bg-gray-800/95 backdrop-blur-sm text-white placeholder-gray-300 placeholder:text-xs xs:placeholder:text-sm sm:placeholder:text-base md:placeholder:text-lg placeholder:font-normal rounded-lg sm:rounded-xl border-2 border-gray-600 focus:outline-none focus:ring-4 focus:ring-cyan-400/50 focus:border-cyan-400 shadow-2xl text-xs xs:text-sm sm:text-base md:text-lg"
              />
            </div>
            <button className="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-4 xs:px-6 sm:px-8 md:px-10 py-2.5 xs:py-3 sm:py-4 md:py-5 rounded-lg sm:rounded-xl transition-all duration-300 font-bold text-xs xs:text-sm sm:text-base md:text-lg shadow-2xl hover:shadow-cyan-500/25 transform hover:scale-105 flex items-center justify-center space-x-1 xs:space-x-2">
              <svg className="w-3 h-3 xs:w-4 xs:h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <span className="hidden xs:inline">Tìm kiếm</span>
              <span className="xs:hidden">Tìm</span>
            </button>
          </div>
        </div>

        {/* Enhanced Action Buttons - Mobile Optimized */}
        <div className="flex flex-col xs:flex-row gap-3 xs:gap-4 sm:gap-6 md:gap-8 justify-center mb-8 sm:mb-12 md:mb-16 px-2">
          <button className="bg-gradient-to-r from-red-500 via-pink-500 to-orange-500 hover:from-red-600 hover:via-pink-600 hover:to-orange-600 text-white px-4 xs:px-6 sm:px-8 md:px-12 py-3 xs:py-4 sm:py-5 md:py-6 rounded-lg xs:rounded-xl sm:rounded-2xl text-sm xs:text-base sm:text-lg md:text-xl font-bold flex items-center justify-center space-x-1 xs:space-x-2 sm:space-x-3 transition-all duration-300 shadow-2xl hover:shadow-red-500/25 transform hover:scale-105">
            <span className="text-lg xs:text-xl sm:text-2xl md:text-3xl">🎬</span>
            <span className="text-sm xs:text-base sm:text-lg md:text-xl">Tìm phim ngay</span>
          </button>
          <button className="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white px-4 xs:px-6 sm:px-8 md:px-12 py-3 xs:py-4 sm:py-5 md:py-6 rounded-lg xs:rounded-xl sm:rounded-2xl text-sm xs:text-base sm:text-lg md:text-xl font-bold flex items-center justify-center space-x-1 xs:space-x-2 sm:space-x-3 transition-all duration-300 shadow-2xl hover:shadow-yellow-500/25 transform hover:scale-105">
            <span className="text-lg xs:text-xl sm:text-2xl md:text-3xl">🎟️</span>
            <span className="text-sm xs:text-base sm:text-lg md:text-xl">Đặt vé ngay</span>
          </button>
        </div>

        {/* Scroll Down Indicator - Mobile Optimized */}
        <div className="flex flex-col items-center animate-bounce px-2">
          <span className="text-gray-300 text-xs sm:text-sm mb-1 sm:mb-2">Khám phá ngay</span>
          <svg className="w-4 h-4 xs:w-5 xs:h-5 sm:w-6 sm:h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </div>
      </div>
    </div>
  )
}

export default HeroSection
