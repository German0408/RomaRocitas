<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Variant;
use App\Services\CartService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddToCart extends Component
{
    public $productId;
    public $quantity = 1;
    public $selectedFeatures = [];
    public $selectedVariantId = null;
    public $product;
    public $options = [];
    public $currentPrice;
    public $availableStock = 0;
    public $isAdding = false;
    public $currentImage;

    protected $listeners = ['variantSelected' => 'setSelectedVariant'];

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->loadProduct();
        $this->currentImage = $this->product->image_path && $this->product->image_path !== 'products/' && file_exists(public_path('storage/' . $this->product->image_path))
            ? asset('storage/' . $this->product->image_path)
            : asset('img/images.png');
        $this->updateVariantAndPrice();
    }

    public function loadProduct()
    {
        $this->product = Product::with(['options.features', 'variants.features'])->findOrFail($this->productId);
        $this->options = $this->product->options;
        $this->currentPrice = $this->product->price;
    }

    public function updatedSelectedFeatures()
    {
        $this->updateVariantAndPrice();
    }

    public function updateVariantAndPrice()
    {
        // Find variant that matches all selected features
        $selectedFeatureIds = array_filter($this->selectedFeatures);

        if (empty($selectedFeatureIds)) {
            $this->selectedVariantId = null;
            $this->availableStock = $this->product->stock;
            return;
        }

        $matchingVariant = null;
        foreach ($this->product->variants as $variant) {
            $variantFeatureIds = $variant->features->pluck('id')->toArray();
            if (count($selectedFeatureIds) === count($variantFeatureIds) &&
                empty(array_diff($selectedFeatureIds, $variantFeatureIds))) {
                $matchingVariant = $variant;
                break;
            }
        }

        $this->selectedVariantId = $matchingVariant ? $matchingVariant->id : null;
        $this->availableStock = $matchingVariant ? $this->product->stock : $this->product->stock; // Assuming stock is on product, not variant

        // Update current image if variant has one
        if ($matchingVariant && $matchingVariant->image_path && file_exists(public_path('storage/' . $matchingVariant->image_path))) {
            $this->currentImage = asset('storage/' . $matchingVariant->image_path);
            $this->dispatch('update-main-image', ['src' => $this->currentImage]);
        } elseif (!$matchingVariant) {
            // Reset to product image if no variant selected
            $this->currentImage = $this->product->image_path && $this->product->image_path !== 'products/' && file_exists(public_path('storage/' . $this->product->image_path))
                ? asset('storage/' . $this->product->image_path)
                : asset('img/images.png');
            $this->dispatch('update-main-image', ['src' => $this->currentImage]);
        }
    }

    public function setSelectedVariant($variantId)
    {
        $this->selectedVariantId = $variantId;
        $this->updateVariantAndPrice();
    }

    public function addToCart()
    {
        $this->validateSelection();

        $this->isAdding = true;

        try {
            $cartService = app(CartService::class);
            $cartService->addItem($this->productId, $this->selectedVariantId, $this->quantity);

            $this->dispatch('show-toast', [
                'message' => __('cart.product_added'),
                'type' => 'success'
            ]);

            $this->dispatch('cart-updated'); // To update cart components

            $this->reset(['quantity']);
            $this->quantity = 1;

        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'message' => $e->getMessage(),
                'type' => 'error'
            ]);
        } finally {
            $this->isAdding = false;
        }
    }

    public function updateQuantity($change)
    {
        $newQuantity = $this->quantity + $change;
        if ($newQuantity >= 1 && $newQuantity <= $this->availableStock) {
            $this->quantity = $newQuantity;
        }
    }

    public function validateSelection()
    {
        // Check if all required options are selected
        foreach ($this->options as $option) {
            if (!isset($this->selectedFeatures[$option->id])) {
                throw new \Exception(__('cart.select_option', ['option' => $option->name]));
            }
        }

        // Check stock
        if ($this->quantity > $this->availableStock) {
            throw new \Exception(__('cart.insufficient_stock', ['stock' => $this->availableStock]));
        }

        // If variants exist and none selected, but options are selected, ensure variant exists
        if ($this->product->variants->count() > 0 && !$this->selectedVariantId && !empty(array_filter($this->selectedFeatures))) {
            throw new \Exception(__('cart.variant_not_found'));
        }
    }

    public function render()
    {
        return view('livewire.add-to-cart');
    }
}