<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private PaymentMethod $seedPaymentMethod = PaymentMethod::VNPay;

    /**
     * Run the database seeds.
     */
    public function run(): void {}
}
