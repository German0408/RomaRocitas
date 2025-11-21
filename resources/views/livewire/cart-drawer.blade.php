<!-- Cart Drawer Overlay -->
<div
    x-show="isOpen"
    x-data="{ isOpen: @entangle('isOpen') }"
    class="fixed inset-0 z-50 overflow-hidden"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <!-- Backdrop -->
    <div
        @click="isOpen = false"
        class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
    ></div>

    <!-- Drawer -->
    <div class="absolute inset-y-0 right-0 max-w-full flex">
        <div
            x-show="isOpen"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen max-w-md bg-white dark:bg-gray-800 shadow-xl"
        >
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Carrito de Compras
                    </h2>
                    <button
                        @click="isOpen = false"
                        wire:click="closeDrawer"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md p-1"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-4">
                    @if(count($items) > 0)
                        <div class="space-y-4">
                            @foreach($items as $key => $item)
                                <div class="flex items-center space-x-4 border-b border-gray-200 dark:border-gray-700 pb-4">
                                    <!-- Product Image Placeholder -->
                                    <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-md flex-shrink-0">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $item['name'] }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            ${{ number_format($item['price'], 2) }}
                                        </p>

                                        <!-- Quantity Controls -->
                                        <div class="flex items-center mt-2">
                                            <button
                                                wire:click="updateQuantity({{ $item['id'] ?? $key }}, {{ $item['quantity'] - 1 }})"
                                                :disabled="$item['quantity'] <= 1"
                                                class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </button>

                                            <input
                                                type="number"
                                                wire:model.live="quantities.{{ $item['id'] ?? $key }}"
                                                min="1"
                                                class="w-16 mx-2 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                                            />

                                            <button
                                                wire:click="updateQuantity({{ $item['id'] ?? $key }}, {{ $item['quantity'] + 1 }})"
                                                class="p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Subtotal and Remove -->
                                    <div class="flex flex-col items-end space-y-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                        </span>
                                        <button
                                            wire:click="removeItem({{ $item['id'] ?? $key }})"
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty Cart -->
                        <div class="flex flex-col items-center justify-center h-full text-center">
                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13v8a2 2 0 002 2h10a2 2 0 002-2v-3"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                Tu carrito está vacío
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">
                                Agrega algunos productos para comenzar
                            </p>
                            <button
                                @click="isOpen = false"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium transition duration-150"
                            >
                                Continuar Comprando
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                @if(count($items) > 0)
                    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-lg font-medium text-gray-900 dark:text-gray-100">Total:</span>
                            <span class="text-lg font-medium text-gray-900 dark:text-gray-100">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex space-x-3">
                            <button
                                @click="isOpen = false"
                                class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-3 rounded-md font-medium transition duration-150"
                            >
                                Continuar Comprando
                            </button>
                            <a
                                href="{{ route('cart.index') }}"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-md font-medium text-center transition duration-150"
                            >
                                Ver Carrito Completo
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>