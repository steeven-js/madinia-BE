<?php

use App\Mail\TestEmail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Commande inspire par défaut
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Commande pour l'envoi des emails planifiés
Artisan::command('mail:test {email?}', function (string $email = null) {
    $targetEmail = $email ?? config('mail.from.address');

    try {
        Mail::to($targetEmail)->send(new TestEmail());
        $this->info("Email test envoyé avec succès à : $targetEmail");

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
->everyFiveMinutes(); // Ajout de la planification ici
