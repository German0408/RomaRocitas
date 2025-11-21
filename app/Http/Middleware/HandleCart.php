<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleCart
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user just logged in, merge guest cart
        if (Auth::check() && session()->has('guest_cart_merged')) {
            $this->cartService->mergeGuestCart();
            session()->forget('guest_cart_merged');
        }

        // Mark that we need to merge cart on next login if user logs in
        if (!Auth::check() && $request->routeIs('login')) {
            session()->put('guest_cart_pending_merge', true);
        }

        return $next($request);
    }
}