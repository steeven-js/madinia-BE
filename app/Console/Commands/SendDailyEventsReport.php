<?php
// app/Console/Commands/SendDailyEventsReport.php

namespace App\Console\Commands;

use App\Models\Event;
use App\Mail\DailyEventsSummaryMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyEventsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie le rapport quotidien des événements programmés';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            date_default_timezone_set('America/Martinique');

            $this->info('Début de la génération du rapport quotidien');

            // Récupérer tous les événements non passés
            $events = Event::where('status', '!=', 'past')->get();
            $count = $events->count();

            $this->info("Nombre d'événements à inclure dans le rapport : {$count}");

            // Envoyer l'email
            Mail::to(config('mail.admin_address'))
                ->send(new DailyEventsSummaryMail($events, $count));

            $this->info('Rapport quotidien envoyé avec succès');
            return 0;
        } catch (\Exception $e) {
            $this->error('Une erreur est survenue : ' . $e->getMessage());
            $this->error('Stack trace : ' . $e->getTraceAsString());
            return 1;
        }
    }
}
