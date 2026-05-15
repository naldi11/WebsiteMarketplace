<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'type'           => 'required|in:buyer_issue,seller_issue',
            'reason'         => 'required|string|max:100',
            'description'    => 'required|string|max:1000',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        if (auth()->id() !== $transaction->buyer_id && auth()->id() !== $transaction->seller_id) {
            abort(403);
        }

        Report::create([
            'transaction_id' => $request->transaction_id,
            'user_id'        => auth()->id(),
            'type'           => $request->type,
            'reason'         => $request->reason,
            'description'    => $request->description,
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Laporan Anda telah berhasil dikirim ke Admin. Kami akan segera meninjau masalah ini.');
    }
}
