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
        Schema::create('product_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('product_listings')->cascadeOnDelete();
            $table->string('key_code', 255);
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();

            $table->foreign('order_item_id')->references('id')->on('order_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_keys');
    }
};
