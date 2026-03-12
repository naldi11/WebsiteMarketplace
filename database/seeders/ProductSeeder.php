<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Setup Categories & User
        $elektronik = Category::firstOrCreate(['slug' => 'elektronik'], ['name' => 'Elektronik']);
        $fashion = Category::firstOrCreate(['slug' => 'fashion'], ['name' => 'Fashion']);
        $otomotif = Category::firstOrCreate(['slug' => 'otomotif'], ['name' => 'Otomotif']);
        $buku = Category::firstOrCreate(['slug' => 'buku-alat-tulis'], ['name' => 'Buku & Alat Tulis']);
        $kebutuhan = Category::firstOrCreate(['slug' => 'kebutuhan-harian'], ['name' => 'Kebutuhan Harian']);

        $seller = User::firstOrCreate(
            ['email' => 'seller@techno.com'],
            [
                'name' => 'Techno Seller',
                'password' => bcrypt('password'),
                'role' => 'user',
                'phone' => '081234567890',
            ]
        );

        // 2. Prepare Storage
        Storage::disk('public')->makeDirectory('products');

        // 3. Product Data (20 Items)
        $products = [
            // Elektronik
            [
                'name' => 'MacBook Air M1 2020 Space Grey',
                'category_id' => $elektronik->id,
                'price' => 12500000,
                'description' => 'Kondisi mulus 99%, CC baterai rendah. Fullset original ibox.',
                'image_keyword' => 'laptop',
            ],
            [
                'name' => 'iPhone 13 128GB Pink',
                'category_id' => $elektronik->id,
                'price' => 9800000,
                'description' => 'Ex inter, sinyal aman all operator. Fisik 95% ada dent tipis.',
                'image_keyword' => 'iphone',
            ],
            [
                'name' => 'Mouse Logitech G304 Wireless',
                'category_id' => $elektronik->id,
                'price' => 450000,
                'description' => 'Mouse gaming wireless badak. Baterai awet berbulan-bulan.',
                'image_keyword' => 'mouse',
            ],
            [
                'name' => 'Keyboard Mechanical VortexSeries',
                'category_id' => $elektronik->id,
                'price' => 650000,
                'description' => 'Switch red, linear enak buat ngetik dan game. RGB mantap.',
                'image_keyword' => 'keyboard',
            ],
            [
                'name' => 'Monitor LG 24MP400 IPS',
                'category_id' => $elektronik->id,
                'price' => 1400000,
                'description' => 'Monitor IPS 24 inch, cocok buat desain dan coding. No dead pixel.',
                'image_keyword' => 'monitor',
            ],

            // Fashion
            [
                'name' => 'Hoodie H&M Polos Hitam',
                'category_id' => $fashion->id,
                'price' => 250000,
                'description' => 'Size L, bahan fleece tebal. Baru dipakai 2x.',
                'image_keyword' => 'hoodie',
            ],
            [
                'name' => 'Sepatu Converse Chuck Taylor 70s',
                'category_id' => $fashion->id,
                'price' => 700000,
                'description' => 'Size 42, original. Warna parchment. Box lengkap.',
                'image_keyword' => 'shoes',
            ],
            [
                'name' => 'Tas Ransel Eiger',
                'category_id' => $fashion->id,
                'price' => 450000,
                'description' => 'Kuat tahan banting, slot laptop aman. Cocok buat kuliah.',
                'image_keyword' => 'backpack',
            ],
            [
                'name' => 'Kemeja Flannel Uniqlo',
                'category_id' => $fashion->id,
                'price' => 150000,
                'description' => 'Motif kotak merah hitam. Size M. Kondisi terawat.',
                'image_keyword' => 'shirt',
            ],
            [
                'name' => 'Jam Tangan Casio G-Shock',
                'category_id' => $fashion->id,
                'price' => 950000,
                'description' => 'Tahan air, baterai baru ganti. Strap original.',
                'image_keyword' => 'watch',
            ],

            // Otomotif
            [
                'name' => 'Helm KYT TT Course',
                'category_id' => $otomotif->id,
                'price' => 850000,
                'description' => 'Motif Venom, spoiler transparan. Busa masih tebal.',
                'image_keyword' => 'helmet',
            ],
            [
                'name' => 'Sarung Tangan Motor Full Finger',
                'category_id' => $otomotif->id,
                'price' => 75000,
                'description' => 'Anti slip, bisa touch screen HP.',
                'image_keyword' => 'gloves',
            ],
            [
                'name' => 'Jas Hujan Axio Europe',
                'category_id' => $otomotif->id,
                'price' => 180000,
                'description' => 'Size XXL, anti rembes. Baru.',
                'image_keyword' => 'raincoat',
            ],
            [
                'name' => 'Oli Motul Scooter LE',
                'category_id' => $otomotif->id,
                'price' => 65000,
                'description' => 'Stok 2 botol, salah beli spek.',
                'image_keyword' => 'oil',
            ],

            // Buku
            [
                'name' => 'Buku Kalkulus Purcel Edisi 9',
                'category_id' => $buku->id,
                'price' => 120000,
                'description' => 'Buku wajib anak teknik. Original terjemahan, bukan fotokopian.',
                'image_keyword' => 'book',
            ],
            [
                'name' => 'Binder Kuliah B5',
                'category_id' => $buku->id,
                'price' => 35000,
                'description' => 'Bonus kertas file 100 lembar. Warna biru pastel.',
                'image_keyword' => 'binder',
            ],
            [
                'name' => 'Kalkulator Scientific Casio',
                'category_id' => $buku->id,
                'price' => 150000,
                'description' => 'FX-991ES Plus. Mulus fungsi normal.',
                'image_keyword' => 'calculator',
            ],

            // Lainnya / Kebutuhan
            [
                'name' => 'Kipas Angin Portable Robot',
                'category_id' => $kebutuhan->id,
                'price' => 50000,
                'description' => 'Lumayan buat ngampus kalo gerah. Baterai awet.',
                'image_keyword' => 'fan',
            ],
            [
                'name' => 'Tumbler Corkcicle Imitasi',
                'category_id' => $kebutuhan->id,
                'price' => 85000,
                'description' => 'Tahan dingin 12 jam. Desain mirip ori.',
                'image_keyword' => 'bottle',
            ],
            [
                'name' => 'Lampu Meja Belajar LED',
                'category_id' => $kebutuhan->id,
                'price' => 75000,
                'description' => 'Bisa diatur terang redupnya. Rechargeable.',
                'image_keyword' => 'lamp',
            ],
        ];

        // 4. Loop & Seed
        foreach ($products as $index => $item) {
            $this->command->info("Seeding product: " . $item['name']);

            // Download Image
            $imageName = 'products/' . Str::slug($item['name']) . '-' . time() . '.jpg';

            // Use Picsum with random seed to get consistent but random images, based on index
            // Adding a keyword doesn't work well with simple picsum, unsplash source is dead.
            // We'll use picsum with a seed.
            $imageUrl = "https://picsum.photos/seed/" . ($index + 555) . "/600/600";

            try {
                $content = file_get_contents($imageUrl);
                if ($content) {
                    Storage::disk('public')->put($imageName, $content);
                } else {
                    $imageName = null; // Failed
                }
            } catch (\Exception $e) {
                $this->command->error("Failed to download image for: " . $item['name']);
                $imageName = null;
            }

            // Create Product
            $product = Product::create([
                'user_id' => $seller->id,
                'category_id' => $item['category_id'],
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'stock' => rand(1, 5),
                'weight' => rand(500, 2000),
                'condition' => rand(0, 1) ? 'new' : 'used',
                'location' => 'Jakarta',
                'image' => $imageName ?? 'products/default.jpg', // Fallback
            ]);

            // Create Additional Images (2-3 per product)
            if ($imageName) {
                ProductImage::create(['product_id' => $product->id, 'image_path' => $imageName]);
                // Add one duplicate as secondary
                ProductImage::create(['product_id' => $product->id, 'image_path' => $imageName]);
            }
        }
    }
}
