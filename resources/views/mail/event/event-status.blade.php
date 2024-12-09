@component('mail::message')
# Événement actif

L'événement "{{ $event->title }}" est maintenant actif.

## Détails :
- Date : {{ \Carbon\Carbon::parse($event->scheduled_date)->format('d/m/Y') }}
- Heure : {{ \Carbon\Carbon::parse($event->scheduled_date)->format('H:i') }}
- Statut : {{ $event->status }}

@component('mail::button', ['url' => $url])
Voir l'événement
@endcomponent

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
