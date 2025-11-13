<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return view('products.index');
    }

    public function show(Product $product)
    {
        $product->load([
            'subcategory.category.family',
            'variants.features',
            'options' => function ($query) {
                $query->withPivot('value');
            },
            'options.features'
        ]);

        // Get related products from same subcategory
        $relatedProducts = collect();
        if ($product->subcategory_id) {
            $relatedProducts = Product::where('subcategory_id', $product->subcategory_id)
                ->where('id', '!=', $product->id)
                ->with(['subcategory.category.family'])
                ->limit(4)
                ->get();
        }

        return view('products.show', compact('product', 'relatedProducts'));
    }
}