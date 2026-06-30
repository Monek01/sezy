<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => ucfirst($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(5),
            'sku' => 'SEZY-'.strtoupper(Str::random(8)),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'price' => fake()->numberBetween(2000, 50000),
            'stock' => fake()->numberBetween(0, 50),
            'low_stock_threshold' => 5,
            'track_stock' => true,
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
        ];
    }
}
