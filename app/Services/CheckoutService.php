<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /** Frais de livraison forfaitaires par ville (simplifié pour le MVP). */
    private const SHIPPING_RATES = [
        'cotonou' => 1500,
        'porto-novo' => 1500,
        'parakou' => 3000,
        'abomey-calavi' => 2000,
    ];

    private const DEFAULT_SHIPPING_RATE = 3500;

    public function calculateShippingFee(string $city, string $deliveryMethod): float
    {
        if ($deliveryMethod === 'click_and_collect') {
            return 0;
        }

        return self::SHIPPING_RATES[strtolower($city)] ?? self::DEFAULT_SHIPPING_RATE;
    }

    public function validateCoupon(?string $code, float $subtotal): ?Coupon
    {
        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValidFor($subtotal)) {
            return null;
        }

        return $coupon;
    }

    /**
     * Crée la commande à partir du panier (transaction atomique).
     * Décrémente le stock, vide le panier, attribue les points de fidélité.
     */
    public function createOrderFromCart(
        Cart $cart,
        array $shippingData,
        ?User $user,
        string $deliveryMethod,
        ?int $pickupPointId = null,
        ?string $couponCode = null,
        int $loyaltyPointsToUse = 0,
    ): Order {
        return DB::transaction(function () use (
            $cart, $shippingData, $user, $deliveryMethod, $pickupPointId, $couponCode, $loyaltyPointsToUse
        ) {
            $cart->loadMissing('items.product', 'items.variant');

            $subtotal = $cart->subtotal;
            $shippingFee = $this->calculateShippingFee($shippingData['city'], $deliveryMethod);

            $coupon = $this->validateCoupon($couponCode, $subtotal);
            $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;

            // 1 point fidélité = 1 FCFA (§3.1), plafonné au montant de la commande
            $loyaltyDiscount = $user
                ? min($loyaltyPointsToUse, $user->loyalty_points, $subtotal - $discount)
                : 0;

            $total = max(0, $subtotal + $shippingFee - $discount - $loyaltyDiscount);

            $order = Order::create([
                'user_id' => $user?->id,
                'customer_name' => $shippingData['full_name'],
                'customer_email' => $shippingData['email'],
                'customer_phone' => $shippingData['phone'],
                'shipping_city' => $shippingData['city'],
                'shipping_district' => $shippingData['district'] ?? null,
                'shipping_address' => $shippingData['address_line'] ?? '',
                'shipping_landmark' => $shippingData['landmark'] ?? null,
                'delivery_method' => $deliveryMethod,
                'pickup_point_id' => $pickupPointId,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discount,
                'loyalty_points_used' => $loyaltyDiscount,
                'total' => $total,
                'coupon_code' => $coupon?->code,
                'loyalty_points_earned' => (int) floor($total), // 1 FCFA dépensé = 1 point gagné
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            foreach ($cart->items as $item) {
                $stockSource = $item->variant ?: $item->product;

                if ($stockSource->track_stock ?? true) {
                    if ($stockSource->stock < $item->quantity) {
                        throw new \RuntimeException("Stock insuffisant pour {$item->product->title}.");
                    }
                    $stockSource->decrement('stock', $item->quantity);
                }

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_title' => $item->product->title,
                    'product_sku' => $item->variant->sku ?? $item->product->sku,
                    'variant_label' => $item->variant?->label,
                    'product_image' => $item->product->primaryImage?->path,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                ]);

                $item->product->increment('sales_count', $item->quantity);
            }

            $order->statusHistories()->create(['status' => 'pending', 'note' => 'Commande créée']);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            if ($user) {
                $user->decrement('loyalty_points', $loyaltyDiscount);
            }

            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            return $order->fresh('items');
        });
    }

    /** Appelé après confirmation du paiement : crédite les points fidélité gagnés. */
    public function applyLoyaltyRewards(Order $order): void
    {
        if (! $order->user_id || $order->loyalty_points_earned <= 0) {
            return;
        }

        $user = $order->user;
        $user->increment('loyalty_points', $order->loyalty_points_earned);
        $this->refreshLoyaltyTier($user);
    }

    private function refreshLoyaltyTier(User $user): void
    {
        $points = $user->fresh()->loyalty_points;

        $tier = match (true) {
            $points >= 100000 => 'platinum',
            $points >= 50000 => 'gold',
            $points >= 15000 => 'silver',
            default => 'bronze',
        };

        if ($user->loyalty_tier !== $tier) {
            $user->update(['loyalty_tier' => $tier]);
        }
    }
}
