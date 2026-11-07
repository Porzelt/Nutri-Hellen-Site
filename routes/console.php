<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Limpeza diária da anamnese (contrato, seção 9). Em produção, o Coolify roda
// "php artisan anamnese:limpar" uma vez por dia (tarefa agendada da aplicação).
Schedule::command('anamnese:limpar')->dailyAt('03:30')->timezone('America/Sao_Paulo');
