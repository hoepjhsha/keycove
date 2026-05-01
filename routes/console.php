<?php

use App\Console\Commands\ExpirePendingOrders;
use App\Console\Commands\RetryProcessingWithdrawals;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ExpirePendingOrders::class)
    ->hourly()
    ->withoutOverlapping();

Schedule::command(RetryProcessingWithdrawals::class, ['--minutes' => 5])
    ->everyFiveMinutes()
    ->withoutOverlapping(10);
