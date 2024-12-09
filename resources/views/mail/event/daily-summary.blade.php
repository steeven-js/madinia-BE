@component('mail::message')
# Rapport quotidien des événements

Il y a actuellement {{ $count }} événement(s) programmé(s).

@if($events->count() > 0)
## Liste des événements :

@component('mail::table')
| Titre | Date programmée | Statut |
|:------|:---------------|:--------|
@foreach($events as $event)
| {{ $event->title }} | {{ \Carbon\Carbon::parse($event->scheduled_date)->format('d/m/Y H:i') }} | {{ $event->status }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => $url])
Voir tous les événements
@endcomponent
@endif

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
