<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Action\Product\ProductDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

describe('ProductDetail Route Tests', function () {
    test('admin can access product detail page with valid product id', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $product = Product::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(ProductDetail::class, ['id' => $product->id])
            ->assertOk();
    });
});

describe('ProductDetail 404 Tests', function () {
    test('product detail page returns 404 for invalid product id', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        expect(fn () => Livewire::actingAs($admin, 'admin')
            ->test(ProductDetail::class, ['id' => 99999]))
            ->toThrow(ModelNotFoundException::class);
    });

    test('product detail page returns 404 for soft deleted product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $product = Product::factory()->create();
        $product->delete();

        expect(fn () => Livewire::actingAs($admin, 'admin')
            ->test(ProductDetail::class, ['id' => $product->id]))
            ->toThrow(ModelNotFoundException::class);
    });
});
