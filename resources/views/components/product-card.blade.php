@props(['product'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
    <!-- Product Image -->
    <div class="aspect-w-1 aspect-h-1 bg-gray-200 dark:bg-gray-700">
        @if($product->image_path && $product->image_path !== 'products/' && file_exists(public_path('storage/' . $product->image_path)))
            <img src="{{ asset('storage/' . $product->image_path) }}"
                 alt="{{ $product->name }}"
                 class="w-full h-48 object-cover">
        @else
            <div class="w-full h-48 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                <span class="text-gray-500 dark:text-gray-400 text-sm">Sin imagen</span>
            </div>
        @endif
    </div>

    <!-- Product Info -->
    <div class="p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
            <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                {{ $product->name }}
            </a>
        </h3>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            {{ Str::limit($product->description, 100) }}
        </p>

        <div class="flex items-center justify-between mb-2">
            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">
                ${{ number_format($product->price, 0, ',', '.') }}
            </span>

            <a href="{{ route('products.show', $product) }}"
               class="bg-gray-600 hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">
                Ver Detalles
            </a>
        </div>

        <!-- Add to Cart -->
        @livewire('add-to-cart', ['productId' => $product->id])
    </div>
</div>