<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('home');
        }

        $user = Auth::user();
        $userRoleName = optional($user->vaiTro)->ten;

        if ($userRoleName === null || (!empty($roles) && !in_array($userRoleName, $roles))) {
            abort(403, 'Bạn không có quyền truy cập');
        }

        return $next($request);
    }
}