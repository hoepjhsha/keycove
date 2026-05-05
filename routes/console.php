<?php

use App\Console\Commands\CompleteSettledOrderItems;
use App\Console\Commands\ExpirePendingOrders;
use App\Console\Commands\ProcessPlatformProfitPayouts;
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

Schedule::command(CompleteSettledOrderItems::class)
    ->daily()
    ->withoutOverlapping();

Schedule::command(RetryProcessingWithdrawals::class, ['--minutes' => 5])
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

Schedule::command(ProcessPlatformProfitPayouts::class)
    ->weeklyOn(1, '02:00')
    ->withoutOverlapping();
