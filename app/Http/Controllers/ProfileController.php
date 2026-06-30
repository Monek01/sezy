<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email,'.$user->id],
            'phone'      => ['nullable', 'string', 'max:30', 'unique:users,phone,'.$user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed'],
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Mot de passe mis à jour.');
    }

    public function orders(Request $request): Response
    {
        $query = Auth::user()->orders()->with('items');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Profile/Orders', [
            'orders'  => $orders,
            'filters' => $request->only(['status']),
        ]);
    }

    public function orderShow(Order $order): Response
    {
        abort_unless($order->user_id === Auth::id(), 403);

        return Inertia::render('Profile/OrderShow', [
            'order' => $order->load('items', 'statusHistories', 'pickupPoint'),
        ]);
    }

    public function orderInvoice(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless(in_array($order->payment_status, ['paid', 'refunded']), 403);

        $order->load('items');
        $pdf = app('dompdf.wrapper')->loadView('pdf.invoice', ['order' => $order]);
        return $pdf->download("facture-{$order->order_number}.pdf");
    }

    public function wishlist(): Response
    {
        $wishlist = Auth::user()->wishlists()
            ->with('product.primaryImage')
            ->latest()
            ->get();

        return Inertia::render('Profile/Wishlist', ['wishlist' => $wishlist]);
    }

    public function loyalty(): Response
    {
        $user = Auth::user();

        $pointsHistory = \App\Models\Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->select('order_number', 'total', 'loyalty_points_earned', 'loyalty_points_used', 'created_at')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($o) => [
                'order_number' => $o->order_number,
                'total'        => (float) $o->total,
                'earned'       => (int) $o->loyalty_points_earned,
                'used'         => (int) $o->loyalty_points_used,
                'date'         => $o->created_at,
            ]);

        $totalEarned = $pointsHistory->sum('earned');
        $totalUsed   = $pointsHistory->sum('used');

        return Inertia::render('Profile/Loyalty', [
            'user'          => $user,
            'pointsHistory' => $pointsHistory,
            'totalEarned'   => $totalEarned,
            'totalUsed'     => $totalUsed,
        ]);
    }
}
