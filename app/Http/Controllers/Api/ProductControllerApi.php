<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\StoreProductRequest;

class ProductControllerApi extends Controller
{
    /**
     * Get a list of products, optionally filtered by keyword, category,
     * or nearest location based on user's lat/lng.
     */
    public function index(Request $request)
    {
        $query = Product::with('category', 'images', 'user')->where('stock', '>', 0)->withAvgRating();

        // Apply general filters
        $query->filter($request->only('search', 'category'));

        // Apply Haversine Distance Sorting if lat/lng are provided and not empty
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $radius = $request->input('radius', null);
            // Convert radius to integer; treat 100 or null as "no filter"
            $radiusKm = ($radius !== null && (int)$radius < 100) ? (int)$radius : null;

            $query->nearby($request->latitude, $request->longitude, $radiusKm)
                  ->orderByRating('desc');
        } else {
            // Priority: Rating, then latest
            $query->orderByRating('desc')->latest();
        }

        if ($request->has('all') || $request->has('no_paginate')) {
            $products = $query->get();
        } else {
            $perPage = $request->input('per_page', 10);
            $products = $query->paginate($perPage);
        }

        $wishlistIds = collect();
        if (auth('sanctum')->check()) {
            $wishlistIds = \App\Models\Wishlist::where('user_id', auth('sanctum')->id())->pluck('product_id');
        }

        // Map data to calculate effective price and distance
        $items = ($products instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $products->getCollection() : $products;

        $items = $items->values();

        $roadDistances = [];
        if ($request->filled('latitude') && $request->filled('longitude') && $items->isNotEmpty()) {
            $bLat = (float)$request->latitude;
            $bLon = (float)$request->longitude;

            // Gunakan OSRM Route Service (SAMA dengan Checkout) via curl_multi concurrent
            // Key mapping disimpan langsung bersama handle curl, dijamin akurat
            $curlHandles = []; // array of ['key' => product_key, 'ch' => curl_handle]
            $mh = curl_multi_init();

            foreach ($items as $key => $product) {
                $sLat = $product->latitude;
                $sLon = $product->longitude;
                if ($sLat && $sLon && !($sLat == $bLat && $sLon == $bLon)) {
                    $url = "http://router.project-osrm.org/route/v1/driving/{$bLon},{$bLat};{$sLon},{$sLat}?overview=false";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                    curl_multi_add_handle($mh, $ch);
                    $curlHandles[] = ['key' => $key, 'ch' => $ch];
                }
            }

            if (!empty($curlHandles)) {
                try {
                    $running = null;
                    do {
                        $status = curl_multi_exec($mh, $running);
                        if ($running) curl_multi_select($mh, 0.5);
                    } while ($running > 0 && $status === CURLM_OK);

                    // Key mapping langsung dari $curlHandles — tidak ada index offset
                    foreach ($curlHandles as $info) {
                        $body     = curl_multi_getcontent($info['ch']);
                        $httpCode = curl_getinfo($info['ch'], CURLINFO_HTTP_CODE);
                        curl_multi_remove_handle($mh, $info['ch']);
                        curl_close($info['ch']);

                        if ($httpCode === 200 && $body) {
                            $json = json_decode($body, true);
                            if (isset($json['code']) && $json['code'] === 'Ok' && !empty($json['routes'])) {
                                $roadDistances[$info['key']] = $json['routes'][0]['distance'] / 1000.0;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("OSRM curl_multi exception: " . $e->getMessage());
                }
                curl_multi_close($mh);
            }
        }

        $items->transform(function ($product, $key) use ($wishlistIds, $roadDistances) {
            $data = $product->toArray();
            $data['effective_price'] = $product->effective_price;
            $data['has_discount'] = $product->hasDiscount();
            $data['discount_percent'] = $product->discount_percent;

            // Gunakan jarak jalan raya OSRM Route (identik dengan Checkout)
            $dist = null;
            if (isset($roadDistances[$key])) {
                $dist = round($roadDistances[$key], 2);
            } elseif (isset($product->distance)) {
                // Fallback Haversine jika OSRM gagal
                $dist = $product->distance;
            }

            if ($dist !== null) {
                $data['distance'] = round($dist, 2);
            }

            $userId = auth('sanctum')->id();
            $data['is_wishlisted'] = $wishlistIds->contains($product->id);
            $data['is_mine'] = $userId && $userId === $product->user_id;

            if (!empty($data['image']) && !filter_var($data['image'], FILTER_VALIDATE_URL)) {
                $data['image'] = url('storage/' . $data['image']);
            }

            return $data;
        });

        if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $products->setCollection($items);
        } else {
            $products = $items;
        }

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Get single product details
     */
    public function show($id)
    {
        $product = Product::with(['category', 'images', 'user', 'reviews.reviewer'])
            ->findOrFail($id);

        $data = $product->toArray();
        $data['effective_price'] = $product->effective_price;
        $data['has_discount'] = $product->hasDiscount();
        $data['discount_percent'] = $product->discount_percent;

        $userId = auth('sanctum')->id();
        $data['is_wishlisted'] = $userId ? \App\Models\Wishlist::where('user_id', $userId)->where('product_id', $product->id)->exists() : false;
        $data['is_mine'] = $userId && $userId === $product->user_id;

        if (!empty($data['image']) && !filter_var($data['image'], FILTER_VALIDATE_URL)) {
            $data['image'] = url('storage/' . $data['image']);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $mainImagePath = 'default.jpg';
        $imagePaths = [];

        if ($request->hasFile('images')) {
            $isFirst = true;
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
                if ($isFirst) {
                    $mainImagePath = $path;
                    $isFirst = false;
                }
            }
        }

        $product = Product::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock' => $request->stock,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'condition' => $request->condition,
            'weight' => $request->weight,
            'location' => $request->location,
            'image' => $mainImagePath,
            'shipping_suggestion' => $request->shipping_suggestion,
        ]);

        $isPrimary = true;
        foreach ($imagePaths as $path) {
            $product->images()->create([
                'image_path' => $path,
                'is_primary' => $isPrimary
            ]);
            $isPrimary = false;
        }

        $product->load('images');

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan!',
            'data' => $product
        ], 201);
    }

    /**
     * Get authenticated user's products
     */
    public function myProducts(Request $request)
    {
        $products = $request->user()->products()
            ->with('category', 'images')
            ->latest()
            ->get();

        $products->transform(function ($product) {
            $data = $product->toArray();
            $data['effective_price'] = $product->effective_price;
            $data['has_discount'] = $product->hasDiscount();
            $data['discount_percent'] = $product->discount_percent;

            if (!empty($data['image']) && !filter_var($data['image'], FILTER_VALIDATE_URL)) {
                $data['image'] = url('storage/' . $data['image']);
            }

            return $data;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Update a product (owner only)
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'condition' => 'sometimes|required|in:new,used,like_new,good,fair',
            'weight' => 'sometimes|required|integer|min:1',
            'location' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'shipping_suggestion' => 'nullable|string|in:motor,becak,pickup,jemput_sendiri',
        ]);

        $product->update($request->only([
            'name',
            'price',
            'discount_price',
            'stock',
            'category_id',
            'condition',
            'weight',
            'location',
            'description',
            'shipping_suggestion'
        ]));

        $product->load('category', 'images');

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diperbarui!',
            'data' => $product
        ]);
    }

    /**
     * Delete a product (owner only)
     */
    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Delete images from storage
        foreach ($product->images as $image) {
            \Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        if ($product->image && $product->image !== 'default.jpg') {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus!'
        ]);
    }
}
