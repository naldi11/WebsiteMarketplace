<?php

namespace App\Http\Controllers;

use App\Models\SellerBalance;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function balance()
    {
        $user = auth()->user();

        // Get or create seller balance
        $balance = SellerBalance::getOrCreate($user->id);

        // Get recent transactions as seller
        $recentTransactions = Transaction::where('seller_id', $user->id)
            ->whereIn('status', ['completed', 'received', 'shipped', 'paid_verified'])
            ->with(['buyer', 'items.product'])
            ->latest()
            ->take(10)
            ->get();

        // Calculate stats
        $stats = [
            'total_sales' => Transaction::where('seller_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'total_earnings' => Transaction::where('seller_id', $user->id)
                ->where('status', 'completed')
                ->sum('seller_amount'),
            'pending_orders' => Transaction::where('seller_id', $user->id)
                ->whereIn('status', ['paid_verified', 'shipped'])
                ->count(),
        ];

        return view('seller.balance', compact('balance', 'recentTransactions', 'stats'));
    }

    public function withdrawRequest(Request $request)
    {
        $user = auth()->user();
        $balance = SellerBalance::getOrCreate($user->id);

        $request->validate([
            'amount' => 'required|numeric|min:50000|max:' . $balance->available_balance,
            'bank_name' => 'required|string|max:50',
            'account_number' => 'required|string|max:30',
            'account_name' => 'required|string|max:100',
        ]);

        $amount = $request->amount;

        // Deduct from available balance
        $balance->available_balance -= $amount;
        $balance->withdrawn_balance += $amount;
        $balance->save();

        // Here you would create a withdrawal record for admin to process
        // For now, we'll just log it and show success

        // TODO: Create WithdrawalRequest model and record
        // WithdrawalRequest::create([
        //     'user_id' => $user->id,
        //     'amount' => $amount,
        //     'bank_name' => $request->bank_name,
        //     'account_number' => $request->account_number,
        //     'account_name' => $request->account_name,
        //     'status' => 'pending',
        // ]);

        return back()->with('success', 'Permintaan penarikan Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diajukan!');
    }

    public function transactions()
    {
        $user = auth()->user();

        $transactions = Transaction::where('seller_id', $user->id)
            ->with(['buyer', 'items.product'])
            ->latest()
            ->paginate(20);

        return view('seller.transactions', compact('transactions'));
    }
}
