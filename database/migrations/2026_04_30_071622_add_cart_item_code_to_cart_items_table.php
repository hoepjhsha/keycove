<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('cart_item_code', 255)->nullable()->unique()->after('id');
        });

        DB::table('cart_items')
            ->whereNull('cart_item_code')
            ->orderBy('id')
            ->chunkById(100, function ($cartItems): void {
                foreach ($cartItems as $cartItem) {
                    DB::table('cart_items')
                        ->where('id', $cartItem->id)
                        ->update([
                            'cart_item_code' => 'CI-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_item_code']);
            $table->dropColumn('cart_item_code');
        });
    }
};
