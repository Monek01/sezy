<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WishlistToggleController extends Controller
{
    public function store(Product $product): RedirectResponse
    {
        $existing = Auth::user()->wishlists()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();

            return back()->with('success', 'Retiré de votre liste de souhaits.');
        }

        Auth::user()->wishlists()->create([
            'product_id' => $product->id,
            'price_when_added' => $product->price,
        ]);

        return back()->with('success', 'Ajouté à votre liste de souhaits.');
    }
}
