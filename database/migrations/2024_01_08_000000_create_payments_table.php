<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique(); // référence interne SEZY
            $table->string('provider_reference')->nullable(); // référence côté opérateur
            $table->enum('method', ['mtn_momo', 'moov_money', 'wave', 'card']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('XOF');
            $table->string('payer_phone')->nullable();
            $table->enum('status', ['initiated', 'pending', 'succeeded', 'failed', 'cancelled'])->default('initiated');
            $table->json('gateway_payload')->nullable(); // réponse brute de l'opérateur (debug)
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
