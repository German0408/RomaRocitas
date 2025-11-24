<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MagazineProducts extends Component
{
    use WithPagination;
    public $products = [];
    public $carouselProducts = [];
    public $loading = false;
    public $search = '';
    public $category_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'category_id' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadCarouselProducts();
        $this->loadProducts();
    }

    public function loadCarouselProducts()
    {
        Log::info('MagazineProducts: Loading carousel products (top 20 by sales from ALL products)');

        $query = Product::with(['subcategory.category'])
            ->select('products.*')
            ->selectRaw('(SELECT COUNT(*) FROM cart_items WHERE cart_items.product_id = products.id) as sales_count')
            ->orderBy('sales_count', 'desc')
            ->orderBy('products.created_at', 'desc') // Secondary sort by creation date
            ->limit(20);

        // Note: Carousel shows top 20 from ALL products, not filtered
        // Filters only affect the grid below

        $carouselProducts = $query->get();

        $this->carouselProducts = $carouselProducts->map(function ($product) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'image_path' => $product->image_path,
                'price' => $product->price,
                'subcategory' => $product->subcategory->name ?? null,
                'category' => $product->subcategory->category->name ?? null,
                'stock' => $product->stock,
                'sales_count' => $product->sales_count,
            ];
        })->toArray();

        Log::info('MagazineProducts: Carousel products loaded', [
            'count' => count($this->carouselProducts)
        ]);
    }

    public function updatedSearch()
    {
        // WithPagination trait automatically resets page when search changes
        // Carousel remains static (top 20 from all products)
    }

    public function updatedCategoryId()
    {
        // WithPagination trait automatically resets page when category changes
        // Carousel remains static (top 20 from all products)
    }

    public function resetPagination()
    {
        $this->resetPage();
    }

    public function loadProducts()
    {
        $this->loading = true;

        Log::info('MagazineProducts: Starting loadProducts', [
            'search' => $this->search,
            'category_id' => $this->category_id
        ]);

        // Products will be loaded in render() method with pagination
        // This method is kept for backward compatibility but simplified

        Log::info('MagazineProducts: Products loading delegated to render method');

        $this->loading = false;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->category_id = '';
        $this->resetPage();
        // Carousel remains static (top 20 from all products)
    }

    public function getCategories()
    {
        return Cache::remember('api_categories_with_counts', 3600, function () {
            return Category::with(['family'])
                ->select('categories.*')
                ->selectRaw('(SELECT COUNT(*) FROM subcategories sc INNER JOIN products p ON sc.id = p.subcategory_id WHERE sc.category_id = categories.id) as products_count')
                ->having('products_count', '>', 0)
                ->orderBy('products_count', 'desc')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'family' => $category->family->name ?? null,
                        'products_count' => $category->products_count,
                    ];
                });
        });
    }

    public function render()
    {
        $categories = $this->getCategories();

        // Get paginated products for the view
        $query = Product::with(['subcategory.category']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->category_id) {
            $query->whereHas('subcategory', function ($q) {
                $q->where('category_id', $this->category_id);
            });
        }

        $paginatedProducts = $query->paginate(20);

        Log::info('MagazineProducts: Render called', [
            'productsCount' => $paginatedProducts->count(),
            'total' => $paginatedProducts->total(),
            'categoriesCount' => count($categories),
            'loading' => $this->loading
        ]);

        return view('livewire.products.magazine-products', compact('categories', 'paginatedProducts'));
    }
}