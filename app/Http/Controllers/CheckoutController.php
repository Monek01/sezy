<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PickupPoint;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService,
        private PaymentManager $paymentManager,
    ) {}

    /** Étape 1 : Livraison */
    public function shipping(): Response|RedirectResponse
    {
        $cart = $this->cartService->current()->load('items');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide.');
        }

        $user = Auth::user();

        return Inertia::render('Checkout/Shipping', [
            'cart' => $cart->load('items.product.primaryImage', 'items.variant'),
            'addresses' => $user?->addresses ?? [],
            'pickupPoints' => PickupPoint::active()->get(),
        ]);
    }

    public function storeShipping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'delivery_method' => ['required', 'in:home_delivery,click_and_collect'],
            'city' => ['required_if:delivery_method,home_delivery', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'address_line' => ['required_if:delivery_method,home_delivery', 'string', 'max:500'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'pickup_point_id' => ['required_if:delivery_method,click_and_collect', 'exists:pickup_points,id'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        session(['checkout.shipping' => $validated]);

        return redirect()->route('checkout.payment');
    }

    /** Étape 2 : Paiement */
    public function payment(): Response|RedirectResponse
    {
        if (! session('checkout.shipping')) {
            return redirect()->route('checkout.shipping');
        }

        $cart = $this->cartService->current()->load('items.product');
        $shipping = session('checkout.shipping');

        $subtotal = $cart->subtotal;
        $shippingFee = $this->checkoutService->calculateShippingFee(
            $shipping['city'] ?? '',
            $shipping['delivery_method']
        );

        return Inertia::render('Checkout/Payment', [
            'cart' => $cart,
            'shipping' => $shipping,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $subtotal + $shippingFee,
            'paymentMethods' => $this->paymentManager->availableMethods(),
            'loyaltyPoints' => Auth::user()?->loyalty_points ?? 0,
        ]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:mtn_momo,moov_money,wave,card'],
            'payer_phone' => ['required_unless:payment_method,card', 'nullable', 'string'],
            'coupon_code' => ['nullable', 'string'],
            'loyalty_points_to_use' => ['nullable', 'integer', 'min:0'],
        ]);

        $shipping = session('checkout.shipping');

        if (! $shipping) {
            return redirect()->route('checkout.shipping');
        }

        $cart = $this->cartService->current()->load('items.product', 'items.variant');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide.');
        }

        try {
            $order = $this->checkoutService->createOrderFromCart(
                cart: $cart,
                shippingData: $shipping,
                user: Auth::user(),
                deliveryMethod: $shipping['delivery_method'],
                pickupPointId: $shipping['pickup_point_id'] ?? null,
                couponCode: $validated['coupon_code'] ?? null,
                loyaltyPointsToUse: $validated['loyalty_points_to_use'] ?? 0,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $order->update(['payment_method' => $validated['payment_method']]);

        $gateway = $this->paymentManager->driver($validated['payment_method']);
        $gateway->initiate($order, ['phone' => $validated['payer_phone'] ?? null]);

        session()->forget('checkout.shipping');

        return redirect()->route('checkout.confirmation', $order);
    }

    /** Étape 3 : Confirmation (polling du statut de paiement) */
    public function confirmation(Order $order): Response
    {
        $order->load('items', 'latestPayment', 'pickupPoint');

        return Inertia::render('Checkout/Confirmation', [
            'order' => $order,
        ]);
    }

    /** Polling AJAX appelé par le front pour vérifier le statut du paiement Mobile Money. */
    public function checkPaymentStatus(Order $order)
    {
        $payment = $order->latestPayment;

        if (! $payment) {
            return response()->json(['status' => 'unknown']);
        }

        $gateway = $this->paymentManager->driver($payment->method);
        $payment = $gateway->checkStatus($payment);

        if ($payment->isSuccessful() && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'paid']);
            $order->updateStatus('confirmed', note: 'Paiement confirmé automatiquement.');
            app(CheckoutService::class)->applyLoyaltyRewards($order);
        }

        if ($payment->status === 'failed') {
            $order->update(['payment_status' => 'failed']);
        }

        return response()->json([
            'payment_status' => $order->fresh()->payment_status,
            'order_status' => $order->fresh()->status,
            'gateway_status' => $payment->status,
        ]);
    }
}
