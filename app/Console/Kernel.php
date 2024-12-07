<?php

namespace App\Console;

use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Pour débugger, ajoutons un log
        $schedule->call(function () {
            Mail::to('test@example.com')
                ->send(new TestEmail());
        })->everyFiveMinutes()
        ->name('send-test-email') // Donnons un nom à la tâche
        ->appendOutputTo(storage_path('logs/scheduler.log')); // Ajoutons des logs
    }
}
