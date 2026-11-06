@php
    use App\Anamnese\PrimeiraConsulta as P;
    $input = 'w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base focus:border-[var(--cor-marca)] focus:ring-2 focus:ring-[var(--cor-marca)]/30 outline-none';
    $label = 'block text-sm font-semibold text-gray-700 mb-1';
    $card = 'bg-white rounded-2xl shadow-sm p-5 space-y-5';
    $erro = fn ($campo) => $errors->has($campo) ? '<p class="text-red-600 text-sm mt-1">'.e($errors->first($campo)).'</p>' : '';
    $total = count($etapas);
@endphp

<div>
    {{-- Link inválido, expirado ou já usado: mensagem genérica (contrato, seção 8) --}}
    @if (! $conviteId && ! $enviado)
        <div class="{{ $card }} text-center">
            <h1 class="text-xl font-bold text-gray-800">Link indisponível</h1>
            <p class="text-gray-600">Este link não está mais disponível. Se precisar, fale com a nutricionista para receber um novo.</p>
            <a href="https://wa.me/{{ $marca['whatsapp'] }}" class="inline-block rounded-full px-6 py-3 font-bold text-white" style="background: var(--cor-marca)">Falar no WhatsApp</a>
        </div>

    {{-- Agradecimento --}}
    @elseif ($enviado)
        <div class="{{ $card }} text-center">
            <div class="text-5xl">✅</div>
            <h1 class="text-xl font-bold text-gray-800">Recebemos suas respostas!</h1>
            <p class="text-gray-600">Obrigada por preencher. A {{ $marca['nome'] }} vai analisar tudo antes da sua consulta. Você já pode fechar esta página.</p>
        </div>

    {{-- Retorno: chega numa próxima etapa do desenvolvimento --}}
    @elseif ($tipo !== 'primeira')
        <div class="{{ $card }} text-center">
            <h1 class="text-xl font-bold text-gray-800">Formulário em preparação</h1>
            <p class="text-gray-600">Este formulário ainda não está disponível. Fale com a nutricionista.</p>
        </div>

    {{-- Consentimento --}}
    @elseif ($etapa === 0)
        <div class="{{ $card }}">
            @if (config('consentimento.rascunho'))
                <p class="rounded-lg bg-yellow-100 text-yellow-900 text-xs font-bold px-3 py-2">RASCUNHO: texto ainda não aprovado pela nutricionista.</p>
            @endif
            <h1 class="text-xl font-bold text-gray-800">{{ config('consentimento.titulo') }}</h1>
            <div class="space-y-3 text-gray-700 text-sm leading-relaxed">
                @foreach (config('consentimento.paragrafos') as $paragrafo)
                    <p>{{ str_replace(':nome', $marca['nome'], $paragrafo) }}</p>
                @endforeach
            </div>
            <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4">
                <input type="checkbox" wire:model="aceite" class="mt-1 h-5 w-5 accent-[var(--cor-marca)]">
                <span class="text-sm font-medium text-gray-800">{{ config('consentimento.aceite') }}</span>
            </label>
            {!! $erro('aceite') !!}
            <button type="button" wire:click="aceitar" class="w-full rounded-full py-4 font-bold text-white text-lg" style="background: var(--cor-marca)">
                Começar
            </button>
            <p class="text-xs text-gray-500 text-center">Leva cerca de 10 minutos. Você pode parar e voltar a este link depois: o que já respondeu fica salvo.</p>
        </div>

    {{-- Formulário em etapas --}}
    @else
        <div class="mb-4">
            <div class="flex justify-between text-xs text-gray-600 mb-1">
                <span>Etapa {{ $etapa }} de {{ $total }}</span>
                <span class="font-semibold">{{ $etapas[$etapa] }}</span>
            </div>
            <div class="h-2 rounded-full bg-white overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $etapa }}">
                <div class="h-full rounded-full transition-all" style="width: {{ round($etapa / $total * 100) }}%; background: var(--cor-marca)"></div>
            </div>
        </div>

        <form wire:submit="{{ $etapa === $total ? 'enviar' : 'avancar' }}" class="space-y-4" novalidate>
            <div class="{{ $card }}">
                <h2 class="text-lg font-bold text-gray-800">{{ $etapas[$etapa] }}</h2>

                {{-- 1. Sobre você --}}
                @if ($etapa === 1)
                    <div>
                        <label class="{{ $label }}" for="nome">Nome completo</label>
                        <input id="nome" type="text" wire:model="f.nome" class="{{ $input }}" autocomplete="name">
                        {!! $erro('f.nome') !!}
                    </div>

                    @if (! $f['usa_idade'])
                        <div>
                            <label class="{{ $label }}" for="nascimento">Data de nascimento</label>
                            <input id="nascimento" type="date" wire:model="f.nascimento" class="{{ $input }}" max="{{ now()->toDateString() }}">
                            {!! $erro('f.nascimento') !!}
                        </div>
                    @else
                        <div>
                            <label class="{{ $label }}" for="idade">Idade (anos)</label>
                            <input id="idade" type="text" inputmode="numeric" wire:model="f.idade_informada" class="{{ $input }}">
                            {!! $erro('f.idade_informada') !!}
                        </div>
                    @endif
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model.live="f.usa_idade" class="h-5 w-5 accent-[var(--cor-marca)]">
                        Prefiro informar só a idade
                    </label>

                    <div>
                        <span class="{{ $label }}">Sexo</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (P::SEXOS as $opcao)
                                <label class="flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-3 has-[:checked]:border-[var(--cor-marca)] has-[:checked]:bg-[var(--cor-marca)]/10">
                                    <input type="radio" wire:model.live="f.sexo" value="{{ $opcao }}" class="accent-[var(--cor-marca)]"> {{ $opcao }}
                                </label>
                            @endforeach
                        </div>
                        {!! $erro('f.sexo') !!}
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}" for="altura">Altura (cm)</label>
                            <input id="altura" type="text" inputmode="numeric" placeholder="ex.: 164" wire:model="f.altura_cm" class="{{ $input }}">
                            {!! $erro('f.altura_cm') !!}
                        </div>
                        <div>
                            <label class="{{ $label }}" for="peso">Peso atual (kg)</label>
                            <input id="peso" type="text" inputmode="decimal" placeholder="ex.: 72,5" wire:model="f.peso_kg" class="{{ $input }}">
                            {!! $erro('f.peso_kg') !!}
                        </div>
                    </div>

                    @if ($f['sexo'] === 'Feminino')
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model.live="f.gestante" class="h-5 w-5 accent-[var(--cor-marca)]">
                            Estou gestante
                        </label>
                        @if ($f['gestante'])
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="{{ $label }}" for="semanas">Semanas de gestação</label>
                                    <input id="semanas" type="text" inputmode="numeric" wire:model="f.semanas_gestacao" class="{{ $input }}">
                                    {!! $erro('f.semanas_gestacao') !!}
                                </div>
                                <div>
                                    <label class="{{ $label }}" for="peso_pre">Peso antes da gestação (kg)</label>
                                    <input id="peso_pre" type="text" inputmode="decimal" wire:model="f.peso_pre_gestacional_kg" class="{{ $input }}">
                                    {!! $erro('f.peso_pre_gestacional_kg') !!}
                                </div>
                            </div>
                        @endif
                    @endif
                @endif

                {{-- 2. Rotina e objetivo --}}
                @if ($etapa === 2)
                    <div>
                        <label class="{{ $label }}" for="profissao">Profissão</label>
                        <input id="profissao" type="text" wire:model="f.profissao" class="{{ $input }}">
                        {!! $erro('f.profissao') !!}
                    </div>
                    <div>
                        <label class="{{ $label }}" for="horario">Horário de trabalho</label>
                        <input id="horario" type="text" placeholder="ex.: segunda a sexta, das 8h às 18h" wire:model="f.horario_trabalho" class="{{ $input }}">
                        {!! $erro('f.horario_trabalho') !!}
                    </div>
                    <div>
                        <label class="{{ $label }}" for="objetivo">Principal objetivo</label>
                        <select id="objetivo" wire:model.live="f.objetivo" class="{{ $input }}">
                            <option value="">Escolha…</option>
                            @foreach (P::OBJETIVOS as $opcao) <option value="{{ $opcao }}">{{ $opcao }}</option> @endforeach
                        </select>
                        {!! $erro('f.objetivo') !!}
                        @if ($f['objetivo'] === 'Outro')
                            <input type="text" placeholder="Qual?" wire:model="f.objetivo_outro" class="{{ $input }} mt-2">
                            {!! $erro('f.objetivo_outro') !!}
                        @endif
                    </div>
                    <div>
                        <label class="{{ $label }}" for="metas">Outras metas</label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 mb-2">
                            <input type="checkbox" wire:model.live="f.metas_nao_tem" class="h-5 w-5 accent-[var(--cor-marca)]"> Não tenho outras metas
                        </label>
                        @unless ($f['metas_nao_tem'])
                            <textarea id="metas" rows="2" placeholder="ex.: melhorar a disposição" wire:model="f.metas_texto" class="{{ $input }}"></textarea>
                            {!! $erro('f.metas_texto') !!}
                        @endunless
                    </div>
                    <div>
                        <span class="{{ $label }}">Atividade física</span>
                        <label class="flex items-center gap-2 text-sm text-gray-700 mb-2">
                            <input type="checkbox" wire:model.live="f.atividade_nao_pratica" class="h-5 w-5 accent-[var(--cor-marca)]"> Não pratico atividade física
                        </label>
                        @unless ($f['atividade_nao_pratica'])
                            <div class="space-y-3">
                                @foreach ($f['atividades'] as $i => $linha)
                                    <div wire:key="ativ-{{ $i }}" class="rounded-xl border border-gray-200 p-3 space-y-2">
                                        <input type="text" placeholder="Qual? (ex.: caminhada)" wire:model="f.atividades.{{ $i }}.tipo" class="{{ $input }}">
                                        {!! $erro("f.atividades.$i.tipo") !!}
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" placeholder="Frequência (3x/semana)" wire:model="f.atividades.{{ $i }}.frequencia" class="{{ $input }}">
                                            <input type="text" placeholder="Duração (30 min)" wire:model="f.atividades.{{ $i }}.duracao" class="{{ $input }}">
                                        </div>
                                        @if (count($f['atividades']) > 1)
                                            <button type="button" wire:click="removerLinha('atividades', {{ $i }})" class="text-sm text-red-600">Remover</button>
                                        @endif
                                    </div>
                                @endforeach
                                <button type="button" wire:click="adicionarLinha('atividades')" class="text-sm font-semibold" style="color: var(--cor-marca)">+ Adicionar atividade</button>
                            </div>
                        @endunless
                    </div>
                @endif

                {{-- 3. Sono e hábitos --}}
                @if ($etapa === 3)
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}" for="sono_horas">Horas de sono por noite</label>
                            <input id="sono_horas" type="text" inputmode="decimal" placeholder="ex.: 7" wire:model="f.sono_horas" class="{{ $input }}">
                            {!! $erro('f.sono_horas') !!}
                        </div>
                        <div>
                            <label class="{{ $label }}" for="sono_horario">Horário de sono</label>
                            <input id="sono_horario" type="text" placeholder="ex.: 23h às 7h" wire:model="f.sono_horario" class="{{ $input }}">
                        </div>
                    </div>
                    @foreach ([['sono_reparador', 'O sono é reparador?', P::SONO_REPARADOR], ['estresse', 'Nível de estresse', P::ESTRESSE]] as [$campo, $titulo, $opcoes])
                        <div>
                            <span class="{{ $label }}">{{ $titulo }}</span>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($opcoes as $opcao)
                                    <label class="flex items-center gap-2 rounded-xl border border-gray-300 px-3 py-3 text-sm has-[:checked]:border-[var(--cor-marca)] has-[:checked]:bg-[var(--cor-marca)]/10">
                                        <input type="radio" wire:model="f.{{ $campo }}" value="{{ $opcao }}" class="accent-[var(--cor-marca)]"> {{ $opcao }}
                                    </label>
                                @endforeach
                            </div>
                            {!! $erro("f.$campo") !!}
                        </div>
                    @endforeach
                    <div>
                        <label class="{{ $label }}" for="hidratacao">Quanta água bebe por dia?</label>
                        <input id="hidratacao" type="text" placeholder="ex.: 1,5 L" wire:model="f.hidratacao" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="intestino">Como funciona o intestino?</label>
                        <input id="intestino" type="text" placeholder="ex.: 1 vez ao dia" wire:model="f.intestino" class="{{ $input }}">
                    </div>
                @endif

                {{-- 4. Alimentação --}}
                @if ($etapa === 4)
                    @foreach ([['alergias', 'Tem alergia ou intolerância alimentar?', 'Quais?'], ['aversoes', 'Tem alimentos que não come de jeito nenhum?', 'Quais?']] as [$campo, $titulo, $pergunta])
                        <div>
                            <span class="{{ $label }}">{{ $titulo }}</span>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach (P::SIM_NAO as $opcao)
                                    <label class="flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-3 has-[:checked]:border-[var(--cor-marca)] has-[:checked]:bg-[var(--cor-marca)]/10">
                                        <input type="radio" wire:model.live="f.{{ $campo }}" value="{{ $opcao }}" class="accent-[var(--cor-marca)]"> {{ $opcao }}
                                    </label>
                                @endforeach
                            </div>
                            {!! $erro("f.$campo") !!}
                            @if ($f[$campo] === 'Sim')
                                <input type="text" placeholder="{{ $pergunta }}" wire:model="f.{{ $campo }}_texto" class="{{ $input }} mt-2">
                                {!! $erro("f.{$campo}_texto") !!}
                            @endif
                        </div>
                    @endforeach
                    @foreach ([['padrao_alimentar', 'Padrão alimentar', P::PADRAO_ALIMENTAR], ['orcamento', 'Orçamento para alimentação', P::ORCAMENTO], ['culinaria', 'Habilidade na cozinha', P::CULINARIA]] as [$campo, $titulo, $opcoes])
                        <div>
                            <span class="{{ $label }}">{{ $titulo }}</span>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($opcoes as $opcao)
                                    <label class="flex items-center gap-2 rounded-xl border border-gray-300 px-3 py-3 text-sm has-[:checked]:border-[var(--cor-marca)] has-[:checked]:bg-[var(--cor-marca)]/10">
                                        <input type="radio" wire:model="f.{{ $campo }}" value="{{ $opcao }}" class="accent-[var(--cor-marca)]"> {{ $opcao }}
                                    </label>
                                @endforeach
                            </div>
                            {!! $erro("f.$campo") !!}
                        </div>
                    @endforeach
                    <div>
                        <span class="{{ $label }}">Compulsão ou “beliscos” (ex.: doce à noite)</span>
                        <label class="flex items-center gap-2 text-sm text-gray-700 mb-2">
                            <input type="checkbox" wire:model.live="f.compulsao_nao_tem" class="h-5 w-5 accent-[var(--cor-marca)]"> Não tenho
                        </label>
                        @unless ($f['compulsao_nao_tem'])
                            <textarea rows="2" placeholder="Conte como é" wire:model="f.compulsao_texto" class="{{ $input }}"></textarea>
                            {!! $erro('f.compulsao_texto') !!}
                        @endunless
                    </div>
                @endif

                {{-- 5. Saúde --}}
                @if ($etapa === 5)
                    <div>
                        <span class="{{ $label }}">Tem alguma destas condições? (marque todas que tiver)</span>
                        <div class="space-y-2">
                            @foreach (P::CONDICOES as $opcao)
                                <label class="flex items-start gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model="f.condicoes" value="{{ $opcao }}" class="mt-0.5 h-5 w-5 accent-[var(--cor-marca)]"> {{ $opcao }}
                                </label>
                            @endforeach
                        </div>
                        <input type="text" placeholder="Outras condições" wire:model="f.condicoes_outras" class="{{ $input }} mt-3">
                    </div>

                    @foreach ([['medicamentos', 'medicamentos_nao_toma', 'Medicamentos', 'Não tomo medicamentos'], ['suplementos', 'suplementos_nao_usa', 'Suplementos e vitaminas', 'Não uso suplementos']] as [$lista, $nenhum, $titulo, $textoNenhum])
                        <div>
                            <span class="{{ $label }}">{{ $titulo }}</span>
                            <label class="flex items-center gap-2 text-sm text-gray-700 mb-2">
                                <input type="checkbox" wire:model.live="f.{{ $nenhum }}" class="h-5 w-5 accent-[var(--cor-marca)]"> {{ $textoNenhum }}
                            </label>
                            @unless ($f[$nenhum])
                                <div class="space-y-3">
                                    @foreach ($f[$lista] as $i => $item)
                                        <div wire:key="{{ $lista }}-{{ $i }}" class="rounded-xl border border-gray-200 p-3 space-y-2">
                                            <input type="text" placeholder="Nome" wire:model="f.{{ $lista }}.{{ $i }}.nome" class="{{ $input }}">
                                            {!! $erro("f.$lista.$i.nome") !!}
                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" wire:model.live="f.{{ $lista }}.{{ $i }}.nao_informado" class="h-5 w-5 accent-[var(--cor-marca)]"> Não sei a dose nem o horário
                                            </label>
                                            @unless ($item['nao_informado'])
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="text" placeholder="Dose" wire:model="f.{{ $lista }}.{{ $i }}.dose" class="{{ $input }}">
                                                    <input type="text" placeholder="Horário" wire:model="f.{{ $lista }}.{{ $i }}.horario" class="{{ $input }}">
                                                </div>
                                            @endunless
                                            @if (count($f[$lista]) > 1)
                                                <button type="button" wire:click="removerLinha('{{ $lista }}', {{ $i }})" class="text-sm text-red-600">Remover</button>
                                            @endif
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="adicionarLinha('{{ $lista }}')" class="text-sm font-semibold" style="color: var(--cor-marca)">+ Adicionar</button>
                                </div>
                            @endunless
                        </div>
                    @endforeach

                    <div>
                        <span class="{{ $label }}">Histórico na família</span>
                        <div class="space-y-2">
                            @foreach (P::HISTORICO_FAMILIAR as $opcao)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model="f.historico_familiar" value="{{ $opcao }}" class="h-5 w-5 accent-[var(--cor-marca)]"> {{ $opcao }}
                                </label>
                            @endforeach
                        </div>
                        <input type="text" placeholder="Quem? (ex.: mãe, avô)" wire:model="f.historico_familiar_obs" class="{{ $input }} mt-3">
                    </div>
                @endif

                {{-- 6. Observações e envio --}}
                @if ($etapa === 6)
                    <div>
                        <label class="{{ $label }}" for="obs">Quer contar mais alguma coisa para a nutricionista?</label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 mb-2">
                            <input type="checkbox" wire:model.live="f.observacoes_sem" class="h-5 w-5 accent-[var(--cor-marca)]"> Não tenho observações
                        </label>
                        @unless ($f['observacoes_sem'])
                            <textarea id="obs" rows="4" wire:model="f.observacoes_texto" class="{{ $input }}"></textarea>
                            {!! $erro('f.observacoes_texto') !!}
                        @endunless
                    </div>
                    <p class="text-sm text-gray-600">Ao enviar, suas respostas vão para a nutricionista e este link deixa de funcionar.</p>
                @endif
            </div>

            <div class="flex gap-3">
                @if ($etapa > 1)
                    <button type="button" wire:click="voltar" class="flex-1 rounded-full border border-gray-300 bg-white py-4 font-bold text-gray-700">Voltar</button>
                @endif
                <button type="submit" wire:loading.attr="disabled" class="flex-[2] rounded-full py-4 font-bold text-white text-lg disabled:opacity-60" style="background: var(--cor-marca)">
                    <span wire:loading.remove>{{ $etapa === $total ? 'Enviar respostas' : 'Continuar' }}</span>
                    <span wire:loading>Salvando…</span>
                </button>
            </div>
            @if ($errors->any())
                <p class="text-center text-sm text-red-600">Confira os campos destacados acima.</p>
            @endif
        </form>
    @endif
</div>
