<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
        ]);

        $input = $request->email_or_phone;
        $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($field, $input)->first();

        if (!$user) {
            return back()->withErrors(['email_or_phone' => 'User tidak ditemukan.']);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        DB::table('password_reset_otps')->updateOrInsert(
            ['email' => $user->email, 'phone' => $user->phone], // Compound key for simplicity logic
            [
                'email' => $user->email, // Ensure email is saved
                'otp' => $otp,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(5)
            ]
        );

        // Simulation logic
        session(['reset_contact' => $input]);
        return redirect()->route('password.otp')->with('success', "OTP Dikirim ke $input: $otp (Simulasi)");
    }

    public function showOtp()
    {
        if (!session('reset_contact'))
            return redirect()->route('password.request');
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric|digits:6']);
        $contact = session('reset_contact');

        // Find OTP
        $record = DB::table('password_reset_otps')
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        // Simple check (in real app check contact match)
        if (!$record) {
            return back()->withErrors(['otp' => 'OTP Salah atau Kadaluarsa.']);
        }

        // OTP Valid
        session(['otp_verified' => true]);
        return redirect()->route('password.reset');
    }

    public function showReset()
    {
        if (!session('otp_verified'))
            return redirect()->route('password.request');
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['password' => 'required|min:8|confirmed']);

        $contact = session('reset_contact');
        $field = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($field, $contact)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // Cleanup
        DB::table('password_reset_otps')->where('email', $user->email)->delete();
        session()->forget(['reset_contact', 'otp_verified']);

        return redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login.');
    }
}
