<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showRegister()
    {
        $terms = \App\Models\SystemSetting::where('key', 'terms_and_conditions')->first();
        $privacy = \App\Models\SystemSetting::where('key', 'privacy_policy')->first();
        return view('auth.register', compact('terms', 'privacy'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:9|max:13',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ], [
            'terms.required' => 'Anda harus menyetujui syarat dan ketentuan.',
            'terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        // Auto-add +62 prefix and clean input
        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        // Remove leading 0 if exists
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        $phone = '+62' . $phone;

        // Check if phone already exists
        if (User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Nomor telepon sudah terdaftar'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $request->login;

        // Determine if login is email or phone
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            // Login with email
            $credentials = ['email' => $login, 'password' => $request->password];
        } else {
            // Login with phone - auto add +62 if needed
            $phone = preg_replace('/[^0-9+]/', '', $login);

            if (!str_starts_with($phone, '+')) {
                // Remove leading 0 if exists
                if (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                $phone = '+62' . $phone;
            }

            $credentials = ['phone' => $phone, 'password' => $request->password];
        }

        if (Auth::attempt($credentials)) {
            // Check if user is suspended
            if (auth()->user()->is_suspended) {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'Akun Anda telah ditangguhkan/disuspend oleh Admin. Silakan hubungi CS.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

            // Direct Redirect for Admin
            if (auth()->user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended('home');
        }

        return back()->withErrors([
            'login' => 'Email, No HP, atau password salah.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
