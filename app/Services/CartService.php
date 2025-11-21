<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CartService
{
    const CART_KEY = 'cart';

    public function getItems()
    {
        if (Auth::check()) {
            return $this->getDatabaseItems();
        }

        return $this->getSessionItems();
    }

    private function getSessionItems()
    {
        // Try session first, then localStorage as fallback
        $cart = Session::get(self::CART_KEY, []);
        if (empty($cart)) {
            $cart = $this->getLocalStorageCart();
        }
        return $cart;
    }

    private function getLocalStorageCart()
    {
        // This will be handled by JavaScript, but we can provide a server-side fallback
        // For now, return empty array - JavaScript will handle localStorage
        return [];
    }

    public function addItem($productId, $variantId = null, $quantity = 1)
    {
        if (Auth::check()) {
            $this->addDatabaseItem($productId, $variantId, $quantity);
        } else {
            $this->addSessionItem($productId, $variantId, $quantity);
        }
    }

    public function updateQuantity($key, $quantity)
    {
        if (Auth::check()) {
            $this->updateDatabaseQuantity($key, $quantity);
        } else {
            $this->updateSessionQuantity($key, $quantity);
        }
    }

    public function removeItem($key)
    {
        if (Auth::check()) {
            $this->removeDatabaseItem($key);
        } else {
            $this->removeSessionItem($key);
        }
    }

    public function getTotal()
    {
        if (Auth::check()) {
            return $this->getDatabaseTotal();
        }

        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function clear()
    {
        if (Auth::check()) {
            $this->clearDatabaseCart();
        } else {
            Session::forget(self::CART_KEY);
        }
    }

    public function mergeGuestCart()
    {
        if (!Auth::check()) {
            return;
        }

        $sessionItems = Session::get(self::CART_KEY, []);
        if (empty($sessionItems)) {
            return;
        }

        $cart = $this->getOrCreateUserCart();

        foreach ($sessionItems as $item) {
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $item['product_id'])
                ->where('variant_id', $item['variant_id'])
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $item['quantity'];
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        }

        Session::forget(self::CART_KEY);
    }

    private function getOrCreateUserCart()
    {
        // Try to find existing cart first
        $cart = Cart::where('user_id', Auth::id())->first();
        
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'session_id' => null,
            ]);
        }
        return $cart;
    }

    private function getDatabaseItems()
    {
        $cart = $this->getOrCreateUserCart();
        // Force fresh load of items relationship
        $cart->load('items');
        
        return $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'name' => $this->getName($item->product_id, $item->variant_id),
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();
    }

    private function addDatabaseItem($productId, $variantId, $quantity)
    {
        $cart = $this->getOrCreateUserCart();

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $this->getPrice($productId, $variantId),
            ]);
        }

        $this->validateStock($cart->items->toArray());
    }

    private function updateDatabaseQuantity($itemId, $quantity)
    {
        $cart = $this->getOrCreateUserCart();
        $item = $cart->items()->find($itemId);
        if ($item) {
            $item->quantity = $quantity;
            $item->save();
            $this->validateStock($cart->items->toArray());
        }
    }

    private function removeDatabaseItem($itemId)
    {
        $cart = $this->getOrCreateUserCart();
        $cart->items()->find($itemId)?->delete();
    }

    private function getDatabaseTotal()
    {
        $cart = $this->getOrCreateUserCart();
        return $cart->total;
    }

    private function clearDatabaseCart()
    {
        $cart = $this->getOrCreateUserCart();
        $cart->items()->delete();
    }

    private function addSessionItem($productId, $variantId, $quantity)
    {
        $cart = $this->getSessionItems();
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
        $this->saveCartToSessionAndLocalStorage($cart);
    }

    private function updateSessionQuantity($key, $quantity)
    {
        $cart = $this->getSessionItems();
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $quantity;
            $this->validateStock($cart);
            $this->saveCartToSessionAndLocalStorage($cart);
        }
    }

    private function removeSessionItem($key)
    {
        $cart = $this->getSessionItems();
        if (isset($cart[$key])) {
            unset($cart[$key]);
            $this->saveCartToSessionAndLocalStorage(array_values($cart)); // reindex
        }
    }

    private function saveCartToSessionAndLocalStorage($cart)
    {
        Session::put(self::CART_KEY, $cart);
        // localStorage will be handled by JavaScript
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
        $cacheKey = "product_price_{$productId}";
        return Cache::remember($cacheKey, 3600, function () use ($productId) {
            $product = Product::find($productId);
            return $product ? $product->price : 0;
        });
    }

    private function getName($productId, $variantId)
    {
        $cacheKey = "product_name_{$productId}";
        $name = Cache::remember($cacheKey, 3600, function () use ($productId) {
            $product = Product::find($productId);
            return $product ? $product->name : 'Producto desconocido';
        });

        if ($variantId) {
            $variantCacheKey = "variant_sku_{$variantId}";
            $variantSku = Cache::remember($variantCacheKey, 3600, function () use ($variantId) {
                $variant = Variant::find($variantId);
                return $variant ? $variant->sku : '';
            });
            if ($variantSku) {
                $name .= ' - ' . $variantSku;
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
        $cacheKey = "product_stock_{$productId}";
        return Cache::remember($cacheKey, 1800, function () use ($productId) { // Cache for 30 minutes
            $product = Product::find($productId);
            return $product ? $product->stock : 0;
        });
    }
}