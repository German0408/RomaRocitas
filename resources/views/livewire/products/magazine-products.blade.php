<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Nuestros Productos</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Descubre nuestra amplia selección de productos de calidad en un formato de revista interactiva.</p>

        <!-- Search and Category Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filtros</h2>
                <button wire:click="clearFilters" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    Limpiar filtros
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }} ({{ $category['products_count'] }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Magazine Container -->
    <div class="magazine-container relative">

        <!-- Product Carousel Container -->
        <div id="product-carousel" class="carousel-container mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden mb-12" style="max-width: 900px;" data-products="{{ json_encode($carouselProducts) }}">
            <!-- Loading state -->
            <div class="text-center py-12" id="carousel-loading">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Cargando productos destacados...</p>
            </div>
        </div>

        <!-- Products Grid Section -->
        <div class="products-grid-section">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Todos los Productos</h2>

            <!-- Products Grid Container -->
            <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-products="{{ json_encode($paginatedProducts->items()) }}">
                @if(!$paginatedProducts->isEmpty())
                    @foreach($paginatedProducts as $product)
                        <div class="product-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300" role="article">
                            <div class="aspect-w-1 aspect-h-1 bg-gray-100 dark:bg-gray-700">
                                <img src="{{ $product->image_path ?? '/img/images.png' }}"
                                     alt="{{ $product->name }}"
                                     class="w-full h-48 object-cover"
                                     loading="lazy">
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 line-clamp-2">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ $product->description }}</p>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xl font-bold text-blue-600">${{ $product->price }}</span>
                                    <span class="text-sm text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">Stock: {{ $product->stock }}</span>
                                </div>
                                <button class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors view-details" data-product-id="{{ $product->id }}" aria-label="Ver detalles de {{ $product->name }}">
                                    Ver detalles
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Pagination -->
            @if($paginatedProducts->hasPages())
                <div class="mt-8">
                    {{ $paginatedProducts->links() }}
                </div>
            @endif

            <!-- No Products Message -->
            @if($paginatedProducts->isEmpty() && !$loading)
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-5.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No se encontraron productos</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No hay productos que coincidan con los filtros aplicados.</p>
                    <div class="mt-6">
                        <button wire:click="clearFilters" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Limpiar filtros
                        </button>
                    </div>
                </div>
            @endif

            <!-- Loading Indicator -->
            <div wire:loading class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Cargando productos...</p>
            </div>
        </div>

        </div>

        <!-- Product Details Modal (for mobile single view) -->
    <div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 id="modal-title" class="text-xl font-bold text-gray-900 dark:text-gray-100"></h3>
                        <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="modal-content">
                        <!-- Product details will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Debug: Check PageFlip availability immediately
        console.log('Script loaded - checking PageFlip availability...');
        console.log('window.PageFlip:', typeof window.PageFlip);
        console.log('PageFlip in global scope:', typeof PageFlip);

        // Update debug info
        document.addEventListener('DOMContentLoaded', function() {
            const debugEl = document.getElementById('pageflip-debug');
            const statusEl = document.getElementById('pageflip-status');
            if (debugEl && statusEl) {
                debugEl.style.display = 'block';
                statusEl.textContent = `window.PageFlip: ${typeof window.PageFlip}, PageFlip: ${typeof PageFlip}`;
            }
        });

        let currentSlideIndex = 0;
        let products = [];
        let carouselInterval = null;

        console.log('Setting up carousel event listeners...');

        document.addEventListener('livewire:loaded', function () {
            console.log('livewire:loaded event fired!');
            initializeCarousel();
            // Grid is now rendered server-side, no need for JavaScript initialization
        });

        // Reinitialize when products change
        document.addEventListener('livewire:updated', function () {
            console.log('livewire:updated event fired!');
            initializeCarousel();
            // Grid is re-rendered server-side, no need for JavaScript rebuild
        });

        console.log('Event listeners set up complete');

        // Fallback: try to initialize after a delay if livewire:loaded doesn't fire
        setTimeout(() => {
            console.log('Fallback timeout reached - checking if carousel is initialized...');
            const carouselElement = document.getElementById('product-carousel');
            if (carouselElement && carouselElement.querySelector('#carousel-loading')) {
                console.log('Carousel still loading, forcing initialization...');
                initializeCarousel();
            } else {
                console.log('Carousel already initialized or no loading element found');
            }
        }, 3000);

        // Update page indicators for grid view - removed since we now use proper pagination

        function initializeCarousel() {
            console.log('🎠 initializeCarousel() function called!');

            const carouselElement = document.getElementById('product-carousel');
            if (!carouselElement) {
                console.error('❌ Carousel element not found!');
                return;
            }

            console.log('✅ Carousel element found');

            products = JSON.parse(carouselElement.dataset.products || '[]');
            console.log('📦 Products data parsed, count:', products.length);

            if (products.length === 0) {
                console.log('⚠️ No products to display');
                return;
            }

            console.log('🚀 Initializing carousel with', products.length, 'products');

            // Clear loading state and build carousel
            carouselElement.innerHTML = '';
            buildCarousel(carouselElement, products);

            // Setup navigation
            setupCarouselNavigation();

            // Auto-play carousel (optional)
            startAutoPlay();

            console.log('✅ Carousel initialized successfully!');
        }

        function buildCarousel(container, products) {
            // Create carousel structure
            const carouselHTML = `
                <div class="carousel-wrapper relative overflow-hidden">
                    <div class="carousel-slides flex transition-transform duration-500 ease-in-out" id="carousel-slides">
                        ${products.map((product, index) => createCarouselSlide(product, index)).join('')}
                    </div>

                    <!-- Navigation buttons -->
                    <button class="carousel-nav carousel-prev absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-75 transition-all z-10" aria-label="Producto anterior">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <button class="carousel-nav carousel-next absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-75 transition-all z-10" aria-label="Producto siguiente">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <!-- Indicators -->
                    <div class="carousel-indicators absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
                        ${products.map((_, index) => `
                            <button class="carousel-indicator w-3 h-3 rounded-full ${index === 0 ? 'bg-blue-600' : 'bg-gray-400'} transition-colors" data-slide="${index}" aria-label="Ir al producto ${index + 1}"></button>
                        `).join('')}
                    </div>
                </div>
            `;

            container.innerHTML = carouselHTML;
        }

        function createCarouselSlide(product, index) {
            return `
                <div class="carousel-slide flex-shrink-0 w-full" data-slide="${index}">
                    <div class="grid md:grid-cols-2 gap-0 min-h-[400px]">
                        <!-- Product Image -->
                        <div class="bg-gray-100 dark:bg-gray-700 flex items-center justify-center p-8">
                            <img src="${product.image_path || '/img/images.png'}"
                                 alt="${product.name}"
                                 class="max-w-full max-h-80 object-contain rounded-lg shadow-lg"
                                 loading="lazy">
                        </div>

                        <!-- Product Details -->
                        <div class="bg-white dark:bg-gray-800 p-8 flex flex-col justify-center">
                            <div class="max-w-md mx-auto">
                                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">${product.name}</h2>
                                <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">${product.description}</p>

                                <div class="space-y-4 mb-6">
                                    <div class="flex justify-between items-center">
                                        <span class="text-3xl font-bold text-blue-600">$${product.price}</span>
                                        <span class="text-sm text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">Stock: ${product.stock}</span>
                                    </div>

                                    <div class="text-sm text-gray-500 space-y-1">
                                        <p><strong>Categoría:</strong> ${product.category?.name || 'N/A'}</p>
                                        <p><strong>SKU:</strong> ${product.sku}</p>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <button class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors font-medium view-details" data-product-id="${product.id}">
                                        Ver detalles
                                    </button>
                                    <button class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-3 px-6 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function setupCarouselNavigation() {
            const prevBtn = document.querySelector('.carousel-prev');
            const nextBtn = document.querySelector('.carousel-next');
            const indicators = document.querySelectorAll('.carousel-indicator');

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    goToSlide(currentSlideIndex - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    goToSlide(currentSlideIndex + 1);
                });
            }

            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    goToSlide(index);
                });
            });

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    goToSlide(currentSlideIndex - 1);
                } else if (e.key === 'ArrowRight') {
                    goToSlide(currentSlideIndex + 1);
                }
            });

            // Touch/swipe support for mobile
            let startX = 0;
            let endX = 0;

            const carouselSlides = document.getElementById('carousel-slides');
            if (carouselSlides) {
                carouselSlides.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                });

                carouselSlides.addEventListener('touchend', (e) => {
                    endX = e.changedTouches[0].clientX;
                    const diff = startX - endX;

                    if (Math.abs(diff) > 50) { // Minimum swipe distance
                        if (diff > 0) {
                            goToSlide(currentSlideIndex + 1); // Swipe left - next slide
                        } else {
                            goToSlide(currentSlideIndex - 1); // Swipe right - previous slide
                        }
                    }
                });
            }
        }

        function goToSlide(index) {
            const slides = document.querySelectorAll('.carousel-slide');
            const indicators = document.querySelectorAll('.carousel-indicator');
            const slidesContainer = document.getElementById('carousel-slides');

            if (!slides.length || !slidesContainer) return;

            // Handle wrap-around
            if (index < 0) {
                index = slides.length - 1;
            } else if (index >= slides.length) {
                index = 0;
            }

            currentSlideIndex = index;

            // Move slides
            slidesContainer.style.transform = `translateX(-${index * 100}%)`;

            // Update indicators
            indicators.forEach((indicator, i) => {
                if (i === index) {
                    indicator.classList.remove('bg-gray-400');
                    indicator.classList.add('bg-blue-600');
                } else {
                    indicator.classList.remove('bg-blue-600');
                    indicator.classList.add('bg-gray-400');
                }
            });

            // Update slide counter
            // Removed since carousel navigation controls were removed

            // Update navigation buttons
            const prevBtn = document.querySelector('.carousel-prev');
            const nextBtn = document.querySelector('.carousel-next');

            if (prevBtn) prevBtn.disabled = false;
            if (nextBtn) nextBtn.disabled = false;

            // Track interaction
            console.log('Carousel navigation:', { from: currentSlideIndex, to: index });
        }

        function startAutoPlay() {
            if (carouselInterval) {
                clearInterval(carouselInterval);
            }

            carouselInterval = setInterval(() => {
                goToSlide(currentSlideIndex + 1);
            }, 5000); // Change slide every 5 seconds

            // Pause on hover
            const carousel = document.getElementById('product-carousel');
            if (carousel) {
                carousel.addEventListener('mouseenter', () => {
                    clearInterval(carouselInterval);
                });

                carousel.addEventListener('mouseleave', () => {
                    startAutoPlay();
                });
            }
        }

        function initializeProductsGrid() {
            console.log('📋 initializeProductsGrid() function called!');

            // Grid is now rendered server-side, no need to build it with JavaScript
            // But we still need to handle modal functionality
            console.log('✅ Products grid handled server-side');
        }

        // Handle view details buttons (for modal functionality)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-details')) {
                const productId = e.target.dataset.productId;
                // Find product data from carousel or grid
                let product = null;

                // Try to find in carousel products first
                const carouselProducts = JSON.parse(document.getElementById('product-carousel')?.dataset.products || '[]');
                product = carouselProducts.find(p => p.id == productId);

                if (!product) {
                    // Try to find in current grid products
                    // Since grid is rendered server-side, we can't easily get the data
                    // For now, just show a basic modal
                    showBasicModal(productId);
                    return;
                }

                showProductModal(product);
            }
        });

        function showBasicModal(productId) {
            const modal = document.getElementById('product-modal');
            const title = document.getElementById('modal-title');
            const content = document.getElementById('modal-content');

            title.textContent = 'Producto #' + productId;
            content.innerHTML = `
                <p>Detalles del producto próximamente...</p>
                <button class="mt-6 w-full bg-blue-600 text-white py-3 px-4 rounded hover:bg-blue-700">
                    Agregar al carrito
                </button>
            `;

            modal.classList.remove('hidden');
        }

        function showProductModal(product) {
            const modal = document.getElementById('product-modal');
            const title = document.getElementById('modal-title');
            const content = document.getElementById('modal-content');

            title.textContent = product.name;
            content.innerHTML = `
                <img src="${product.image_path || '/img/images.png'}" alt="${product.name}" class="w-full h-48 object-cover rounded mb-4">
                <p class="text-gray-600 dark:text-gray-400 mb-4">${product.description}</p>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-2xl font-bold text-blue-600">$${product.price}</span>
                    <span class="text-sm text-gray-500">Stock: ${product.stock}</span>
                </div>
                <div class="text-sm text-gray-500">
                    <p>Categoría: ${product.category || 'N/A'}</p>
                    <p>SKU: ${product.sku}</p>
                </div>
                <button class="mt-6 w-full bg-blue-600 text-white py-3 px-4 rounded hover:bg-blue-700">
                    Agregar al carrito
                </button>
            `;

            modal.classList.remove('hidden');
        }

        // Close modal
        document.getElementById('close-modal').addEventListener('click', () => {
            document.getElementById('product-modal').classList.add('hidden');
        });

        document.getElementById('product-modal').addEventListener('click', (e) => {
            if (e.target.id === 'product-modal') {
                e.target.classList.add('hidden');
            }
        });
    </script>
    @endpush
</div>