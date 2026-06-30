<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'brand', 'primaryImage']);

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $request->only(['q', 'status', 'category_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Form', [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(5);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $product = Product::create($validated);

        $this->handleImageUploads($request, $product);

        AuditLog::record('product.created', $product, [], $validated);

        return redirect()->route('admin.products.index')->with('success', 'Produit créé.');
    }

    public function edit(Product $product): Response
    {
        $product->load('images', 'variants.attributeValues.attribute');

        return Inertia::render('Admin/Products/Form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validated($request, $product->id);

        if ($validated['status'] === 'published' && ! $product->published_at) {
            $validated['published_at'] = now();
        }

        $old = $product->only(array_keys($validated));
        $product->update($validated);

        $this->handleImageUploads($request, $product);

        AuditLog::record('product.updated', $product, $old, $validated);

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        AuditLog::record('product.archived', $product, ['status' => $product->status], ['status' => 'archived']);
        $product->update(['status' => 'archived']);

        return back()->with('success', 'Produit archivé.');
    }

    /** Import en masse CSV (§5.2) */
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));

        $imported = 0;

        foreach ($rows as $row) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);
            $category = Category::where('name', $data['category'] ?? '')->first();

            if (! $category || empty($data['title']) || empty($data['sku'])) {
                continue;
            }

            Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']).'-'.Str::random(5),
                    'price' => $data['price'] ?? 0,
                    'compare_at_price' => $data['compare_at_price'] ?? null,
                    'stock' => $data['stock'] ?? 0,
                    'description' => $data['description'] ?? null,
                    'status' => $data['status'] ?? 'draft',
                ]
            );

            $imported++;
        }

        return back()->with('success', "{$imported} produit(s) importé(s).");
    }

    public function exportCsv()
    {
        $products = Product::with('category')->get();

        $filename = 'sezy-produits-'.now()->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['sku', 'title', 'category', 'price', 'compare_at_price', 'stock', 'status']);

            foreach ($products as $p) {
                fputcsv($handle, [$p->sku, $p->title, $p->category->name ?? '', $p->price, $p->compare_at_price, $p->stock, $p->status]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function handleImageUploads(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('products', 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'position' => $index,
                'is_primary' => $index === 0 && ! $product->images()->exists(),
            ]);
        }
    }

    private function validated(Request $request, ?int $productId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'title' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.$productId],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'track_stock' => ['boolean'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
            'video_url' => ['nullable', 'url'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
