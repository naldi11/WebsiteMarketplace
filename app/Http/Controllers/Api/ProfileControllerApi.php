<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileControllerApi extends Controller
{
    /**
     * Get user profile details
     */
    public function show(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }

    /**
     * Update basic user profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Password saat ini tidak cocok.'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah.'
        ]);
    }

    /**
     * Get list of user addresses
     */
    public function addresses(Request $request)
    {
        $addresses = UserAddress::where('user_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $addresses
        ]);
    }

    /**
     * Get user wishlists
     */
    public function wishlists(Request $request)
    {
        $wishlists = $request->user()->wishlist()->with('product.images')->get();

        // Transform product images to full URLs (same as ProductControllerApi@index)
        $wishlists->transform(function ($wishlist) {
            if ($wishlist->product) {
                $product = $wishlist->product;
                if (!empty($product->image) && !filter_var($product->image, FILTER_VALIDATE_URL)) {
                    $product->image = url('storage/' . $product->image);
                }
            }
            return $wishlist;
        });

        return response()->json([
            'status' => 'success',
            'data' => $wishlists
        ]);
    }

    /**
     * Toggle wishlist
     */
    public function toggleWishlist(Request $request, $id)
    {
        $user = $request->user();
        $product = \App\Models\Product::findOrFail($id);

        $exists = $user->wishlist()->where('product_id', $product->id)->exists();

        if ($exists) {
            $user->wishlist()->where('product_id', $product->id)->delete();
            $message = 'Dihapus dari wishlist.';
            $isWishlisted = false;
        } else {
            $user->wishlist()->create(['product_id' => $product->id]);
            $message = 'Ditambahkan ke wishlist!';
            $isWishlisted = true;
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'is_wishlisted' => $isWishlisted
        ]);
    }

    /**
     * Store new UserAddress
     */
    public function storeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'nullable|string',
            'recipient_name' => 'required|string',
            'phone' => 'required|string',
            'full_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable' // Soften validation to handle various string formats
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['label', 'recipient_name', 'phone', 'full_address', 'latitude', 'longitude', 'is_default']);

        // Parse is_default robustly (handles string "true", "false", 1, 0, etc.)
        $isDefault = filter_var($request->input('is_default'), FILTER_VALIDATE_BOOLEAN);

        if (empty($data['label'])) {
            $data['label'] = 'Rumah';
        }

        // Check if this is the first address or if is_default is explicitly true
        if (!UserAddress::where('user_id', $request->user()->id)->exists() || $isDefault) {
            $data['is_default'] = true;
            // Reset other addresses if this one is default
            UserAddress::where('user_id', $request->user()->id)->update(['is_default' => false]);
        } else {
            $data['is_default'] = false;
        }

        $data['user_id'] = $request->user()->id;

        // Prevent duplicate addresses for the same user
        $existingAddress = UserAddress::where('user_id', $data['user_id'])
            ->where('full_address', $data['full_address'])
            ->where('recipient_name', $data['recipient_name'])
            ->first();

        if ($existingAddress) {
            // If it exists, just update it (syncing is_default if requested)
            if ($data['is_default']) {
                UserAddress::where('user_id', $data['user_id'])->update(['is_default' => false]);
                $existingAddress->update(['is_default' => true]);
            }
            return response()->json(['status' => 'success', 'data' => $existingAddress, 'message' => 'Alamat sudah ada, menggunakan alamat yang sudah ada']);
        }

        $address = UserAddress::create($data);

        return response()->json(['status' => 'success', 'data' => $address, 'message' => 'Alamat berhasil ditambahkan']);
    }

    /**
     * Set UserAddress as default
     */
    public function setDefaultAddress(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->findOrFail($id);
        UserAddress::where('user_id', $request->user()->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return response()->json(['status' => 'success', 'message' => 'Alamat default diperbarui.']);
    }

    /**
     * Update UserAddress
     */
    public function updateAddress(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->findOrFail($id);
        $validator = Validator::make($request->all(), [
            'label' => 'nullable|string',
            'recipient_name' => 'required|string',
            'phone' => 'required|string',
            'full_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }
        $data = $request->only(['label', 'recipient_name', 'phone', 'full_address', 'latitude', 'longitude']);
        $address->update($data);
        return response()->json(['status' => 'success', 'data' => $address, 'message' => 'Alamat diperbarui']);
    }

    /**
     * Delete UserAddress
     */
    public function destroyAddress(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->findOrFail($id);
        $address->delete();
        return response()->json(['status' => 'success', 'message' => 'Alamat dihapus']);
    }
    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'status' => 'success',
                'message' => 'Foto profil berhasil diperbarui.',
                'data' => $user
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan.'], 400);
    }
}
