<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Gera um token novo para a API da anamnese. O token vai só para o app local
 * (arquivo fora do git); o servidor guarda apenas o hash (contrato, seção 4).
 */
class GerarTokenApi extends Command
{
    protected $signature = 'anamnese:token';

    protected $description = 'Gera um token para a API da anamnese e o hash para o ambiente do servidor';

    public function handle(): int
    {
        $token = Str::random(64);

        $this->warn('Guarde o TOKEN só no app local (fora do git). Ele não é mostrado de novo.');
        $this->newLine();
        $this->line('TOKEN (app local):');
        $this->line($token);
        $this->newLine();
        $this->line('Variável do servidor (Coolify / .env):');
        $this->line('ANAMNESE_API_TOKEN_HASH='.hash('sha256', $token));

        return self::SUCCESS;
    }
}
