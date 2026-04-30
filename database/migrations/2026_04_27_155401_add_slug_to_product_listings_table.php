<?php

declare(strict_types=1);

use App\Models\ProductListing;
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
        Schema::table('product_listings', function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('seller_id');
            $table->unique('slug');
        });

        ProductListing::query()
            ->with(['variant.product', 'variant.region', 'variant.platform', 'variant.operatingSystem'])
            ->chunkById(100, function ($listings): void {
                foreach ($listings as $listing) {
                    $listing->slug = ProductListing::generateSlug($listing);
                    $listing->saveQuietly();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_listings', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
