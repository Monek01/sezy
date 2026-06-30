<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(): Response
    {
        $cart = $this->cartService->current()
            ->load(['items.product.primaryImage', 'items.variant.attributeValues']);

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $variant = isset($validated['product_variant_id'])
            ? ProductVariant::find($validated['product_variant_id'])
            : null;

        $availableStock = $variant?->stock ?? $product->stock;

        if ($product->track_stock && $availableStock < $validated['quantity']) {
            return back()->with('error', 'Stock insuffisant pour ce produit.');
        }

        $this->cartService->addItem($product, $validated['quantity'], $variant);

        return back()->with('success', 'Produit ajouté au panier.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwnership($cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $this->cartService->updateQuantity($cartItem, $validated['quantity']);

        return back();
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwnership($cartItem);

        $this->cartService->removeItem($cartItem);

        return back()->with('success', 'Produit retiré du panier.');
    }

    private function authorizeOwnership(CartItem $cartItem): void
    {
        $cart = $this->cartService->current();

        abort_unless($cartItem->cart_id === $cart->id, 403);
    }
}
