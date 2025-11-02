<!-- Notification System -->
<div id="notification-system" class="fixed top-4 right-4 z-50 space-y-3">
  <!-- Notifications will be dynamically added here -->
</div>

<!-- Notification Bell with Dropdown -->
<div class="notification-dropdown relative hidden" id="notification-dropdown">
  <div class="absolute right-0 mt-2 w-80 bg-[#1b1d24] border border-[#262833] rounded-lg shadow-xl opacity-0 invisible transition-all duration-300 z-50">
    <div class="p-4 border-b border-[#262833]">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-white">Thông báo</h3>
        <button class="text-[#a6a6b0] hover:text-white transition-colors" onclick="markAllAsRead()">
          Đánh dấu tất cả đã đọc
        </button>
      </div>
    </div>
    
    <div class="max-h-96 overflow-y-auto">
      <!-- Movie Release Notifications -->
      <div class="notification-item p-4 border-b border-[#262833] hover:bg-[#222533] transition-colors cursor-pointer" data-type="movie-release">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-gradient-to-r from-[#F53003] to-orange-400 rounded-full flex items-center justify-center text-white font-bold">
            🎬
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-white">Phim mới ra mắt!</h4>
            <p class="text-sm text-[#a6a6b0]">"Vùng Đất Linh Hồn" đã có mặt tại rạp</p>
            <p class="text-xs text-[#a6a6b0] mt-1">2 giờ trước</p>
          </div>
          <div class="w-2 h-2 bg-[#F53003] rounded-full"></div>
        </div>
      </div>
      
      <!-- Promotion Notifications -->
      <div class="notification-item p-4 border-b border-[#262833] hover:bg-[#222533] transition-colors cursor-pointer" data-type="promotion">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold">
            🎁
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-white">Khuyến mãi đặc biệt!</h4>
            <p class="text-sm text-[#a6a6b0]">Giảm 30% cho thành viên VIP cuối tuần này</p>
            <p class="text-xs text-[#a6a6b0] mt-1">5 giờ trước</p>
          </div>
          <div class="w-2 h-2 bg-green-500 rounded-full"></div>
        </div>
      </div>
      
      <!-- Showtime Reminder -->
      <div class="notification-item p-4 border-b border-[#262833] hover:bg-[#222533] transition-colors cursor-pointer" data-type="reminder">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold">
            ⏰
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-white">Nhắc nhở suất chiếu</h4>
            <p class="text-sm text-[#a6a6b0]">Suất chiếu "Hành Tinh Bí Ẩn" lúc 19:30 sắp bắt đầu</p>
            <p class="text-xs text-[#a6a6b0] mt-1">1 ngày trước</p>
          </div>
          <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
        </div>
      </div>
      
      <!-- Event Notifications -->
      <div class="notification-item p-4 border-b border-[#262833] hover:bg-[#222533] transition-colors cursor-pointer" data-type="event">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold">
            🎪
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-white">Sự kiện đặc biệt</h4>
            <p class="text-sm text-[#a6a6b0]">Gặp gỡ diễn viên "Săn Lùng Siêu Trộm" tại CGV Landmark</p>
            <p class="text-xs text-[#a6a6b0] mt-1">2 ngày trước</p>
          </div>
          <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
        </div>
      </div>
    </div>
    
    <div class="p-4 border-t border-[#262833]">
      <a href="#" class="text-center block text-[#F53003] hover:text-orange-400 transition-colors">
        Xem tất cả thông báo
      </a>
    </div>
  </div>
</div>

<!-- Popup Notifications -->
<div id="popup-notifications" class="fixed inset-0 z-50 pointer-events-none">
  <!-- Popup notifications will be dynamically added here -->
</div>

<script>
  class NotificationSystem {
    constructor() {
      this.notifications = [];
      this.unreadCount = 4;
      this.init();
    }
    
    init() {
      this.bindEvents();
      this.showWelcomeNotification();
      this.scheduleNotifications();
    }
    
    bindEvents() {
      // Notification bell click
      const bellBtn = document.querySelector('[title="Thông báo"]');
      if (bellBtn) {
        bellBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.toggleDropdown();
        });
      }
      
      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!e.target.closest('.notification-dropdown') && !e.target.closest('[title="Thông báo"]')) {
          this.closeDropdown();
        }
      });
      
      // Notification item clicks
      document.addEventListener('click', (e) => {
        if (e.target.closest('.notification-item')) {
          this.handleNotificationClick(e.target.closest('.notification-item'));
        }
      });
    }
    
    toggleDropdown() {
      const dropdown = document.getElementById('notification-dropdown');
      if (dropdown) {
        dropdown.classList.toggle('hidden');
        setTimeout(() => {
          dropdown.querySelector('.absolute').classList.toggle('opacity-0');
          dropdown.querySelector('.absolute').classList.toggle('invisible');
        }, 10);
      }
    }
    
    closeDropdown() {
      const dropdown = document.getElementById('notification-dropdown');
      if (dropdown && !dropdown.classList.contains('hidden')) {
        dropdown.querySelector('.absolute').classList.add('opacity-0', 'invisible');
        setTimeout(() => {
          dropdown.classList.add('hidden');
        }, 300);
      }
    }
    
    showWelcomeNotification() {
      setTimeout(() => {
        this.showPopupNotification({
          type: 'welcome',
          title: '🎉 Chào mừng đến với MovieHub!',
          message: 'Đăng ký ngay để nhận ưu đãi đặc biệt 20% cho lần đặt vé đầu tiên!',
          action: 'Đăng ký ngay',
          duration: 8000
        });
      }, 2000);
    }
    
    scheduleNotifications() {
      // Movie release notification
      setTimeout(() => {
        this.showPopupNotification({
          type: 'movie-release',
          title: '🎬 Phim mới ra mắt!',
          message: '"Vùng Đất Linh Hồn" - Cuộc phiêu lưu kỳ thú đã có mặt tại rạp!',
          action: 'Xem ngay',
          duration: 6000
        });
      }, 10000);
      
      // Promotion notification
      setTimeout(() => {
        this.showPopupNotification({
          type: 'promotion',
          title: '🎁 Khuyến mãi cuối tuần!',
          message: 'Giảm 30% cho tất cả suất chiếu cuối tuần. Áp dụng đến hết Chủ nhật!',
          action: 'Đặt vé ngay',
          duration: 7000
        });
      }, 20000);
    }
    
    showPopupNotification(config) {
      const popup = document.createElement('div');
      popup.className = `popup-notification fixed top-4 right-4 w-80 bg-[#1b1d24] border border-[#262833] rounded-lg shadow-xl transform translate-x-full transition-transform duration-500 z-50`;
      
      const iconMap = {
        'welcome': '🎉',
        'movie-release': '🎬',
        'promotion': '🎁',
        'reminder': '⏰',
        'event': '🎪'
      };
      
      popup.innerHTML = `
        <div class="p-4">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-gradient-to-r from-[#F53003] to-orange-400 rounded-full flex items-center justify-center text-white font-bold">
              ${iconMap[config.type] || '🔔'}
            </div>
            <div class="flex-1">
              <h4 class="font-semibold text-white">${config.title}</h4>
              <p class="text-sm text-[#a6a6b0] mt-1">${config.message}</p>
              ${config.action ? `<button class="mt-2 px-3 py-1 bg-[#F53003] text-white text-xs rounded hover:bg-[#ff4d4d] transition-colors">${config.action}</button>` : ''}
            </div>
            <button class="text-[#a6a6b0] hover:text-white transition-colors" onclick="this.parentElement.parentElement.parentElement.remove()">
              ✕
            </button>
          </div>
        </div>
      `;
      
      document.getElementById('popup-notifications').appendChild(popup);
      
      // Animate in
      setTimeout(() => {
        popup.classList.remove('translate-x-full');
      }, 100);
      
      // Auto remove
      setTimeout(() => {
        popup.classList.add('translate-x-full');
        setTimeout(() => {
          if (popup.parentElement) {
            popup.remove();
          }
        }, 500);
      }, config.duration || 5000);
    }
    
    handleNotificationClick(notificationItem) {
      // Mark as read
      const dot = notificationItem.querySelector('.w-2.h-2');
      if (dot) {
        dot.style.display = 'none';
        this.unreadCount--;
        this.updateBellBadge();
      }
      
      // Handle different notification types
      const type = notificationItem.dataset.type;
      switch(type) {
        case 'movie-release':
          window.location.href = '/phim/3';
          break;
        case 'promotion':
          this.showPromotionModal();
          break;
        case 'reminder':
          window.location.href = '/dat-ve-dong/1';
          break;
        case 'event':
          this.showEventModal();
          break;
      }
    }
    
    showPromotionModal() {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 w-full max-w-md mx-4">
          <div class="text-center">
            <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
              🎁
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Khuyến mãi VIP</h3>
            <p class="text-[#a6a6b0] mb-6">Giảm 30% cho tất cả suất chiếu cuối tuần này. Chỉ dành cho thành viên VIP!</p>
            <div class="flex gap-3">
              <button class="flex-1 px-4 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-colors" onclick="this.closest('.fixed').remove()">
                Đặt vé ngay
              </button>
              <button class="flex-1 px-4 py-2 bg-[#2a2d3a] text-white rounded-lg hover:bg-[#3a3d4a] transition-colors" onclick="this.closest('.fixed').remove()">
                Đóng
              </button>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }
    
    showEventModal() {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="bg-[#1b1d24] border border-[#262833] rounded-xl p-6 w-full max-w-md mx-4">
          <div class="text-center">
            <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
              🎪
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Gặp gỡ diễn viên</h3>
            <p class="text-[#a6a6b0] mb-4">Cơ hội gặp gỡ và chụp ảnh cùng dàn diễn viên "Săn Lùng Siêu Trộm"</p>
            <div class="bg-[#222533] rounded-lg p-3 mb-4">
              <p class="text-sm text-white"><strong>Thời gian:</strong> 15:00 - 17:00, Chủ nhật</p>
              <p class="text-sm text-white"><strong>Địa điểm:</strong> CGV Landmark 81</p>
            </div>
            <div class="flex gap-3">
              <button class="flex-1 px-4 py-2 bg-[#F53003] text-white rounded-lg hover:bg-[#ff4d4d] transition-colors" onclick="this.closest('.fixed').remove()">
                Tham gia
              </button>
              <button class="flex-1 px-4 py-2 bg-[#2a2d3a] text-white rounded-lg hover:bg-[#3a3d4a] transition-colors" onclick="this.closest('.fixed').remove()">
                Đóng
              </button>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }
    
    updateBellBadge() {
      const badge = document.querySelector('.notification-badge');
      if (badge) {
        badge.textContent = this.unreadCount;
        if (this.unreadCount === 0) {
          badge.style.display = 'none';
        }
      }
    }
  }
  
  // Global functions
  function markAllAsRead() {
    document.querySelectorAll('.notification-item .w-2.h-2').forEach(dot => {
      dot.style.display = 'none';
    });
    const badge = document.querySelector('.notification-badge');
    if (badge) {
      badge.style.display = 'none';
    }
  }
  
  // Initialize notification system
  document.addEventListener('DOMContentLoaded', () => {
    new NotificationSystem();
  });
</script>
