<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSaleProduct extends Model
{
    protected $fillable = ['flash_sale_id', 'product_id', 'flash_price', 'stock_limit', 'sold_count'];

    protected function casts(): array
    {
        return ['flash_price' => 'decimal:2'];
    }

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->stock_limit !== null && $this->sold_count >= $this->stock_limit;
    }
}
