# Fix: Staff Login Redirect Issue

## Problem Description
Khi tài khoản staff đăng nhập, hệ thống không thể chuyển trang mà bị stuck hoặc không redirect đúng.

## Root Cause
Vấn đề nằm ở route `admin.dashboard` trong file `routes/web.php`:

1. **Inline function issue**: Route sử dụng inline closure function khó debug
2. **Redirect loop**: Cả admin và staff redirect sang `/admin`, rồi `/admin` lại xử lý logic redirect
3. **Middleware complexity**: Middleware kiểm tra role không hiệu quả với closure

## Solution Applied

### 1. Sửa AuthController - Direct Redirect dựa trên Role
```php
// Thay vì: cả admin và staff → redirect('admin.dashboard')
// Sang: 
if ($userRole === 'admin') {
    return redirect()->route('admin.dashboard');
} elseif ($userRole === 'staff') {
    return redirect()->route('admin.movies.index');  // ← Direct redirect
}
```

**Lợi ích:**
- Admin → Dashboard (full dashboard view)
- Staff → Movies Management (movies list)
- No extra redirect hops

### 2. Sửa AdminController::handleDashboard()
```php
public function handleDashboard(Request $request)
{
    // Nếu somehow staff vẫn access /admin
    // Render movies page directly (không redirect để tránh loop)
    return (new MovieController())->adminIndex($request);
}
```

### 3. Cải thiện Logging
Added detailed logging ở:
- AuthController: Track login attempt → role detection → redirect decision
- AdminController: Track which action được thực thi

## Login Flow After Fix

### Admin Login:
```
1. Email/Password validation ✓
2. Authentication ✓
3. Check role → "admin" ✓
4. Redirect to route('admin.dashboard') ✓
5. URL changes to /admin ✓
6. AdminController::handleDashboard() called ✓
7. Recognize as admin → call dashboard() method ✓
8. Admin dashboard displayed ✓
```

### Staff Login:
```
1. Email/Password validation ✓
2. Authentication ✓
3. Check role → "staff" ✓
4. Redirect to route('admin.movies.index') ✓
5. URL changes to /admin/movies ✓
6. MovieController::adminIndex() called directly ✓
7. Movies list displayed ✓
```

## Files Changed

### 1. app/Http/Controllers/AuthController.php
**Changes:**
- Modified login() method
- Staff now redirects directly to `admin.movies.index` instead of `admin.dashboard`
- Added logging for better debugging

**Key code:**
```php
elseif ($userRole === 'staff') {
    Log::info('Redirecting staff to movies');
    return redirect()->route('admin.movies.index');
}
```

### 2. app/Http/Controllers/AdminController.php
**Changes:**
- Added handleDashboard() method (được reference ở route)
- If staff somehow access /admin, render movies directly
- Added detailed logging

**Key code:**
```php
public function handleDashboard(Request $request)
{
    // Check role, then render appropriate view
    if (admin) return dashboard();
    if (staff) return (new MovieController())->adminIndex($request);
}
```

### 3. routes/web.php
**Changes:**
- Changed inline function to controller method call
- Route now: `Route::get('/', [AdminController::class, 'handleDashboard'])`

## Testing

### Setup:
```bash
# Clear all cache
php artisan config:clear && php artisan cache:clear
```

### Test Staff Login:
1. Go to http://localhost/login
2. Enter staff credentials: staff@example.com / password
3. Click login
4. Wait for redirect
5. Should land on `/admin/movies` (movies management page)
6. Check logs: `tail -f storage/logs/laravel.log`

### Expected Logs:
```
[2025-12-09 ...] local.INFO: Login attempt {"email":"staff@example.com"}
[2025-12-09 ...] local.INFO: User authenticated {"user_id":5,"email":"staff@example.com"}
[2025-12-09 ...] local.INFO: User role {"role":"staff"}
[2025-12-09 ...] local.INFO: Redirecting staff to movies
[Shows /admin/movies page in browser]
```

### Test Admin Login:
1. Enter admin credentials
2. Should land on `/admin` (admin dashboard)
3. Expected logs will show "Redirecting admin to dashboard"

## Benefits of This Solution

1. ✅ **Direct routing**: No unnecessary redirect hops
2. ✅ **Clear separation**: Admin and Staff have different landing pages
3. ✅ **Better debugging**: Logging shows exact path taken
4. ✅ **Maintainable**: Logic is in controller, not inline functions
5. ✅ **No redirect loops**: Each user type has single, definitive destination

## Configuration Details

- **Session driver**: Database (`SESSION_DRIVER=database`)
- **Middleware**: Uses `FinalRoleMiddleware` for role checking
- **Admin group middleware**: `['auth', 'role:admin,staff']`
- **Staff can access**:
  - `/admin/movies` - Full CRUD
  - `/admin/phong-chieu` - Room management
  - `/admin/combos` - Combo management
  - `/admin/khuyenmai` - Promotion management
  - And other routes permitted in definitions

## If Issues Persist

1. **Check database**: `SELECT * FROM nguoi_dung WHERE email='staff@example.com'` - ensure `id_vai_tro` is set
2. **Check vai_tro table**: Ensure role exists with name "staff"
3. **Check logs**: Look for any ERROR or EXCEPTION entries
4. **Clear browser cache**: Ctrl+Shift+Del in browser
5. **Check middleware**: Ensure no other middleware is blocking staff users
