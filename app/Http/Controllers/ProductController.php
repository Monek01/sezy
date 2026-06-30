<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::published()->with(['primaryImage', 'category', 'brand']);

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->whereFullText(['title', 'description'], $search)
                ->orWhere('title', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->string('category'))->first();
            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->string('brand')));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->float('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->float('max_price'));
        }

        if ($request->boolean('in_stock_only')) {
            $query->inStock();
        }

        match ($request->string('sort')->toString()) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->latest(),
            'rating' => $query->orderByDesc('average_rating'),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        $products = $query->paginate(24)->withQueryString();

        return Inertia::render('Catalog/Index', [
            'products' => $products,
            'categories' => Category::active()->roots()->orderBy('position')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'filters' => $request->only(['q', 'category', 'brand', 'min_price', 'max_price', 'sort', 'in_stock_only']),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = Product::published()
            ->with([
                'images', 'category', 'brand',
                'variants.attributeValues.attribute',
                'approvedReviews.user',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $product->increment('views_count');

        $relatedProducts = Product::published()->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('primaryImage')
            ->take(8)
            ->get();

        return Inertia::render('Catalog/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
