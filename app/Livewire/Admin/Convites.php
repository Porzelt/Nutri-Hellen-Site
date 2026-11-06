<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\SaiDoSistema;
use App\Models\Convite;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Convites de anamnese | Nutri Admin')]
class Convites extends Component
{
    use SaiDoSistema;

    public string $tipo = 'primeira';

    public string $nome_paciente = '';

    public string $paciente_ref = '';

    public int $validade_dias = Convite::VALIDADE_PADRAO;

    public string $filtro = 'todos';

    /** Link do convite recém-criado, mostrado em destaque para copiar. */
    public ?string $linkCriado = null;

    protected function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(Convite::TIPOS)],
            'nome_paciente' => ['nullable', 'string', 'max:100'],
            'paciente_ref' => ['nullable', 'string', 'max:120'],
            'validade_dias' => ['required', 'integer', 'between:'.Convite::VALIDADE_MIN.','.Convite::VALIDADE_MAX],
        ];
    }

    protected function messages(): array
    {
        return [
            'validade_dias.between' => 'A validade deve ser de 1 a 30 dias.',
            'nome_paciente.max' => 'O nome pode ter até 100 caracteres.',
        ];
    }

    public function criar(): void
    {
        $dados = $this->validate();

        $convite = Convite::criar(
            $dados['tipo'],
            $dados['nome_paciente'] ?? null,
            $dados['paciente_ref'] ?? null,
            (int) $dados['validade_dias'],
        );

        $this->linkCriado = $convite->url();
        $this->reset(['nome_paciente', 'paciente_ref']);
        $this->validade_dias = Convite::VALIDADE_PADRAO;
    }

    public function cancelar(string $id): void
    {
        // Apaga o convite (e, nas próximas etapas, qualquer resposta e arquivo dele)
        Convite::whereKey($id)->first()?->delete();
        $this->linkCriado = null;
    }

    public function render()
    {
        $convites = Convite::query()
            ->comStatus($this->filtro)
            ->latest()
            ->get();

        return view('livewire.admin.convites', [
            'convites' => $convites,
            'contagem' => [
                'criado' => Convite::comStatus('criado')->count(),
                'respondido' => Convite::comStatus('respondido')->count(),
                'expirado' => Convite::comStatus('expirado')->count(),
            ],
        ]);
    }
}
