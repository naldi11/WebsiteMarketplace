<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerBalance;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SellerControllerApi extends Controller
{
    /**
     * Get seller's transactions (incoming orders)
     */
    public function transactions(Request $request)
    {
        $userId = $request->user()->id;
        $transactions = Transaction::where('seller_id', $userId)
            ->where('user_hidden', false)
            ->with(['buyer', 'items.product'])
            ->latest()
            ->get();

        $counts = [
            'waiting_payment' => Transaction::where('seller_id', $userId)->where('status', 'waiting_payment')->where('seller_seen', false)->count(),
            'pending' => Transaction::where('seller_id', $userId)->where('status', 'pending')->where('seller_seen', false)->count(),
            'processing' => Transaction::where('seller_id', $userId)->whereIn('status', ['processing', 'packed'])->where('seller_seen', false)->count(),
            'shipped' => Transaction::where('seller_id', $userId)->where('status', 'shipped')->where('seller_seen', false)->count(),
            'received' => Transaction::where('seller_id', $userId)->whereIn('status', ['received', 'completed'])->where('seller_seen', false)->count(),
            'cancelled' => Transaction::where('seller_id', $userId)->where('status', 'cancelled')->where('user_hidden', false)->where('seller_seen', false)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
            'counts' => $counts
        ]);
    }

    /**
     * Get single transaction detail for seller
     */
    public function show(Request $request, $id)
    {
        $transaction = Transaction::where('seller_id', $request->user()->id)
            ->with(['buyer', 'items.product', 'shippingAddressRecord'])
            ->findOrFail($id);

        // Mark as seen when viewed
        $transaction->update(['seller_seen' => true]);

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    /**
     * Remove (hide) cancelled transaction from seller view
     */
    public function destroy(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('seller_id', $request->user()->id)
            ->firstOrFail();

        if ($transaction->status !== 'cancelled') {
            return response()->json(['status' => 'error', 'message' => 'Hanya pesanan dibatalkan yang dapat dihapus.'], 400);
        }

        $transaction->update(['user_hidden' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil dihapus dari riwayat Anda.',
        ]);
    }

    /**
     * Get seller balance and stats
     */
    public function balance(Request $request)
    {
        $userId = $request->user()->id;
        $balance = SellerBalance::getOrCreate($userId);

        $stats = [
            'total_sales' => Transaction::where('seller_id', $userId)
                ->where('status', 'completed')
                ->count(),
            'total_earnings' => Transaction::where('seller_id', $userId)
                ->where('status', 'completed')
                ->sum('seller_amount'),
            'pending_orders' => Transaction::where('seller_id', $userId)
                ->whereIn('status', ['paid_verified', 'shipped'])
                ->count(),
            'avg_earnings' => Transaction::where('seller_id', $userId)
                ->where('status', 'completed')
                ->avg('seller_amount') ?? 0,
        ];

        $recentTransactions = Transaction::where('seller_id', $userId)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'updated_at', 'seller_amount']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'balance' => $balance,
                'stats' => $stats,
                'recent_transactions' => $recentTransactions
            ]
        ]);
    }

    /**
     * Request withdrawal
     */
    public function withdraw(Request $request)
    {
        $user = $request->user();
        $balance = SellerBalance::getOrCreate($user->id);

        $request->validate([
            'amount' => 'required|numeric|min:50000|max:' . $balance->available_balance,
            'bank_name' => 'required|string|max:50',
            'account_number' => 'required|string|max:30',
            'account_name' => 'required|string|max:100',
        ]);

        $amount = $request->amount;
        $balance->available_balance -= $amount;
        $balance->total_withdrawn += $amount;
        $balance->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan penarikan Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diajukan!',
            'data' => $balance
        ]);
    }

    /**
     * Mark transactions of a specific status as seen for seller
     */
    public function markAsSeen(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->status;

        $query = Transaction::where('seller_id', $userId);

        if ($status === 'waiting_payment') {
            $query->where('status', 'waiting_payment');
        } elseif ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'processing') {
            $query->whereIn('status', ['processing', 'packed']);
        } elseif ($status === 'shipped') {
            $query->where('status', 'shipped');
        } elseif ($status === 'received') {
            $query->whereIn('status', ['received', 'completed']);
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $query->update(['seller_seen' => true]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Get seller dashboard stats
     */
    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        $totalSales = Transaction::where('seller_id', $userId)->whereIn('status', ['completed', 'received'])->sum('seller_amount');
        $totalOrders = Transaction::where('seller_id', $userId)->count();
        $totalProducts = \App\Models\Product::where('user_id', $userId)->count();
        $conversionRate = $totalProducts > 0 ? min(100, round(($totalOrders / $totalProducts) * 10)) : 0;

        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $amount = Transaction::where('seller_id', $userId)
                ->whereIn('status', ['received', 'completed'])
                ->whereDate('created_at', $date->toDateString())
                ->sum('seller_amount');
            
            $salesTrend[] = [
                'date' => $date->translatedFormat('D'),
                'amount' => $amount
            ];
        }

        $topProductsQuery = \Illuminate\Support\Facades\DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.seller_id', $userId)
            ->whereIn('transactions.status', ['received', 'completed'])
            ->select('products.name', \Illuminate\Support\Facades\DB::raw('SUM(transaction_details.quantity) as sales'))
            ->groupBy('products.name')
            ->orderBy('sales', 'desc')
            ->limit(5)
            ->get();

        $topProducts = [];
        foreach ($topProductsQuery as $b) {
            $topProducts[] = [
                'productName' => mb_strimwidth($b->name, 0, 15, "..."),
                'sales' => (int) $b->sales
            ];
        }
        
        $orderStatus = [
            'pending' => Transaction::where('seller_id', $userId)->where('status', 'pending')->count(),
            'processing' => Transaction::where('seller_id', $userId)->whereIn('status', ['processing', 'packed'])->count(),
            'shipped' => Transaction::where('seller_id', $userId)->where('status', 'shipped')->count(),
            'completed' => Transaction::where('seller_id', $userId)->whereIn('status', ['received', 'completed'])->count(),
            'cancelled' => Transaction::where('seller_id', $userId)->where('status', 'cancelled')->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'totalProducts' => $totalProducts,
                'conversionRate' => $conversionRate,
                'salesTrend' => $salesTrend,
                'topProducts' => $topProducts,
                'orderStatus' => $orderStatus
            ]
        ]);
    }
}
