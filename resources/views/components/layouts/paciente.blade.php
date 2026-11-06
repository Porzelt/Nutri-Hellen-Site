<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="no-referrer">
    <title>{{ $title ?? 'Formulário de anamnese' }} | {{ config('marca.titulo') }} {{ config('marca.nome') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --cor-marca: {{ preg_match('/^#[0-9a-fA-F]{3,8}$/', config('marca.cor_primaria')) ? config('marca.cor_primaria') : '#5F6F3A' }}; }</style>
</head>
<body class="bg-brand-light text-gray-800 antialiased font-sans min-h-screen">
    <header class="bg-white shadow-sm">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center gap-3">
            <img src="{{ config('marca.logo') }}" alt="" class="h-10 w-auto" width="107" height="40">
            <div class="leading-tight">
                <div class="font-bold text-gray-800">{{ config('marca.nome') }}</div>
                <div class="text-xs text-gray-500">{{ config('marca.titulo') }}@if (config('marca.registro')) · {{ config('marca.registro') }}@endif</div>
            </div>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    <footer class="max-w-xl mx-auto px-4 pb-8 text-center text-xs text-gray-500">
        Seus dados são protegidos e usados só para o seu atendimento.
    </footer>
</body>
</html>
