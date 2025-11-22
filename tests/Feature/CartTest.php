<?php

use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_add_item_via_controller()
    {
        $user = User::factory()->create();
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST003',
            'name' => 'Test Product 3',
            'description' => 'Test description 3',
            'price' => 150.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $response = $this->actingAs($user)->withoutMiddleware()->post(route('cart.store'), [
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success', 'Producto añadido al carrito.');

        $cart = \App\Models\Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);
        $this->assertEquals(2, $cart->items->first()->quantity);
    }

    public function test_update_quantity_via_controller()
    {
        $user = User::factory()->create();
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST004',
            'name' => 'Test Product 4',
            'description' => 'Test description 4',
            'price' => 80.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        // Add item first
        $this->actingAs($user)->withoutMiddleware()->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cart = \App\Models\Cart::where('user_id', $user->id)->first();
        $itemId = $cart->items->first()->id;

        $response = $this->actingAs($user)->withoutMiddleware()->put(route('cart.update', $itemId), [
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success', 'Cantidad actualizada.');

        $cart->refresh();
        $this->assertEquals(3, $cart->items->first()->quantity);
    }

    public function test_remove_item_via_controller()
    {
        $user = User::factory()->create();
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST005',
            'name' => 'Test Product 5',
            'description' => 'Test description 5',
            'price' => 60.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        // Add item first
        $this->actingAs($user)->withoutMiddleware()->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cart = \App\Models\Cart::where('user_id', $user->id)->first();
        $itemId = $cart->items->first()->id;

        $response = $this->actingAs($user)->withoutMiddleware()->delete(route('cart.destroy', $itemId));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success', 'Producto eliminado del carrito.');

        $cart->refresh();
        $this->assertCount(0, $cart->items);
    }

    public function test_cart_validation_errors()
    {
        $user = User::factory()->create();

        // Test invalid product_id
        $response = $this->actingAs($user)->withoutMiddleware()->post(route('cart.store'), [
            'product_id' => 999,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('product_id');

        // Test invalid quantity
        $response = $this->actingAs($user)->withoutMiddleware()->post(route('cart.store'), [
            'product_id' => 1,
            'quantity' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('quantity');
    }

    public function test_guest_cart_persistence()
    {
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST006',
            'name' => 'Test Product 6',
            'description' => 'Test description 6',
            'price' => 90.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        // Add to cart as guest
        $cartService = app(\App\Services\CartService::class);
        $cartService->addItem($product->id, null, 2);

        $items = $cartService->getItems();
        $this->assertCount(1, $items);

        // Simulate page reload by creating new service instance
        $newCartService = app(\App\Services\CartService::class);
        $persistedItems = $newCartService->getItems();
        $this->assertCount(1, $persistedItems);
        $this->assertEquals(2, $persistedItems[0]['quantity']);
    }

    public function test_add_to_cart_livewire_component()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST007',
            'name' => 'Test Product 7',
            'description' => 'Test description 7',
            'price' => 100.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        // Test the Livewire component
        Livewire::test(\App\Livewire\AddToCart::class, ['productId' => $product->id])
            ->assertSet('productId', $product->id)
            ->assertSet('quantity', 1)
            ->assertSet('availableStock', 10)
            ->call('addToCart')
            ->assertEmitted('cart-updated')
            ->assertEmitted('show-toast');

        // Verify item was added to cart
        $cartService = app(\App\Services\CartService::class);
        $items = $cartService->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals($product->id, $items[0]['product_id']);
        $this->assertEquals(1, $items[0]['quantity']);
    }

    public function test_add_to_cart_with_variant_selection()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST008',
            'name' => 'Test Product 8',
            'description' => 'Test description 8',
            'price' => 150.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        // Create option and features
        $option = \App\Models\Option::create(['name' => 'Color', 'type' => 'select']);
        $feature1 = \App\Models\Feature::create(['value' => 'Rojo', 'option_id' => $option->id]);
        $feature2 = \App\Models\Feature::create(['value' => 'Azul', 'option_id' => $option->id]);

        // Associate option with product
        $product->options()->attach($option->id, ['value' => '']);

        // Create variants
        $variant1 = \App\Models\Variant::create([
            'sku' => 'TEST008-RED',
            'product_id' => $product->id
        ]);
        $variant2 = \App\Models\Variant::create([
            'sku' => 'TEST008-BLUE',
            'product_id' => $product->id
        ]);

        // Associate features with variants
        $variant1->features()->attach($feature1->id);
        $variant2->features()->attach($feature2->id);

        // Test selecting a variant
        Livewire::test(\App\Livewire\AddToCart::class, ['productId' => $product->id])
            ->set('selectedFeatures', [$option->id => $feature1->id])
            ->assertSet('selectedVariantId', $variant1->id)
            ->call('addToCart')
            ->assertEmitted('cart-updated');

        // Verify variant was added
        $cartService = app(\App\Services\CartService::class);
        $items = $cartService->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals($variant1->id, $items[0]['variant_id']);
    }
}