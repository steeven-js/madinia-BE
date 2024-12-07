<?php

use App\Mail\TestEmail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

// Commande inspire par défaut
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Commande pour l'envoi des emails planifiés à une adresse spécifique
Artisan::command('mail:test', function () {
    try {
        Mail::to('s.jacques@madin-ia.com')->send(new TestEmail());
        $this->info("Email test envoyé avec succès à : s.jacques@madin-ia.com");

        // Afficher plus d'informations pour le débogage
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
    ->hourly()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
