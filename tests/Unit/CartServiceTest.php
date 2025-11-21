<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Family;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            $this->app['db']->getSchemaBuilder()->disableForeignKeyConstraints();
        }

        $this->artisan('migrate');

        if (config('database.default') === 'sqlite') {
            $this->app['db']->getSchemaBuilder()->enableForeignKeyConstraints();
        }
    }

    public function test_add_item_to_session_cart()
    {
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

        $cartService = new CartService();

        $cartService->addItem($product->id, null, 2);

        $items = $cartService->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals(2, $items[0]['quantity']);
        $this->assertEquals($product->id, $items[0]['product_id']);
    }

    public function test_add_item_to_database_cart_when_authenticated()
    {
        $user = User::factory()->create();
        Auth::login($user);

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

        $cartService = new CartService();

        $cartService->addItem($product->id, null, 1);

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);
        $this->assertEquals(1, $cart->items->first()->quantity);
    }

    public function test_update_quantity_in_session_cart()
    {
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST003',
            'name' => 'Test Product 3',
            'description' => 'Test description 3',
            'price' => 50.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $cartService = new CartService();
        $cartService->addItem($product->id, null, 1);

        $items = $cartService->getItems();
        $key = 0; // First item

        $cartService->updateQuantity($key, 5);

        $updatedItems = $cartService->getItems();
        $this->assertEquals(5, $updatedItems[0]['quantity']);
    }

    public function test_remove_item_from_session_cart()
    {
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST004',
            'name' => 'Test Product 4',
            'description' => 'Test description 4',
            'price' => 75.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $cartService = new CartService();
        $cartService->addItem($product->id, null, 1);

        $items = $cartService->getItems();
        $this->assertCount(1, $items);

        $key = 0;
        $cartService->removeItem($key);

        $remainingItems = $cartService->getItems();
        $this->assertCount(0, $remainingItems);
    }

    public function test_get_total_for_session_cart()
    {
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product1 = Product::create([
            'sku' => 'TEST005',
            'name' => 'Test Product 5',
            'description' => 'Test description 5',
            'price' => 100.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $product2 = Product::create([
            'sku' => 'TEST006',
            'name' => 'Test Product 6',
            'description' => 'Test description 6',
            'price' => 50.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $cartService = new CartService();
        $cartService->addItem($product1->id, null, 2); // 200
        $cartService->addItem($product2->id, null, 1); // 50

        $total = $cartService->getTotal();
        $this->assertEquals(250.00, $total);
    }

    public function test_merge_guest_cart_on_login()
    {
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST007',
            'name' => 'Test Product 7',
            'description' => 'Test description 7',
            'price' => 120.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $cartService = new CartService();
        $cartService->addItem($product->id, null, 3);

        // Simulate session cart
        $sessionItems = Session::get(CartService::CART_KEY);
        $this->assertNotEmpty($sessionItems);

        $user = User::factory()->create();
        Auth::login($user);

        $cartService->mergeGuestCart();

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);
        $this->assertEquals(3, $cart->items->first()->quantity);

        // Session should be cleared
        $this->assertEmpty(Session::get(CartService::CART_KEY));
    }

    public function test_stock_validation_throws_exception()
    {
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST008',
            'name' => 'Test Product 8',
            'description' => 'Test description 8',
            'price' => 30.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 2
        ]);

        $cartService = new CartService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('cart.insufficient_stock', ['stock' => 2]));

        $cartService->addItem($product->id, null, 5);
    }

    public function test_clear_cart()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);
        $product = Product::create([
            'sku' => 'TEST009',
            'name' => 'Test Product 9',
            'description' => 'Test description 9',
            'price' => 40.00,
            'subcategory_id' => $subcategory->id,
            'stock' => 10
        ]);

        $cartService = new CartService();
        $cartService->addItem($product->id, null, 1);

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertCount(1, $cart->items);

        $cartService->clear();

        $cart->refresh();
        $this->assertCount(0, $cart->items);
    }
}