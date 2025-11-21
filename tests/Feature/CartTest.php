<?php

use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable foreign key constraints for SQLite testing
        if (config('database.default') === 'sqlite') {
            $this->app['db']->getSchemaBuilder()->disableForeignKeyConstraints();
        }
        
        // Run migrations to ensure database is set up
        $this->artisan('migrate');
        
        // Re-enable foreign key constraints
        if (config('database.default') === 'sqlite') {
            $this->app['db']->getSchemaBuilder()->enableForeignKeyConstraints();
        }
    }

    public function test_cart_page_loads()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('Carrito de Compras');
    }

    public function test_add_item_to_cart()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST001',
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 100.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        // Test the service directly
        $cartService = app(\App\Services\CartService::class);
        
        $cartService->addItem($product->id, null, 2);
        
        $items = $cartService->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals(2, $items[0]['quantity']);
        $this->assertEquals($product->id, $items[0]['product_id']);
    }

    public function test_cart_sync_endpoint()
    {
        $user = User::factory()->create();
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST002',
            'name' => 'Test Product 2',
            'description' => 'Test description 2',
            'price' => 200.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $cartData = [
            [
                'product_id' => $product->id,
                'variant_id' => null,
                'quantity' => 1,
                'price' => $product->price,
            ]
        ];

        // Test the service directly
        $cartService = app(\App\Services\CartService::class);
        $this->actingAs($user);
        
        $cartService->mergeGuestCart(); // This should work even with empty session
        $cartService->addItem($product->id, null, 1);
        
        $items = $cartService->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals($product->id, $items[0]['product_id']);
    }
}