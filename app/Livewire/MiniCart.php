<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class MiniCart extends Component
{
    public $itemCount = 0;
    public $total = 0;
    public $isOpen = false;

    protected $cartService;
    protected $listeners = ['cart-updated' => 'loadCart'];

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $items = $this->cartService->getItems();
        $this->itemCount = collect($items)->sum('quantity');
        $this->total = $this->cartService->getTotal();
    }

    public function toggleCart()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function render()
    {
        return view('livewire.mini-cart');
    }
}