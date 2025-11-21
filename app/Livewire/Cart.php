<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class Cart extends Component
{
    public $items = [];
    public $total = 0;
    public $quantities = [];

    protected $cartService;

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->loadCart();
        $this->quantities = collect($this->items)->pluck('quantity', function ($item, $key) {
            return $item['id'] ?? $key;
        })->toArray();
    }

    public function updatedQuantities($value, $key)
    {
        $this->updateQuantity($key, $value);
    }

    public function addItem($productId, $variantId = null, $quantity = 1)
    {
        try {
            $this->cartService->addItem($productId, $variantId, $quantity);
            $this->loadCart();
            $this->dispatch('cart-updated');
        } catch (\Exception $e) {
            $this->addError('cart', $e->getMessage());
        }
    }

    public function updateQuantity($key, $quantity)
    {
        try {
            $this->cartService->updateQuantity($key, $quantity);
            $this->loadCart();
            $this->dispatch('cart-updated');
        } catch (\Exception $e) {
            $this->addError('cart', $e->getMessage());
        }
    }

    public function removeItem($key)
    {
        $this->cartService->removeItem($key);
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    private function loadCart()
    {
        $this->items = $this->cartService->getItems();
        $this->total = $this->cartService->getTotal();
        $this->quantities = collect($this->items)->pluck('quantity', function ($item, $key) {
            return $item['id'] ?? $key;
        })->toArray();
    }

    public function render()
    {
        return view('livewire.cart');
    }
}