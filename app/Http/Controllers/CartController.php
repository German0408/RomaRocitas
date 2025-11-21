<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(): View
    {
        $items = $this->cartService->getItems();
        $total = $this->cartService->getTotal();
        return view('cart.index', compact('items', 'total'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:variants,id',
            'quantity' => 'required|integer|min:1',
        ], [
            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists' => 'El producto no existe.',
            'variant_id.exists' => 'La variante no existe.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
        ]);

        try {
            $this->cartService->addItem(
                $request->product_id,
                $request->variant_id,
                $request->quantity
            );
            return redirect()->route('cart.index')->with('success', 'Producto añadido al carrito.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function update(Request $request, $key): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ], [
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
        ]);

        try {
            $this->cartService->updateQuantity($key, $request->quantity);
            return redirect()->route('cart.index')->with('success', 'Cantidad actualizada.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function destroy($key): RedirectResponse
    {
        $this->cartService->removeItem($key);
        return redirect()->route('cart.index')->with('success', 'Producto eliminado del carrito.');
    }
}