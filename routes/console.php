<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('helios:hsp:refresh')->cron((string) config('hsp.refresh_cron', '0 3 1 * *'))
    ->timezone('UTC')->withoutOverlapping(120);
