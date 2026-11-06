<?php

/*
 * Marca configurável por ambiente (contrato INTEGRACAO-CONSULTORIO.md, seção 11).
 * Permite que o mesmo site sirva outra nutricionista só trocando o .env.
 * Valor vazio no .env cai no padrão abaixo.
 */
return [
    'nome' => env('MARCA_NOME') ?: 'Hellen Fátima',
    'titulo' => env('MARCA_TITULO') ?: 'Nutricionista',
    'registro' => env('MARCA_REGISTRO') ?: null, // ex.: CRN-3 00000
    'logo' => env('MARCA_LOGO') ?: '/img/logo-hellen.webp',
    'cor_primaria' => env('MARCA_COR_PRIMARIA') ?: '#5F6F3A',
    'whatsapp' => env('MARCA_WHATSAPP') ?: '5511967261034', // só dígitos, com DDI
    'email' => env('MARCA_EMAIL') ?: null,
];
