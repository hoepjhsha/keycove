<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Action\Product\ProductDetail;
use App\Models\Product;
use App\Models\User;

describe('ProductDetail Route Tests', function () {
    test('admin can access product detail page with valid product id', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.products.detail', $product));

        $response->assertOk();
        $response->assertSeeLivewire(ProductDetail::class);
    });
});

describe('ProductDetail 404 Tests', function () {
    test('product detail page returns 404 for invalid product id', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/products/99999');

        $response->assertNotFound();
    });

    test('product detail page returns 404 for soft deleted product', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $product = Product::factory()->create();
        $product->delete();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/products/'.$product->id);

        $response->assertNotFound();
    });
});
