<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewControllerApi extends Controller
{
    /**
     * Store a review for a completed transaction
     */
    public function store(Request $request, $transactionId)
    {
        $transaction = Transaction::with('items.product')->findOrFail($transactionId);

        if ($transaction->buyer_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($transaction->status, ['received', 'completed'])) {
            return response()->json(['status' => 'error', 'message' => 'Transaksi belum selesai.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // Create review for each product in the transaction
        foreach ($transaction->items as $item) {
            \App\Models\Review::updateOrCreate(
                [
                    'reviewer_id' => $request->user()->id,
                    'product_id' => $item->product_id,
                    'transaction_id' => $transaction->id,
                ],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Review berhasil ditambahkan!',
        ]);
    }
}
