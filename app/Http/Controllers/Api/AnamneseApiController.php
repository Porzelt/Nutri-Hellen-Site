<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * API v1 para o app local (contrato INTEGRACAO-CONSULTORIO.md, seção 5).
 * Só respostas já enviadas aparecem aqui; rascunhos nunca saem do site.
 */
class AnamneseApiController extends Controller
{
    private const JSON = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    private function json(array $dados, int $status = 200): JsonResponse
    {
        return response()->json($dados, $status, [], self::JSON);
    }

    public function saude(): JsonResponse
    {
        return $this->json(['ok' => true, 'versao_esquema' => Resposta::VERSAO_ESQUEMA]);
    }

    public function criarConvite(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'tipo' => ['required', Rule::in(Convite::TIPOS)],
            'nome_paciente' => ['nullable', 'string', 'max:100'],
            'paciente_ref' => ['nullable', 'string', 'max:120'],
            'validade_dias' => ['nullable', 'integer', 'between:'.Convite::VALIDADE_MIN.','.Convite::VALIDADE_MAX],
        ]);

        $convite = Convite::criar(
            $dados['tipo'],
            $dados['nome_paciente'] ?? null,
            $dados['paciente_ref'] ?? null,
            (int) ($dados['validade_dias'] ?? Convite::VALIDADE_PADRAO),
        );

        return $this->json([
            'id' => $convite->id,
            'url' => $convite->url(),
            'expira_em' => Resposta::iso($convite->expira_em),
        ], 201);
    }

    public function cancelarConvite(string $id): Response
    {
        $convite = Convite::find($id);
        abort_unless($convite, 404);

        $convite->delete(); // apaga também a resposta e os arquivos (banco e disco)

        return response()->noContent();
    }

    public function listarRespostas(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', Rule::in(['pendente'])]]);

        $respostas = Resposta::query()
            ->whereNotNull('enviado_em')
            ->with('convite')
            ->withCount('arquivos')
            ->oldest('enviado_em')
            ->get();

        return $this->json(['dados' => $respostas->map->paraLista()->values()->all()]);
    }

    public function verResposta(string $id): JsonResponse
    {
        $resposta = $this->respostaEnviada($id);

        return $this->json($resposta->paraContrato());
    }

    public function baixarArquivo(string $id, string $arquivoId): Response
    {
        $arquivo = $this->respostaEnviada($id)->arquivos()->whereKey($arquivoId)->first();
        abort_unless($arquivo, 404);

        $nome = str_replace(['"', "\r", "\n"], '', $arquivo->nome_original);

        return response($arquivo->conteudo(), 200, [
            'Content-Type' => $arquivo->mime,
            'Content-Length' => (string) $arquivo->tamanho_bytes,
            'Content-Disposition' => 'attachment; filename="arquivo"; filename*=UTF-8\'\''.rawurlencode($nome),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * O app local salvou tudo: apaga resposta, arquivos e o convite (banco e disco).
     * Idempotente: se já foi apagada, responde 204 também.
     */
    public function importado(string $id): Response
    {
        $resposta = Resposta::whereKey($id)->whereNotNull('enviado_em')->first();

        if ($resposta) {
            DB::transaction(function () use ($resposta) {
                $convite = $resposta->convite;
                $resposta->delete();
                $convite?->delete();
            });
        }

        return response()->noContent();
    }

    private function respostaEnviada(string $id): Resposta
    {
        $resposta = Resposta::whereKey($id)->whereNotNull('enviado_em')->with('convite')->first();
        abort_unless($resposta, 404);

        return $resposta;
    }
}
