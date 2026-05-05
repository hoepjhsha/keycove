<?php

declare(strict_types=1);

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
        Schema::dropIfExists('platform_payout_items');

        Schema::create('platform_payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_payout_id')->constrained('platform_payouts')->cascadeOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained('order_items')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('profit_type', 50);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_payout_items');
    }
};
