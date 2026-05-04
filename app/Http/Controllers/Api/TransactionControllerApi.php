<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Voucher;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\SellerBalance;

class TransactionControllerApi extends Controller
{
    /**
     * Get user transactions history with status counts
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Base query for active (not hidden) transactions
        $query = Transaction::where('buyer_id', $userId)
            ->where('user_hidden', false);

        // Fetch paginated transactions
        $transactions = (clone $query)
            ->with(['items.product', 'seller', 'review'])
            ->latest()
            ->paginate(15);

        // Calculate counts for badges (only unseen)
        $counts = [
            'waiting_payment' => Transaction::where('buyer_id', $userId)->where('status', 'waiting_payment')->where('buyer_seen', false)->count(),
            'pending' => Transaction::where('buyer_id', $userId)->where('status', 'pending')->where('buyer_seen', false)->count(),
            'processing' => Transaction::where('buyer_id', $userId)->whereIn('status', ['processing', 'packed'])->where('buyer_seen', false)->count(),
            'shipped' => Transaction::where('buyer_id', $userId)->where('status', 'shipped')->where('buyer_seen', false)->count(),
            'received' => Transaction::where('buyer_id', $userId)->whereIn('status', ['received', 'completed'])->where('buyer_seen', false)->count(),
            'cancelled' => Transaction::where('buyer_id', $userId)->where('status', 'cancelled')->where('user_hidden', false)->where('buyer_seen', false)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
            'counts' => $counts
        ]);
    }

    /**
     * Get single transaction detail
     */
    public function show(Request $request, $id)
    {
        $transaction = Transaction::where('buyer_id', $request->user()->id)
            ->with(['items.product', 'seller', 'review'])
            ->findOrFail($id);

        // Mark as seen when viewed
        $transaction->update(['buyer_seen' => true]);

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    /**
     * Store transaction directly from single product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'user_address_id' => 'required|exists:user_addresses,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'voucher_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
            $address = \App\Models\UserAddress::findOrFail($request->user_address_id);

            if ($product->stock < $request->quantity) {
                return response()->json(['status' => 'error', 'message' => 'Stok tidak mencukupi.'], 400);
            }

            $totalPrice = $product->effective_price * $request->quantity;
            $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
            $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
            $serviceFee = ceil($totalPrice * $serviceFeePercent / 100);
            $discount = 0;
            $appliedVoucher = null;

            if ($request->filled('voucher_code')) {
                $voucher = Voucher::where('code', strtoupper($request->voucher_code))->where('is_active', true)->first();
                if ($voucher && $voucher->isValidFor($totalPrice, $userId)) {
                    $discount = $voucher->calculateDiscount($totalPrice);
                    $appliedVoucher = $voucher;
                    $voucher->increment('usage_count');
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Voucher tidak valid atau syarat tidak terpenuhi.'], 400);
                }
            }

            $adminFee = 0;
            if ($paymentMethod) {
                if ($paymentMethod->admin_fee_percent > 0) {
                    $adminFee += ceil($totalPrice * $paymentMethod->admin_fee_percent / 100);
                }
                if ($paymentMethod->admin_fee > 0) {
                    $adminFee += $paymentMethod->admin_fee;
                }
            }

            $grandTotal = max(0, $totalPrice + $serviceFee + $adminFee - $discount);

            $transaction = Transaction::create([
                'buyer_id' => $userId,
                'seller_id' => $product->user_id,
                'total_amount' => $grandTotal,
                'seller_amount' => $totalPrice,
                'service_fee' => $serviceFee,
                'admin_fee' => $adminFee,
                'status' => 'waiting_payment',
                'payment_method' => $paymentMethod->name,
                'payment_method_code' => $paymentMethod->code,
                'shipping_address' => $address->full_address,
                'shipping_address_id' => $request->user_address_id,
                'voucher_code' => $appliedVoucher ? $appliedVoucher->code : null,
                'discount_total' => $discount
            ]);

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->effective_price
            ]);

            $product->decrement('stock', $request->quantity);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store transaction from cart
     */
    public function storeCart(Request $request)
    {
        \Log::info("Checkout Request:", $request->all());

        $validator = Validator::make($request->all(), [
            'user_address_id' => 'required|exists:user_addresses,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'cart_ids' => 'nullable|array',
            'cart_ids.*' => 'exists:carts,id',
            'voucher_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            \Log::error("StoreCart Validation Failed:", $validator->errors()->toArray());
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;

        $query = Cart::where('user_id', $userId)->with('product');
        if ($request->has('cart_ids') && is_array($request->cart_ids) && count($request->cart_ids) > 0) {
            $query->whereIn('id', $request->cart_ids);
        }

        $carts = $query->get();

        if ($carts->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada item keranjang yang dipilih.'], 400);
        }

        try {
            DB::beginTransaction();

            $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
            $address = \App\Models\UserAddress::findOrFail($request->user_address_id);

            $totalPrice = 0;
            foreach ($carts as $cart) {
                if ($cart->product->stock < $cart->quantity) {
                    throw new \Exception("Stok {$cart->product->name} tidak cukup.");
                }
                $totalPrice += $cart->product->effective_price * $cart->quantity;
            }

            $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
            $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
            $serviceFee = ceil($totalPrice * $serviceFeePercent / 100);
            $discount = 0;
            $appliedVoucher = null;

            if ($request->filled('voucher_code')) {
                $voucher = Voucher::where('code', strtoupper($request->voucher_code))->where('is_active', true)->first();
                if ($voucher && $voucher->isValidFor($totalPrice, $userId)) {
                    $discount = $voucher->calculateDiscount($totalPrice);
                    $appliedVoucher = $voucher;
                    $voucher->increment('usage_count');
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Voucher tidak valid atau syarat tidak terpenuhi.'], 400);
                }
            }

            $adminFee = 0;
            if ($paymentMethod) {
                if ($paymentMethod->admin_fee_percent > 0) {
                    $adminFee += ceil($totalPrice * $paymentMethod->admin_fee_percent / 100);
                }
                if ($paymentMethod->admin_fee > 0) {
                    $adminFee += $paymentMethod->admin_fee;
                }
            }

            $grandTotal = max(0, $totalPrice + $serviceFee + $adminFee - $discount);

            $transaction = Transaction::create([
                'buyer_id' => $userId,
                // Taking seller_id from the first item for simplicity in mixed carts, or ideally looping.
                'seller_id' => $carts->first()->product->user_id ?? 1,
                'total_amount' => $grandTotal,
                'seller_amount' => $totalPrice,
                'service_fee' => $serviceFee,
                'admin_fee' => $adminFee,
                'status' => 'waiting_payment',
                'payment_method' => $paymentMethod->name,
                'payment_method_code' => $paymentMethod->code,
                'shipping_address' => $address->full_address,
                'shipping_address_id' => $request->user_address_id,
                'voucher_code' => $appliedVoucher ? $appliedVoucher->code : null,
                'discount_total' => $discount
            ]);

            foreach ($carts as $cart) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->product->effective_price
                ]);

                $cart->product->decrement('stock', $cart->quantity);
                Cart::destroy($cart->id); // Delete individual checked out cart
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi (dari keranjang) berhasil dibuat',
                'data' => $transaction->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Preview calculation before final checkout
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'cart_ids' => 'nullable|array',
            'cart_ids.*' => 'exists:carts,id',
            'voucher_code' => 'nullable|string',
            'delivery_type' => 'required|in:pickup,courier',
            'user_address_id' => 'nullable|exists:user_addresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $totalPrice = 0;
        $items = [];
        $sellerId = null;

        if ($request->filled('product_id')) {
            $product = Product::findOrFail($request->product_id);
            if ($product->stock < $request->quantity) {
                return response()->json(['status' => 'error', 'message' => 'Stok tidak mencukupi.'], 400);
            }
            $totalPrice = $product->effective_price * $request->quantity;
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $request->quantity,
                'price' => $product->effective_price,
                'product' => $product
            ];
            $sellerId = $product->user_id;
        } else if ($request->filled('cart_ids')) {
            $carts = Cart::where('user_id', $userId)->whereIn('id', $request->cart_ids)->with('product')->get();
            if ($carts->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Cart kosong.'], 400);
            }
            foreach ($carts as $cart) {
                if ($cart->product->stock < $cart->quantity) {
                    return response()->json(['status' => 'error', 'message' => "Stok {$cart->product->name} tidak cukup."], 400);
                }
                $totalPrice += $cart->product->effective_price * $cart->quantity;
                $items[] = [
                    'product_id' => $cart->product->id,
                    'name' => $cart->product->name,
                    'quantity' => $cart->quantity,
                    'price' => $cart->product->effective_price,
                    'product' => $cart->product
                ];
                $sellerId = $cart->product->user_id; // assuming single seller checkout
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Pilih produk atau keranjang.'], 400);
        }

        $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
        $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
        $serviceFee = ceil($totalPrice * $serviceFeePercent / 100);

        $shippingCost = 0;
        $distanceKm = null;
        if ($request->delivery_type === 'courier') {
            $buyerAddress = $request->filled('user_address_id') ? \App\Models\UserAddress::find($request->user_address_id) : null;
            $seller = \App\Models\User::find($sellerId);
            $sellerAddress = $seller ? $seller->addresses()->where('is_default', true)->first() : null;

            // Seller coordinates: prefer seller's default address, fallback to product coordinates
            $sLat = $sellerAddress && $sellerAddress->latitude ? $sellerAddress->latitude : ($items[0]['product']->latitude ?? 0);
            $sLon = $sellerAddress && $sellerAddress->longitude ? $sellerAddress->longitude : ($items[0]['product']->longitude ?? 0);

            // Buyer coordinates: prefer saved address, fallback to device GPS from request
            $bLat = ($buyerAddress && $buyerAddress->latitude) ? $buyerAddress->latitude : ($request->input('buyer_latitude', 0));
            $bLon = ($buyerAddress && $buyerAddress->longitude) ? $buyerAddress->longitude : ($request->input('buyer_longitude', 0));

            if ($sLat && $sLon && $bLat && $bLon) {
                $distanceKm = $this->calculateDistance($sLat, $sLon, $bLat, $bLon);

                $totalWeight = array_reduce($items, function ($carry, $item) {
                    return $carry + ($item['product']->weight * $item['quantity']);
                }, 0);

                $shippingCost = $this->calculateShippingCost($distanceKm, $totalWeight);
            } else {
                $shippingCost = 15000; // Flat fallback only if no coordinates at all
            }
        }
        $discount = 0;
        $appliedVoucher = null;

        // Extract category IDs from items
        $itemCategoryIds = collect($items)->pluck('product.category_id')->unique()->toArray();

        if ($request->filled('voucher_code')) {
            $voucher = Voucher::where('code', strtoupper($request->voucher_code))->where('is_active', true)->first();
            if ($voucher && $voucher->isValidFor($totalPrice, $userId, $itemCategoryIds)) {
                $discount = $voucher->calculateDiscount($totalPrice);
                $appliedVoucher = $voucher;
            }
        }

        $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
        $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
        $serviceFee = ceil($totalPrice * $serviceFeePercent / 100);
        
        $adminFee = 0;
        // Not passing payment_method for preview usually, but if provided, calculate it. 
        if ($request->filled('payment_method_id')) {
            $pm = PaymentMethod::find($request->payment_method_id);
            if ($pm) {
                 if ($pm->admin_fee_percent > 0) {
                     $adminFee += ceil($totalPrice * $pm->admin_fee_percent / 100);
                 }
                 if ($pm->admin_fee > 0) {
                     $adminFee += $pm->admin_fee;
                 }
            }
        }

        $grandTotal = max(0, $totalPrice + $serviceFee + $shippingCost + $adminFee - $discount);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $items,
                'subtotal_product' => $totalPrice,
                'service_fee' => $serviceFee,
                'shipping_cost' => $shippingCost,
                'admin_fee' => $adminFee,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'seller_id' => $sellerId,
                'voucher' => $appliedVoucher,
                'distance_km' => $distanceKm ? round($distanceKm, 2) : null,
            ]
        ]);
    }

    /**
     * Finalize and create transaction
     */
    public function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'cart_ids' => 'nullable|array',
            'user_address_id' => 'required|exists:user_addresses,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'voucher_code' => 'nullable|string',
            'delivery_type' => 'required|in:pickup,courier',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;

        try {
            DB::beginTransaction();

            $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
            $address = \App\Models\UserAddress::findOrFail($request->user_address_id);

            $totalPrice = 0;
            $itemsToCheckout = [];
            $sellerId = null;

            if ($request->filled('product_id')) {
                $product = Product::findOrFail($request->product_id);
                if ($product->stock < $request->quantity) {
                    throw new \Exception('Stok tidak mencukupi.');
                }
                $totalPrice = $product->effective_price * $request->quantity;
                $sellerId = $product->user_id;
                $itemsToCheckout[] = ['product' => $product, 'quantity' => $request->quantity];
            } else if ($request->filled('cart_ids')) {
                $carts = Cart::where('user_id', $userId)->whereIn('id', $request->cart_ids)->with('product')->get();
                foreach ($carts as $cart) {
                    if ($cart->product->stock < $cart->quantity) {
                        throw new \Exception("Stok {$cart->product->name} tidak cukup.");
                    }
                    $totalPrice += $cart->product->effective_price * $cart->quantity;
                    $sellerId = $cart->product->user_id;
                    $itemsToCheckout[] = ['product' => $cart->product, 'quantity' => $cart->quantity, 'cart_id' => $cart->id];
                }
            } else {
                throw new \Exception('Pilih produk atau keranjang.');
            }

            $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
            $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
            $serviceFee = ceil($totalPrice * $serviceFeePercent / 100);

            $shippingCost = 0;
            if ($request->delivery_type === 'courier') {
                $seller = \App\Models\User::find($sellerId);
                $sellerAddress = $seller ? $seller->addresses()->where('is_default', true)->first() : null;
                $sLat = $sellerAddress && $sellerAddress->latitude ? $sellerAddress->latitude : ($itemsToCheckout[0]['product']->latitude ?? 0);
                $sLon = $sellerAddress && $sellerAddress->longitude ? $sellerAddress->longitude : ($itemsToCheckout[0]['product']->longitude ?? 0);

                // Buyer coordinates: prefer saved address, fallback to device GPS
                $bLat = $address->latitude ? $address->latitude : ($request->input('buyer_latitude', 0));
                $bLon = $address->longitude ? $address->longitude : ($request->input('buyer_longitude', 0));

                if ($sLat && $sLon && $bLat && $bLon) {
                    $distance = $this->calculateDistance($sLat, $sLon, $bLat, $bLon);

                    $totalWeight = array_reduce($itemsToCheckout, function ($carry, $item) {
                        return $carry + ($item['product']->weight * $item['quantity']);
                    }, 0);

                    $shippingCost = $this->calculateShippingCost($distance, $totalWeight);
                } else {
                    $shippingCost = 15000;
                }
            }
            $discount = 0;
            $appliedVoucher = null;

            // Extract category IDs for validation in confirmation
            $itemCategoryIds = collect($itemsToCheckout)->pluck('product.category_id')->unique()->toArray();

            if ($request->filled('voucher_code')) {
                $voucher = Voucher::where('code', strtoupper($request->voucher_code))->where('is_active', true)->first();
                if ($voucher && $voucher->isValidFor($totalPrice, $userId, $itemCategoryIds)) {
                    $discount = $voucher->calculateDiscount($totalPrice);
                    $appliedVoucher = $voucher;
                    $voucher->increment('usage_count');
                }
            }

            $adminFee = 0;
            if ($paymentMethod) {
                if ($paymentMethod->admin_fee_percent > 0) {
                     $adminFee += ceil($totalPrice * $paymentMethod->admin_fee_percent / 100);
                }
                if ($paymentMethod->admin_fee > 0) {
                     $adminFee += $paymentMethod->admin_fee;
                }
            }

            $grandTotal = max(0, $totalPrice + $serviceFee + $shippingCost + $adminFee - $discount);

            $transaction = Transaction::create([
                'buyer_id' => $userId,
                'seller_id' => $sellerId,
                'total_amount' => $grandTotal,
                'seller_amount' => $totalPrice, // Without fee & shipping
                'service_fee' => $serviceFee,
                'shipping_cost' => $shippingCost,
                'admin_fee' => $adminFee,
                'delivery_type' => $request->delivery_type,
                'status' => 'waiting_payment',
                'expires_at' => now()->addHours(24),
                'payment_method' => $paymentMethod->name,
                'payment_method_code' => $paymentMethod->code,
                'shipping_address' => $address->full_address,
                'shipping_address_id' => $request->user_address_id,
                'voucher_code' => $appliedVoucher ? $appliedVoucher->code : null,
                'discount_total' => $discount
            ]);

            \App\Models\TransactionStatusLog::create([
                'transaction_id' => $transaction->id,
                'status' => 'waiting_payment',
                'note' => 'Transaksi dibuat, menunggu pembayaran',
                'changed_by' => $userId
            ]);

            foreach ($itemsToCheckout as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['product']->effective_price
                ]);
                $item['product']->decrement('stock', $item['quantity']);

                if (isset($item['cart_id'])) {
                    Cart::destroy($item['cart_id']);
                }
            }


            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat, silakan lakukan pembayaran.',
                'data' => $transaction->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload payment proof
     */
    public function uploadProof(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $transaction = Transaction::where('buyer_id', $request->user()->id)
                ->findOrFail($id);

            if (!in_array($transaction->status, ['waiting_payment', 'pending', 'payment_rejected'])) {
                return response()->json(['status' => 'error', 'message' => 'Bukti bayar hanya bisa diunggah jika status masih menunggu pembayaran, tertunda, atau ditolak.'], 400);
            }

            $path = $request->file('proof_of_payment')->store('images/proofs', 'public');

            // Append to existing proofs (support multiple uploads)
            $raw = $transaction->payment_proof;
            $existingProofs = [];
            if (!empty($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $existingProofs = $decoded;
                } else {
                    // Old format: plain string path
                    $existingProofs = [$raw];
                }
            }
            $existingProofs[] = $path;

            $transaction->update([
                'payment_proof' => json_encode($existingProofs),
                'status' => 'pending' // Waiting for admin verification
            ]);

            \App\Models\TransactionStatusLog::create([
                'transaction_id' => $transaction->id,
                'status' => 'pending',
                'note' => 'Bukti pembayaran diunggah oleh pembeli, menunggu verifikasi admin',
                'changed_by' => $request->user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bukti pembayaran berhasil diunggah',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            \Log::error("UploadProof Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * Seller ships order with tracking number
     */
    public function shipOrder(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->seller_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($transaction->status !== 'paid_verified' && $transaction->status !== 'packed') {
            return response()->json(['status' => 'error', 'message' => 'Transaksi tidak dalam status siap kirim.'], 400);
        }

        $request->validate([
            'courier' => 'required|string|max:50',
            'tracking_number' => 'required|string|max:100',
            'shipping_proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('shipping_proof')) {
            $proofPath = $request->file('shipping_proof')->store('images/proofs', 'public');
        }

        $transaction->update([
            'courier' => $request->courier,
            'tracking_number' => $request->tracking_number,
            'shipping_proof' => $proofPath,
            'shipped_at' => now(),
            'status' => 'shipped',
        ]);

        \App\Models\TransactionStatusLog::create([
            'transaction_id' => $transaction->id,
            'status' => 'shipped',
            'note' => 'Pesanan dikirim via ' . $request->courier . '. Resi: ' . $request->tracking_number,
            'changed_by' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengiriman dikonfirmasi! Resi: ' . $request->tracking_number,
            'data' => $transaction
        ]);
    }

    /**
     * Seller updates order status (processing, packaging, ready_to_ship)
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->seller_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // PREVENT SELLER FROM BYPASSING ADMIN VERIFICATION
        if ($request->user()->role !== 'admin' && in_array($transaction->status, ['waiting_payment', 'pending'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat mengubah status pesanan ini sampai pembayaran diverifikasi oleh Admin.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:processing,packed,ready_to_ship,payment_rejected,waiting_payment',
            'note' => 'nullable|string'
        ]);

        $statusTitles = [
            'processing' => 'Pesanan Diproses',
            'packed' => 'Sedang Dikemas / Siap Kirim',
            'payment_rejected' => 'Pembayaran Ditolak',
            'waiting_payment' => 'Menunggu Pembayaran'
        ];

        $note = $request->note ?: ($statusTitles[$request->status] ?? 'Diperbarui');
        $transaction->update(['status' => $request->status, 'seller_notes' => $note]);

        \App\Models\TransactionStatusLog::create([
            'transaction_id' => $transaction->id,
            'status' => $request->status,
            'note' => 'Seller mengupdate status ke: ' . $statusTitles[$request->status],
            'changed_by' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status diperbarui: ' . $statusTitles[$request->status],
        ]);
    }

    /**
     * Buyer confirms receipt with multiple photos/videos
     */
    public function confirmReceived(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->buyer_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($transaction->status !== 'shipped') {
            return response()->json(['status' => 'error', 'message' => 'Transaksi tidak dalam status dikirim.'], 400);
        }

        // Validate multimedia files
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480', // Max 20MB per file
        ]);

        if ($validator->fails()) {
            \Log::error("ConfirmReceived Validation Failed:", $validator->errors()->toArray());
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $index => $file) {
                    \Log::error("File $index Error:", [
                        'name' => $file->getClientOriginalName(),
                        'error' => $file->getError(),
                        'size' => $file->getSize(),
                        'valid' => $file->isValid(),
                    ]);
                }
            } else {
                \Log::error("No files found in request OR files invalid in PHP (exceeds post_max_size?). All data:", $request->all());
            }
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('receipt_proofs', 'public');
                $filePaths[] = $path;
            }
        }

        $transaction->update([
            'status' => 'received',
            'receipt_photos' => $filePaths,
            'receipt_confirmed_at' => now(),
        ]);


        \App\Models\TransactionStatusLog::create([
            'transaction_id' => $transaction->id,
            'status' => 'received',
            'note' => 'Pesanan diterima dengan bukti multimedia oleh pembeli',
            'changed_by' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Barang diterima! Terima kasih.',
            'data' => $transaction
        ]);
    }

    /**
     * Remove (hide) cancelled transaction from user view
     */
    public function destroy(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('buyer_id', $request->user()->id)
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
     * Cancel order
     */
    public function cancel(Request $request, $id)
    {
        $transaction = Transaction::with('items.product')->findOrFail($id);

        if ($transaction->buyer_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($transaction->status, ['pending', 'waiting_payment', 'payment_rejected'])) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak dapat dibatalkan (sudah dikonfirmasi admin atau dalam proses).'], 400);
        }

        // Restore stock
        foreach ($transaction->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $transaction->update(['status' => 'cancelled']);

        \App\Models\TransactionStatusLog::create([
            'transaction_id' => $transaction->id,
            'status' => 'cancelled',
            'note' => 'Pesanan dibatalkan oleh pembeli sebelum konfirmasi admin',
            'changed_by' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil dibatalkan. Stok dikembalikan.',
        ]);
    }

    /**
     * Mark transactions of a specific status as seen for buyer
     */
    public function markAsSeen(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->status;

        $query = Transaction::where('buyer_id', $userId);

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

        $query->update(['buyer_seen' => true]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Calculate shipping cost using realistic tiered pricing (Indonesian courier style)
     * Base cost by distance + per-kg rate that scales with distance
     * 
     * @param float $distanceKm Distance in kilometers
     * @param int $totalWeightGram Total weight in grams
     * @return int Shipping cost in Rupiah
     */
    private function calculateShippingCost($distanceKm, $totalWeightGram)
    {
        // Distance tiers: [baseCost, perKgRate]
        if ($distanceKm <= 5) {
            $baseCost = 8000;       // Sangat dekat (sekitar kampus)
            $perKgRate = 2000;
        } elseif ($distanceKm <= 15) {
            $baseCost = 12000;      // Dalam kota
            $perKgRate = 2500;
        } elseif ($distanceKm <= 50) {
            $baseCost = 18000;      // Antar kecamatan
            $perKgRate = 3000;
        } elseif ($distanceKm <= 150) {
            $baseCost = 25000;      // Antar kota dalam provinsi
            $perKgRate = 4000;
        } elseif ($distanceKm <= 500) {
            $baseCost = 35000;      // Antar provinsi dekat
            $perKgRate = 6000;
        } elseif ($distanceKm <= 1000) {
            $baseCost = 50000;      // Antar provinsi jauh
            $perKgRate = 8000;
        } else {
            $baseCost = 65000;      // Antar pulau (mis. Medan - Jakarta)
            $perKgRate = 10000;
        }

        // Hitung berat (minimal 1 kg). Base cost sudah termasuk 1 kg pertama.
        $weightKg = max(1, ceil($totalWeightGram / 1000));
        $weightSurcharge = ($weightKg > 1) ? ($weightKg - 1) * $perKgRate : 0;

        return $baseCost + $weightSurcharge;
    }
}
