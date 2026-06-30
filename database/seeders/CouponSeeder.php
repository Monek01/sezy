<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'code' => 'BIENVENUE10',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 5000,
            'usage_limit' => null,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'SEZY2000',
            'type' => 'fixed',
            'value' => 2000,
            'min_order_amount' => 15000,
            'usage_limit' => 100,
            'is_active' => true,
        ]);
    }
}
