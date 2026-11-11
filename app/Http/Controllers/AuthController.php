<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN (Production Ready)
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {

            // Regenerate session untuk keamanan
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect berdasarkan role
            if ($user->role === 'admin') {
                return redirect('/admin/dashboard');
            }

            if ($user->role === 'approver') {
                return redirect('/approver/dashboard');
            }

            return redirect('/member/dashboard');
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT (Production Ready)
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus session lama
        $request->session()->invalidate();

        // Regenerate token CSRF
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}