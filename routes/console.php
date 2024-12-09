<?php

use Carbon\Carbon;
use App\Models\Event;
use App\Mail\TestEmail;
use App\Mail\DailyEventsSummaryMail;
use App\Mail\EventStatusChangedMail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Commande inspire par défaut
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Commande pour l'envoi des emails planifiés à une adresse spécifique
Artisan::command('mail:test', function () {
    try {
        Mail::to(config('services.mail.admin_address'))->send(new TestEmail());
        $this->info("Email test envoyé avec succès à : " . config('services.mail.admin_address'));

        $this->line('Configuration utilisée :');
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Mailer', config('mail.default')],
                ['From', config('mail.from.address')],
                ['Host', config('mail.mailers.smtp.host')],
                ['Port', config('mail.mailers.smtp.port')],
            ]
        );
    } catch (\Exception $e) {
        $this->error("Erreur lors de l'envoi : " . $e->getMessage());
    }
})->purpose('Envoyer un email test en format Markdown')
    ->twiceDaily(1, 13)
    ->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::command('app:check-event-command')
    ->everyThirtyMinutes()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
