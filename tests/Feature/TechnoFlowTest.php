<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechnoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_phone()
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'phone' => '+6281234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', [
            'phone' => '+6281234567890',
        ]);
    }

    public function test_user_cannot_register_without_plus_62()
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890', // Invalid format
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'phone' => '+6281234567890',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'phone' => '+6281234567890',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_post_product()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']);
        Storage::fake('public');
        $file = UploadedFile::fake()->image('laptop.jpg');

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Laptop Gaming',
            'description' => 'Laptop super kencang',
            'price' => 15000000,
            'stock' => 5,
            'category_id' => $category->id,
            'location' => 'Jakarta',
            'image' => $file,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Laptop Gaming',
            'user_id' => $user->id,
            'stock' => 5,
        ]);
    }

    public function test_checkout_reduces_stock()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']);

        $product = Product::create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'HP Mahal',
            'description' => 'HP Bagus',
            'price' => 5000000,
            'stock' => 10,
            'location' => 'Bandung',
            'image' => 'products/hp.jpg',
        ]);

        $response = $this->actingAs($buyer)->post(route('transactions.store', $product));

        $response->assertRedirect(route('transactions.history'));

        // Assert Transaction Created
        $this->assertDatabaseHas('transactions', [
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        // Assert Stock Reduced
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 9, // 10 - 1
        ]);
    }

    public function test_cannot_buy_out_of_stock_item()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']);

        $product = Product::create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'HP Langka',
            'description' => 'HP Bagus',
            'price' => 5000000,
            'stock' => 0, // Habis
            'location' => 'Bandung',
            'image' => 'products/hp.jpg',
        ]);

        $response = $this->actingAs($buyer)->post(route('transactions.store', $product));

        // Should return back with error
        $response->assertSessionHas('error', 'Stok habis!');

        // Stock remains 0
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 0,
        ]);
    }
}
