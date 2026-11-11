<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Belum login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role ?? null;

        // Role kosong / rusak
        if (!$userRole) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Role user tidak valid.');
        }

        // Jika role diizinkan
        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        // Redirect aman sesuai role
        return match ($userRole) {
            'admin' => redirect('/admin/dashboard'),
            'approver' => redirect('/approver/dashboard'),
            'member' => redirect('/member/dashboard'),
            default => abort(403),
        };
    }
}
