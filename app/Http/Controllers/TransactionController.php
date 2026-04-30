<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Step 1: Review Cart & Input Address
    public function checkoutCart(Request $request)
    {
        // Handle both POST (from Cart form) and GET (from Direct Buy with query params)
        // Cart form sends 'items' array. Direct Buy might send 'selected_items' query.
        $selectedIds = $request->input('items', $request->query('selected_items', []));

        if (is_string($selectedIds)) {
            $selectedIds = explode(',', $selectedIds);
        }

        if (empty($selectedIds)) {
            return redirect()->route('cart.index')->with('error', 'Pilih minimal satu barang untuk checkout.');
        }

        $cartItems = Cart::whereIn('id', $selectedIds)
            ->where('user_id', auth()->id())
            ->with('product.user')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        // Group by Seller for UI display
        $groupedItems = $cartItems->groupBy('product.user.shop_name');

        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->effective_price;
        });


        // --- VOUCHER CALCULATION ---
        $discountAmount = 0;
        $appliedVoucher = session('applied_voucher');
        $voucherError = null;

        if ($appliedVoucher) {
            $voucher = \App\Models\Voucher::where('code', $appliedVoucher['code'])
                ->where('is_active', true)
                ->first();

            if ($voucher && $voucher->isValidFor($subtotal)) {
                $discountAmount = $voucher->calculateDiscount($subtotal);
            } else {
                // Invalid voucher (expired or min purchase not met), remove from session
                session()->forget('applied_voucher');
                $appliedVoucher = null;
                $voucherError = 'Voucher tidak lagi valid untuk transaksi ini.';
            }
        }

        // Calculate service fee dynamically from settings (default 10%)
        $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
        $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
        // Discount is applied BEFORE service fee in this logic? 
        // Usually Service Fee is on the FINAL amount or Subtotal?
        // Let's keep Service Fee on Subtotal (Platform fee based on GMV).
        // Buyer pays: (Subtotal - Discount) + ServiceFee.

        $serviceFee = ceil($subtotal * $serviceFeePercent / 100);
        $totalPrice = ($subtotal - $discountAmount) + $serviceFee;
        if ($totalPrice < 0)
            $totalPrice = 0;

        // Note: Admin Payment Fee will be calculated dynamically on the frontend via JavaScript
        // and finalized in the backend during storeCart.

        // Load saved addresses
        $addresses = auth()->user()->addresses()->latest()->get();
        $defaultAddress = auth()->user()->defaultAddress;

        // Load active payment methods
        $paymentMethods = \App\Models\PaymentMethod::active()->get()->groupBy('type');

        // Pass the IDs to the view to be submitted in the next step
        $selectedItemString = implode(',', $selectedIds);

        return view('transactions.checkout', compact(
            'cartItems',
            'groupedItems',
            'subtotal',
            'serviceFee',
            'serviceFeePercent',
            'totalPrice',
            'selectedItemString',
            'addresses',
            'defaultAddress',
            'paymentMethods',
            'appliedVoucher',
            'discountAmount',
            'voucherError'
        ));
    }

    public function applyVoucher(Request $request)
    {
        try {
            $request->validate(['code' => 'required|string']);

            $voucher = \App\Models\Voucher::where('code', $request->code)->where('is_active', true)->first();

            if (!$voucher) {
                return response()->json(['success' => false, 'message' => 'Kode voucher tidak ditemukan atau tidak aktif.']);
            }

            // Calculate current cart subtotal from Cart (database)
            $cartItems = \App\Models\Cart::where('user_id', auth()->id())->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Keranjang kosong.']);
            }

            $subtotal = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            // Extract category IDs from cart items
            $itemCategoryIds = $cartItems->pluck('product.category_id')->unique()->toArray();

            // Validate minimum purchase
            if ($subtotal < $voucher->min_purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belanja minimum untuk voucher ini adalah Rp ' . number_format($voucher->min_purchase, 0, ',', '.') . '. Subtotal Anda: Rp ' . number_format($subtotal, 0, ',', '.')
                ]);
            }

            if (!$voucher->isValidFor($subtotal, auth()->id(), $itemCategoryIds)) {
                $message = 'Voucher tidak valid untuk Anda atau sudah melebihi batas penggunaan.';
                if ($voucher->category_id && !in_array($voucher->category_id, $itemCategoryIds)) {
                    $categoryName = $voucher->category ? $voucher->category->name : 'kategori tertentu';
                    $message = 'Voucher ini hanya berlaku untuk produk dalam kategori ' . $categoryName . '.';
                }
                return response()->json(['success' => false, 'message' => $message]);
            }

            $discountAmount = $voucher->calculateDiscount($subtotal);

            session(['applied_voucher' => [
                'code' => $voucher->code,
                'discount' => $discountAmount,
                'terms' => $voucher->terms
            ]]);

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil digunakan!',
                'discount' => $discountAmount,
                'terms' => $voucher->terms
            ]);
        } catch (\Exception $e) {
            \Log::error('Voucher application error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function removeVoucher()
    {
        session()->forget('applied_voucher');
        return response()->json(['success' => true, 'message' => 'Voucher dihapus.']);
    }


    // Step 2: Process Checkout
    public function storeCart(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        // Get address - either from saved addresses or manual input
        $shippingAddress = $request->address;
        $addressId = null;

        if ($request->filled('address_id')) {
            $address = \App\Models\UserAddress::where('id', $request->address_id)
                ->where('user_id', auth()->id())
                ->first();
            if ($address) {
                $shippingAddress = $address->formatted_address;
                $addressId = $address->id;
            }
        }

        if (empty($shippingAddress)) {
            return back()->with('error', 'Alamat pengiriman harus diisi.');
        }

        $selectedIds = explode(',', $request->selected_items);
        $cartItems = Cart::whereIn('id', $selectedIds)
            ->where('user_id', auth()->id())
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Item tidak valid.');
        }

        // Start Database Transaction
        DB::beginTransaction();
        try {
            // --- Voucher Logic ---
            $voucher = null;
            $totalDiscount = 0;
            $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->product->effective_price);

            if ($request->filled('voucher_code')) {
                $voucher = \App\Models\Voucher::where('code', $request->voucher_code)
                    ->where('is_active', true)
                    ->first();

                if (!$voucher) {
                    return back()->with('voucher_error', 'Kode voucher tidak valid.');
                }

                $itemCategoryIds = $cartItems->pluck('product.category_id')->unique()->toArray();
                if (!$voucher->isValidFor($subtotal, auth()->id(), $itemCategoryIds)) {
                    $message = 'Voucher tidak dapat digunakan.';
                    if ($voucher->category_id && !in_array($voucher->category_id, $itemCategoryIds)) {
                        $categoryName = $voucher->category ? $voucher->category->name : 'kategori tertentu';
                        $message = 'Voucher ini hanya berlaku untuk kategori ' . $categoryName . '.';
                    }
                    return back()->with('voucher_error', $message);
                }

                $totalDiscount = $voucher->calculateDiscount($subtotal);
            }

            // Calculate service fee dynamically
            $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
            $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
            $serviceFee = ceil($subtotal * $serviceFeePercent / 100);

            // Group items by Seller ID to create separate transactions
            $itemsBySeller = $cartItems->groupBy('product.user_id');

            foreach ($itemsBySeller as $sellerId => $items) {
                // Calculate Proportional Discount for this seller
                $sellerSubtotal = $items->sum(fn($item) => $item->quantity * $item->product->effective_price);
                $sellerDiscount = 0;
                if ($totalDiscount > 0) {
                    $sellerDiscount = floor(($sellerSubtotal / $subtotal) * $totalDiscount);
                }

                // Proportional service fee
                $sellerServiceFee = ceil(($sellerSubtotal / $subtotal) * $serviceFee);

                // --- Admin Payment Fee (Gateway) Logic ---
                $paymentMethodObj = \App\Models\PaymentMethod::where('code', $request->payment_method)->first();
                $totalAdminFee = 0;
                if ($paymentMethodObj) {
                    if ($paymentMethodObj->admin_fee_percent > 0) {
                        $totalAdminFee += ceil($subtotal * $paymentMethodObj->admin_fee_percent / 100);
                    }
                    if ($paymentMethodObj->admin_fee > 0) {
                        $totalAdminFee += $paymentMethodObj->admin_fee;
                    }
                }
                $sellerAdminFee = ceil(($sellerSubtotal / $subtotal) * $totalAdminFee);

                // --- Shipping Cost Logic ---
                $sellerShippingCost = 0;
                $deliveryType = $request->delivery_type ?? 'courier'; // Default to courier for web if not specified

                if ($deliveryType === 'courier') {
                    $sellerUser = \App\Models\User::find($sellerId);
                    $sellerAddress = $sellerUser ? $sellerUser->addresses()->where('is_primary', true)->first() : null;
                    $buyerAddress = \App\Models\UserAddress::find($addressId);

                    if ($sellerAddress && $buyerAddress && $sellerAddress->latitude && $sellerAddress->longitude && $buyerAddress->latitude && $buyerAddress->longitude) {
                        $distance = $this->calculateDistance($sellerAddress->latitude, $sellerAddress->longitude, $buyerAddress->latitude, $buyerAddress->longitude);

                        $baseShipping = 10000;
                        if ($distance > 5) {
                            $baseShipping += (ceil($distance - 5) * 3000);
                        }

                        $multiplier = 1;
                        $totalWeightGrams = $items->sum(fn($item) => $item->quantity * $item->product->weight);
                        $totalWeightKg = ceil($totalWeightGrams / 1000);
                        if ($totalWeightKg > 25) {
                            $multiplier = ceil($totalWeightKg / 25);
                        }
                        $sellerShippingCost = $baseShipping * $multiplier;
                    } else {
                        $sellerShippingCost = 15000; // Fallback
                    }
                }

                // Seller amount (what seller will receive after service fee)
                $sellerAmount = $sellerSubtotal - $sellerDiscount;

                // Total buyer pays (subtotal - discount + service fee + shipping + admin fee)
                $totalAmount = $sellerAmount + $sellerServiceFee + $sellerShippingCost + $sellerAdminFee;

                // 1. Create Transaction Record
                $transaction = Transaction::create([
                    'buyer_id' => auth()->id(),
                    'seller_id' => $sellerId,
                    'shipping_address' => $shippingAddress,
                    'shipping_address_id' => $addressId,
                    'payment_method' => $request->payment_method,
                    'payment_method_code' => $request->payment_method,
                    'message' => $request->message,
                    'total_amount' => $totalAmount,
                    'service_fee' => $sellerServiceFee,
                    'shipping_cost' => $sellerShippingCost,
                    'delivery_type' => $deliveryType,
                    'seller_amount' => $sellerAmount,
                    'admin_fee' => $sellerAdminFee,
                    'status' => 'waiting_payment',
                ]);

                // 2. Add to seller's pending balance (escrow)
                $sellerBalance = \App\Models\SellerBalance::getOrCreate($sellerId);
                $sellerBalance->addPending($sellerAmount);

                // 3. Create Details & Deduct Stock
                foreach ($items as $item) {
                    // Check Stock
                    if ($item->product->stock < $item->quantity) {
                        throw new \Exception("Stok untuk {$item->product->name} tidak mencukupi.");
                    }

                    $effectivePrice = $item->product->effective_price;
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $effectivePrice,
                    ]);

                    $item->product->decrement('stock', $item->quantity);
                }

                // 4. Add tracking log
                \App\Models\OrderTrackingLog::addLog(
                    $transaction->id,
                    'order_created',
                    null,
                    'Pesanan dibuat oleh ' . auth()->user()->name,
                    'buyer',
                    auth()->id()
                );
            }

            // 4. Update Voucher Usage
            if ($voucher) {
                $voucher->increment('usage_count');
            }

            // 5. Clear Processed Cart Items
            Cart::whereIn('id', $selectedIds)->delete();

            DB::commit();

            return redirect()->route('transactions.history')->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function history()
    {
        // Fetch transactions where user is buyer
        $purchases = Transaction::where('buyer_id', auth()->id())
            ->with(['items.product', 'seller'])
            ->latest()
            ->get();

        // Fetch transactions where user is seller
        $sales = Transaction::where('seller_id', auth()->id())
            ->with(['items.product', 'buyer'])
            ->latest()
            ->get();

        return view('transactions.history', compact('purchases', 'sales'));
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->buyer_id !== auth()->id() && $transaction->seller_id !== auth()->id()) {
            abort(403);
        }
        return view('transactions.show', compact('transaction'));
    }

    public function uploadProof(Request $request, Transaction $transaction)
    {
        if ($transaction->buyer_id !== auth()->id())
            abort(403);

        $request->validate(['proof' => 'required|image|max:2048']);
        $path = $request->file('proof')->store('proofs', 'public');

        $transaction->update([
            'payment_proof' => $path,
            'status' => 'pending' // Pending verification by admin
        ]);

        // Add tracking log
        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'payment_uploaded',
            null,
            null,
            'buyer',
            auth()->id()
        );

        return back()->with('success', 'Bukti pembayaran diunggah. Menunggu verifikasi Admin.');
    }

    // Single item checkout direct (optional, redirects to cart-like flow or handles directly)
    public function checkout(Product $product)
    {
        // For simplicity in this new flow, we can just add to cart and redirect to checkout
        // Or create a temporary array structure to reuse the checkout view.
        // Let's force Add to Cart for consistency for now, or handle it properly.

        // Simulating "Add and Select"
        $cart = Cart::updateOrCreate(
            ['user_id' => auth()->id(), 'product_id' => $product->id],
            ['quantity' => DB::raw('quantity + 0')] // Don't increment if exists, just select
        );

        // Redirect to checkout with this item selected
        return redirect()->route('checkout.cart', ['selected_items' => [$cart->id]]);
    }

    // Seller updates order status (processing, packaging, ready_to_ship)
    public function updateOrderStatus(Request $request, Transaction $transaction)
    {
        if ($transaction->seller_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:processing,packaging,ready_to_ship',
        ]);

        $newStatus = $request->status;
        $statusTitles = [
            'processing' => 'Pesanan Diproses',
            'packaging' => 'Sedang Dikemas',
            'ready_to_ship' => 'Siap Dikirim',
        ];

        // Add tracking log
        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            $newStatus,
            $statusTitles[$newStatus],
            $request->note ?? null,
            'seller',
            auth()->id()
        );

        return back()->with('success', 'Status diperbarui: ' . $statusTitles[$newStatus]);
    }

    // Seller ships order with tracking number
    public function shipOrder(Request $request, Transaction $transaction)
    {
        if ($transaction->seller_id !== auth()->id()) {
            abort(403);
        }

        if ($transaction->status !== 'paid_verified') {
            return back()->with('error', 'Transaksi tidak dalam status siap kirim.');
        }

        $request->validate([
            'courier' => 'required|string|max:50',
            'tracking_number' => 'required|string|max:100',
        ]);

        $courierName = strtoupper($request->courier);

        $transaction->update([
            'courier' => $request->courier,
            'tracking_number' => $request->tracking_number,
            'shipped_at' => now(),
            'status' => 'shipped',
        ]);

        // Add tracking log for handed to courier
        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'handed_to_courier',
            'Diserahkan ke ' . $courierName,
            'No. Resi: ' . $request->tracking_number,
            'seller',
            auth()->id()
        );

        // Add in_transit log
        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'in_transit',
            'Dalam Pengiriman via ' . $courierName,
            'Lacak pengiriman dengan nomor resi: ' . $request->tracking_number,
            'system',
            null
        );

        return back()->with('success', 'Pengiriman dikonfirmasi! Nomor resi: ' . $request->tracking_number);
    }

    // Buyer confirms receipt with photos
    public function confirmReceived(Request $request, Transaction $transaction)
    {
        if ($transaction->buyer_id !== auth()->id()) {
            abort(403);
        }

        if ($transaction->status !== 'shipped') {
            return back()->with('error', 'Transaksi tidak dalam status dikirim.');
        }

        $request->validate([
            'receipt_photos' => 'required|array|min:1|max:5',
            'receipt_photos.*' => 'image|max:2048',
        ], [
            'receipt_photos.required' => 'Foto bukti penerimaan wajib diupload.',
            'receipt_photos.min' => 'Upload minimal 1 foto.',
        ]);

        // Store photos
        $photos = [];
        foreach ($request->file('receipt_photos') as $photo) {
            $path = $photo->store('receipt_photos', 'public');
            $photos[] = $path;
        }

        $transaction->update([
            'status' => 'received',
            'received_at' => now(),
            'receipt_photos' => $photos,
        ]);

        // Add tracking logs
        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'delivered',
            null,
            null,
            'system',
            null
        );

        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'received',
            'Barang Diterima',
            'Dikonfirmasi oleh ' . auth()->user()->name . ' dengan ' . count($photos) . ' foto',
            'buyer',
            auth()->id()
        );

        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'completed',
            'Transaksi Selesai',
            'Dana diteruskan ke Penjual.',
            'system',
            null
        );

        return back()->with('success', 'Transaksi Selesai! Terima kasih telah berbelanja.');
    }
    public function cancel(Request $request, Transaction $transaction)
    {
        if ($transaction->buyer_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($transaction->status, ['pending', 'waiting_payment', 'processing'])) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan saat ini (Status: ' . $transaction->status . ').');
        }

        // Restore stock for all items in transaction
        foreach ($transaction->items as $item) {
            $product = $item->product;
            if ($product) {
                $product->increment('stock', $item->quantity);
            }
        }

        $transaction->update(['status' => 'cancelled']);

        \App\Models\OrderTrackingLog::addLog(
            $transaction->id,
            'cancelled',
            'Pesanan Dibatalkan',
            'Dibatalkan oleh pembeli. Stok dikembalikan.',
            'buyer',
            auth()->id()
        );

        return back()->with('success', 'Pesanan berhasil dibatalkan. Stok produk telah dikembalikan.');
    }

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
}
