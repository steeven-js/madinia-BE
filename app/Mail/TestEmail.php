<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;

class TestEmail extends Mailable
{
    public function __construct(
        public string $userName = 'Utilisateur'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'test@example.com'),
                config('mail.from.name', 'Test Sender')
            ),
            subject: 'Test Email - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.test.test',
            with: [
                'userName' => $this->userName,
                'appName' => config('app.name'),
                'appUrl' => config('app.url')
            ]
        );
    }
}
