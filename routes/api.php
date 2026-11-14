<?php

use App\Http\Controllers\Api\AnamneseApiController as Api;
use App\Http\Middleware\AutenticaApiAnamnese;
use Illuminate\Support\Facades\Route;

// API v1 para o app local (contrato INTEGRACAO-CONSULTORIO.md, seção 5).
// Limite antes da autenticação, para frear tentativa de adivinhar o token.
Route::prefix('v1')
    ->middleware(['throttle:api-anamnese', AutenticaApiAnamnese::class])
    ->group(function () {
        Route::get('saude', [Api::class, 'saude']);

        Route::post('convites', [Api::class, 'criarConvite']);
        Route::delete('convites/{id}', [Api::class, 'cancelarConvite'])->whereUuid('id');

        Route::get('respostas', [Api::class, 'listarRespostas']);
        Route::get('respostas/{id}', [Api::class, 'verResposta'])->whereUuid('id');
        Route::get('respostas/{id}/arquivos/{arquivoId}', [Api::class, 'baixarArquivo'])->whereUuid(['id', 'arquivoId']);
        Route::post('respostas/{id}/importado', [Api::class, 'importado'])->whereUuid('id');
    });
