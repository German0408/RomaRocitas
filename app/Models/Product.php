<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'image_path',
        'price',
        'subcategory_id',
        'stock'
    ];


    //Relacion uno a muchos inversa
    public function subcategory(){
        return $this->belongsTo(Subcategory::class);
    }

    //Relacion de uno a muchos 
    public function variants(){
        return $this->hasMany(Variant::class);
    }

    //Relacion muchos a muchos 
    public function options(){
        return $this->belongsToMany(Option::class)->withPivot('value')->withTimestamps();
    }

    // Cart items relationship
    public function cartItems(){
        return $this->hasMany(CartItem::class);
    }
}
