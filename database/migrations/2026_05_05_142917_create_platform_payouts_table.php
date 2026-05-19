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
        Schema::create('platform_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_code')->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->dateTime('settlement_cutoff_at');
            $table->decimal('amount', 15, 2);
            $table->tinyInteger('status')->default(0);
            $table->string('bank_name', 255);
            $table->string('bank_code', 100);
            $table->string('bank_account_number', 255);
            $table->string('bank_account_name', 255);
            $table->timestamp('processed_at')->nullable();
            $table->string('idempotency_key')->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_payouts');
    }
};
