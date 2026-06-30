<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'preparing' => 'En préparation',
        'shipped' => 'Expédiée',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
        'refunded' => 'Remboursée',
    ];

    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_city', 'shipping_district', 'shipping_address', 'shipping_landmark',
        'delivery_method', 'pickup_point_id', 'pickup_qr_code',
        'subtotal', 'shipping_fee', 'discount_amount', 'loyalty_points_used', 'total',
        'coupon_code', 'loyalty_points_earned', 'status', 'payment_status', 'payment_method',
        'admin_note', 'customer_note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'SEZY-'.now()->format('Y').'-'.str_pad(
                    (string) (static::whereYear('created_at', now()->year)->count() + 1),
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function pickupPoint()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function refunds()
    {
        return $this->hasMany(OrderRefund::class);
    }

    // Helpers
    public function updateStatus(string $status, ?User $changedBy = null, ?string $note = null): void
    {
        $this->update(['status' => $status, ...match ($status) {
            'confirmed' => ['confirmed_at' => now()],
            'shipped' => ['shipped_at' => now()],
            'delivered' => ['delivered_at' => now()],
            'cancelled' => ['cancelled_at' => now()],
            default => [],
        }]);

        $this->statusHistories()->create([
            'status' => $status,
            'changed_by' => $changedBy?->id,
            'note' => $note,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true);
    }
}
