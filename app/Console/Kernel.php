<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        
    ];

    protected function schedule(Schedule $schedule)
    {
        // Ejecutar diariamente a la 1:00 AM
        $schedule->command('usuarios:eliminar-inactivos')->dailyAt('15:00');
        
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}