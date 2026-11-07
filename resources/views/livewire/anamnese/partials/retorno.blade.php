{{-- Etapas do formulário de retorno (contrato, seção 6.3). Usa as variáveis da view principal. --}}
@if ($tipo === 'retorno')

    {{-- 1. Como foi o plano --}}
    @if ($etapa === 1)
        <div>
            <label class="{{ $label }}" for="r_nome">Seu nome</label>
            <input id="r_nome" type="text" wire:model="f.nome" class="{{ $input }}" autocomplete="name">
            {!! $erro('f.nome') !!}
        </div>
        <div>
            <label class="{{ $label }}" for="r_peso">Peso atual (kg)</label>
            {!! $dica('peso_kg') !!}
            <input id="r_peso" type="text" inputmode="decimal" placeholder="ex.: 72,5" wire:model="f.peso_kg" class="{{ $input }}">
            {!! $erro('f.peso_kg') !!}
        </div>
        <div>
            <span class="{{ $label }}">Como foi seguir o plano alimentar?</span>
            {!! $dica('adesao') !!}
            <div class="grid grid-cols-2 gap-2">
                @foreach (\App\Anamnese\Retorno::ADESAO as $opcao)
                    <label class="flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-3 has-[:checked]:border-[var(--cor-marca)] has-[:checked]:bg-[var(--cor-marca)]/10">
                        <input type="radio" wire:model="f.adesao" value="{{ $opcao }}" class="accent-[var(--cor-marca)]"> {{ $opcao }}
                    </label>
                @endforeach
            </div>
            {!! $erro('f.adesao') !!}
        </div>
        <div>
            <label class="{{ $label }}" for="r_dif">O que foi mais difícil?</label>
            <textarea id="r_dif" rows="2" placeholder="ex.: jantar fora, beliscar à tarde" wire:model="f.dificuldades" class="{{ $input }}"></textarea>
            {!! $erro('f.dificuldades') !!}
        </div>
        <div>
            <label class="{{ $label }}" for="r_mud">O que mudou desde a última consulta?</label>
            <textarea id="r_mud" rows="2" placeholder="ex.: mais disposição, roupas mais folgadas" wire:model="f.mudancas" class="{{ $input }}"></textarea>
            {!! $erro('f.mudancas') !!}
        </div>
    @endif

    {{-- 2. Rotina e saúde --}}
    @if ($etapa === 2)
        <div>
            <label class="{{ $label }}" for="r_ativ">Atividade física agora</label>
            <input id="r_ativ" type="text" placeholder="ex.: caminhada 3x por semana" wire:model="f.atividade" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}" for="r_sono">Como está o sono?</label>
            <input id="r_sono" type="text" placeholder="ex.: 7 horas, acordando descansada" wire:model="f.sono" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}" for="r_int">Como está o intestino?</label>
            {!! $dica('intestino') !!}
            <input id="r_int" type="text" placeholder="ex.: 1 vez ao dia" wire:model="f.intestino" class="{{ $input }}">
        </div>
        <div>
            <span class="{{ $label }}">Quantos litros de água bebe por dia?</span>
            {!! $dica('hidratacao') !!}
            <div class="grid grid-cols-2 gap-2">
                @foreach (\App\Anamnese\PrimeiraConsulta::HIDRATACAO as $opcao)
                    <label class="flex items-center gap-2 rounded-xl border border-gray-300 px-3 py-3 text-sm has-[:checked]:border-[var(--cor-marca)] has-[:checked]:bg-[var(--cor-marca)]/10">
                        <input type="radio" wire:model="f.hidratacao" value="{{ $opcao }}" class="accent-[var(--cor-marca)]"> {{ $opcao }}
                    </label>
                @endforeach
            </div>
            {!! $erro('f.hidratacao') !!}
        </div>
        <div>
            <label class="{{ $label }}" for="r_cond">Alguma novidade de saúde?</label>
            {!! $dica('condicoes_novas') !!}
            <textarea id="r_cond" rows="2" placeholder="Se não houver, deixe em branco" wire:model="f.condicoes_novas" class="{{ $input }}"></textarea>
            {!! $erro('f.condicoes_novas') !!}
        </div>
    @endif

    {{-- 3. Medicamentos e suplementos --}}
    @if ($etapa === 3)
        @include('livewire.anamnese.partials.itens')
    @endif
@endif
