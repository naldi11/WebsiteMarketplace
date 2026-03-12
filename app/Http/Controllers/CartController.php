<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->cart()->with('product.user')->get();
        // Group by Seller for better UI
        $groupedItems = $cartItems->groupBy('product.user.shop_name');

        $totalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return view('cart.index', compact('cartItems', 'groupedItems', 'totalPrice'));
    }

    public function store(Request $request, Product $product)
    {
        if ($product->stock < 1) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Stok habis!'], 400);
            }
            return back()->with('error', 'Stok habis!');
        }

        if ($product->user_id == auth()->id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Tidak bisa membeli barang sendiri!'], 400);
            }
            return back()->with('error', 'Tidak bisa membeli barang sendiri!');
        }

        $cart = Cart::firstOrNew([
            'user_id' => auth()->id(),
            'product_id' => $product->id
        ]);

        // Check stock limit before incrementing
        $currentQty = $cart->exists ? $cart->quantity : 0;

        if ($currentQty >= $product->stock) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Stok tidak mencukupi!'], 400);
            }
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        $cart->quantity = $currentQty + 1;
        $cart->save();

        // Removed old capping logic entirely as it's now handled by pre-check

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Barang masuk keranjang!',
                'cart_count' => auth()->user()->cart()->sum('quantity')
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Barang masuk keranjang!');
    }

    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== auth()->id())
            abort(403);

        $request->validate(['quantity' => 'required|integer|min:1']);

        if ($request->quantity > $cart->product->stock) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Stok tidak mencukupi!'], 400);
            }
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        $cart->update(['quantity' => $request->quantity]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Jumlah diupdate!',
                'cart_count' => auth()->user()->cart()->sum('quantity')
            ]);
        }
        return back();
    }

    public function destroy(Request $request, Cart $cart)
    {
        if ($cart->user_id !== auth()->id())
            abort(403);
        $cart->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Barang dihapus.']);
        }
        return back()->with('success', 'Barang dihapus dari keranjang.');
    }
}
