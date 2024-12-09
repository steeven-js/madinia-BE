<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
    public function handle()
    {
        try {
            // Configurer le fuseau horaire pour la Martinique (UTC-4)
            date_default_timezone_set('America/Martinique');

            // Récupérer tous les événements à vérifier
            $events = Event::where('status', '!=', 'past')->get();

            foreach ($events as $event) {
                $scheduledDate = Carbon::parse($event->scheduled_date);
                $now = now();

                // Vérifier si l'événement doit être mis à jour
                if ($this->shouldUpdateToCurrent($scheduledDate, $now)) {
                    $this->updateEventStatus($event, true, 'current');
                    $this->notifyCloudFunction($event->id);
                } elseif ($this->shouldUpdateToPast($scheduledDate, $now)) {
                    $this->updateEventStatus($event, false, 'past');
                }
            }

            $this->info('Vérification des événements terminée avec succès');
            return 0;

        } catch (\Exception $e) {
            $this->error('Une erreur est survenue : ' . $e->getMessage());
            return 1;
        }
    }

    private function shouldUpdateToCurrent(Carbon $scheduledDate, Carbon $now): bool
    {
        return $scheduledDate->format('Y-m-d H') === $now->format('Y-m-d H');
    }

    private function shouldUpdateToPast(Carbon $scheduledDate, Carbon $now): bool
    {
        return $scheduledDate->addDay()->format('Y-m-d H') === $now->format('Y-m-d H');
    }

    private function updateEventStatus(Event $event, bool $isActive, string $status): void
    {
        $event->update([
            'is_active' => $isActive,
            'status' => $status,
            'last_updated' => now()
        ]);
    }

    private function notifyCloudFunction(string $eventId): void
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.bearer_cloud_token')
            ])->post('https://scheduleevent-641409071815.us-central1.run.app', [
                'eventId' => $eventId
            ]);

            if (!$response->successful()) {
                throw new \Exception('Échec de la notification Cloud Function: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Erreur lors de la notification Cloud Function: ' . $e->getMessage());
        }
    }
}
