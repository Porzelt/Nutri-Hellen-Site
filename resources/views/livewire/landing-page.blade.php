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
<<<<<<< Updated upstream
=======
    <a href="https://wa.me/5511967261034?text=Olá,%20gostaria%20de%20saber%20mais%20sobre%20a%20consulta."
        target="_blank"
        class="fixed bottom-6 right-6 bg-green-500 text-white p-4 rounded-full shadow-2xl hover:bg-green-600 transition z-50 flex items-center justify-center group">

        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
            <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
        </svg>

        <span class="hidden group-hover:block ml-2 font-bold">Falar no WhatsApp</span>
    </a>
>>>>>>> Stashed changes
</div>