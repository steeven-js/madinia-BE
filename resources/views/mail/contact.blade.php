<x-mail::message>
# Introduction

## Contact Information
- **Company:** {{ $contactMail->company }}
- **First Name:** {{ $contactMail->firstName }}
- **Last Name:** {{ $contactMail->lastName }}
- **Email:** {{ $contactMail->email }}
- **Phone Number:** {{ $contactMail->phoneNumber }}

@if ($contactMail->message)
## Message
{{ $contactMail->message }}
@endif

Merci,<br>
{{ config('app.name') }}
</x-mail::message>