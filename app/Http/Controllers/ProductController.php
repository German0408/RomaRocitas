<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['subcategory.category.family'])
            ->paginate(12);

        // Get categories with product counts, only including categories that have products
        $categoriesWithCounts = \App\Models\Category::with(['family'])
            ->select('categories.*')
            ->selectRaw('(SELECT COUNT(*) FROM subcategories sc INNER JOIN products p ON sc.id = p.subcategory_id WHERE sc.category_id = categories.id) as products_count')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->get();

        return view('products.index', compact('products', 'categoriesWithCounts'));
    }

    public function show(Product $product)
    {
        $product->load(['subcategory.category.family', 'variants', 'options']);

        return view('products.show', compact('product'));
    }
}