<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Nuestros Productos</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Descubre nuestra amplia selección de productos de calidad.</p>

        <!-- Search and Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar productos</label>
                    <input type="text" wire:model.debounce.300ms="search" placeholder="Nombre, descripción o SKU..."
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoría</label>
                    <select wire:model.live="category_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Todas las categorías</option>
                        @foreach($categoriesWithCounts->groupBy('family.name') as $familyName => $categories)
                            <optgroup label="{{ $familyName }}">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->products_count }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <!-- Min Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio mínimo</label>
                    <input type="number" wire:model.live="min_price" placeholder="0" min="0" step="0.01"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>

                <!-- Max Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio máximo</label>
                    <input type="number" wire:model.live="max_price" placeholder="0" min="0" step="0.01"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
            </div>

            <!-- Sorting -->
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ordenar por:</span>
                <select wire:model.live="sort_by" class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="name">Nombre</option>
                    <option value="price">Precio</option>
                    <option value="created_at">Más recientes</option>
                </select>
                <select wire:model.live="sort_direction" class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="asc">Ascendente</option>
                    <option value="desc">Descendente</option>
                </select>
                @if($search || $category_id || $min_price || $max_price)
                    <button wire:click="$set('search', ''); $set('category_id', ''); $set('min_price', ''); $set('max_price', ''); $set('sort_by', 'name'); $set('sort_direction', 'asc')" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">Limpiar filtros</button>
                @endif
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <x-product-card :product="$product" />
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No se encontraron productos que coincidan con los criterios de búsqueda.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-8 flex justify-center">
            <div class="flex space-x-1">
                {{-- Previous Button --}}
                @if($products->onFirstPage())
                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">« Anterior</span>
                @else
                    <button wire:click="gotoPage({{ $products->currentPage() - 1 }})" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">« Anterior</button>
                @endif

                {{-- Page Numbers --}}
                @php
                    $currentPage = $products->currentPage();
                    $lastPage = $products->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);

                    // Adjust to always show 5 pages when possible
                    if ($end - $start < 4) {
                        if ($start == 1) {
                            $end = min($lastPage, $start + 4);
                        } elseif ($end == $lastPage) {
                            $start = max(1, $end - 4);
                        }
                    }
                @endphp

                @for($page = $start; $page <= $end; $page++)
                    @if($page == $currentPage)
                        <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">{{ $page }}</button>
                    @endif
                @endfor

                {{-- Next Button --}}
                @if($products->hasMorePages())
                    <button wire:click="gotoPage({{ $products->currentPage() + 1 }})" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">Siguiente »</button>
                @else
                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">Siguiente »</span>
                @endif
            </div>
        </div>
    @endif
</div>
