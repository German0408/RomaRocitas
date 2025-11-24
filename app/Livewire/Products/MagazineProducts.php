<?php

namespace App\Livewire\Products;

use Livewire\Component;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class MagazineProducts extends Component
{
    public $products = [];
    public $currentPage = 1;
    public $hasMorePages = true;
    public $loading = false;
    public $search = '';
    public $category_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'category_id' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadProducts();
    }

    public function updatedSearch()
    {
        $this->resetPagination();
        $this->loadProducts();
    }

    public function updatedCategoryId()
    {
        $this->resetPagination();
        $this->loadProducts();
    }

    public function resetPagination()
    {
        $this->currentPage = 1;
        $this->hasMorePages = true;
        $this->products = [];
    }

    public function loadProducts()
    {
        $this->loading = true;

        $query = Product::with(['subcategory.category']);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        // Category filter
        if ($this->category_id) {
            $query->whereHas('subcategory', function ($q) {
                $q->where('category_id', $this->category_id);
            });
        }

        $perPage = 20;
        $paginatedProducts = $query->paginate($perPage, ['*'], 'page', $this->currentPage);

        $newProducts = $paginatedProducts->map(function ($product) {
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
            ];
        })->toArray();

        if ($this->currentPage === 1) {
            $this->products = $newProducts;
        } else {
            $this->products = array_merge($this->products, $newProducts);
        }

        $this->hasMorePages = $paginatedProducts->hasMorePages();

        $this->loading = false;
    }

    public function loadMore()
    {
        if ($this->hasMorePages && !$this->loading) {
            $this->currentPage++;
            $this->loadProducts();
        }
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
        return view('livewire.products.magazine-products', compact('categories'));
    }
}