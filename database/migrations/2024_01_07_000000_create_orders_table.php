<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ex: SEZY-2026-000123
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot client (utile pour guest checkout, §4.4)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            // Snapshot adresse de livraison
            $table->string('shipping_city');
            $table->string('shipping_district')->nullable();
            $table->text('shipping_address');
            $table->string('shipping_landmark')->nullable();

            $table->enum('delivery_method', ['home_delivery', 'click_and_collect'])->default('home_delivery');
            $table->foreignId('pickup_point_id')->nullable()->constrained('pickup_points')->nullOnDelete();
            $table->string('pickup_qr_code')->nullable(); // §3.5 Click & Collect

            // Montants
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('loyalty_points_used', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('coupon_code')->nullable();

            // Fidélité (§3.1)
            $table->unsignedInteger('loyalty_points_earned')->default(0);

            // Parrainage (§3.4)
            $table->boolean('referral_reward_granted')->default(false);

            // Workflow commande (§5.3) : En attente > Confirmée > En préparation
            // > Expédiée > Livrée / Annulée / Remboursée
            $table->enum('status', [
                'pending',
                'confirmed',
                'preparing',
                'shipped',
                'delivered',
                'cancelled',
                'refunded',
            ])->default('pending');

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['mtn_momo', 'moov_money', 'wave', 'card'])->nullable();

            $table->text('admin_note')->nullable();
            $table->text('customer_note')->nullable();

            $table->string('invoice_path')->nullable(); // PDF facture générée (§5.3)

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['user_id']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot produit au moment de l'achat (l'historique ne doit
            // jamais changer si le produit est modifié/supprimé après coup)
            $table->string('product_title');
            $table->string('product_sku');
            $table->string('variant_label')->nullable(); // ex: "Rouge / M"
            $table->string('product_image')->nullable();

            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);

            $table->timestamps();
        });

        // Historique des changements de statut (traçabilité, §5.1 journal d'audit)
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Avoirs / remboursements (§5.3)
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
