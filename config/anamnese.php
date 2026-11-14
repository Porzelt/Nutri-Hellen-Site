<?php

/*
 * Anamnese e API v1 para o app local (contrato INTEGRACAO-CONSULTORIO.md).
 */
return [
    // Hash sha256 do token da API (nunca o token em si). Gere com: php artisan anamnese:token
    'api_token_hash' => env('ANAMNESE_API_TOKEN_HASH') ?: null,

    // Requisições por minuto, por IP e por token
    'api_limite_por_minuto' => (int) (env('ANAMNESE_API_LIMITE') ?: 60),

    // Respostas enviadas e não importadas são apagadas depois de N dias (contrato, seção 9)
    'retencao_dias' => 7,
];
