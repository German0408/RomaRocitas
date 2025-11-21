<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Session;

class CartService
{
    const CART_KEY = 'cart';

    public function getItems()
    {
        return Session::get(self::CART_KEY, []);
    }

    public function addItem($productId, $variantId = null, $quantity = 1)
    {
        $cart = $this->getItems();
        $key = $this->findItemKey($productId, $variantId);
        if ($key !== false) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $this->getName($productId, $variantId),
                'quantity' => $quantity,
                'price' => $this->getPrice($productId, $variantId),
            ];
        }
        $this->validateStock($cart);
        Session::put(self::CART_KEY, $cart);
    }

    public function updateQuantity($key, $quantity)
    {
        $cart = $this->getItems();
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $quantity;
            $this->validateStock($cart);
            Session::put(self::CART_KEY, $cart);
        }
    }

    public function removeItem($key)
    {
        $cart = $this->getItems();
        if (isset($cart[$key])) {
            unset($cart[$key]);
            Session::put(self::CART_KEY, array_values($cart)); // reindex
        }
    }

    public function getTotal()
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function clear()
    {
        Session::forget(self::CART_KEY);
    }

    private function findItemKey($productId, $variantId)
    {
        $cart = $this->getItems();
        foreach ($cart as $key => $item) {
            if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
                return $key;
            }
        }
        return false;
    }

    private function getPrice($productId, $variantId)
    {
        $product = Product::find($productId);
        return $product ? $product->price : 0;
    }

    private function getName($productId, $variantId)
    {
        $product = Product::find($productId);
        $name = $product ? $product->name : 'Producto desconocido';
        if ($variantId) {
            $variant = Variant::find($variantId);
            if ($variant) {
                $name .= ' - ' . $variant->sku; // or some variant description
            }
        }
        return $name;
    }

    private function validateStock($cart)
    {
        foreach ($cart as $item) {
            $stock = $this->getStock($item['product_id'], $item['variant_id']);
            if ($item['quantity'] > $stock) {
                throw new \Exception(__('cart.insufficient_stock', ['stock' => $stock]));
            }
        }
    }

    private function getStock($productId, $variantId)
    {
        $product = Product::find($productId);
        return $product ? $product->stock : 0;
    }
}