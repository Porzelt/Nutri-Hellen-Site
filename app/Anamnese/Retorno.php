<?php

namespace App\Anamnese;

use Illuminate\Validation\Rule;

/**
 * Formulário de retorno (versão curta): mesmas operações de PrimeiraConsulta,
 * montando o JSON da seção 6.3 do contrato (INTEGRACAO-CONSULTORIO.md).
 */
class Retorno
{
    public const ADESAO = ['Baixa', 'Parcial', 'Boa', 'Total'];

    public const ETAPAS = [
        1 => 'Como foi o plano',
        2 => 'Rotina e saúde',
        3 => 'Medicamentos e suplementos',
        4 => 'Exames e arquivos',
        5 => 'Observações e envio',
    ];

    public const ETAPA_ARQUIVOS = 4;

    public const DICAS = PrimeiraConsulta::DICAS + [
        'adesao' => 'Baixa: quase não segui. Parcial: segui em parte. Boa: segui na maior parte. Total: segui tudo.',
        'peso_kg' => 'Se puder, pese-se de manhã, em jejum.',
        'condicoes_novas' => 'Algum diagnóstico, exame alterado, sintoma ou mudança de remédio desde a última consulta.',
    ];

    public static function estadoInicial(?string $nome = null): array
    {
        return [
            'nome' => (string) $nome, 'peso_kg' => '',
            'adesao' => '', 'dificuldades' => '', 'mudancas' => '',
            'atividade' => '', 'sono' => '', 'intestino' => '', 'hidratacao' => '', 'condicoes_novas' => '',
            'medicamentos_nao_toma' => false, 'medicamentos' => [PrimeiraConsulta::linhaItem()],
            'suplementos_nao_usa' => false, 'suplementos' => [PrimeiraConsulta::linhaItem()],
            'sem_exames' => false, 'sem_bio' => false, 'sem_outros' => false,
            'observacoes_sem' => false, 'observacoes_texto' => '',
        ];
    }

    public static function regras(int $etapa, array $f): array
    {
        $texto = ['nullable', 'string', 'max:500'];
        $curto = ['nullable', 'string', 'max:150'];

        return match ($etapa) {
            1 => [
                'f.nome' => ['required', 'string', 'min:2', 'max:120'],
                'f.peso_kg' => ['required', 'numeric', 'between:2,350', 'decimal:0,1'],
                'f.adesao' => ['required', Rule::in(self::ADESAO)],
                'f.dificuldades' => $texto,
                'f.mudancas' => $texto,
            ],
            2 => [
                'f.atividade' => $curto,
                'f.sono' => $curto,
                'f.intestino' => $curto,
                'f.hidratacao' => ['nullable', Rule::in(PrimeiraConsulta::HIDRATACAO)],
                'f.condicoes_novas' => $texto,
            ],
            3 => array_merge(
                PrimeiraConsulta::regrasItens('medicamentos', $f['medicamentos_nao_toma']),
                PrimeiraConsulta::regrasItens('suplementos', $f['suplementos_nao_usa']),
            ),
            5 => [
                'f.observacoes_texto' => $f['observacoes_sem'] ? ['nullable', 'string', 'max:2000'] : ['required', 'string', 'max:2000'],
            ],
            default => [],
        };
    }

    public static function mensagens(): array
    {
        return PrimeiraConsulta::mensagens() + [
            'f.adesao.required' => 'Escolha como foi seguir o plano.',
            'f.peso_kg.required' => 'Informe o seu peso atual.',
        ];
    }

    public static function normalizar(array $f): array
    {
        if (is_string($f['peso_kg'] ?? null)) {
            $f['peso_kg'] = str_replace(',', '.', trim($f['peso_kg']));
        }

        return $f;
    }

    /** JSON da seção 6.3 (sem os metadados, que o modelo acrescenta). */
    public static function montar(array $f, array $arquivos = []): array
    {
        $t = fn ($v) => trim((string) $v);
        $base = PrimeiraConsulta::montarItens($f);

        return [
            'identificacao' => ['nome' => trim($f['nome']), 'peso_kg' => (float) $f['peso_kg']],
            'retorno' => [
                'adesao' => $f['adesao'],
                'dificuldades' => $t($f['dificuldades']),
                'mudancas' => $t($f['mudancas']),
                'atividade' => $t($f['atividade']),
                'sono' => $t($f['sono']),
                'intestino' => $t($f['intestino']),
                'hidratacao' => $t($f['hidratacao']),
                'condicoes_novas' => $t($f['condicoes_novas']),
            ],
            'saude' => $base,
            'observacoes' => ['sem_observacoes' => (bool) $f['observacoes_sem'], 'texto' => $f['observacoes_sem'] ? null : ($t($f['observacoes_texto']) ?: null)],
            'arquivos' => [
                'sem_exames' => (bool) $f['sem_exames'],
                'sem_bio' => (bool) $f['sem_bio'],
                'sem_outros' => (bool) $f['sem_outros'],
                'lista' => $arquivos,
            ],
        ];
    }
}
