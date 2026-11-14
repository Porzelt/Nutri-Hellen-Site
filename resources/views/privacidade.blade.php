<x-layouts.app>
    <x-slot:title>Política de privacidade | {{ config('marca.titulo') }} {{ config('marca.nome') }}</x-slot:title>
    <x-slot:description>Como {{ config('marca.titulo') }} {{ config('marca.nome') }} trata os dados do agendamento e do formulário de anamnese, conforme a LGPD.</x-slot:description>

    <main class="max-w-3xl mx-auto px-6 py-12">
        <a href="{{ route('home') }}" class="text-sm text-brand-secondary hover:underline">&larr; Voltar ao site</a>

        <h1 class="mt-6 text-3xl font-bold text-gray-800">Política de privacidade</h1>
        <p class="mt-2 text-sm text-gray-500">Versão 2026-11-13 (aprovada pela nutricionista)</p>

        <div class="mt-8 space-y-8 text-gray-700 leading-relaxed">
            <section>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Quem cuida dos seus dados</h2>
                <p>
                    {{ config('marca.titulo') }} {{ config('marca.nome') }}@if (config('marca.registro')) ({{ config('marca.registro') }})@endif,
                    responsável pelo tratamento dos dados coletados neste site, conforme a Lei Geral de Proteção de Dados (Lei 13.709/2018).
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Formulário de agendamento</h2>
                <p>
                    Quando você pede um agendamento, guardamos seu nome, telefone e a preferência de horário informada,
                    só para entrar em contato pelo WhatsApp e marcar a consulta. Esses dados ficam até o contato ser
                    concluído ou até você pedir a exclusão.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Formulário de anamnese (antes da consulta)</h2>
                <p>
                    O formulário de anamnese só é acessado por um link individual enviado pela nutricionista. Ele coleta
                    informações sobre sua saúde, rotina e alimentação, e permite anexar exames. São dados pessoais sensíveis,
                    usados somente para o seu atendimento nutricional, com o seu consentimento registrado no início do formulário.
                </p>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li>Não pedimos CPF, RG nem endereço.</li>
                    <li>As respostas e os arquivos ficam guardados de forma criptografada, em área sem acesso público.</li>
                    <li>Depois de transferidos para o prontuário do consultório, são apagados deste site.</li>
                    <li>Respostas não transferidas são apagadas automaticamente em até {{ config('anamnese.retencao_dias') }} dias após o envio, e links não respondidos, ao vencer.</li>
                    <li>As cópias de segurança do banco de dados são mantidas por até 7 dias e depois descartadas.</li>
                    <li>As páginas do formulário não usam ferramentas de análise, propaganda ou scripts de terceiros.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Compartilhamento</h2>
                <p>
                    Seus dados não são vendidos nem compartilhados para propaganda. O site fica hospedado em servidor contratado
                    pela nutricionista, que só armazena as informações.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Seus direitos</h2>
                <p>
                    Você pode pedir a qualquer momento acesso, correção ou exclusão dos seus dados, ou retirar o consentimento,
                    falando diretamente com a nutricionista:
                </p>
                <ul class="mt-3 list-disc pl-6 space-y-1">
                    <li>WhatsApp: <a class="text-brand-secondary underline" href="https://wa.me/{{ config('marca.whatsapp') }}">falar pelo WhatsApp</a></li>
                    @if (config('marca.email'))
                        <li>E-mail: <a class="text-brand-secondary underline" href="mailto:{{ config('marca.email') }}">{{ config('marca.email') }}</a></li>
                    @endif
                </ul>
            </section>
        </div>
    </main>
</x-layouts.app>
