<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::where('role', 'client')->withCount('orders');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only('q'),
        ]);
    }

    public function show(User $customer): Response
    {
        abort_unless($customer->role === 'client', 404);

        $customer->load(['orders' => fn ($q) => $q->latest()->take(20), 'addresses']);

        return Inertia::render('Admin/Customers/Show', ['customer' => $customer]);
    }

    public function toggleBlock(User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'client', 404);

        $customer->update(['is_blocked' => ! $customer->is_blocked]);

        AuditLog::record(
            $customer->is_blocked ? 'customer.blocked' : 'customer.unblocked',
            $customer
        );

        return back()->with('success', $customer->is_blocked ? 'Client bloqué.' : 'Client débloqué.');
    }

    public function adjustLoyaltyPoints(Request $request, User $customer): RedirectResponse
    {
        $validated = $request->validate([
            'points' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $customer->increment('loyalty_points', $validated['points']);

        AuditLog::record('customer.loyalty_points_adjusted', $customer, [], $validated);

        return back()->with('success', 'Points de fidélité ajustés.');
    }
}
