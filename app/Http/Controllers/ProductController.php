<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return view('products.index');
    }

    public function apiIndex(Request $request)
    {
        $query = Product::with(['subcategory.category']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Category filter
        if ($request->category_id) {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Sorting - default to name asc, but allow override
        $sortBy = $request->sort_by ?? 'name';
        $sortDirection = $request->sort_direction ?? 'asc';
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $request->per_page ?? 12;
        $products = $query->paginate($perPage);

        // Transform data for magazine layout
        $products->getCollection()->transform(function ($product) {
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
        });

        return response()->json($products);
    }

    public function apiCategories()
    {
        $categories = Cache::remember('api_categories_with_counts', 3600, function () {
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

        return response()->json($categories);
    }
}