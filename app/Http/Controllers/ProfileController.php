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
        
        $rules = [
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image',
            'password' => 'nullable|min:6|confirmed',
        ];

        if ($user->role === 'seller') {
            $rules['bank_name'] = 'required|string|max:100';
            $rules['bank_account_number'] = 'required|string|max:50';
            $rules['bank_account_name'] = 'required|string|max:100';
        } else {
            $rules['bank_name'] = 'nullable|string|max:100';
            $rules['bank_account_number'] = 'nullable|string|max:50';
            $rules['bank_account_name'] = 'nullable|string|max:100';
        }

        $request->validate($rules, [
            'bank_name.required' => 'Nama Bank wajib diisi untuk Penjual.',
            'bank_account_number.required' => 'Nomor Rekening wajib diisi untuk Penjual.',
            'bank_account_name.required' => 'Nama Pemilik Rekening wajib diisi untuk Penjual.',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $data = [
            'name' => $request->name,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
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
