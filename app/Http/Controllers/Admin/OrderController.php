<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderRefund;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Order::with('items');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%");
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load('items', 'user', 'statusHistories.changedBy', 'payments', 'pickupPoint', 'refunds');

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,preparing,shipped,delivered,cancelled,refunded'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $order->status;
        $order->updateStatus($validated['status'], Auth::user(), $validated['note'] ?? null);

        if ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                $stockTarget = $item->variant ?: $item->product;
                $stockTarget?->increment('stock', $item->quantity);
            }
        }

        AuditLog::record('order.status_updated', $order, ['status' => $oldStatus], ['status' => $validated['status']]);

        return back()->with('success', 'Statut de la commande mis à jour.');
    }

    public function storeRefund(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:'.$order->total],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        OrderRefund::create([
            'order_id' => $order->id,
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? null,
            'processed_by' => Auth::id(),
            'status' => 'completed',
        ]);

        $order->update(['payment_status' => 'refunded']);
        $order->updateStatus('refunded', Auth::user(), 'Remboursement de '.$validated['amount'].' FCFA');

        AuditLog::record('order.refunded', $order, [], $validated);

        return back()->with('success', 'Remboursement enregistré.');
    }

    public function invoice(Order $order)
    {
        $order->load('items');

        $pdf = app('dompdf.wrapper')->loadView('pdf.invoice', ['order' => $order]);

        return $pdf->download("facture-{$order->order_number}.pdf");
    }

    public function preparationSlip(Order $order)
    {
        $order->load('items');

        $pdf = app('dompdf.wrapper')->loadView('pdf.preparation-slip', ['order' => $order]);

        return $pdf->download("bon-preparation-{$order->order_number}.pdf");
    }
}
