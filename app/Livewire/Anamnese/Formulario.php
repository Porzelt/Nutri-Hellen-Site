<?php

namespace App\Livewire\Anamnese;

use App\Anamnese\PrimeiraConsulta;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Página do paciente (contrato, seção 8): consentimento, formulário em etapas,
 * rascunho salvo a cada etapa e envio. Link inválido, expirado ou usado mostra
 * uma mensagem genérica.
 */
#[Layout('components.layouts.paciente')]
#[Title('Formulário de anamnese')]
class Formulario extends Component
{
    #[Locked]
    public ?string $conviteId = null;

    #[Locked]
    public string $tipo = 'primeira';

    /** 0 = consentimento; 1..6 = etapas do formulário. */
    public int $etapa = 0;

    public bool $aceite = false;

    public bool $enviado = false;

    public array $f = [];

    public function mount(string $token): void
    {
        $convite = Convite::peloToken($token);

        if (! $convite || ! $convite->utilizavel()) {
            return; // tela genérica de link indisponível
        }

        $this->conviteId = $convite->id;
        $this->tipo = $convite->tipo;
        $this->f = PrimeiraConsulta::estadoInicial();

        // Retoma o rascunho, se houver
        $rascunho = $convite->resposta;
        if ($rascunho && ! $rascunho->enviada()) {
            $this->f = array_replace(PrimeiraConsulta::estadoInicial(), $rascunho->dados['rascunho'] ?? []);
            $this->etapa = min($rascunho->etapa, max(array_keys(PrimeiraConsulta::ETAPAS)));
            $this->aceite = $rascunho->consentimento_aceito_em !== null;
        }
    }

    /** Recarrega o convite e confirma que o link ainda vale (a cada ação). */
    private function conviteValido(): ?Convite
    {
        $convite = $this->conviteId ? Convite::find($this->conviteId) : null;

        if (! $convite || ! $convite->utilizavel()) {
            $this->conviteId = null;

            return null;
        }

        return $convite;
    }

    public function aceitar(): void
    {
        $this->validate(['aceite' => 'accepted'], ['aceite.accepted' => 'Para continuar, marque que leu e concorda.']);

        if (! $convite = $this->conviteValido()) {
            return;
        }

        $resposta = $convite->resposta ?? new Resposta(['convite_id' => $convite->id, 'tipo' => $convite->tipo]);
        $resposta->consentimento_versao = config('consentimento.versao');
        $resposta->consentimento_aceito_em = now();
        $resposta->etapa = max($resposta->etapa ?? 0, 1);
        $resposta->dados = ['rascunho' => $this->f];
        $resposta->save();

        $this->etapa = 1;
    }

    public function avancar(): void
    {
        if (! $convite = $this->conviteValido()) {
            return;
        }

        $this->f = PrimeiraConsulta::normalizar($this->f);
        $this->validate(PrimeiraConsulta::regras($this->etapa, $this->f), PrimeiraConsulta::mensagens());

        $this->etapa = min($this->etapa + 1, max(array_keys(PrimeiraConsulta::ETAPAS)));
        $this->salvarRascunho($convite);
    }

    public function voltar(): void
    {
        if ($this->etapa > 1) {
            $this->etapa--;
        }
    }

    public function enviar(): void
    {
        if (! $convite = $this->conviteValido()) {
            return;
        }

        $this->f = PrimeiraConsulta::normalizar($this->f);

        // Valida tudo; se algo de uma etapa anterior estiver errado, volta para ela
        foreach (array_keys(PrimeiraConsulta::ETAPAS) as $etapa) {
            $validador = validator($this->all(), PrimeiraConsulta::regras($etapa, $this->f), PrimeiraConsulta::mensagens());
            if ($validador->fails()) {
                $this->etapa = $etapa;
                $this->validate(PrimeiraConsulta::regras($etapa, $this->f), PrimeiraConsulta::mensagens());

                return;
            }
        }

        $resposta = $convite->resposta;
        if (! $resposta || ! $resposta->consentimento_aceito_em) {
            $this->etapa = 0;

            return;
        }

        DB::transaction(function () use ($convite, $resposta) {
            $resposta->dados = PrimeiraConsulta::montar($this->f);
            $resposta->enviado_em = now();
            $resposta->etapa = 0;
            $resposta->save();

            $convite->respondido_em = now();
            $convite->save();
        });

        $this->enviado = true;
        $this->conviteId = null;
        $this->f = [];
    }

    private function salvarRascunho(Convite $convite): void
    {
        $resposta = $convite->resposta;
        if (! $resposta) {
            return;
        }
        $resposta->dados = ['rascunho' => $this->f];
        $resposta->etapa = $this->etapa;
        $resposta->save();
    }

    public function adicionarLinha(string $lista): void
    {
        $permitidas = ['atividades' => PrimeiraConsulta::linhaAtividade(), 'medicamentos' => PrimeiraConsulta::linhaItem(), 'suplementos' => PrimeiraConsulta::linhaItem()];
        if (isset($permitidas[$lista]) && count($this->f[$lista]) < 10) {
            $this->f[$lista][] = $permitidas[$lista];
        }
    }

    public function removerLinha(string $lista, int $indice): void
    {
        if (in_array($lista, ['atividades', 'medicamentos', 'suplementos'], true) && isset($this->f[$lista][$indice])) {
            unset($this->f[$lista][$indice]);
            $this->f[$lista] = array_values($this->f[$lista]);
            if ($this->f[$lista] === []) {
                $this->adicionarLinha($lista);
            }
        }
    }

    public function render()
    {
        return view('livewire.anamnese.formulario', [
            'etapas' => PrimeiraConsulta::ETAPAS,
            'marca' => config('marca'),
        ]);
    }
}
