<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function autocomplete(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim();

        if (strlen($q) < 2) {
            return response()->json(['products' => [], 'categories' => []]);
        }

        $products = Product::where('status', 'published')
            ->where('title', 'like', "%{$q}%")
            ->with('primaryImage')
            ->select('id', 'title', 'slug', 'price', 'compare_at_price')
            ->take(6)
            ->get()
            ->map(fn($p) => [
                'id'    => $p->id,
                'title' => $p->title,
                'slug'  => $p->slug,
                'price' => (float) $p->price,
                'image' => $p->primaryImage?->path,
            ]);

        $categories = Category::where('name', 'like', "%{$q}%")
            ->select('id', 'name', 'slug')
            ->take(3)
            ->get();

        return response()->json([
            'products'   => $products,
            'categories' => $categories,
        ]);
    }

    public function index(Request $request): Response
    {
        $q = $request->string('q')->trim();

        $products = Product::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhere('short_description', 'like', "%{$q}%");
            })
            ->with(['primaryImage', 'category'])
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Search', [
            'products' => $products,
            'query'    => (string) $q,
        ]);
    }
}
