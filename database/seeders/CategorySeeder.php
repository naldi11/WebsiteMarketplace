<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Elektronik Gadget',
                'slug' => 'elektronik-gadget',
                'description' => 'Smartphone, Laptop, Tablet, Kamera, dll.',
                'icon' => 'smartphone'
            ],
            [
                'name' => 'Fashion Pria',
                'slug' => 'fashion-pria',
                'description' => 'Baju, Celana, Sepatu, Aksesoris Pria.',
                'icon' => 'shirt'
            ],
            [
                'name' => 'Fashion Wanita',
                'slug' => 'fashion-wanita',
                'description' => 'Dress, Tas, Sepatu, Hijab, dll.',
                'icon' => 'shopping-bag'
            ],
            [
                'name' => 'Komputer & Aksesoris',
                'slug' => 'komputer-aksesoris',
                'description' => 'PC Gaming, Mouse, Keyboard, Monitor.',
                'icon' => 'monitor'
            ],
            [
                'name' => 'Hobi & Koleksi',
                'slug' => 'hobi-koleksi',
                'description' => 'Mainan, Action Figure, Alat Musik.',
                'icon' => 'gamepad'
            ],
            [
                'name' => 'Otomotif',
                'slug' => 'otomotif',
                'description' => 'Aksesori Motor & Mobil, Helm, Oli.',
                'icon' => 'tool'
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
