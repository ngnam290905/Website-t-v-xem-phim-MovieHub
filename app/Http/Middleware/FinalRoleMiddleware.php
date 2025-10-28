<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FinalRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Kiểm tra authentication
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        $user = Auth::user();
        
        // Kiểm tra nếu user có vai trò
        if (!$user->vaiTro) {
            abort(403, 'Người dùng chưa được phân quyền.');
        }
        
        $userRole = $user->vaiTro->ten;

        // Kiểm tra quyền
        if (!in_array($userRole, $roles)) {
            abort(403, 'Bạn không có quyền truy cập trang này. Yêu cầu quyền: ' . implode(', ', $roles) . '. Bạn có quyền: ' . $userRole);
        }

        return $next($request);
    }
}
