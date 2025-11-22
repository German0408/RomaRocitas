<div class="add-to-cart-component">
    <!-- Quantity Selector -->
    <div class="flex items-center mb-4">
        <label class="mr-2 text-sm font-medium">{{ __('cart.quantity') }}:</label>
        <div class="flex items-center border rounded">
            <button
                type="button"
                wire:click="updateQuantity(-1)"
                class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
                :disabled="quantity <= 1"
            >
                -
            </button>
            <span class="px-3 py-1 border-l border-r">{{ $quantity }}</span>
            <button
                type="button"
                wire:click="updateQuantity(1)"
                class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
                :disabled="quantity >= availableStock"
            >
                +
            </button>
        </div>
        <span class="ml-2 text-sm text-gray-500">{{ __('cart.available', ['stock' => $availableStock]) }}</span>
    </div>

    <!-- Variant Selection -->
    @if($options->count() > 0)
        <div class="mb-4">
            <h4 class="text-sm font-medium mb-2">{{ __('cart.options') }}:</h4>
            @foreach($options as $option)
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">{{ $option->name }}</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($option->features as $feature)
                            @if($option->type == 2)
                                <!-- Color option -->
                                <button
                                    type="button"
                                    wire:click="$set('selectedFeatures.{{ $option->id }}', {{ $feature->id }})"
                                    class="w-10 h-10 rounded-full border-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ isset($selectedFeatures[$option->id]) && $selectedFeatures[$option->id] == $feature->id ? 'ring-2 ring-blue-500 ring-offset-2 scale-110' : 'border-gray-300 dark:border-gray-600' }}"
                                    :style="`background-color: {{ $feature->value }}`"
                                    title="{{ $feature->description }}">
                                </button>
                            @else
                                <!-- Text option -->
                                <button
                                    type="button"
                                    wire:click="$set('selectedFeatures.{{ $option->id }}', {{ $feature->id }})"
                                    class="px-3 py-1 border rounded text-sm hover:bg-blue-50 {{ isset($selectedFeatures[$option->id]) && $selectedFeatures[$option->id] == $feature->id ? 'bg-blue-100 border-blue-300' : 'border-gray-300' }}">
                                    {{ $feature->value }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Price Display -->
    <div class="mb-4">
        <span class="text-lg font-bold text-green-600">${{ number_format($currentPrice, 0, ',', '.') }}</span>
    </div>

    <!-- Add to Cart Button -->
    <button
        type="button"
        wire:click="addToCart"
        wire:loading.attr="disabled"
        class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center"
        :disabled="isAdding"
    >
        <span wire:loading.remove>{{ __('cart.add_to_cart') }}</span>
        <span wire:loading class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('cart.adding') }}
        </span>
    </button>

    <!-- Selected Variant Info -->
    @if($selectedVariantId)
        <div class="mt-2 text-sm text-gray-600">
            {{ __('cart.selected_variant', ['sku' => $product->variants->find($selectedVariantId)?->sku]) }}
        </div>
    @endif
</div>