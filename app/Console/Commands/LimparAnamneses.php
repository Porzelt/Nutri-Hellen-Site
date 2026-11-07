<?php

namespace App\Console\Commands;

use App\Models\Arquivo;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Limpeza diária (contrato, seção 9). Apaga de verdade, no banco e no disco:
 * - convites expirados (com rascunho e arquivos)
 * - respostas enviadas e não importadas há mais de N dias (com arquivos e convite)
 * - arquivos soltos no disco privado (sem linha no banco) e uploads temporários antigos
 * O log registra só quantidades, nunca dados do paciente.
 */
class LimparAnamneses extends Command
{
    protected $signature = 'anamnese:limpar';

    protected $description = 'Apaga convites expirados, respostas não importadas antigas e arquivos soltos';

    public function handle(): int
    {
        $limite = now()->subDays(config('anamnese.retencao_dias'));

        // Convites expirados sem resposta enviada (rascunho e arquivos vão junto)
        $expirados = 0;
        Convite::comStatus('expirado')->each(function (Convite $c) use (&$expirados) {
            $c->delete();
            $expirados++;
        });

        // Respostas enviadas há mais de N dias e não importadas
        $antigas = 0;
        Resposta::whereNotNull('enviado_em')->where('enviado_em', '<', $limite)->with('convite')
            ->each(function (Resposta $r) use (&$antigas) {
                $convite = $r->convite;
                $r->delete();
                $convite?->delete();
                $antigas++;
            });

        // Arquivos no disco sem linha no banco (ex.: falha no meio de um envio), com mais de 1 dia
        $disco = Storage::disk('anamnese');
        $conhecidos = Arquivo::pluck('caminho')->flip();
        $soltos = 0;
        foreach ($disco->allFiles() as $caminho) {
            if (! isset($conhecidos[$caminho]) && $disco->lastModified($caminho) < now()->subDay()->getTimestamp()) {
                $disco->delete($caminho);
                $soltos++;
            }
        }

        // Uploads temporários do Livewire com mais de 1 dia
        $tmp = Storage::disk('local');
        $temporarios = 0;
        foreach ($tmp->files('livewire-tmp') as $caminho) {
            if ($tmp->lastModified($caminho) < now()->subDay()->getTimestamp()) {
                $tmp->delete($caminho);
                $temporarios++;
            }
        }

        $resumo = compact('expirados', 'antigas', 'soltos', 'temporarios');
        Log::info('anamnese:limpar', $resumo);
        $this->info(sprintf(
            'Convites expirados: %d | respostas antigas: %d | arquivos soltos: %d | temporários: %d',
            $expirados, $antigas, $soltos, $temporarios
        ));

        return self::SUCCESS;
    }
}
