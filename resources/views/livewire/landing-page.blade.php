<div class="bg-brand-light min-h-screen">
    <section class="relative py-20 lg:py-32 overflow-hidden">
        <div class="container mx-auto px-6 flex flex-col-reverse lg:flex-row items-center gap-12">
            <div class="lg:w-1/2 text-center lg:text-left">
                <h1 class="text-4xl lg:text-5xl font-bold text-brand-secondary leading-tight mb-6">
                    Nutrição com afeto e <span class="text-brand-primary">estratégia.</span>
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    Atingir seus objetivos de saúde não precisa ser uma guerra contra a comida.
                </p>
                <a href="#agendamento" class="inline-block bg-brand-primary hover:bg-orange-700 text-white font-semibold px-8 py-4 rounded-lg transition shadow-lg hover:-translate-y-1">
                    Agendar Consulta
                </a>
            </div>
            <div class="lg:w-1/2">
                <div class="bg-brand-secondary/20 border-2 border-brand-secondary border-dashed rounded-tr-[4rem] rounded-bl-[4rem] w-full h-64 lg:h-96 flex items-center justify-center text-brand-secondary font-bold shadow-xl">
                    FOTO PRINCIPAL
                </div>
            </div>
        </div>
    </section>

    <section id="sobre" class="py-20 bg-white">
        <div class="container mx-auto px-6 flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-1/2">
                <div class="bg-brand-primary/10 rounded-xl w-full h-80 flex items-center justify-center text-brand-primary font-bold">
                    FOTO CONSULTÓRIO
                </div>
            </div>
            <div class="lg:w-1/2">
                <h2 class="text-3xl font-bold text-brand-secondary mb-6">Olá, sou a Hellen</h2>
                <div class="space-y-4 text-gray-600 leading-relaxed">
                    <p>
                        Acredito na nutrição que acolhe. Minha paleta de trabalho envolve ciência e comportamento.
                    </p>
                    <p>
                        Especialista em saúde da mulher e emagrecimento, meu consultório é um espaço seguro para você fazer as pazes com o espelho e com o prato.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-brand-light">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-brand-secondary mb-12">Especialidades</h2>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition border-t-4 border-brand-primary">
                    <div class="text-brand-primary text-4xl mb-4">🍎</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Reeducação</h3>
                    <p class="text-gray-600 text-sm">Autonomia para fazer boas escolhas em qualquer lugar.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition border-t-4 border-brand-secondary">
                    <div class="text-brand-secondary text-4xl mb-4">⚖️</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Emagrecimento</h3>
                    <p class="text-gray-600 text-sm">Estratégias metabólicas sem dietas restritivas.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition border-t-4 border-brand-primary">
                    <div class="text-brand-primary text-4xl mb-4">🤰</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Materno Infantil</h3>
                    <p class="text-gray-600 text-sm">Nutrição programada da gestação à infância.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="agendamento" class="py-20 bg-white">
        <div class="container mx-auto px-6 max-w-2xl text-center">
            <h2 class="text-3xl font-bold text-brand-secondary mb-6">Vamos cuidar de você?</h2>
            <p class="text-gray-600 mb-8">Preencha seus dados. Eu mesma entrarei em contato pelo WhatsApp.</p>

            <livewire:agendamento-form />
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-10 text-center text-sm relative">
        <div class="container mx-auto px-6 ">
            <p class="font-medium">&copy; {{ date('Y') }} Nutri Hellen. Todos os direitos reservados.</p>
            <p class="mt-2 text-gray-400">Desenvolvido com 🖤 por Henrique Porzelt</p>

            <div class="mt-8 border-t border-gray-700 pt-8 flex justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-brand-primary hover:text-white transition text-xs font-bold uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        Acessar Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-400 transition" title="Área Restrita">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </a>
                @endauth
            </div>
        </div>
    </footer>
</div>