<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ExpireRecords; // ✅ bunu ekle

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ExpireRecords::class, // ✅ bunu ekle
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('records:expire')->everyMinute();
    }
}