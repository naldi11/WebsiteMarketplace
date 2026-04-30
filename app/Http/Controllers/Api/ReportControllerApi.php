<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportControllerApi extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'type' => 'required|in:buyer_issue,seller_issue',
            'reason' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        $userId = $request->user()->id;

        // Security: Ensure user is part of transaction
        if ($userId !== $transaction->buyer_id && $userId !== $transaction->seller_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Logic Verification: You can only report the opposite party
        if ($userId === $transaction->seller_id && $request->type !== 'buyer_issue') {
            return response()->json(['status' => 'error', 'message' => 'Sebagai penjual, Anda hanya dapat melaporkan masalah terkait pembeli.'], 400);
        }

        if ($userId === $transaction->buyer_id && $request->type !== 'seller_issue') {
            return response()->json(['status' => 'error', 'message' => 'Sebagai pembeli, Anda hanya dapat melaporkan masalah terkait penjual.'], 400);
        }

        $report = Report::create([
            'transaction_id' => $request->transaction_id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan Anda telah berhasil dikirim ke Admin.',
            'data' => $report
        ], 201);
    }
}
