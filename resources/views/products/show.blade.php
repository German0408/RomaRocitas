@extends('layouts.storefront')

@section('title', $product->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('products.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
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
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">{{ $product->subcategory?->category?->family?->name ?? 'Categoría' }}</span>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Image Gallery -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <img id="main-image"
                     src="{{ $product->image_path && $product->image_path !== 'products/' && file_exists(public_path('storage/' . $product->image_path)) ? asset('storage/' . $product->image_path) : asset('img/images.png') }}"
                     alt="{{ $product->name }}"
                     class="w-full h-96 object-cover rounded-lg">
            </div>

            <!-- Thumbnail Gallery -->
            @if($product->variants->count() > 0)
                <div class="grid grid-cols-4 gap-2">
                    <!-- Main product image thumbnail -->
                    @if($product->image_path && $product->image_path !== 'products/' && file_exists(public_path('storage/' . $product->image_path)))
                        <button data-image-src="{{ asset('storage/' . $product->image_path) }}"
                                class="thumbnail-btn border-2 border-blue-500 rounded-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-20 object-cover">
                        </button>
                    @endif

                    <!-- Variant thumbnails -->
                    @foreach($product->variants as $variant)
                        @if($variant->image_path && file_exists(public_path('storage/' . $variant->image_path)))
                            <button data-image-src="{{ asset('storage/' . $variant->image_path) }}"
                                    class="thumbnail-btn border-2 border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:border-blue-500 transition-colors">
                                <img src="{{ asset('storage/' . $variant->image_path) }}"
                                     alt="Variante {{ $variant->sku }}"
                                     class="w-full h-20 object-cover">
                            </button>
                        @else
                            <button class="thumbnail-btn border-2 border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:border-blue-500 transition-colors">
                                <img src="{{ asset('img/images.png') }}"
                                     alt=""
                                     class="w-full h-20 object-cover">
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $product->name }}</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">SKU: {{ $product->sku }}</p>
            </div>

            <!-- Add to Cart Component -->
            @livewire('add-to-cart', ['productId' => $product->id])
        </div>
    </div>

    <!-- Product Description and Specifications -->
    <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Description -->
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Descripción</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                @if($product->description)
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $product->description }}</p>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">No hay descripción disponible para este producto.</p>
                @endif
            </div>
        </div>

        <!-- Specifications -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Especificaciones</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                @if($product->options->count() > 0)
                    <dl class="space-y-3">
                        @foreach($product->options as $option)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $option->name }}</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                                    @if($option->pivot && $option->pivot->value)
                                        {{ $option->pivot->value }}
                                    @else
                                        {{ $option->features->pluck('value')->join(', ') }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">No hay especificaciones disponibles.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Products Section -->
    @if($relatedProducts->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Productos Relacionados</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow flex flex-col h-full">
                        <a href="{{ route('products.show', $relatedProduct) }}" class="block">
                            @if($relatedProduct->image_path && $relatedProduct->image_path !== 'products/' && file_exists(public_path('storage/' . $relatedProduct->image_path)))
                                <img src="{{ asset('storage/' . $relatedProduct->image_path) }}"
                                     alt="{{ $relatedProduct->name }}"
                                     class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <img src="{{ asset('img/images.png') }}" alt="Sin imagen" class="w-12 h-12 opacity-50">
                                </div>
                            @endif
                        </a>
                        <div class="p-4 flex flex-col flex-grow">
                            <div class="h-32 overflow-hidden">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    <a href="{{ route('products.show', $relatedProduct) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ Str::limit($relatedProduct->name, 50) }}
                                    </a>
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">{{ $relatedProduct->sku }}</p>
                                @if($relatedProduct->description)
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                                        {{ Str::limit($relatedProduct->description, 80) }}
                                        @if(strlen($relatedProduct->description) > 80)
                                            <a href="{{ route('products.show', $relatedProduct) }}" class="text-blue-600 dark:text-blue-400 hover:underline">ver más</a>
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="mt-auto">
                                <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-3">
                                    ${{ number_format($relatedProduct->price, 0, ',', '.') }}
                                </p>
                                <a href="{{ route('products.show', $relatedProduct) }}"
                                   class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white py-2 px-4 rounded-md font-medium transition-colors text-center block">
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
function changeMainImage(src) {
    document.getElementById('main-image').src = src;
}

// Handle thumbnail clicks
document.addEventListener('DOMContentLoaded', function() {
    // Set background colors for color option buttons (if any remain, but they shouldn't)
    document.querySelectorAll('.color-option-btn').forEach(button => {
        const color = button.dataset.color;
        if (color) {
            button.style.backgroundColor = color;
        }
    });

    // Thumbnail image switching
    document.addEventListener('click', function(e) {
        if (e.target.closest('.thumbnail-btn')) {
            const button = e.target.closest('.thumbnail-btn');
            const imageSrc = button.dataset.imageSrc;
            if (imageSrc) {
                changeMainImage(imageSrc);
            }
        }
    });
});

// Listen for Livewire events to update main image
document.addEventListener('livewire:loaded', function() {
    Livewire.on('update-main-image', (data) => {
        changeMainImage(data.src);
    });
});
</script>
@endsection