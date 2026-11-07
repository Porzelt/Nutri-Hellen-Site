{{-- Medicamentos e suplementos (primeira consulta e retorno). Usa $f, $input, $label e $erro da view principal. --}}
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
