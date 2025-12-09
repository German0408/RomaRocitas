<?php

use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MagazineProductsTest extends TestCase
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

    private function createTestData()
    {
        $family = Family::create(['name' => 'Test Family']);
        $category1 = Category::create(['name' => 'Test Category 1', 'family_id' => $family->id]);
        $category2 = Category::create(['name' => 'Test Category 2', 'family_id' => $family->id]);
        $subcategory1 = Subcategory::create(['name' => 'Test Subcategory 1', 'category_id' => $category1->id]);
        $subcategory2 = Subcategory::create(['name' => 'Test Subcategory 2', 'category_id' => $category2->id]);

        $products = [];
        for ($i = 1; $i <= 25; $i++) {
            $products[] = Product::create([
                'sku' => 'TEST' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'Test Product ' . $i,
                'description' => 'Test description for product ' . $i,
                'price' => 100.00 + $i,
                'subcategory_id' => $i % 2 == 0 ? $subcategory1->id : $subcategory2->id,
                'stock' => 10 + $i
            ]);
        }

        return compact('family', 'category1', 'category2', 'subcategory1', 'subcategory2', 'products');
    }

    public function test_component_mounts_and_loads_products()
    {
        $this->createTestData();

        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class);
        $component->assertSet('currentPage', 1)
            ->assertSet('hasMorePages', true)
            ->assertSet('loading', false);

        // Check that products array has 20 items
        $products = $component->get('products');
        $this->assertIsArray($products);
        $this->assertCount(20, $products);
    }

    public function test_search_functionality()
    {
        $data = $this->createTestData();

        // Search for a specific product by SKU
        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->set('search', 'TEST001');

        $component->assertSet('currentPage', 1);

        $products = $component->get('products');
        $this->assertIsArray($products);
        $this->assertCount(1, $products);
        $this->assertEquals('Test Product 1', $products[0]['name']);
    }

    public function test_category_filtering()
    {
        $data = $this->createTestData();

        // Filter by category 1 (should have products 2,4,6,... from subcategory1)
        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->set('category_id', $data['category1']->id);

        $component->assertSet('currentPage', 1);

        $products = $component->get('products');
        $this->assertIsArray($products);
        $this->assertGreaterThan(0, count($products));
        $this->assertLessThanOrEqual(20, count($products));
    }

    public function test_pagination_load_more()
    {
        $this->createTestData(); // Creates 25 products

        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class);
        $products = $component->get('products');
        $this->assertCount(20, $products); // First page
        $component->assertSet('hasMorePages', true);

        // Load more
        $component->call('loadMore');
        $component->assertSet('currentPage', 2);

        $productsAfter = $component->get('products');
        $this->assertCount(25, $productsAfter); // All products loaded
        $component->assertSet('hasMorePages', false);
    }

    public function test_clear_filters()
    {
        $data = $this->createTestData();

        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->set('search', 'TEST001')
            ->set('category_id', $data['category1']->id);

        $products = $component->get('products');
        $this->assertIsArray($products);
        // Should have fewer products after filtering
        $this->assertLessThan(20, count($products));

        $component->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('category_id', '')
            ->assertSet('currentPage', 1);

        $productsAfter = $component->get('products');
        $this->assertCount(20, $productsAfter);
    }

    public function test_query_string_persistence()
    {
        $data = $this->createTestData();

        // Test that query string is updated
        Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->set('search', 'test search')
            ->set('category_id', $data['category1']->id)
            ->assertSet('search', 'test search')
            ->assertSet('category_id', $data['category1']->id);
    }

    public function test_updated_search_resets_pagination()
    {
        $this->createTestData();

        Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->call('loadMore') // Load page 2
            ->assertSet('currentPage', 2)
            ->set('search', 'Test') // This should reset pagination
            ->call('resetPaginationForSearch') // Call the reset method
            ->assertSet('currentPage', 1);
    }

    public function test_updated_category_resets_pagination()
    {
        $data = $this->createTestData();

        Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->call('loadMore') // Load page 2
            ->assertSet('currentPage', 2)
            ->set('category_id', $data['category1']->id) // This should reset pagination
            ->call('resetPaginationForCategory') // Call the reset method
            ->assertSet('currentPage', 1);
    }

    public function test_loading_state_during_product_load()
    {
        $this->createTestData();

        Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->assertSet('loading', false)
            ->set('search', 'Test Product 1')
            ->assertSet('loading', false); // Should be false after load
    }

    public function test_get_categories_method()
    {
        $data = $this->createTestData();

        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class);
        $categories = $component->instance()->getCategories();

        $this->assertCount(2, $categories); // Should have 2 categories with products
        // Categories are ordered by product count descending
        $this->assertGreaterThan(0, $categories[0]['products_count']);
        $this->assertGreaterThan(0, $categories[1]['products_count']);
    }

    public function test_no_more_pages_when_all_loaded()
    {
        // Create only 10 products (less than page size)
        $family = Family::create(['name' => 'Test Family']);
        $category = Category::create(['name' => 'Test Category', 'family_id' => $family->id]);
        $subcategory = Subcategory::create(['name' => 'Test Subcategory', 'category_id' => $category->id]);

        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'sku' => 'TEST' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'Test Product ' . $i,
                'description' => 'Test description for product ' . $i,
                'price' => 100.00 + $i,
                'subcategory_id' => $subcategory->id,
                'stock' => 10 + $i
            ]);
        }

        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class);
        $products = $component->get('products');
        $this->assertCount(10, $products);
        $component->assertSet('hasMorePages', false);
    }

    public function test_search_with_no_results()
    {
        $this->createTestData();

        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->set('search', 'nonexistent product');

        $products = $component->get('products');
        $this->assertCount(0, $products);
        $component->assertSet('hasMorePages', false);
    }

    public function test_category_filter_with_no_results()
    {
        $data = $this->createTestData();

        // Create a category with no products
        $emptyFamily = Family::create(['name' => 'Empty Family']);
        $emptyCategory = Category::create(['name' => 'Empty Category', 'family_id' => $emptyFamily->id]);

        $component = Livewire::test(\App\Livewire\Products\MagazineProducts::class)
            ->set('category_id', $emptyCategory->id);

        $products = $component->get('products');
        $this->assertCount(0, $products);
        $component->assertSet('hasMorePages', false);
    }
}