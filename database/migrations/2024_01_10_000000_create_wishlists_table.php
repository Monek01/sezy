<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Wishlist (§3.6)
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->boolean('notify_on_price_drop')->default(true);
            $table->boolean('notify_on_restock')->default(true);
            $table->decimal('price_when_added', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        // Avis vérifiés (§3.2) — déposables uniquement par acheteurs confirmés
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete(); // preuve d'achat
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->unsignedTinyInteger('quality_rating')->nullable();
            $table->unsignedTinyInteger('conformity_rating')->nullable();
            $table->unsignedTinyInteger('delay_rating')->nullable();
            $table->text('comment')->nullable();
            $table->json('photos')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->unique(['order_item_id']); // un avis par article acheté
        });

        // Journal d'audit horodaté (§5.1)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // ex: "order.status_updated"
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('wishlists');
    }
};
