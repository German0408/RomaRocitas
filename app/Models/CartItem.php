<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }

    protected static function booted()
    {
        static::saving(function ($cartItem) {
            if ($cartItem->variant) {
                // If variant exists, check variant stock (assuming variant has stock)
                // For now, assume product stock
            } else {
                // Check product stock
                $product = $cartItem->product;
                if ($cartItem->quantity > $product->stock) {
                    throw new \Exception('Insufficient stock for product: ' . $product->name);
                }
            }
        });
    }
}
