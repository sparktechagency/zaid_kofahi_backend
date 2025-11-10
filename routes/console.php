<?php

use App\Console\Commands\EventEndStatus;
use App\Console\Commands\EventStartOrCancelStatus;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(EventStartOrCancelStatus::class)->everyTenSeconds();
// Schedule::command(UpdateChallengeGroupStatus::class)->daily();

Schedule::command(EventEndStatus::class)->everyTenSeconds();
// Schedule::command(UpdateChallengeGroupStatus::class)->daily();
