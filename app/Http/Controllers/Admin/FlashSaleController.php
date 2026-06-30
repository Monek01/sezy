<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FlashSaleController extends Controller
{
    public function index(): Response
    {
        $flashSales = FlashSale::with('products.product.primaryImage')
            ->latest()
            ->paginate(10);

        $products = Product::where('status', 'published')
            ->with('primaryImage')
            ->select('id', 'title', 'price', 'stock')
            ->orderBy('title')
            ->get();

        return Inertia::render('Admin/FlashSales/Index', [
            'flashSales' => $flashSales,
            'products'   => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'starts_at'  => 'required|date',
            'ends_at'    => 'required|date|after:starts_at',
            'is_active'  => 'boolean',
            'items'      => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.discount_price' => 'required|numeric|min:0',
            'items.*.qty_limit'      => 'nullable|integer|min:1',
        ]);

        $sale = FlashSale::create([
            'title'     => $validated['title'],
            'starts_at' => $validated['starts_at'],
            'ends_at'   => $validated['ends_at'],
            'is_active' => $validated['is_active'] ?? false,
        ]);

        foreach ($validated['items'] as $item) {
            FlashSaleProduct::create([
                'flash_sale_id'  => $sale->id,
                'product_id'     => $item['product_id'],
                'discount_price' => $item['discount_price'],
                'qty_limit'      => $item['qty_limit'] ?? null,
            ]);
        }

        AuditLog::record('flash_sale.created', $sale);

        return back()->with('success', 'Vente flash créée avec succès.');
    }

    public function update(Request $request, FlashSale $flashSale): RedirectResponse
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'starts_at' => 'required|date',
            'ends_at'   => 'required|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        $flashSale->update($validated);
        AuditLog::record('flash_sale.updated', $flashSale);

        return back()->with('success', 'Vente flash mise à jour.');
    }

    public function toggle(FlashSale $flashSale): RedirectResponse
    {
        $flashSale->update(['is_active' => !$flashSale->is_active]);
        return back()->with('success', $flashSale->is_active ? 'Vente flash activée.' : 'Vente flash désactivée.');
    }

    public function destroy(FlashSale $flashSale): RedirectResponse
    {
        AuditLog::record('flash_sale.deleted', $flashSale);
        $flashSale->delete();
        return back()->with('success', 'Vente flash supprimée.');
    }
}
