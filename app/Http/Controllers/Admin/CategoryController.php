<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::with('children')
            ->withCount('products')
            ->roots()
            ->orderBy('position')
            ->get();

        return Inertia::render('Admin/Categories/Index', ['categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);
        AuditLog::record('category.created', $category, [], $validated);

        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['slug'] = Str::slug($validated['name']);

        $old = $category->only(array_keys($validated));
        $category->update($validated);
        AuditLog::record('category.updated', $category, $old, $validated);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return back()->with('error', 'Impossible de supprimer : catégorie utilisée par des produits ou sous-catégories.');
        }

        AuditLog::record('category.deleted', $category, $category->toArray(), []);
        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'exists:categories,id'],
            'order.*.position' => ['required', 'integer'],
        ]);

        foreach ($validated['order'] as $entry) {
            Category::where('id', $entry['id'])->update(['position' => $entry['position']]);
        }

        return back();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
