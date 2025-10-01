import React from 'react'

const Header = () => {
  return (
    <header className="sticky top-0 z-50 bg-gray-800/95 backdrop-blur-lg w-full px-6 py-4 border-b border-gray-700/50 shadow-lg">
      <div className="flex items-center justify-between max-w-7xl mx-auto">
        {/* Logo */}
        <div className="flex items-center space-x-3">
          <div className="w-12 h-12 flex items-center justify-center">
            <img 
              src="/logo.svg" 
              alt="MovieHub Logo" 
              className="w-full h-full object-contain"
            />
          </div>
          <span className="text-white text-xl font-bold">MovieHub</span>
        </div>

        {/* Navigation Links */}
        <nav className="hidden md:flex items-center space-x-8">
          <a href="#" className="text-white hover:text-red-400 transition-colors relative group">
            Trang chủ
            <span className="absolute -bottom-1 left-0 w-0 h-0.5 bg-red-400 transition-all group-hover:w-full"></span>
          </a>
          <a href="#" className="text-white hover:text-red-400 transition-colors relative group">
            Phim
            <span className="absolute -bottom-1 left-0 w-0 h-0.5 bg-red-400 transition-all group-hover:w-full"></span>
          </a>
          <a href="#" className="text-white hover:text-red-400 transition-colors relative group">
            Giờ vé
            <span className="absolute -bottom-1 left-0 w-0 h-0.5 bg-red-400 transition-all group-hover:w-full"></span>
          </a>
          <div className="relative group">
            <button className="text-white hover:text-red-400 transition-colors flex items-center space-x-1 relative">
              <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
              </svg>
              <span>Thể loại</span>
              <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
              </svg>
              <span className="absolute -bottom-1 left-0 w-0 h-0.5 bg-red-400 transition-all group-hover:w-full"></span>
            </button>
          </div>
        </nav>

        {/* Action Buttons */}
        <div className="flex items-center space-x-4">
          <button className="text-white hover:text-red-400 transition-colors flex items-center space-x-2 hover:bg-gray-700/50 px-3 py-2 rounded-lg">
            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Vé</span>
          </button>
          <button className="text-white hover:text-red-400 transition-colors flex items-center space-x-2 hover:bg-gray-700/50 px-3 py-2 rounded-lg">
            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
            </svg>
            <span>Đăng nhập</span>
          </button>
          <button className="border border-red-600 text-red-400 hover:bg-red-600 hover:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
            </svg>
            <span>Đăng ký</span>
          </button>
        </div>
      </div>
    </header>
  )
}

export default Header

