<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredProducts = Product::published()->inStock()->featured()
            ->with(['primaryImage', 'category'])
            ->latest()
            ->take(8)
            ->get();

        $newArrivals = Product::published()->inStock()
            ->with(['primaryImage', 'category'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::active()->roots()
            ->withCount('products')
            ->orderBy('position')
            ->take(8)
            ->get();

        $activeFlashSale = FlashSale::with(['products.product.primaryImage'])
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();

        return Inertia::render('Home', [
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'categories' => $categories,
            'flashSale' => $activeFlashSale,
        ]);
    }
}
