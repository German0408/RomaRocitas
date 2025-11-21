<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartDrawer extends Component
{
    public $isOpen = false;
    public $items = [];
    public $total = 0;
    public $quantities = [];

    protected $cartService;
    protected $listeners = ['open-cart-drawer' => 'openDrawer', 'cart-updated' => 'loadCart'];

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->loadCart();
    }

    public function openDrawer()
    {
        $this->isOpen = true;
        $this->loadCart();
    }

    public function closeDrawer()
    {
        $this->isOpen = false;
    }

    public function loadCart()
    {
        $this->items = $this->cartService->getItems();
        $this->total = $this->cartService->getTotal();
        $this->quantities = collect($this->items)->pluck('quantity', function ($item, $key) {
            return $item['id'] ?? $key;
        })->toArray();
    }

    public function updatedQuantities($value, $key)
    {
        $this->updateQuantity($key, $value);
    }

    public function updateQuantity($key, $quantity)
    {
        try {
            $this->cartService->updateQuantity($key, $quantity);
            $this->loadCart();
            $this->dispatch('cart-updated');
            $this->dispatch('show-toast', ['message' => 'Cantidad actualizada', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function removeItem($key)
    {
        $this->cartService->removeItem($key);
        $this->loadCart();
        $this->dispatch('cart-updated');
        $this->dispatch('show-toast', ['message' => 'Producto eliminado', 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.cart-drawer');
    }
}