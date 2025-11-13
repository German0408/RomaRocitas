@extends('layouts.storefront')

@section('title', $product->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2A1 1 0 0 0 1 10h2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8h2a1 1 0 0 0 .707-1.707Z"/>
                    </svg>
                    Inicio
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $product->subcategory->category->family->name }}</span>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Image -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            @if($product->image_path && $product->image_path !== 'products/' && file_exists(public_path('storage/' . $product->image_path)))
                <img src="{{ asset('storage/' . $product->image_path) }}"
                     alt="{{ $product->name }}"
                     class="w-full h-96 object-cover rounded-lg">
            @else
                <div class="w-full h-96 bg-gray-300 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400">Sin imagen</span>
                </div>
            @endif
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $product->name }}</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">{{ $product->sku }}</p>
            </div>

            <div>
                <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                    ${{ number_format($product->price, 0, ',', '.') }}
                </span>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Descripción</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $product->description }}</p>
            </div>

            <!-- Variants and Options will be added in Phase 2 -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <p class="text-gray-500 dark:text-gray-400">Opciones de variante próximamente.</p>
            </div>

            <!-- Add to Cart Button (placeholder for now) -->
            <button class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white py-3 px-6 rounded-lg font-medium transition-colors">
                Agregar al Carrito
            </button>
        </div>
    </div>

    <!-- Related Products Section (placeholder) -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Productos Relacionados</h2>
        <p class="text-gray-500 dark:text-gray-400">Próximamente: productos de la misma categoría.</p>
    </div>
</div>
@endsection