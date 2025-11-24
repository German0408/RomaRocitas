<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Nuestros Productos</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Descubre nuestra amplia selección de productos de calidad en un formato de revista interactiva.</p>

        <!-- Search and Category Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
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
        <!-- Navigation Controls -->
        <div class="magazine-nav flex justify-between items-center mb-4">
            <button id="prev-page" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                ← Anterior
            </button>
            <div class="page-indicator text-sm text-gray-600 dark:text-gray-400">
                Página <span id="current-page">1</span> de <span id="total-pages">1</span>
            </div>
            <button id="next-page" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Siguiente →
            </button>
        </div>

        <!-- Magazine Flip Container -->
        <div id="magazine" class="magazine mx-auto shadow-2xl" data-products="{{ json_encode($products) }}"></div>

        <!-- Loading Indicator -->
        <div wire:loading class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Cargando productos...</p>
        </div>

        <!-- Load More Button (for infinite scroll fallback) -->
        @if($hasMorePages && !$loading)
            <div class="text-center mt-8">
                <button wire:click="loadMore" class="px-6 py-3 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Cargar más productos
                </button>
            </div>
        @endif
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
    <script type="module">
        import { PageFlip } from 'page-flip';

        let pageFlipInstance = null;

        document.addEventListener('livewire:loaded', function () {
            initializeMagazine();
        });

        // Reinitialize when products change
        document.addEventListener('livewire:updated', function () {
            if (pageFlipInstance) {
                pageFlipInstance.destroy();
            }
            initializeMagazine();
        });

        function initializeMagazine() {
            const magazineElement = document.getElementById('magazine');
            if (!magazineElement) return;

            const products = JSON.parse(magazineElement.dataset.products || '[]');
            if (products.length === 0) return;
            if (!magazineElement) return;

            const prevBtn = document.getElementById('prev-page');
            const nextBtn = document.getElementById('next-page');
            const currentPageSpan = document.getElementById('current-page');
            const totalPagesSpan = document.getElementById('total-pages');

            // Clear existing content
            magazineElement.innerHTML = '';

            // Determine if mobile or desktop
            const isMobile = window.innerWidth < 768;

            // Create pages from products
            const pages = createPages(products, isMobile);

            // Initialize PageFlip
            pageFlipInstance = new PageFlip(magazineElement, {
                width: isMobile ? 300 : 600, // Single product width for mobile, double for desktop
                height: 400,
                size: isMobile ? 'fixed' : 'stretch',
                minWidth: 200,
                maxWidth: 1000,
                minHeight: 300,
                maxHeight: 600,
                showCover: false,
                useMouseEvents: true,
                usePortrait: isMobile,
                mobileScrollSupport: true
            });

            // Load pages
            pageFlipInstance.loadFromHTML(pages);

            // Update navigation
            function updateNavigation() {
                if (!pageFlipInstance) return;
                const current = pageFlipInstance.getCurrentPageIndex() + 1;
                const total = pageFlipInstance.getPageCount();

                if (currentPageSpan) currentPageSpan.textContent = current;
                if (totalPagesSpan) totalPagesSpan.textContent = total;

                if (prevBtn) prevBtn.disabled = current <= 1;
                if (nextBtn) nextBtn.disabled = current >= total;
            }

            // Event listeners
            pageFlipInstance.on('flip', updateNavigation);

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    pageFlipInstance?.flipPrev();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    pageFlipInstance?.flipNext();
                });
            }

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    pageFlipInstance?.flipPrev();
                } else if (e.key === 'ArrowRight') {
                    pageFlipInstance?.flipNext();
                }
            });

            // Mouse wheel navigation
            magazineElement.addEventListener('wheel', (e) => {
                e.preventDefault();
                if (e.deltaY > 0) {
                    pageFlipInstance?.flipNext();
                } else {
                    pageFlipInstance?.flipPrev();
                }
            });

            // Initial navigation update
            updateNavigation();

            // Handle window resize
            window.addEventListener('resize', () => {
                const newIsMobile = window.innerWidth < 768;
                if (newIsMobile !== isMobile) {
                    // Reinitialize on layout change
                    location.reload();
                }
            });
        }

        function createPages(products, isMobile) {
            const pages = [];

            // For Phase 3, use single product per page
            products.forEach(product => {
                pages.push(createProductPageHTML(product, isMobile));
            });

            return pages;
        }

        function createProductPageHTML(product, isMobile = false) {
            return `
                <div class="magazine-page bg-white dark:bg-gray-800 p-6 rounded shadow h-full flex flex-col">
                    <div class="product-image mb-4 flex-1">
                        <img src="${product.image_path || '/images/placeholder.jpg'}"
                             alt="${product.name}"
                             class="w-full h-full object-cover rounded">
                    </div>
                    <div class="product-info">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">${product.name}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${product.description}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-blue-600">$${product.price}</span>
                            <span class="text-sm text-gray-500">Stock: ${product.stock}</span>
                        </div>
                        ${isMobile ? `<button class="mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 view-details" data-product-id="${product.id}">Ver detalles</button>` : ''}
                    </div>
                </div>
            `;
        }

        function showProductModal(product) {
            const modal = document.getElementById('product-modal');
            const title = document.getElementById('modal-title');
            const content = document.getElementById('modal-content');

            title.textContent = product.name;
            content.innerHTML = `
                <img src="${product.image_path || '/images/placeholder.jpg'}" alt="${product.name}" class="w-full h-48 object-cover rounded mb-4">
                <p class="text-gray-600 dark:text-gray-400 mb-4">${product.description}</p>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-2xl font-bold text-blue-600">$${product.price}</span>
                    <span class="text-sm text-gray-500">Stock: ${product.stock}</span>
                </div>
                <div class="text-sm text-gray-500">
                    <p>Categoría: ${product.category || 'N/A'}</p>
                    <p>Subcategoría: ${product.subcategory || 'N/A'}</p>
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