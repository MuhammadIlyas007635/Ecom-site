<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Add_To_Cart extends Model
{
    protected $table = 'add_to_cart';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
