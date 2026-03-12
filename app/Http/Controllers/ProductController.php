<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'user')->where('stock', '>', 0);

        if ($request->has('latitude') && $request->has('longitude')) {
            $userLat = $request->latitude;
            $userLng = $request->longitude;

            $haversine = "(6371 * acos(cos(radians($userLat)) 
                         * cos(radians(latitude)) 
                         * cos(radians(longitude) - radians($userLng)) 
                         + sin(radians($userLat)) 
                         * sin(radians(latitude))))";

            $query->selectRaw("*, {$haversine} AS distance")
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('distance', 'asc');
        } else {
            $query->latest();
        }

        $products = $query->get();

        $categories = \App\Models\Category::all();

        $wishlistIds = auth()->check()
            ? \App\Models\Wishlist::where('user_id', auth()->id())->pluck('product_id')->toArray()
            : [];


        $vouchers = \App\Models\Voucher::where('is_active', true)
            ->where('usage_count', '<', \DB::raw('usage_limit'))
            ->latest()
            ->take(6)
            ->get();

        return view('products.index', compact('products', 'categories', 'wishlistIds', 'vouchers'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'product_images' => 'required|array|min:1|max:6',
            'product_images.*' => 'image|max:2048',
            'stock' => 'required|integer|min:1',
            'weight' => 'required|integer|min:1',
            'condition' => 'required|in:new,used,like_new,good,fair',
            'location' => 'required',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
        ]);

        // First image becomes main image
        $mainImagePath = $request->file('product_images')[0]->store('products', 'public');

        $product = Product::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock' => $request->stock,
            'weight' => $request->weight,
            'condition' => $request->condition,
            'image' => $mainImagePath,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'description' => $request->description,
        ]);

        // Store remaining images as additional images
        $uploadedImages = $request->file('product_images');
        if (count($uploadedImages) > 1) {
            for ($i = 1; $i < count($uploadedImages); $i++) {
                $path = $uploadedImages[$i]->store('products', 'public');
                $product->images()->create(['image_path' => $path]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Barang berhasil diposting!');
    }

    public function show(Product $product)
    {
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function myProducts()
    {
        $products = auth()->user()->products()->latest()->get();
        return view('products.my_products', compact('products'));
    }

    public function edit(Product $product)
    {
        if ($product->user_id !== auth()->id())
            abort(403);
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id())
            abort(403);

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'location' => 'required',
            'additional_images.*' => 'nullable|image|max:2048',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
        ]);

        // Update main image if uploaded
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            // Delete old images
            foreach ($product->images as $oldImage) {
                \Storage::disk('public')->delete($oldImage->image_path);
                $oldImage->delete();
            }

            // Store new images
            foreach ($request->file('additional_images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['image_path' => $path]);
            }
        }

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'discount_price' => $request->has('remove_discount') ? null : $request->discount_price,
            'stock' => $request->stock,
            'location' => $request->location,
            'description' => $request->description,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('products.my')->with('success', 'Barang berhasil diupdate!');
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id())
            abort(403);
        $product->delete();
        return back()->with('success', 'Barang dihapus.');
    }
}
