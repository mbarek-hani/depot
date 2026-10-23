<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('stock:check-alerts')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
