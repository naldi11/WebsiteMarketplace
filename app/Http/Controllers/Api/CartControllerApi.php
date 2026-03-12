<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CartControllerApi extends Controller
{
    /**
     * Get user's cart items
     */
    public function index(Request $request)
    {
        $carts = Cart::where('user_id', $request->user()->id)
            ->with(['product.images'])
            ->get();

        $carts->transform(function ($cart) {
            $data = $cart->toArray();
            $data['total_price'] = $cart->product->effective_price * $cart->quantity;
            return $data;
        });

        $grandTotal = $carts->sum('total_price');

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $carts,
                'grand_total' => $grandTotal
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $product = Product::findOrFail($request->product_id);

        if ($product->user_id === $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak bisa membeli produk sendiri.'], 403);
        }

        if ($product->stock < $request->quantity) {
            return response()->json(['status' => 'error', 'message' => 'Stok produk tidak mencukupi.'], 400);
        }

        $cart = Cart::firstOrNew([
            'user_id' => $request->user()->id,
            'product_id' => $product->id
        ]);

        $cart->quantity = ($cart->exists ? $cart->quantity : 0) + $request->quantity;

        if ($cart->quantity > $product->stock) {
            $cart->quantity = $product->stock;
        }

        $cart->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'data' => $cart
        ]);
    }

    /**
     * Update cart quantity
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $cart = Cart::where('user_id', $request->user()->id)->findOrFail($id);

        if ($cart->product->stock < $request->quantity) {
            return response()->json(['status' => 'error', 'message' => 'Stok produk tidak mencukupi.'], 400);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'status' => 'success',
            'message' => 'Keranjang diperbarui.',
            'data' => $cart
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $id)
    {
        $cart = Cart::where('user_id', $request->user()->id)->findOrFail($id);
        $cart->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk dihapus dari keranjang.'
        ]);
    }
}
