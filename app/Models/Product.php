<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table ="products";
    protected $primaryKey ="id";
    protected $fillable = [
        'product_title',
        'description',
        'image',
        'price',
        'quantity',
        'category_id',
        'discount',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
