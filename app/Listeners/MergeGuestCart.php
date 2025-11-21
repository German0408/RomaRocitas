<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MergeGuestCart
{
    protected $cartService;

    /**
     * Create the event listener.
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Check if there's a guest cart to merge
        if (session()->has('guest_cart_pending_merge')) {
            $this->cartService->mergeGuestCart();
            session()->forget('guest_cart_pending_merge');
        }
    }
}
