<?php

/*
 * Marca configurável por ambiente (contrato INTEGRACAO-CONSULTORIO.md, seção 11).
 * Permite que o mesmo site sirva outra nutricionista só trocando o .env.
 * Valor vazio no .env cai no padrão abaixo.
 */
return [
    'nome' => env('MARCA_NOME') ?: 'Hellen Fátima',
    'titulo' => env('MARCA_TITULO') ?: 'Nutricionista',
    'registro' => env('MARCA_REGISTRO') ?: 'CRN-3 73749', // registro profissional (exigência do CFN)
    'logo' => env('MARCA_LOGO') ?: '/img/logo-hellen.webp',
    'cor_primaria' => env('MARCA_COR_PRIMARIA') ?: '#5F6F3A',
    'whatsapp' => env('MARCA_WHATSAPP') ?: '5511967261034', // só dígitos, com DDI
    'email' => env('MARCA_EMAIL') ?: null,
    'instagram' => env('MARCA_INSTAGRAM') ?: 'hellenfatima.nutri', // sem @
    'bairro' => env('MARCA_BAIRRO') ?: 'Parada Inglesa',
    'cidade' => env('MARCA_CIDADE') ?: 'São Paulo',
    'atendimento' => env('MARCA_ATENDIMENTO') ?: 'presencial e online',
];
