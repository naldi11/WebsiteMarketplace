<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAccessTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']);
        $this->product = Product::create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100000,
            'stock' => 5,
            'location' => 'Jakarta',
            'image' => 'products/test.jpg',
        ]);
    }

    public function test_guest_can_access_homepage()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_guest_can_access_product_details_on_web()
    {
        $response = $this->get(route('products.show', $this->product));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_product_details_on_web()
    {
        $response = $this->actingAs($this->user)->get(route('products.show', $this->product));
        $response->assertStatus(200);
    }

    public function test_guest_can_access_product_details_on_api()
    {
        $response = $this->getJson("/api/products/{$this->product->id}");
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_product_details_on_api()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/products/{$this->product->id}");
        $response->assertStatus(200);
    }
}
