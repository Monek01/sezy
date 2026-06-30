<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_item_id', 'rating', 'quality_rating',
        'conformity_rating', 'delay_rating', 'comment', 'photos', 'status',
    ];

    protected function casts(): array
    {
        return ['photos' => 'array'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
