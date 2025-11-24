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
        <!-- Navigation Controls -->
        <div class="magazine-nav flex justify-between items-center mb-4" role="navigation" aria-label="Navegación de revista">
            <button id="prev-page" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Página anterior">
                ← Anterior
            </button>
            <div class="page-indicator text-sm text-gray-600 dark:text-gray-400" aria-live="polite">
                Página <span id="current-page">1</span> de <span id="total-pages">1</span>
            </div>
            <button id="next-page" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Página siguiente">
                Siguiente →
            </button>
        </div>

        <!-- Magazine Flip Container -->
        <div id="magazine" class="magazine mx-auto shadow-2xl" data-products="{{ json_encode($products) }}"></div>

        <!-- No Products Message -->
        @if(empty($products) && !$loading)
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

            // Initialize PageFlip with error handling
            try {
                pageFlipInstance = new PageFlip(magazineElement, {
                    width: isMobile ? 320 : 800,
                    height: 450,
                    size: isMobile ? 'fixed' : 'stretch',
                    minWidth: 200,
                    maxWidth: 1200,
                    minHeight: 300,
                    maxHeight: 600,
                    showCover: false,
                    useMouseEvents: true,
                    usePortrait: isMobile,
                    mobileScrollSupport: true,
                    flippingTime: 600, // Smooth animation
                    drawShadow: true,
                    autoSize: true
                });

                // Load pages
                pageFlipInstance.loadFromHTML(pages);
            } catch (error) {
                console.error('Error initializing PageFlip:', error);
                // Fallback: show products in a simple list
                showFallbackView(products, magazineElement);
                return;
            }

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
            pageFlipInstance.on('flip', (e) => {
                updateNavigation();
                preloadAdjacentPages(e.data);
                trackInteraction('flip', { from: e.data.oldPage, to: e.data.newPage });
            });

            pageFlipInstance.on('changeState', (e) => {
                trackInteraction('state_change', { state: e.data });
            });

            pageFlipInstance.on('changeOrientation', (e) => {
                trackInteraction('orientation_change', { orientation: e.data });
            });

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
                    if (pageFlipInstance) {
                        pageFlipInstance.destroy();
                    }
                    initializeMagazine();
                }
            });
        }

        function createPages(products, isMobile) {
            const pages = [];

            if (isMobile) {
                // Mobile: single product per page
                products.forEach(product => {
                    pages.push(createProductPageHTML(product, true));
                });
            } else {
                // Desktop: two products per page (magazine spread)
                for (let i = 0; i < products.length; i += 2) {
                    const product1 = products[i];
                    const product2 = products[i + 1] || null; // Handle odd number of products
                    pages.push(createDualProductPageHTML(product1, product2));
                }
            }

            return pages;
        }

        function createProductPageHTML(product, isMobile = false) {
            return `
                <div class="magazine-page bg-white dark:bg-gray-800 p-6 rounded shadow h-full flex flex-col" role="article" aria-labelledby="product-title-${product.id}">
                    <div class="product-image mb-4 flex-1">
                        <img src="${product.image_path || '/images/placeholder.jpg'}"
                             alt="${product.name}"
                             loading="lazy"
                             class="w-full h-full object-cover rounded">
                    </div>
                    <div class="product-info">
                        <h3 id="product-title-${product.id}" class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">${product.name}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${product.description}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-blue-600">$${product.price}</span>
                            <span class="text-sm text-gray-500">Stock: ${product.stock}</span>
                        </div>
                        ${isMobile ? `<button class="mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 view-details" data-product-id="${product.id}" aria-label="Ver detalles de ${product.name}">Ver detalles</button>` : ''}
                    </div>
                </div>
            `;
        }

        function createDualProductPageHTML(product1, product2) {
            const product2HTML = product2 ? `
                <div class="flex-1 ml-4" role="article" aria-labelledby="product-title-${product2.id}">
                    <div class="product-image mb-4 flex-1">
                        <img src="${product2.image_path || '/images/placeholder.jpg'}"
                             alt="${product2.name}"
                             loading="lazy"
                             class="w-full h-full object-cover rounded">
                    </div>
                    <div class="product-info">
                        <h3 id="product-title-${product2.id}" class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">${product2.name}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${product2.description}</p>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xl font-bold text-blue-600">$${product2.price}</span>
                            <span class="text-sm text-gray-500">Stock: ${product2.stock}</span>
                        </div>
                        <button class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 view-details" data-product-id="${product2.id}" aria-label="Ver detalles de ${product2.name}">Ver detalles</button>
                    </div>
                </div>
            ` : '<div class="flex-1 ml-4"></div>'; // Empty space for odd products

            return `
                <div class="magazine-page bg-white dark:bg-gray-800 p-6 rounded shadow h-full flex" role="region" aria-label="Página de revista con productos">
                    <div class="flex-1" role="article" aria-labelledby="product-title-${product1.id}">
                        <div class="product-image mb-4 flex-1">
                            <img src="${product1.image_path || '/images/placeholder.jpg'}"
                                 alt="${product1.name}"
                                 loading="lazy"
                                 class="w-full h-full object-cover rounded">
                        </div>
                        <div class="product-info">
                            <h3 id="product-title-${product1.id}" class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">${product1.name}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${product1.description}</p>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xl font-bold text-blue-600">$${product1.price}</span>
                                <span class="text-sm text-gray-500">Stock: ${product1.stock}</span>
                            </div>
                            <button class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 view-details" data-product-id="${product1.id}" aria-label="Ver detalles de ${product1.name}">Ver detalles</button>
                        </div>
                    </div>
                    ${product2HTML}
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

        function showFallbackView(products, container) {
            container.innerHTML = `
                <div class="fallback-view grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                    ${products.map(product => `
                        <div class="product-card bg-white dark:bg-gray-800 p-4 rounded shadow" role="article">
                            <img src="${product.image_path || '/images/placeholder.jpg'}" alt="${product.name}" loading="lazy" class="w-full h-48 object-cover rounded mb-4">
                            <h3 class="text-lg font-bold mb-2">${product.name}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${product.description}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-blue-600">$${product.price}</span>
                                <button class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700" aria-label="Ver detalles de ${product.name}">Ver detalles</button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        function preloadAdjacentPages(pageData) {
            const currentIndex = pageData.newPage;
            const preloadIndices = [currentIndex - 1, currentIndex + 1].filter(i => i >= 0 && i < pageFlipInstance.getPageCount());

            preloadIndices.forEach(index => {
                const pageElement = pageFlipInstance.getPage(index);
                if (pageElement) {
                    const images = pageElement.querySelectorAll('img');
                    images.forEach(img => {
                        if (!img.complete && img.src) {
                            const preloadImg = new Image();
                            preloadImg.src = img.src;
                        }
                    });
                }
            });
        }

        function trackInteraction(eventType, data) {
            // Analytics tracking - can be integrated with Google Analytics or similar
            console.log('Magazine interaction:', eventType, data);

            // Example: Send to Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'magazine_' + eventType, {
                    event_category: 'magazine',
                    event_label: JSON.stringify(data)
                });
            }
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
    @endpush
</div>