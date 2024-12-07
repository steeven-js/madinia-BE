@component('mail::message')
# Bonjour {{ $userName }}

Ceci est un email de test envoyé depuis {{ $appName }}.

## Informations principales
- Date : {{ now()->format('d/m/Y') }}
- Heure : {{ now()->format('H:i:s') }}
- Application : {{ $appName }}

@component('mail::panel')
Cet email confirme que votre configuration SMTP fonctionne correctement.
@endcomponent

@component('mail::button', ['url' => $appUrl, 'color' => 'success'])
Accéder à l'application
@endcomponent

@component('mail::table')
| Paramètre     | Valeur                           |
|---------------|----------------------------------|
| Serveur SMTP  | {{ config('mail.mailers.smtp.host') }} |
| Port         | {{ config('mail.mailers.smtp.port') }} |
| Encryption   | {{ config('mail.mailers.smtp.encryption') }} |
@endcomponent

Si vous n'avez pas demandé cet email, vous pouvez l'ignorer en toute sécurité.

Cordialement,<br>
L'équipe {{ $appName }}

<small>Envoyé automatiquement - Ne pas répondre</small>
@endcomponent
