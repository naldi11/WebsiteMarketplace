<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Cart;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'selected_items' => 'required|string',
        ]);

        $selectedIds = explode(',', $request->selected_items);
        $totalPrice = Cart::whereIn('id', $selectedIds)
            ->where('user_id', auth()->id())
            ->get()
            ->sum(fn($item) => $item->quantity * $item->product->price);

        $voucher = Voucher::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode voucher tidak valid.'
            ]);
        }

        if (!$voucher->isValidFor($totalPrice)) {
            return response()->json([
                'valid' => false,
                'message' => 'Syarat voucher tidak terpenuhi (cek minimal belanja).'
            ]);
        }

        return response()->json([
            'valid' => true,
            'code' => $voucher->code,
            'discount_amount' => (int) $voucher->discount_amount,
            'message' => 'Voucher berhasil digunakan!'
        ]);
    }
}
