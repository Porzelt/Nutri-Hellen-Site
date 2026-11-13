<?php

namespace App\Livewire\Anamnese;

use App\Anamnese\PrimeiraConsulta;
use App\Anamnese\Retorno;
use App\Models\Arquivo;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Página do paciente (contrato, seção 8): consentimento, formulário em etapas,
 * rascunho salvo a cada etapa, anexos privados e envio. Link inválido, expirado
 * ou usado mostra uma mensagem genérica.
 */
#[Layout('components.layouts.paciente')]
#[Title('Formulário de anamnese')]
class Formulario extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?string $conviteId = null;

    #[Locked]
    public string $tipo = 'primeira';

    /** 0 = consentimento; 1..7 = etapas do formulário. */
    public int $etapa = 0;

    public bool $aceite = false;

    public bool $enviado = false;

    #[Locked]
    public string $primeiroNome = '';

    public array $f = [];

    /** Arquivos recém-escolhidos (temporários do Livewire), por categoria. */
    public array $uploads = ['exames' => [], 'bio' => [], 'outros' => []];

    public function mount(string $token): void
    {
        $convite = Convite::peloToken($token);

        if (! $convite || ! $convite->utilizavel()) {
            return; // tela genérica de link indisponível
        }

        $this->conviteId = $convite->id;
        $this->tipo = $convite->tipo;
        $this->f = $this->estadoInicial($convite);

        // Retoma o rascunho, se houver
        $rascunho = $convite->resposta;
        if ($rascunho && ! $rascunho->enviada()) {
            $this->f = array_replace($this->estadoInicial($convite), $rascunho->dados['rascunho'] ?? []);
            $this->etapa = min($rascunho->etapa, max(array_keys($this->modelo()::ETAPAS)));
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

    /** Classe do formulário conforme o tipo do convite. */
    private function modelo(): string
    {
        return $this->tipo === 'retorno' ? Retorno::class : PrimeiraConsulta::class;
    }

    /** No retorno, o nome já vem preenchido com o nome do convite. */
    private function estadoInicial(Convite $convite): array
    {
        return $this->tipo === 'retorno'
            ? Retorno::estadoInicial($convite->nome_paciente)
            : PrimeiraConsulta::estadoInicial();
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

        $this->f = $this->modelo()::normalizar($this->f);
        $this->validarEtapa($this->etapa);

        if ($this->etapa === $this->modelo()::ETAPA_ARQUIVOS && ! $this->arquivosOk($convite)) {
            return;
        }

        $this->etapa = min($this->etapa + 1, max(array_keys($this->modelo()::ETAPAS)));
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

        $this->f = $this->modelo()::normalizar($this->f);

        // Valida tudo; se algo de uma etapa anterior estiver errado, volta para ela
        foreach (array_keys($this->modelo()::ETAPAS) as $etapa) {
            $validador = validator($this->all(), $this->modelo()::regras($etapa, $this->f), $this->modelo()::mensagens());
            if ($validador->fails()) {
                $this->etapa = $etapa;
                $this->validarEtapa($etapa);

                return;
            }
        }
        if (! $this->arquivosOk($convite)) {
            $this->etapa = $this->modelo()::ETAPA_ARQUIVOS;

            return;
        }

        $resposta = $convite->resposta;
        if (! $resposta || ! $resposta->consentimento_aceito_em) {
            $this->etapa = 0;

            return;
        }

        DB::transaction(function () use ($convite, $resposta) {
            $lista = $resposta->arquivos()->oldest()->get()->map->paraContrato()->all();

            $resposta->dados = $this->modelo()::montar($this->f, $lista);
            $resposta->enviado_em = now();
            $resposta->etapa = 0;
            $resposta->save();

            $convite->respondido_em = now();
            $convite->save();
        });

        // Primeiro nome, só para o agradecimento (os dados saem da tela)
        $this->primeiroNome = \Illuminate\Support\Str::of($this->f['nome'] ?? '')->trim()->before(' ')->title()->limit(40, '')->toString();
        $this->enviado = true;
        $this->conviteId = null;
        $this->f = [];
    }

    private function validarEtapa(int $etapa): void
    {
        $regras = $this->modelo()::regras($etapa, $this->f);
        if ($regras !== []) {
            $this->validate($regras, $this->modelo()::mensagens());
        }
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

    /**
     * Para cada categoria, o paciente anexa arquivos OU marca "não tenho" (contrato, seção 7).
     */
    private function arquivosOk(Convite $convite): bool
    {
        $porCategoria = $convite->resposta?->arquivos()->pluck('categoria')->countBy() ?? collect();
        $ok = true;

        foreach (Arquivo::CATEGORIAS as $categoria) {
            if (! $this->f['sem_'.$categoria] && ! $porCategoria->get($categoria)) {
                $this->addError('arquivos_'.$categoria, 'Anexe o arquivo ou marque “não tenho”.');
                $ok = false;
            }
        }

        return $ok;
    }

    /**
     * Assim que o paciente escolhe arquivos: confere tipo real, tamanho e limites,
     * guarda criptografado no disco privado e apaga o temporário.
     */
    public function updatedUploads($valor, string $chave): void
    {
        $categoria = explode('.', $chave)[0];
        if (! in_array($categoria, Arquivo::CATEGORIAS, true)) {
            return;
        }

        $novos = array_filter((array) ($this->uploads[$categoria] ?? []), fn ($a) => $a instanceof TemporaryUploadedFile);
        $this->uploads[$categoria] = [];

        $convite = $this->conviteValido();
        $resposta = $convite?->resposta;
        if (! $resposta) {
            array_walk($novos, fn ($a) => $a->delete());

            return;
        }

        $this->resetErrorBag('arquivos_'.$categoria);
        $qtd = $resposta->arquivos()->count();
        $total = (int) $resposta->arquivos()->sum('tamanho_bytes');

        foreach ($novos as $arquivo) {
            // Tipo real, pelo conteúdo (sem cair na extensão do nome)
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($arquivo->get()) ?: '';
            $erro = match (true) {
                ! isset(Arquivo::TIPOS[$mime]) => 'Tipo não aceito. Envie PDF ou foto (JPG, PNG, HEIC, WEBP).',
                $arquivo->getSize() > Arquivo::MAX_KB_ARQUIVO * 1024 => 'Arquivo maior que 15 MB.',
                $qtd + 1 > Arquivo::MAX_ARQUIVOS => 'Limite de '.Arquivo::MAX_ARQUIVOS.' arquivos atingido.',
                $total + $arquivo->getSize() > Arquivo::MAX_BYTES_TOTAL => 'Limite de 60 MB no total atingido.',
                default => null,
            };

            if ($erro) {
                $this->addError('arquivos_'.$categoria, $erro);
            } else {
                Arquivo::guardar($resposta, $categoria, $arquivo, $mime);
                $qtd++;
                $total += $arquivo->getSize();
                $this->f['sem_'.$categoria] = false;
            }
            $arquivo->delete();
        }
    }

    public function removerArquivo(string $id): void
    {
        $resposta = $this->conviteValido()?->resposta;
        $resposta?->arquivos()->whereKey($id)->first()?->delete();
    }

    public function adicionarLinha(string $lista): void
    {
        $permitidas = ['atividades' => PrimeiraConsulta::linhaAtividade(), 'medicamentos' => PrimeiraConsulta::linhaItem(), 'suplementos' => PrimeiraConsulta::linhaItem()];
        if (isset($permitidas[$lista], $this->f[$lista]) && count($this->f[$lista]) < 10) {
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
        $arquivos = collect();
        if ($this->conviteId && $this->etapa === $this->modelo()::ETAPA_ARQUIVOS) {
            $arquivos = Resposta::where('convite_id', $this->conviteId)->first()?->arquivos()->oldest()->get()->groupBy('categoria') ?? collect();
        }

        return view('livewire.anamnese.formulario', [
            'etapas' => $this->modelo()::ETAPAS,
            'etapaArquivos' => $this->modelo()::ETAPA_ARQUIVOS,
            'dicas' => $this->modelo()::DICAS,
            'marca' => config('marca'),
            'arquivos' => $arquivos,
        ]);
    }
}
