<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required',
            'shop_name' => 'nullable|string',
            'avatar' => 'nullable|image',
            'password' => 'nullable|min:6|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $data = [
            'name' => $request->name,
            'shop_name' => $request->shop_name,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Profil diperbarui.');
    }

    public function wishlist()
    {
        $wishlists = auth()->user()->wishlist()->with('product')->get();
        return view('profile.wishlist', compact('wishlists'));
    }

    public function toggleWishlist(\App\Models\Product $product, Request $request)
    {
        $user = auth()->user();
        $exists = $user->wishlist()->where('product_id', $product->id)->exists();
        $added = false;

        if ($exists) {
            $user->wishlist()->where('product_id', $product->id)->delete();
            $message = 'Dihapus dari wishlist.';
        } else {
            $user->wishlist()->create(['product_id' => $product->id]);
            $message = 'Ditambahkan ke wishlist!';
            $added = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'added' => $added,
                'message' => $message,
                'wishlist_count' => $user->wishlist()->count()
            ]);
        }

        return back()->with('success', $message);
    }
}
