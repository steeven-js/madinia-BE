<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email test immédiatement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Mail::to('test@example.com')->send(new TestEmail());
            $this->info('Email test envoyé avec succès');
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi : ' . $e->getMessage());
        }
    }
}
