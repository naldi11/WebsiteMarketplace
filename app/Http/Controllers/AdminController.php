<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function checkAdmin()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses Ditolak. Halaman ini khusus Admin.');
        }
    }

    public function dashboard(Request $request)
    {
        $this->checkAdmin();
        
        $period = $request->query('period', 'all');
        $startDate = null;
        $endDate = null;
        
        if ($period === 'today') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($period === 'week') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif ($period === 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($period === 'year') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
        }

        // Apply period filter to queries
        $transactionQuery = Transaction::query();
        $userQuery = User::where('role', '!=', 'admin');
        $productQuery = \App\Models\Product::query();
        
        if ($startDate && $endDate) {
            $transactionQuery->whereBetween('created_at', [$startDate, $endDate]);
            $userQuery->whereBetween('created_at', [$startDate, $endDate]);
            $productQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $stats = [
            'total_sales' => (clone $transactionQuery)->where('status', 'completed')->sum('total_amount'),
            'platform_profit' => (clone $transactionQuery)->where('status', 'completed')->sum('service_fee') + (clone $transactionQuery)->where('status', 'completed')->sum('admin_fee'),
            'total_users' => (clone $userQuery)->count(), 
            'escrow_funds' => \App\Models\SellerBalance::sum('available_balance') + \App\Models\SellerBalance::sum('pending_balance'),
            'total_wallet_balance' => \App\Models\Wallet::sum('balance'),
            'total_wallet_pending' => \App\Models\Wallet::sum('pending_balance'),
            'wallet_tx_count' => \App\Models\WalletTransaction::count(),
        ];

        // Composition of Transaction Statuses
        $orderStatus = [
            'completed' => (clone $transactionQuery)->where('status', 'completed')->count(),
            'pending' => (clone $transactionQuery)->whereIn('status', ['pending', 'waiting_payment', 'processing', 'packaging', 'ready_to_ship'])->count(),
            'cancelled' => (clone $transactionQuery)->where('status', 'cancelled')->count(),
        ];

        // Top Sellers
        $topSellers = User::whereHas('sellerTransactions', function($q) {
                // To keep it simple, fetch top sellers regardless of period for the leaderboard
                $q->where('status', 'completed');
            })
            ->withCount(['sellerTransactions as completed_sales' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->withSum(['sellerTransactions as total_earnings' => function ($q) {
                $q->where('status', 'completed');
            }], 'seller_amount')
            ->orderByDesc('total_earnings')
            ->take(5)
            ->get();

        // Top Categories
        $topCategories = \App\Models\Category::withCount(['products as ordered_count' => function ($q) use ($startDate, $endDate) {
            $q->whereHas('transactionDetails.transaction', function ($txQuery) use ($startDate, $endDate) {
                $txQuery->where('status', 'completed');
                if ($startDate && $endDate) {
                    $txQuery->whereBetween('created_at', [$startDate, $endDate]);
                }
            });
        }])->orderByDesc('ordered_count')->take(5)->get();

        // Recent Transactions
        $recentTransactions = (clone $transactionQuery)->with(['buyer', 'items.product'])->latest()->take(5)->get();

        // Top Selling Products based on period
        $topProducts = \App\Models\Product::withCount([
            'transactionDetails as total_sold' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed');
                    if ($startDate && $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    }
                });
            }
        ])->orderByDesc('total_sold')->take(5)->get();

        // Low Stock Alerts (Absolute)
        $lowStockProducts = \App\Models\Product::where('stock', '<', 5)->take(5)->get();

        // Users Segmentation
        $sellers = (clone $userQuery)->whereHas('products')->latest()->take(50)->get();
        $buyers = (clone $userQuery)->whereDoesntHave('products')->latest()->take(50)->get();

        // Chart Data
        $chartLabels = [];
        $chartSales = [];
        $chartProfit = [];
        
        $chartQuery = (clone $transactionQuery)->where('status', 'completed');

        if ($period === 'today') {
            $records = $chartQuery->selectRaw('HOUR(created_at) as time_key, SUM(total_amount) as sales, SUM(service_fee) as profit, SUM(admin_fee) as other_income')
                ->groupBy('time_key')->get()->keyBy('time_key');
            for ($i = 0; $i < 24; $i++) {
                $chartLabels[] = sprintf('%02d:00', $i);
                $chartSales[] = isset($records[$i]) ? $records[$i]->sales : 0;
                $chartProfit[] = isset($records[$i]) ? $records[$i]->profit : 0;
                $chartOther[] = isset($records[$i]) ? $records[$i]->other_income : 0;
            }
        } elseif ($period === 'week') {
            $records = $chartQuery->selectRaw('DATE(created_at) as time_key, SUM(total_amount) as sales, SUM(service_fee) as profit, SUM(admin_fee) as other_income')
                ->groupBy('time_key')->get()->keyBy('time_key');
            $start = clone $startDate;
            while ($start <= $endDate) {
                $dateString = $start->format('Y-m-d');
                $chartLabels[] = $start->translatedFormat('D');
                $chartSales[] = isset($records[$dateString]) ? $records[$dateString]->sales : 0;
                $chartProfit[] = isset($records[$dateString]) ? $records[$dateString]->profit : 0;
                $chartOther[] = isset($records[$dateString]) ? $records[$dateString]->other_income : 0;
                $start->addDay();
            }
        } elseif ($period === 'month') {
            $records = $chartQuery->selectRaw('DATE(created_at) as time_key, SUM(total_amount) as sales, SUM(service_fee) as profit, SUM(admin_fee) as other_income')
                ->groupBy('time_key')->get()->keyBy('time_key');
            $start = clone $startDate;
            while ($start <= $endDate) {
                $dateString = $start->format('Y-m-d');
                $chartLabels[] = $start->format('d');
                $chartSales[] = isset($records[$dateString]) ? $records[$dateString]->sales : 0;
                $chartProfit[] = isset($records[$dateString]) ? $records[$dateString]->profit : 0;
                $chartOther[] = isset($records[$dateString]) ? $records[$dateString]->other_income : 0;
                $start->addDay();
            }
        } else {
            $q = $chartQuery;
            if ($period === 'all') {
                $q->whereYear('created_at', now()->year);
            }
            $records = $q->selectRaw('MONTH(created_at) as time_key, SUM(total_amount) as sales, SUM(service_fee) as profit, SUM(admin_fee) as other_income')
                ->groupBy('time_key')->get()->keyBy('time_key');
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($i = 1; $i <= 12; $i++) {
                $chartLabels[] = $months[$i - 1];
                $chartSales[] = isset($records[$i]) ? $records[$i]->sales : 0;
                $chartProfit[] = isset($records[$i]) ? $records[$i]->profit : 0;
                $chartOther[] = isset($records[$i]) ? $records[$i]->other_income : 0;
            }
        }

        $chartData = [
            'labels' => $chartLabels,
            'sales' => $chartSales,
            'profit' => $chartProfit,
            'other' => $chartOther ?? [],
        ];

        return view('admin.dashboard', compact('stats', 'recentTransactions', 'topProducts', 'lowStockProducts', 'topSellers', 'topCategories', 'orderStatus', 'chartData', 'period'));
    }

    public function users(Request $request)
    {
        $this->checkAdmin();
        $tab = $request->query('tab', 'all');

        $query = User::where('role', '!=', 'admin');

        if ($tab === 'sellers') {
            $query->whereHas('products');
        } elseif ($tab === 'buyers') {
            $query->whereDoesntHave('products');
        } elseif ($tab === 'suspended') {
            $query->where('is_suspended', true);
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users', compact('users', 'tab'));
    }

    public function toggleSuspendUser(Request $request, $id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id() || $user->role === 'admin') {
            return back()->with('error', 'Admin tidak dapat disuspend.');
        }

        $user->is_suspended = !$user->is_suspended;
        $user->save();

        $action = $user->is_suspended ? 'ditangguhkan' : 'diaktifkan kembali';
        return back()->with('success', "User {$user->name} berhasil $action.");
    }

    public function deleteUser($id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);

        if ($user->id === auth()->id() || $user->role === 'admin') {
            return back()->with('error', 'Admin tidak dapat dihapus.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function transactions(Request $request)
    {
        $this->checkAdmin();
        $tab = $request->query('tab', 'all');

        $query = Transaction::with(['buyer', 'seller', 'items.product'])->latest();

        if ($tab === 'payment') {
            $query->where('status', 'pending');
        } elseif ($tab === 'release') {
            $query->where('status', 'received');
        }

        $transactions = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => Transaction::count(),
            'payment' => Transaction::where('status', 'pending')->count(),
            'release' => Transaction::where('status', 'received')->count(),
        ];

        return view('admin.transactions.index', compact('transactions', 'counts', 'tab'));
    }

    public function showTransaction(Transaction $transaction)
    {
        $this->checkAdmin();
        $transaction->load(['buyer', 'seller', 'items.product', 'items.product.category', 'trackingLogs']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function verifyPayment(Transaction $transaction)
    {
        $this->checkAdmin();

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi tidak valid untuk diverifikasi.');
        }

        $transaction->update(['status' => 'paid_verified']);

        // CRITICAL FIX: Add to seller's pending balance
        $sellerAmount = $transaction->seller_amount ?? $transaction->total_amount;
        $sellerBalance = \App\Models\SellerBalance::firstOrCreate(
            ['user_id' => $transaction->seller_id],
            ['pending_balance' => 0, 'available_balance' => 0]
        );
        $sellerBalance->increment('pending_balance', $sellerAmount);

        // Add tracking log
        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'payment_verified',
            null,
            'Pembayaran diverifikasi oleh Admin. Dana Rp ' . number_format($sellerAmount, 0, ',', '.') . ' ditahan di Saldo Pending',
            'admin',
            auth()->id()
        );

        return back()->with('success', 'Pembayaran diverifikasi! Penjual dapat segera mengirim barang.');
    }

    public function rejectPayment(Request $request, Transaction $transaction)
    {
        $this->checkAdmin();

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi tidak valid untuk ditolak.');
        }

        $request->validate([
            'note' => 'required|string|max:255'
        ]);

        $transaction->update([
            'status' => 'payment_rejected',
            'seller_notes' => 'Pembayaran ditolak Admin: ' . $request->note
        ]);

        // Add tracking log
        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'payment_rejected',
            null,
            'Pembayaran ditolak oleh Admin. Alasan: ' . $request->note,
            'admin',
            auth()->id()
        );

        return back()->with('success', 'Pembayaran ditolak. Pembeli akan menerima notifikasi untuk mengunggah bukti baru.');
    }

    public function releaseFunds(Request $request, Transaction $transaction)
    {
        $this->checkAdmin();

        $request->validate([
            'transfer_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ], [
            'transfer_proof.required' => 'Bukti transfer wajib diunggah.',
            'transfer_proof.mimes' => 'Bukti transfer harus berupa gambar (JPEG/PNG) atau PDF.',
            'transfer_proof.max' => 'Ukuran file maksimal 2MB.',
        ]);

        // Only allow release for received status (completed means already released)
        if ($transaction->status !== 'received') {
            return back()->with('error', 'Dana hanya bisa dilepas untuk status RECEIVED. Status saat ini: ' . strtoupper($transaction->status));
        }

        $sellerAmount = $transaction->seller_amount ?? $transaction->total_amount;

        // Use database transaction for atomicity
        \DB::beginTransaction();
        try {
            // Lock the transaction row to prevent concurrent releases
            $transaction = Transaction::where('id', $transaction->id)->lockForUpdate()->first();

            // Double-check status after lock (no released_at since column doesn't exist)
            if ($transaction->status !== 'received') {
                \DB::rollBack();
                return back()->with('error', 'Status transaksi berubah menjadi ' . strtoupper($transaction->status) . ', tidak bisa dilepas.');
            }

            // Get seller balance with lock
            $sellerBalance = \App\Models\SellerBalance::where('user_id', $transaction->seller_id)->lockForUpdate()->first();
            if (!$sellerBalance) {
                $sellerBalance = \App\Models\SellerBalance::create([
                    'user_id' => $transaction->seller_id,
                    'pending_balance' => 0,
                    'available_balance' => 0,
                ]);
            }

            // FIX: Jika pending balance kurang dari seller_amount, adjust dulu
            // Ini untuk handle old transactions yang dibuat sebelum sistem pending balance ada
            if ($sellerBalance->pending_balance < $sellerAmount) {
                \Log::warning("Pending balance insufficient for TX#{$transaction->id}. Adjusting from {$sellerBalance->pending_balance} to {$sellerAmount}");
                $sellerBalance->pending_balance = $sellerAmount;
            }

            // Move from pending to available
            $sellerBalance->pending_balance -= $sellerAmount;
            $sellerBalance->available_balance += $sellerAmount;
            $sellerBalance->total_earnings += $sellerAmount;
            $sellerBalance->save();

            // Record platform earnings
            \App\Models\PlatformEarning::create([
                'transaction_id' => $transaction->id,
                'service_fee' => $transaction->service_fee ?? 0,
                'payment_fee' => 0,
                'description' => 'Biaya layanan dari transaksi #' . $transaction->id,
            ]);

            // Upload transfer proof
            $transferProofPath = $request->file('transfer_proof')->store('transfer_proofs', 'public');

            // Update transaction
            $transaction->update([
                'status' => 'completed',
                'transfer_proof' => $transferProofPath,
            ]);

            // Add tracking log
            \App\Models\OrderTrackingLog::addLog(
                $transaction->id,
                'completed',
                'Transaksi Selesai',
                'Dana Rp ' . number_format($sellerAmount, 0, ',', '.') . ' telah diteruskan ke penjual',
                'admin',
                auth()->id()
            );

            \DB::commit();
            return back()->with('success', 'Dana Rp ' . number_format($sellerAmount, 0, ',', '.') . ' dilepas ke Penjual. Transaksi Selesai.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Release funds error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Payment Methods Management
    public function paymentMethods()
    {
        $this->checkAdmin();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
        return view('admin.payment_methods.index', compact('paymentMethods'));
    }

    public function storePaymentMethod(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'code' => 'required|unique:payment_methods,code',
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'type' => 'required|in:bank_transfer,ewallet,qris,credit_card,cod',
            'icon' => 'nullable|string|max:10',
            'instructions' => 'nullable|string',
            'admin_fee' => 'nullable|numeric|min:0',
            'admin_fee_percent' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        \App\Models\PaymentMethod::create($data);
        return back()->with('success', 'Metode pembayaran berhasil ditambahkan!');
    }

    public function updatePaymentMethod(Request $request, \App\Models\PaymentMethod $paymentMethod)
    {
        $this->checkAdmin();
        $request->validate([
            'code' => 'required|unique:payment_methods,code,' . $paymentMethod->id,
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'type' => 'required|in:bank_transfer,ewallet,qris,credit_card,cod',
            'icon' => 'nullable|string|max:10',
            'instructions' => 'nullable|string',
            'admin_fee' => 'nullable|numeric|min:0',
            'admin_fee_percent' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $paymentMethod->update($data);
        return back()->with('success', 'Metode pembayaran berhasil diperbarui!');
    }

    public function destroyPaymentMethod(\App\Models\PaymentMethod $paymentMethod)
    {
        $this->checkAdmin();
        $paymentMethod->delete();
        return back()->with('success', 'Metode pembayaran berhasil dihapus!');
    }

    public function vouchers()
    {
        $this->checkAdmin();
        $vouchers = \App\Models\Voucher::latest()->get();
        $categories = \App\Models\Category::all();
        return view('admin.vouchers', compact('vouchers', 'categories'));
    }

    public function storeVoucher(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|unique:vouchers,code',
            'discount_type' => 'required|in:fixed,percent',
            'discount_amount' => 'required|numeric',
            'max_discount_amount' => 'nullable|numeric',
            'usage_limit' => 'required|integer',
            'quota_total' => 'required|integer',
            'min_purchase' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'target_user_id' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'terms' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') || $request->is_active == 1;

        \App\Models\Voucher::create($data);
        return back()->with('success', 'Voucher berhasil dibuat!');
    }

    public function updateVoucher(Request $request, \App\Models\Voucher $voucher)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|unique:vouchers,code,' . $voucher->id,
            'discount_type' => 'required|in:fixed,percent',
            'discount_amount' => 'required|numeric',
            'max_discount_amount' => 'nullable|numeric',
            'usage_limit' => 'required|integer',
            'quota_total' => 'required|integer',
            'min_purchase' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'target_user_id' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'terms' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') || $request->is_active == 1;

        $voucher->update($data);
        return back()->with('success', 'Voucher berhasil diperbarui!');
    }

    public function destroyVoucher(\App\Models\Voucher $voucher)
    {
        $this->checkAdmin();
        $voucher->delete();
        return back()->with('success', 'Voucher berhasil dihapus!');
    }

    // Category Management
    public function categories()
    {
        $this->checkAdmin();
        $categories = \App\Models\Category::withCount('products')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        $this->checkAdmin();
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|max:2048',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('categories', 'public');
        }

        \App\Models\Category::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'icon' => $iconPath,
        ]);

        return redirect()->route('admin.categories')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function editCategory(\App\Models\Category $category)
    {
        $this->checkAdmin();
        return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, \App\Models\Category $category)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            // Delete old icon if exists (optional but good practice)
            if ($category->icon) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($category->icon);
            }
            $category->icon = $request->file('icon')->store('categories', 'public');
        }

        $category->update([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'icon' => $category->icon,
        ]);

        return redirect()->route('admin.categories')->with('success', 'Kategori diperbarui!');
    }

    public function destroyCategory(\App\Models\Category $category)
    {
        $this->checkAdmin();
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki produk.');
        }

        if ($category->icon) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($category->icon);
        }
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    // Balance Management
    public function balances()
    {
        $this->checkAdmin();

        // Seller balances from completed transactions
        $sellers = User::whereHas('sellerTransactions', function ($q) {
            $q->where('status', 'completed');
        })->withSum([
                    'sellerTransactions as total_sales' => function ($q) {
                        $q->where('status', 'completed');
                    }
                ], 'total_amount')->withSum([
                    'sellerTransactions as actual_seller_earnings' => function ($q) {
                        $q->where('status', 'completed');
                    }
                ], 'seller_amount')->withSum([
                    'sellerTransactions as total_service_fees' => function ($q) {
                        $q->where('status', 'completed');
                    }
                ], 'service_fee')->with(['sellerBalance'])->get()->map(function ($seller) {
                    // Note: seller_earnings here is from the transactions, 
                    // whereas available_balance in sellerBalance is what's left after withdrawals
                    $seller->seller_earnings = $seller->actual_seller_earnings ?? 0;
                    $seller->total_withdrawn = $seller->sellerBalance->total_withdrawn ?? 0;
                    return $seller;
                })->sortByDesc('seller_earnings');

        // Platform total earnings
        $platformEarnings = Transaction::where('status', 'completed')->sum('service_fee');

        // Monthly platform earnings breakdown (current year)
        $monthlyEarnings = Transaction::where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(service_fee) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Summary stats
        $totalCompletedTransactions = Transaction::where('status', 'completed')->count();
        $totalSellerEarnings = Transaction::where('status', 'completed')->sum('seller_amount');
        $avgEarningsPerTx = $totalCompletedTransactions > 0 ? $totalSellerEarnings / $totalCompletedTransactions : 0;
        $totalPlatformWithdrawn = \App\Models\SellerBalance::sum('total_withdrawn');

        $latestTransactions = Transaction::where('status', 'completed')
            ->with(['seller', 'buyer'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.balances', compact('sellers', 'platformEarnings', 'monthlyEarnings', 'avgEarningsPerTx', 'totalPlatformWithdrawn', 'latestTransactions'));
    }

    public function settings()
    {
        $this->checkAdmin();
        $settings = \App\Models\SystemSetting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            \App\Models\SystemSetting::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui!');
    }

    // Ad Banners Management
    public function adBanners()
    {
        $this->checkAdmin();
        $adBanners = \App\Models\AdBanner::latest()->get();
        return view('admin.ad_banners.index', compact('adBanners'));
    }

    public function storeAdBanner(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
        ]);

        $imagePath = $request->file('image')->store('ad_banners', 'public');

        \App\Models\AdBanner::create([
            'title' => $request->title,
            'image' => $imagePath,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Banner Iklan berhasil ditambahkan!');
    }

    public function updateAdBanner(Request $request, \App\Models\AdBanner $adBanner)
    {
        $this->checkAdmin();
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'title' => $request->title,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($adBanner->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($adBanner->image);
            }
            $data['image'] = $request->file('image')->store('ad_banners', 'public');
        }

        $adBanner->update($data);

        return back()->with('success', 'Banner Iklan berhasil diperbarui!');
    }

    public function destroyAdBanner(\App\Models\AdBanner $adBanner)
    {
        $this->checkAdmin();
        if ($adBanner->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($adBanner->image);
        }
        $adBanner->delete();
        return back()->with('success', 'Banner Iklan dihapus.');
    }

    // Reports Management
    public function reports(Request $request)
    {
        $this->checkAdmin();
        $status = $request->query('status', 'pending');
        
        $query = \App\Models\Report::with(['user', 'transaction.buyer', 'transaction.seller'])->latest();
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reports = $query->paginate(15)->withQueryString();
        
        $counts = [
            'pending' => \App\Models\Report::where('status', 'pending')->count(),
            'resolved' => \App\Models\Report::where('status', 'resolved')->count(),
            'dismissed' => \App\Models\Report::where('status', 'dismissed')->count(),
            'all' => \App\Models\Report::count(),
        ];

        return view('admin.reports.index', compact('reports', 'counts', 'status'));
    }

    public function showReport(\App\Models\Report $report)
    {
        $this->checkAdmin();
        $report->load(['user', 'transaction.buyer', 'transaction.seller', 'transaction.items.product']);
        return view('admin.reports.show', compact('report'));
    }

    public function updateReport(Request $request, \App\Models\Report $report)
    {
        $this->checkAdmin();
        $request->validate([
            'status' => 'required|in:pending,resolved,dismissed',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $report->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
        ]);

        return redirect()->route('admin.reports')->with('success', 'Laporan berhasil diperbarui.');
    }



    public function walletLogs(Request $request)
    {
        $this->checkAdmin();
        $query = \App\Models\WalletTransaction::with(['wallet.user'])->latest();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('wallet.user', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            })->orWhere('description', 'like', "%$search%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->paginate(20)->withQueryString();
        
        return view('admin.wallet_logs', compact('logs'));
    }

    public function resolveDispute(Request $request, \App\Models\Report $report)
    {
        $this->checkAdmin();
        $request->validate([
            'resolution' => 'required|string',
        ]);

        $report->update([
            'status' => 'resolved',
            'admin_note' => $request->resolution,
        ]);

        return back()->with('success', 'Perselisihan berhasil diselesaikan.');
    }

    public function printInvoice(\App\Models\Transaction $transaction)
    {
        $this->checkAdmin();
        $transaction->load(['items.product', 'buyer', 'shippingAddressRecord']);
        return view('admin.transactions.invoice', compact('transaction'));
    }
}
