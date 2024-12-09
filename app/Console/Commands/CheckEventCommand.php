<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Console\Command;
use App\Mail\EventStatusChangedMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class CheckEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-event-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    private $cloudFunctionUrl;

    public function __construct()
    {
        parent::__construct();
        $this->cloudFunctionUrl = config('services.gcloud.event_function_url');
    }

    public function handle()
    {
        $this->info('DEV - Début de la vérification des événements');

        $now = Carbon::now();
        $this->showCurrentState($now);

        $events = Event::where('scheduled_date', '<=', $now)
            ->whereIn('status', ['pending', 'current'])
            ->get();

        $this->info('DEV - Nombre d\'événements à vérifier : ' . $events->count());

        if ($events->isEmpty()) {
            $this->showEmptyStateInfo();
            return;
        }

        foreach ($events as $event) {
            $this->processEvent($event);
        }

        $this->info('DEV - Vérification des événements terminée avec succès');
    }

    private function showCurrentState(Carbon $now)
    {
        $this->line("\nDEV - État actuel de la vérification:");
        $this->table(
            ['Information', 'Valeur'],
            [
                ['Date et heure actuelles', $now->format('Y-m-d H:i:s')],
                ['Timezone', $now->tzName],
                ['URL Cloud Function', $this->cloudFunctionUrl],
                ['Email administrateur', config('services.mail.admin_address')],
            ]
        );
    }

    private function showEmptyStateInfo()
    {
        $this->line("\nDEV - Analyse des événements dans la base de données:");

        // Récupérer des statistiques sur les événements
        $totalEvents = Event::count();
        $pendingEvents = Event::where('status', 'pending')->count();
        $currentEvents = Event::where('status', 'current')->count();
        $pastEvents = Event::where('status', 'past')->count();
        $futureEvents = Event::where('scheduled_date', '>', Carbon::now())->count();

        $this->table(
            ['Type d\'événements', 'Nombre'],
            [
                ['Total des événements', $totalEvents],
                ['Événements en attente (pending)', $pendingEvents],
                ['Événements en cours (current)', $currentEvents],
                ['Événements passés (past)', $pastEvents],
                ['Événements futurs', $futureEvents],
            ]
        );

        // Afficher le prochain événement programmé
        $nextEvent = Event::where('status', 'pending')
            ->orderBy('scheduled_date', 'asc')
            ->first();

        if ($nextEvent) {
            $this->line("\nDEV - Prochain événement programmé:");
            $this->table(
                ['Champ', 'Valeur'],
                [
                    ['Firebase ID', $nextEvent->firebaseId],
                    ['Date programmée', $nextEvent->scheduled_date],
                    ['Status', $nextEvent->status],
                    ['Is Active', $nextEvent->is_active ? 'true' : 'false'],
                    ['Temps restant', Carbon::parse($nextEvent->scheduled_date)->diffForHumans()],
                ]
            );
        } else {
            $this->info("\nDEV - Aucun événement futur n'est programmé");
        }

        $this->info("\nDEV - Vérification terminée - Aucun événement à traiter pour le moment");
    }

    private function processEvent(Event $event)
    {
        $this->info("DEV - Vérification de l'événement Firebase ID: {$event->firebaseId}");
        $this->info("Date programmée: {$event->scheduled_date}");
        $this->info("Status actuel: {$event->status}");

        if ($this->shouldUpdateEvent($event)) {
            $this->updateEvent($event);
        } else {
            $this->info("DEV - Aucun changement nécessaire pour l'événement {$event->firebaseId}");
        }
    }

    private function shouldUpdateEvent(Event $event): bool
    {
        return $event->status === 'pending' &&
            Carbon::parse($event->scheduled_date)->lte(Carbon::now());
    }

    private function updateEvent(Event $event)
    {
        $this->info("DEV - L'événement {$event->firebaseId} passe à CURRENT");

        // Sauvegarder les anciennes valeurs
        $oldValues = [
            'is_active' => $event->is_active,
            'status' => $event->status,
            'last_updated' => $event->last_updated,
        ];

        // Nouvelles valeurs
        $newValues = [
            'is_active' => true,
            'status' => 'current',
            'last_updated' => Carbon::now(),
        ];

        // Afficher le tableau des changements
        $this->info("DEV - Mise à jour de l'événement {$event->firebaseId}:");
        $this->table(
            ['Champ', 'Ancienne valeur', 'Nouvelle valeur'],
            collect($newValues)->map(function ($newValue, $field) use ($oldValues) {
                return [
                    $field,
                    $oldValues[$field] === true ? 'true' : ($oldValues[$field] === false ? 'false' : $oldValues[$field]),
                    $newValue === true ? 'true' : ($newValue === false ? 'false' : $newValue),
                ];
            })->toArray()
        );

        try {
            // Faire l'appel à la Cloud Function
            $response = $this->callCloudFunction($event, $newValues);

            if ($response->successful()) {
                // Mettre à jour la base de données locale
                $event->update($newValues);
                Mail::to(config('services.mail.admin_address'))->send(new EventStatusChangedMail($event));
                $this->info("DEV - Mise à jour réussie pour l'événement {$event->firebaseId}");
            } else {
                $this->error("DEV - Erreur lors de l'appel à la Cloud Function: " . $response->status());
                $this->error("DEV - Réponse: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("DEV - Exception lors de la mise à jour: " . $e->getMessage());
        }
    }

    private function callCloudFunction(Event $event, array $newValues)
    {
        $this->info("DEV - Appel de la Cloud Function pour l'événement {$event->firebaseId}");

        $requestBody = [
            'firebaseId' => $event->firebaseId,
            'updates' => [
                'status' => $newValues['status'],
                'isActive' => $newValues['is_active'],
                'lastUpdated' => $newValues['last_updated']->format('Y-m-d H:i:s'),
            ]
        ];

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . config('services.bearer_cloud_token'),
        ])->post($this->cloudFunctionUrl, $requestBody);
    }


}
