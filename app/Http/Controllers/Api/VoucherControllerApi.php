<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherControllerApi extends Controller
{
    /**
     * List user's vouchers (for selection in checkout)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $totalAmount = $request->query('total_amount', 0);

        $vouchers = \App\Models\UserVoucher::whereHas('voucher', function($q) use ($request) {
                $q->where(function($sq) {
                    $sq->whereNull('end_date')
                       ->orWhere('end_date', '>=', now());
                });

                if ($request->filled('category_id')) {
                    $q->where('category_id', $request->category_id);
                }
            })
            ->with('voucher')
            ->where('user_id', $user->id)
            ->where('is_used', false)
            ->get()
            ->map(function ($uv) use ($totalAmount, $user) {
                $voucher = $uv->voucher;
                $isValid = $voucher->isValidFor($totalAmount, $user->id);
                
                return [
                    'user_voucher_id' => $uv->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name ?? $voucher->code,
                    'discount_type' => $voucher->discount_type,
                    'discount_amount' => (int) $voucher->discount_amount,
                    'max_discount_amount' => (int) $voucher->max_discount_amount,
                    'min_purchase' => (int) $voucher->min_purchase,
                    'start_date' => $voucher->start_date,
                    'end_date' => $voucher->end_date,
                    'terms' => $voucher->terms,
                    'description' => $voucher->description,
                    'is_valid' => $isValid,
                    'invalid_reason' => $isValid ? null : $this->getInvalidReason($voucher, $totalAmount)
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $vouchers
        ]);
    }

    /**
     * List public vouchers available for everyone
     */
    public function publicIndex(Request $request)
    {
        $vouchers = \App\Models\Voucher::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->whereRaw('usage_count < usage_limit');

        if ($request->user()) {
            $vouchers->whereDoesntHave('userVouchers', function($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        $vouchers = $vouchers->latest()
            ->get()
            ->map(function($voucher) use ($request) {
                $isClaimed = false;
                if ($request->user()) {
                    $isClaimed = \App\Models\UserVoucher::where('user_id', $request->user()->id)
                        ->where('voucher_id', $voucher->id)
                        ->exists();
                }

                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name ?? $voucher->code,
                    'discount_type' => $voucher->discount_type,
                    'discount_amount' => (int) $voucher->discount_amount,
                    'min_purchase' => (int) $voucher->min_purchase,
                    'end_date' => $voucher->end_date,
                    'terms' => $voucher->terms,
                    'description' => $voucher->description,
                    'is_claimed' => $isClaimed
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $vouchers
        ]);
    }

    private function getInvalidReason($voucher, $amount) {
        if (!$voucher->is_active) return "Voucher tidak aktif.";
        if ($voucher->usage_count >= $voucher->usage_limit) return "Kuota voucher habis.";
        if ($amount < $voucher->min_purchase) return "Belanja minimal Rp " . number_format($voucher->min_purchase, 0, ',', '.');
        if ($voucher->end_date && now()->gt($voucher->end_date)) return "Voucher kadaluarsa.";
        return "Syarat tidak terpenuhi.";
    }

    /**
     * Check/validate a specific user voucher
     */
    public function check(Request $request)
    {
        $request->validate([
            'user_voucher_id' => 'required|exists:user_vouchers,id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $userVoucher = \App\Models\UserVoucher::with('voucher')
            ->where('id', $request->user_voucher_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$userVoucher || $userVoucher->is_used) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher tidak ditemukan atau sudah digunakan.'
            ], 404);
        }

        $voucher = $userVoucher->voucher;

        if (!$voucher->isValidFor($request->total_amount, $request->user()->id)) {
            return response()->json([
                'status' => 'error',
                'message' => $this->getInvalidReason($voucher, $request->total_amount)
            ], 400);
        }

        $discount = $voucher->calculateDiscount($request->total_amount);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher berhasil diterapkan!',
            'data' => [
                'user_voucher_id' => $userVoucher->id,
                'code' => $voucher->code,
                'discount_amount' => (int) $discount,
                'min_purchase' => (int) $voucher->min_purchase,
                'max_discount_amount' => (int) $voucher->max_discount_amount,
                'discount_type' => $voucher->discount_type,
            ]
        ]);
    }

    /**
     * Claim a public voucher
     */
    public function claim(Request $request, $id)
    {
        $user = $request->user();
        $voucher = \App\Models\Voucher::findOrFail($id);

        // Check if already claimed
        $exists = \App\Models\UserVoucher::where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher sudah diklaim sebelumnya.'
            ], 400);
        }

        // Check quota
        if ($voucher->usage_count >= $voucher->usage_limit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kuota voucher sudah habis.'
            ], 400);
        }

        // Create user voucher
        \App\Models\UserVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'is_used' => false
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher berhasil diklaim!'
        ]);
    }
}
