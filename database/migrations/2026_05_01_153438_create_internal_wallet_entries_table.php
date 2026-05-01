<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('internal_wallet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->restrictOnDelete();
            $table->nullableMorphs('source');
            $table->tinyInteger('type');
            $table->tinyInteger('direction')->default(2);
            $table->decimal('amount', 15, 2);
            $table->tinyInteger('status')->default(0);
            $table->boolean('affects_balance')->default(false);
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['wallet_id', 'occurred_at']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_wallet_entries');
    }
};
