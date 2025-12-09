<div class="relative">
    <!-- Mini Cart Button -->
    <button
        @click="toggleCart"
        wire:click="toggleCart"
        class="relative flex items-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13v8a2 2 0 002 2h10a2 2 0 002-2v-3"></path>
        </svg>
        @if($itemCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                {{ $itemCount }}
            </span>
        @endif
    </button>

    <!-- Mini Cart Dropdown -->
    <div
        x-show="isOpen"
        @click.away="isOpen = false"
        x-data="{ isOpen: @entangle('isOpen') }"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50 border border-gray-200 dark:border-gray-700"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
    >
        <div class="p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Carrito de Compras</h3>

            @if($itemCount > 0)
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    @foreach($this->cartService->getItems() as $key => $item)
                        <div class="flex items-center space-x-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $item['name'] }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    ${{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}
                                </p>
                            </div>
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                ${{ number_format($item['price'] * $item['quantity'], 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">Total:</span>
                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">${{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('cart.index') }}" class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-md text-center text-sm font-medium transition duration-150">
                            Ver Carrito
                        </a>
                        @auth
                            <a href="#" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-center text-sm font-medium transition duration-150">
                                Pagar
                            </a>
                        @else
                            <button wire:click="$dispatch('open-login-modal')" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-center text-sm font-medium transition duration-150">
                                Iniciar Sesión para Pagar
                            </button>
                        @endauth
                    </div>
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13v8a2 2 0 002 2h10a2 2 0 002-2v-3"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tu carrito está vacío</p>
                </div>
            @endif
        </div>
    </div>
</div>