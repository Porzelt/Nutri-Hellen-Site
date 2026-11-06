<div class="min-h-screen bg-gray-100">
    @include('livewire.admin.partials.nav')

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">
        <h1 class="text-3xl font-bold text-gray-800">Convites de anamnese</h1>

        {{-- Criar convite --}}
        <section class="bg-white shadow-xl sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Novo convite</h2>

            <form wire:submit="criar" class="grid gap-4 md:grid-cols-4 items-end">
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select id="tipo" wire:model="tipo" class="w-full rounded-lg border-gray-300 border px-3 py-2">
                        <option value="primeira">Primeira consulta</option>
                        <option value="retorno">Retorno</option>
                    </select>
                    @error('tipo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="nome_paciente" class="block text-sm font-medium text-gray-700 mb-1">Nome do paciente <span class="text-gray-400">(opcional, só para você identificar)</span></label>
                    <input id="nome_paciente" type="text" wire:model="nome_paciente" maxlength="100" autocomplete="off"
                        class="w-full rounded-lg border-gray-300 border px-3 py-2">
                    @error('nome_paciente') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="validade_dias" class="block text-sm font-medium text-gray-700 mb-1">Validade (dias)</label>
                    <input id="validade_dias" type="number" min="1" max="30" wire:model="validade_dias"
                        class="w-full rounded-lg border-gray-300 border px-3 py-2">
                    @error('validade_dias') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-3">
                    <label for="paciente_ref" class="block text-sm font-medium text-gray-700 mb-1">Pasta do paciente no app <span class="text-gray-400">(opcional, ex.: maria-da-silva)</span></label>
                    <input id="paciente_ref" type="text" wire:model="paciente_ref" maxlength="120" autocomplete="off"
                        class="w-full rounded-lg border-gray-300 border px-3 py-2">
                    @error('paciente_ref') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <button type="submit" class="w-full bg-green-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        Criar convite
                    </button>
                </div>
            </form>

            @if ($linkCriado)
                <div x-data="{ copiado: false }" class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4">
                    <p class="text-sm text-green-800 font-medium mb-2">Convite criado. Envie este link ao paciente pelo WhatsApp:</p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" readonly value="{{ $linkCriado }}" x-ref="link"
                            class="flex-1 rounded-lg border border-green-300 bg-white px-3 py-2 text-sm font-mono">
                        <button type="button"
                            x-on:click="navigator.clipboard.writeText($refs.link.value); copiado = true; setTimeout(() => copiado = false, 2000)"
                            class="bg-green-600 text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-green-700">
                            <span x-show="!copiado">Copiar link</span>
                            <span x-show="copiado" x-cloak>Copiado!</span>
                        </button>
                    </div>
                </div>
            @endif
        </section>

        {{-- Lista --}}
        <section class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
            <div class="flex flex-wrap gap-2 p-4 border-b border-gray-200">
                @foreach (['todos' => 'Todos', 'criado' => 'Aguardando', 'respondido' => 'Respondidos', 'expirado' => 'Expirados'] as $valor => $rotulo)
                    <button type="button" wire:click="$set('filtro', '{{ $valor }}')"
                        class="text-sm px-3 py-1 rounded-full {{ $filtro === $valor ? 'bg-brand-secondary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $rotulo }}
                        @if ($valor !== 'todos') <span class="opacity-75">({{ $contagem[$valor] }})</span> @endif
                    </button>
                @endforeach
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($convites as $convite)
                            @php $status = $convite->status(); @endphp
                            <tr wire:key="convite-{{ $convite->id }}" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $convite->created_at->timezone(\App\Models\Convite::FUSO)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $convite->nome_paciente ?? '—' }}
                                    @if ($convite->paciente_ref)
                                        <div class="text-xs text-gray-400 font-mono">{{ $convite->paciente_ref }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $convite->tipo === 'primeira' ? 'Primeira consulta' : 'Retorno' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    até {{ $convite->expira_em->timezone(\App\Models\Convite::FUSO)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ [
                                        'criado' => 'bg-yellow-100 text-yellow-800',
                                        'respondido' => 'bg-green-100 text-green-800',
                                        'expirado' => 'bg-gray-200 text-gray-600',
                                    ][$status] }}">
                                        {{ ['criado' => 'Aguardando', 'respondido' => 'Respondido', 'expirado' => 'Expirado'][$status] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex justify-center gap-3" x-data="{ copiado: false }">
                                        @if ($status === 'criado')
                                            <button type="button"
                                                x-on:click="navigator.clipboard.writeText(@js($convite->url())); copiado = true; setTimeout(() => copiado = false, 2000)"
                                                class="text-green-700 hover:text-green-900 font-medium">
                                                <span x-show="!copiado">Copiar link</span>
                                                <span x-show="copiado" x-cloak>Copiado!</span>
                                            </button>
                                        @endif
                                        <button type="button" wire:click="cancelar('{{ $convite->id }}')"
                                            wire:confirm="Cancelar e apagar este convite? O link deixa de funcionar."
                                            class="text-red-600 hover:text-red-800 font-medium">
                                            {{ $status === 'criado' ? 'Cancelar' : 'Apagar' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Nenhum convite aqui.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
