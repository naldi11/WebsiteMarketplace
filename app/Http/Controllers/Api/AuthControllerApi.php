<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthControllerApi extends Controller
{
    public function register(Request $request)
    {
        $phoneInput = preg_replace('/[^0-9]/', '', $request->phone);
        if (str_starts_with($phoneInput, '62')) {
            $phoneInput = '+62' . substr($phoneInput, 2);
        } elseif (str_starts_with($phoneInput, '0')) {
            $phoneInput = '+62' . substr($phoneInput, 1);
        } else {
            $phoneInput = '+62' . $phoneInput;
        }

        $request->merge(['phone' => $phoneInput]);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10|max:16|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($request->role === 'seller') {
            $rules['latitude'] = 'nullable|numeric';
            $rules['longitude'] = 'nullable|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phoneInput,
            'password' => Hash::make($request->password),
            'role' => $request->role === 'seller' ? 'seller' : 'buyer',
            'shop_name' => $request->name, // Set shop name as user name by default
            'address' => null,
            'latitude' => $request->role === 'seller' ? $request->latitude : null,
            'longitude' => $request->role === 'seller' ? $request->longitude : null,
        ]);

        // Anti-Fraud Device Tracking & New User Voucher
        $deviceId = $request->device_id;
        if ($deviceId) {
            $deviceLog = \App\Models\DeviceLog::where('device_unique_id', $deviceId)->first();
            
            if (!$deviceLog) {
                // First time this device is used
                \App\Models\DeviceLog::create([
                    'device_unique_id' => $deviceId,
                    'first_user_id' => $user->id,
                    'is_new_user_claimed' => true
                ]);

                // Give "NEW USER" Voucher
                $newVoucher = \App\Models\Voucher::where('code', 'NEWUSER50')->first();
                if ($newVoucher) {
                    \App\Models\UserVoucher::create([
                        'user_id' => $user->id,
                        'voucher_id' => $newVoucher->id,
                        'claimed_at' => now(),
                        'is_used' => false
                    ]);
                }
            } else {
                // Device already exists, no voucher given
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $login = $request->login;
        $credentials = [];

        // Determine if login is email or phone
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $credentials = ['email' => $login, 'password' => $request->password];
        } else {
            $phoneInput = preg_replace('/[^0-9]/', '', $login);
            if (str_starts_with($phoneInput, '62')) {
                $phoneInput = '+62' . substr($phoneInput, 2);
            } elseif (str_starts_with($phoneInput, '0')) {
                $phoneInput = '+62' . substr($phoneInput, 1);
            } else {
                $phoneInput = '+62' . $phoneInput;
            }
            $credentials = ['phone' => $phoneInput, 'password' => $request->password];
        }

        $user = User::where(function ($query) use ($login, $credentials) {
            if (isset($credentials['email'])) {
                $query->where('email', $login);
            } else {
                $query->where('phone', $credentials['phone'] ?? null);
            }
        })->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email, No HP, atau password salah.'
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    public function getCounts(Request $request)
    {
        $user = $request->user();

        $cartCount = \App\Models\Cart::where('user_id', $user->id)->sum('quantity');
        $wishlistCount = \App\Models\Wishlist::where('user_id', $user->id)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'cart_count' => (int) $cartCount,
                'wishlist_count' => $wishlistCount
            ]
        ]);
    }
}
