<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
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
            'total_escrow_pending' => \App\Models\SellerBalance::sum('pending_balance'),
            'pending_refunds' => \App\Models\RefundRecord::where('status', 'pending')->count(),
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
        $chartOther = [];

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
$user = User::findOrFail($id);

        if ($user->id === auth()->id() || $user->role === 'admin') {
            return back()->with('error', 'Admin tidak dapat dihapus.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function transactions(Request $request)
    {
        $query = Transaction::with(['buyer', 'seller', 'items.product']);

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search Query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // If it is numeric, try matching ID exactly
                if (is_numeric($search)) {
                    $q->where('id', $search);
                } else {
                    $q->where('transaction_number', 'like', "%$search%");
                }
                
                $q->orWhereHas('buyer', function ($bq) use ($search) {
                    $bq->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                })
                ->orWhereHas('seller', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            });
        }

        $transactions = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions'));
    }

    public function showTransaction(Transaction $transaction)
    {
$transaction->load(['buyer', 'seller', 'items.product', 'items.product.category', 'trackingLogs']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function verifyPayment(Transaction $transaction)
    {

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

        $grossSellerAmount = $transaction->seller_amount ?? $transaction->total_amount;
        $platformFee = $transaction->service_fee ?? 0;
        $netToSeller = $grossSellerAmount - $platformFee;

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
            if ($sellerBalance->pending_balance < $grossSellerAmount) {
                \Log::warning("Pending balance insufficient for TX#{$transaction->id}. Adjusting from {$sellerBalance->pending_balance} to {$grossSellerAmount}");
                $sellerBalance->pending_balance = $grossSellerAmount;
            }

            // Move from pending to available
            $sellerBalance->pending_balance -= $grossSellerAmount;
            $sellerBalance->available_balance += $netToSeller;
            $sellerBalance->total_earnings += $netToSeller;
            $sellerBalance->save();

            // Record platform earnings
            \App\Models\PlatformEarning::create([
                'transaction_id' => $transaction->id,
                'service_fee' => $platformFee,
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
                'Dana Rp ' . number_format($netToSeller, 0, ',', '.') . ' (setelah dipotong 10% platform fee) telah diteruskan ke penjual',
                'admin',
                auth()->id()
            );

            \DB::commit();
            return back()->with('success', 'Dana Rp ' . number_format($netToSeller, 0, ',', '.') . ' dilepas ke Penjual (setelah potongan 10% platform fee). Transaksi Selesai.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Release funds error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Payment Methods Management
    public function paymentMethods()
    {
$paymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
        return view('admin.payment_methods.index', compact('paymentMethods'));
    }

    public function storePaymentMethod(Request $request)
    {
$request->validate([
            'code' => 'required|unique:payment_methods,code',
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_holder_name' => 'required|string|max:255',
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
$request->validate([
            'code' => 'required|unique:payment_methods,code,' . $paymentMethod->id,
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_holder_name' => 'required|string|max:255',
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
$paymentMethod->delete();
        return back()->with('success', 'Metode pembayaran berhasil dihapus!');
    }

    public function vouchers()
    {
$vouchers = \App\Models\Voucher::latest()->get();
        $categories = \App\Models\Category::all();
        return view('admin.vouchers', compact('vouchers', 'categories'));
    }

    public function storeVoucher(Request $request)
    {
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
$voucher->delete();
        return back()->with('success', 'Voucher berhasil dihapus!');
    }

    // Category Management
    public function categories()
    {
$categories = \App\Models\Category::withCount('products')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
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
return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, \App\Models\Category $category)
    {
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
$settings = \App\Models\SystemSetting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
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
$adBanners = \App\Models\AdBanner::latest()->get();
        return view('admin.ad_banners.index', compact('adBanners'));
    }

    public function storeAdBanner(Request $request)
    {
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
if ($adBanner->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($adBanner->image);
        }
        $adBanner->delete();
        return back()->with('success', 'Banner Iklan dihapus.');
    }

    public function notificationsCheck(Request $request)
    {
        $openCount  = \App\Models\Dispute::whereIn('status', ['open', 'admin_reviewing'])->count();
        $latestId   = \App\Models\Dispute::whereIn('status', ['open', 'admin_reviewing'])->max('id') ?? 0;
        $lastSeenId = $request->session()->get('admin_last_dispute_id', 0);

        $hasNew = $latestId > $lastSeenId;
        if ($hasNew) {
            $request->session()->put('admin_last_dispute_id', $latestId);
        }

        return response()->json([
            'open_count' => $openCount,
            'has_new'    => $hasNew,
        ]);
    }

    public function refundLogs(Request $request)
    {
        $query = \App\Models\RefundRecord::with(['buyer', 'admin', 'transaction'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('buyer', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            })->orWhere('notes', 'like', "%$search%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.refund_logs', compact('logs'));
    }

    public function completeRefund(Request $request, \App\Models\RefundRecord $refundRecord)
    {
        $request->validate([
            'transfer_proof'      => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'bank_name'           => 'required|string|max:100',
            'account_number'      => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'notes'               => 'nullable|string|max:500',
        ]);

        if ($refundRecord->status === 'completed') {
            return back()->with('error', 'Refund ini sudah selesai diproses.');
        }

        $proofPath = $request->file('transfer_proof')->store('refund_proofs', 'public');

        $refundRecord->update([
            'bank_name'           => $request->bank_name,
            'account_number'      => $request->account_number,
            'account_holder_name' => $request->account_holder_name,
            'transfer_proof'      => $proofPath,
            'admin_id'            => auth()->id(),
            'notes'               => $request->notes ?? $refundRecord->notes,
            'status'              => 'completed',
            'refunded_at'         => now(),
        ]);

        return back()->with('success', 'Refund berhasil diselesaikan! Bukti transfer tersimpan.');
    }

    public function printInvoice(\App\Models\Transaction $transaction)
    {
$transaction->load(['items.product', 'buyer', 'shippingAddressRecord']);
        return view('admin.transactions.invoice', compact('transaction'));
    }
}

