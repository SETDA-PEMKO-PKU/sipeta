<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = auth('admin')->user();
            
            // Check if admin is active
            if (!$admin->is_active) {
                auth('admin')->logout();
                return back()->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            }

            $request->session()->regenerate();

            // Add OPD context to session for admin OPD
            if ($admin->isAdminOpd() && $admin->opd_id) {
                $request->session()->put('admin_opd_id', $admin->opd_id);
                $request->session()->put('admin_opd_name', $admin->opd->nama ?? 'Unknown OPD');
            }

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang, ' . auth('admin')->user()->name);
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        
        // Clear OPD session data
        $request->session()->forget(['admin_opd_id', 'admin_opd_name']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Anda berhasil logout');
    }
}
