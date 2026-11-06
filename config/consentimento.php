<?php

/*
 * Texto de consentimento (LGPD) mostrado no início da anamnese.
 * Contrato INTEGRACAO-CONSULTORIO.md, seção 8: o texto é aprovado pela nutricionista
 * antes de entrar no ar. Ao mudar o texto, mude também a 'versao' (data AAAA-MM-DD):
 * cada resposta guarda a versão aceita.
 *
 * RASCUNHO: este texto ainda NÃO foi aprovado. Enquanto 'rascunho' for true,
 * a página mostra um aviso de rascunho.
 */
return [
    'versao' => '2026-11-06',

    'rascunho' => true,

    'titulo' => 'Antes de começar',

    'paragrafos' => [
        'Este formulário coleta informações sobre a sua saúde, rotina e alimentação para preparar a sua consulta com :nome. Essas informações são dados pessoais sensíveis e são tratadas conforme a Lei Geral de Proteção de Dados (Lei 13.709/2018).',
        'Usamos as suas respostas somente para o seu atendimento nutricional. Elas não são compartilhadas com terceiros nem usadas para propaganda.',
        'As respostas e os arquivos ficam guardados de forma protegida (criptografada) neste site apenas até serem transferidos para o prontuário do consultório, e são apagados daqui em no máximo 7 dias depois do envio.',
        'Não pedimos CPF, RG nem endereço. Você pode deixar em branco o que não souber e pode pedir a qualquer momento acesso, correção ou exclusão dos seus dados falando diretamente com a nutricionista.',
    ],

    'aceite' => 'Li e concordo com o uso das minhas informações para o meu atendimento nutricional.',
];
