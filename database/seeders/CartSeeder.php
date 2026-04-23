<?php

namespace Database\Seeders;

use App\Enums\ProductListingStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCarts();
    }

    protected function seedCarts(): void
    {
        $buyers = User::where('role', UserRole::User)
            ->where('status', UserStatus::Active)
            ->get();

        $activeListings = ProductListing::where('status', ProductListingStatus::Active)
            ->with('variant')
            ->get();

        if ($buyers->isEmpty() || $activeListings->isEmpty()) {
            return;
        }

        // Create 8-10 abandoned carts
        $numCarts = random_int(8, 10);
        $selectedBuyers = $buyers->shuffle()->take($numCarts);

        foreach ($selectedBuyers as $buyer) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $buyer->id],
                []
            );

            // Add 1-3 items to each cart
            $numItems = random_int(1, 3);
            $selectedListings = $activeListings->shuffle()->take($numItems);

            foreach ($selectedListings as $listing) {
                CartItem::firstOrCreate([
                    'cart_id'    => $cart->id,
                    'listing_id' => $listing->id,
                    'quantity'   => random_int(1, 2),
                ]);
            }
        }
    }
}
