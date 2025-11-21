<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'image_path',
        'product_id'
    ];

    //Relacion uno a muchos inversa
    public function product(){
        return $this->belongsTo(Product::class);
    }

    //Relacion muchos a muchos 
    public function features(){
        return $this->belongsToMany(Feature::class, 'feature_variant', 'variants_id', 'features_id')->withTimestamps();
    }

    // Cart items relationship
    public function cartItems(){
        return $this->hasMany(CartItem::class);
    }
}
