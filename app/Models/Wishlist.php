<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'notify_on_price_drop', 'notify_on_restock', 'price_when_added',
    ];

    protected function casts(): array
    {
        return [
            'notify_on_price_drop' => 'boolean',
            'notify_on_restock' => 'boolean',
            'price_when_added' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
