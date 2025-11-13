<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $category_id = '';
    public $min_price = '';
    public $max_price = '';
    public $sort_by = 'name';
    public $sort_direction = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'category_id' => ['except' => ''],
        'min_price' => ['except' => ''],
        'max_price' => ['except' => ''],
        'sort_by' => ['except' => 'name'],
        'sort_direction' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingMinPrice()
    {
        $this->resetPage();
    }

    public function updatingMaxPrice()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function render()
    {
        $query = Product::with(['subcategory.category.family']);

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

        // Price filters
        if ($this->min_price) {
            $query->where('price', '>=', $this->min_price);
        }
        if ($this->max_price) {
            $query->where('price', '<=', $this->max_price);
        }

        // Sorting
        $query->orderBy($this->sort_by, $this->sort_direction);

        $products = $query->paginate(12);

        // Get categories with product counts
        $categoriesWithCounts = Category::with(['family'])
            ->select('categories.*')
            ->selectRaw('(SELECT COUNT(*) FROM subcategories sc INNER JOIN products p ON sc.id = p.subcategory_id WHERE sc.category_id = categories.id) as products_count')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->get();

        return view('livewire.products.index', compact('products', 'categoriesWithCounts'));
    }
}
