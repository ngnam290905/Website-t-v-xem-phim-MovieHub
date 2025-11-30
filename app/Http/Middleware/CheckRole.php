<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        $user = Auth::user();
        
        // Check if user has a role
        if (!$user->vaiTro) {
            abort(403, 'Người dùng chưa được phân quyền.');
        }
        
        $userRole = $user->vaiTro->ten;

        // If no roles specified, allow access
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($userRole === $role) {
                return $next($request);
            }
        }

        abort(403, 'Bạn không có quyền truy cập trang này. Yêu cầu quyền: ' . implode(', ', $roles) . '. Bạn có quyền: ' . $userRole);
    }
}
