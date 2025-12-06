<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockAdminStaffOnClient
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $role = optional(optional($user)->vaiTro)->ten;
        $norm = is_string($role) ? mb_strtolower(trim($role)) : '';
        if (in_array($norm, ['admin', 'staff'])) {
            return redirect()->route('admin.dashboard');
        }
        return $next($request);
    }
}
