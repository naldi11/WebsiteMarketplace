<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherControllerApi extends Controller
{
    /**
     * List active vouchers
     */
    public function index()
    {
        $vouchers = Voucher::where('is_active', true)
            ->whereColumn('usage_count', '<', 'usage_limit')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $vouchers
        ]);
    }

    /**
     * Check/validate a voucher code
     */
    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode voucher tidak valid.'
            ], 404);
        }

        if (!$voucher->isValidFor($request->total_amount, $request->user()->id)) {
            $message = 'Syarat voucher tidak terpenuhi (min. belanja Rp ' . number_format($voucher->min_purchase, 0, ',', '.') . ') atau voucher tidak diperuntukkan untuk Anda.';
            
            // Note: Since we don't know the categories in this simple check API yet (it usually comes from checkout),
            // we will just show the generic message, BUT if we want to be proactive:
            if ($voucher->category_id) {
                $categoryName = $voucher->category ? $voucher->category->name : 'kategori tertentu';
                $message .= ' Voucher ini hanya berlaku untuk kategori ' . $categoryName . '.';
            }

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 400);
        }

        $discount = $voucher->calculateDiscount($request->total_amount);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher berhasil digunakan!',
            'data' => [
                'code' => $voucher->code,
                'discount_amount' => (int) $discount,
                'min_purchase' => (int) $voucher->min_purchase,
                'max_discount_amount' => (int) $voucher->max_discount_amount,
                'discount_type' => $voucher->discount_type,
                'terms' => $voucher->terms,
            ]
        ]);
    }
}
