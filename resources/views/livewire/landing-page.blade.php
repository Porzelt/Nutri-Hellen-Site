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

    <footer class="bg-gray-800 text-white py-8 text-center text-sm">
        <p>&copy; {{ date('Y') }} Nutri Hellen. Todos os direitos reservados.</p>
        <p class="mt-2 opacity-50">Desenvolvido com 🖤 por Henrique Porzelt</p>
    </footer>
</div>