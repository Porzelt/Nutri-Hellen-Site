@php
    $marca = config('marca');
    $tituloPadrao = $marca['titulo'].' '.$marca['nome'].' em São Paulo · Emagrecimento e reeducação alimentar';
    $descricaoPadrao = $marca['titulo'].' '.$marca['nome'].' ('.$marca['registro'].'), na '.$marca['bairro'].', '.$marca['cidade']
        .'. Atendimento '.$marca['atendimento'].' para emagrecimento, diabetes, SOP e gestação, com comida de verdade e sem dietas malucas.';
    $titulo = $title ?? $tituloPadrao;
    $descricao = $description ?? $descricaoPadrao;
    $canonico = url()->current();
    $imagemCompartilhamento = url('/img/compartilhar.jpg');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <meta name="description" content="{{ $descricao }}">
    <link rel="canonical" href="{{ $canonico }}">
    <meta name="theme-color" content="{{ $marca['cor_primaria'] }}">
    @if (request()->is('dashboard*', 'login'))
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- Prévia ao compartilhar (WhatsApp, Instagram, Facebook) --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="{{ $marca['titulo'] }} {{ $marca['nome'] }}">
    <meta property="og:title" content="{{ $titulo }}">
    <meta property="og:description" content="{{ $descricao }}">
    <meta property="og:url" content="{{ $canonico }}">
    <meta property="og:image" content="{{ $imagemCompartilhamento }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $marca['titulo'] }} {{ $marca['nome'] }}: emagreça comendo comida de verdade">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $titulo }}">
    <meta name="twitter:description" content="{{ $descricao }}">
    <meta name="twitter:image" content="{{ $imagemCompartilhamento }}">

    {{ $head ?? '' }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased font-sans">

    {{ $slot }}

</body>
</html>
