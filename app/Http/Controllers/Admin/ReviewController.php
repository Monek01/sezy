<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Review::with(['product:id,title', 'user:id,first_name,last_name,email'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->integer('rating'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($q2) use ($q) {
                $q2->where('comment', 'like', "%{$q}%")
                   ->orWhereHas('product', fn($p) => $p->where('title', 'like', "%{$q}%"))
                   ->orWhereHas('user', fn($u) => $u->where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%"));
            });
        }

        $reviews = $query->paginate(20)->withQueryString();

        $counts = Review::selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'counts'  => $counts,
            'filters' => $request->only(['status', 'rating', 'q']),
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => 'approved']);
        $this->updateProductRating($review->product_id);
        AuditLog::record('review.approved', $review);
        return back()->with('success', 'Avis approuvé.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $review->update(['status' => 'rejected']);
        $this->updateProductRating($review->product_id);
        AuditLog::record('review.rejected', $review);
        return back()->with('success', 'Avis rejeté.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $productId = $review->product_id;
        $review->delete();
        $this->updateProductRating($productId);
        AuditLog::record('review.deleted', $review);
        return back()->with('success', 'Avis supprimé.');
    }

    private function updateProductRating(int $productId): void
    {
        $avg = Review::where('product_id', $productId)->where('status', 'approved')->avg('rating');
        \App\Models\Product::where('id', $productId)->update(['average_rating' => round($avg ?? 0, 2)]);
    }
}
