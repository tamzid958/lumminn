<?php

use App\Jobs\CheckDeliveryStatusJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new CheckDeliveryStatusJob)
    ->everyThreeHours()
    ->timezone('Asia/Dhaka')
    ->between('7:00', '23:00')
    ->before(fn () => dump('CheckDeliveryStatusJob Started'))
    ->after(fn () => dump('CheckDeliveryStatusJob Finished'))
    ->withoutOverlapping();
