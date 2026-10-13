<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Google\GmailService;

class TestGoogleGmail extends Command
{
    protected $signature = 'google:test-gmail';

    protected $description = 'Prueba el envío de correo mediante Google Gmail API';

    public function handle(GmailService $gmail)
    {
        $this->info('Enviando correo...');

        $gmail->send(
            'avina2097@gmail.com',
            'Prueba Gmail API - Ifix',
            'Este correo fue enviado desde Laravel utilizando Google Workspace Gmail API.'
        );

        $this->info('Correo enviado correctamente.');

        return self::SUCCESS;
    }
}