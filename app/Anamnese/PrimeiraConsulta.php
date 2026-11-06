<?php

namespace App\Anamnese;

use Illuminate\Validation\Rule;

/**
 * Formulário de primeira consulta: estado inicial, etapas, validação e montagem
 * do JSON no esquema da seção 6.1 do contrato (INTEGRACAO-CONSULTORIO.md).
 */
class PrimeiraConsulta
{
    public const SEXOS = ['Feminino', 'Masculino'];

    public const OBJETIVOS = ['Emagrecimento', 'Ganho de massa muscular', 'Reeducação alimentar', 'Controle de condição de saúde', 'Saúde na gestação', 'Outro'];

    public const SONO_REPARADOR = ['Sim', 'Não', 'Às vezes'];

    public const ESTRESSE = ['Baixo', 'Moderado', 'Alto', 'Muito alto'];

    public const SIM_NAO = ['Não', 'Sim'];

    public const PADRAO_ALIMENTAR = ['Onívoro', 'Vegetariano', 'Vegano'];

    public const ORCAMENTO = ['Baixo', 'Médio', 'Alto'];

    public const CULINARIA = ['Nenhuma', 'Básica', 'Boa', 'Ótima'];

    public const CONDICOES = [
        'SOMP (antiga SOP)', 'Hipotireoidismo', 'Hipertireoidismo', 'Nódulo de tireoide / bócio',
        'Diabetes tipo 2', 'Pré-diabetes / resistência à insulina', 'Hipertensão',
        'Dislipidemia (colesterol / triglicerídeos)', 'Esteatose hepática', 'Gastrite / refluxo',
        'Intestino irritável / constipação crônica', 'Anemia / carência de ferro', 'Endometriose',
        'Ansiedade / depressão', 'Doença celíaca', 'Doença renal crônica',
    ];

    public const HISTORICO_FAMILIAR = ['Diabetes', 'Colesterol', 'Hipertensão', 'Doença cardíaca precoce', 'Câncer'];

    /** Etapas depois do consentimento (a etapa 0 é o consentimento). */
    public const ETAPAS = [
        1 => 'Sobre você',
        2 => 'Rotina e objetivo',
        3 => 'Sono e hábitos',
        4 => 'Alimentação',
        5 => 'Saúde',
        6 => 'Observações e envio',
    ];

    public static function estadoInicial(): array
    {
        return [
            'nome' => '', 'usa_idade' => false, 'nascimento' => '', 'idade_informada' => '',
            'sexo' => '', 'altura_cm' => '', 'peso_kg' => '',
            'gestante' => false, 'semanas_gestacao' => '', 'peso_pre_gestacional_kg' => '',

            'profissao' => '', 'horario_trabalho' => '', 'objetivo' => '', 'objetivo_outro' => '',
            'metas_nao_tem' => false, 'metas_texto' => '',
            'atividade_nao_pratica' => false, 'atividades' => [self::linhaAtividade()],

            'sono_horas' => '', 'sono_horario' => '', 'sono_reparador' => '', 'estresse' => '',
            'hidratacao' => '', 'intestino' => '',

            'alergias' => '', 'alergias_texto' => '', 'padrao_alimentar' => '',
            'aversoes' => '', 'aversoes_texto' => '', 'compulsao_nao_tem' => false, 'compulsao_texto' => '',
            'orcamento' => '', 'culinaria' => '',

            'condicoes' => [], 'condicoes_outras' => '',
            'medicamentos_nao_toma' => false, 'medicamentos' => [self::linhaItem()],
            'suplementos_nao_usa' => false, 'suplementos' => [self::linhaItem()],
            'historico_familiar' => [], 'historico_familiar_obs' => '',

            'observacoes_sem' => false, 'observacoes_texto' => '',
        ];
    }

    public static function linhaAtividade(): array
    {
        return ['tipo' => '', 'frequencia' => '', 'duracao' => ''];
    }

    public static function linhaItem(): array
    {
        return ['nome' => '', 'dose' => '', 'horario' => '', 'nao_informado' => false];
    }

    /** Regras de validação de uma etapa. As chaves usam o prefixo "f." (estado do componente). */
    public static function regras(int $etapa, array $f): array
    {
        $texto = ['nullable', 'string', 'max:500'];
        $curto = ['nullable', 'string', 'max:150'];

        return match ($etapa) {
            1 => array_merge([
                'f.nome' => ['required', 'string', 'min:2', 'max:120'],
                'f.sexo' => ['required', Rule::in(self::SEXOS)],
                'f.altura_cm' => ['required', 'integer', 'between:40,230'],
                'f.peso_kg' => ['required', 'numeric', 'between:2,350', 'decimal:0,1'],
            ], $f['usa_idade']
                ? ['f.idade_informada' => ['required', 'integer', 'between:0,120']]
                : ['f.nascimento' => ['required', 'date', 'before_or_equal:today', 'after:1900-01-01']],
                ($f['sexo'] === 'Feminino' && $f['gestante']) ? [
                    'f.semanas_gestacao' => ['required', 'integer', 'between:1,42'],
                    'f.peso_pre_gestacional_kg' => ['required', 'numeric', 'between:30,250', 'decimal:0,1'],
                ] : []),

            2 => array_merge([
                'f.profissao' => $curto,
                'f.horario_trabalho' => $curto,
                'f.objetivo' => ['required', Rule::in(self::OBJETIVOS)],
                'f.objetivo_outro' => $f['objetivo'] === 'Outro' ? ['required', 'string', 'max:150'] : $curto,
                'f.metas_texto' => $f['metas_nao_tem'] ? $texto : ['required', 'string', 'max:500'],
            ], $f['atividade_nao_pratica'] ? [] : [
                'f.atividades' => ['array', 'min:1', 'max:10'],
                'f.atividades.*.tipo' => ['required', 'string', 'max:80'],
                'f.atividades.*.frequencia' => ['nullable', 'string', 'max:80'],
                'f.atividades.*.duracao' => ['nullable', 'string', 'max:80'],
            ]),

            3 => [
                'f.sono_horas' => ['nullable', 'numeric', 'between:0,16', 'multiple_of:0.5'],
                'f.sono_horario' => $curto,
                'f.sono_reparador' => ['required', Rule::in(self::SONO_REPARADOR)],
                'f.estresse' => ['required', Rule::in(self::ESTRESSE)],
                'f.hidratacao' => $curto,
                'f.intestino' => $curto,
            ],

            4 => [
                'f.alergias' => ['required', Rule::in(self::SIM_NAO)],
                'f.alergias_texto' => $f['alergias'] === 'Sim' ? ['required', 'string', 'max:500'] : $texto,
                'f.padrao_alimentar' => ['required', Rule::in(self::PADRAO_ALIMENTAR)],
                'f.aversoes' => ['required', Rule::in(self::SIM_NAO)],
                'f.aversoes_texto' => $f['aversoes'] === 'Sim' ? ['required', 'string', 'max:500'] : $texto,
                'f.compulsao_texto' => $f['compulsao_nao_tem'] ? $texto : ['required', 'string', 'max:500'],
                'f.orcamento' => ['required', Rule::in(self::ORCAMENTO)],
                'f.culinaria' => ['required', Rule::in(self::CULINARIA)],
            ],

            5 => array_merge([
                'f.condicoes' => ['array'],
                'f.condicoes.*' => [Rule::in(self::CONDICOES)],
                'f.condicoes_outras' => $texto,
                'f.historico_familiar' => ['array'],
                'f.historico_familiar.*' => [Rule::in(self::HISTORICO_FAMILIAR)],
                'f.historico_familiar_obs' => $texto,
            ], self::regrasItens('medicamentos', $f['medicamentos_nao_toma']),
                self::regrasItens('suplementos', $f['suplementos_nao_usa'])),

            6 => [
                'f.observacoes_texto' => $f['observacoes_sem'] ? ['nullable', 'string', 'max:2000'] : ['required', 'string', 'max:2000'],
            ],

            default => [],
        };
    }

    private static function regrasItens(string $campo, bool $nenhum): array
    {
        if ($nenhum) {
            return [];
        }

        return [
            "f.$campo" => ['array', 'min:1', 'max:20'],
            "f.$campo.*.nome" => ['required', 'string', 'max:120'],
            "f.$campo.*.dose" => ['nullable', 'string', 'max:80'],
            "f.$campo.*.horario" => ['nullable', 'string', 'max:80'],
            "f.$campo.*.nao_informado" => ['boolean'],
        ];
    }

    /** Todas as regras, para validar tudo antes do envio. */
    public static function todasAsRegras(array $f): array
    {
        $regras = [];
        foreach (array_keys(self::ETAPAS) as $etapa) {
            $regras += self::regras($etapa, $f);
        }

        return $regras;
    }

    public static function mensagens(): array
    {
        return [
            'required' => 'Preencha este campo.',
            'f.atividades.*.tipo.required' => 'Diga qual atividade (ou marque que não pratica).',
            'f.medicamentos.*.nome.required' => 'Diga o nome (ou marque que não toma).',
            'f.suplementos.*.nome.required' => 'Diga o nome (ou marque que não usa).',
            'f.metas_texto.required' => 'Escreva suas metas ou marque "não tenho".',
            'f.compulsao_texto.required' => 'Descreva ou marque "não tenho".',
            'f.observacoes_texto.required' => 'Escreva aqui ou marque "não tenho observações".',
            'f.nascimento.required' => 'Informe a data de nascimento ou marque para informar só a idade.',
            'f.idade_informada.required' => 'Informe a sua idade.',
            'in' => 'Escolha uma das opções.',
            'integer' => 'Use um número inteiro.',
            'numeric' => 'Use um número (ex.: 72,5).',
            'decimal' => 'Use no máximo uma casa decimal.',
            'between' => 'Valor fora do esperado (entre :min e :max).',
            'multiple_of' => 'Use horas inteiras ou meias horas (ex.: 7 ou 7,5).',
            'max' => 'Texto muito longo (máximo :max caracteres).',
            'min' => 'Preencha pelo menos :min.',
            'date' => 'Data inválida.',
            'before_or_equal' => 'A data não pode ser no futuro.',
            'after' => 'Data inválida.',
        ];
    }

    /** Converte "72,5" em 72.5 nos campos numéricos antes de validar. */
    public static function normalizar(array $f): array
    {
        foreach (['peso_kg', 'peso_pre_gestacional_kg', 'sono_horas', 'altura_cm', 'idade_informada', 'semanas_gestacao'] as $campo) {
            if (is_string($f[$campo] ?? null)) {
                $f[$campo] = str_replace(',', '.', trim($f[$campo]));
            }
        }
        if ($f['sexo'] !== 'Feminino') {
            $f['gestante'] = false;
        }

        return $f;
    }

    /** Monta o JSON da seção 6.1 (sem os metadados id/convite/datas, que o modelo acrescenta). */
    public static function montar(array $f): array
    {
        $t = fn ($v) => ($v = trim((string) $v)) === '' ? null : $v;
        $num = fn ($v) => $t($v) === null ? null : (float) $v;
        $int = fn ($v) => $t($v) === null ? null : (int) $v;
        $gestante = $f['sexo'] === 'Feminino' && (bool) $f['gestante'];

        $itens = fn (array $lista) => array_values(array_map(fn ($i) => [
            'nome' => trim($i['nome']),
            'dose' => $i['nao_informado'] ? null : $t($i['dose']),
            'horario' => $i['nao_informado'] ? null : $t($i['horario']),
            'nao_informado' => (bool) $i['nao_informado'],
        ], array_filter($lista, fn ($i) => trim($i['nome'] ?? '') !== '')));

        return [
            'identificacao' => [
                'nome' => trim($f['nome']),
                'nascimento' => $f['usa_idade'] ? null : $t($f['nascimento']),
                'idade_informada' => $f['usa_idade'] ? $int($f['idade_informada']) : null,
                'sexo' => $f['sexo'],
                'altura_cm' => $int($f['altura_cm']),
                'peso_kg' => $num($f['peso_kg']),
                'gestante' => $gestante,
                'semanas_gestacao' => $gestante ? $int($f['semanas_gestacao']) : null,
                'peso_pre_gestacional_kg' => $gestante ? $num($f['peso_pre_gestacional_kg']) : null,
            ],
            'rotina' => [
                'profissao' => $t($f['profissao']),
                'horario_trabalho' => $t($f['horario_trabalho']),
                'objetivo' => $f['objetivo'] === 'Outro' ? trim($f['objetivo_outro']) : $f['objetivo'],
                'metas_secundarias' => ['nao_tem' => (bool) $f['metas_nao_tem'], 'texto' => $f['metas_nao_tem'] ? null : $t($f['metas_texto'])],
            ],
            'atividade_fisica' => [
                'nao_pratica' => (bool) $f['atividade_nao_pratica'],
                'lista' => $f['atividade_nao_pratica'] ? [] : array_values(array_map(fn ($a) => [
                    'tipo' => trim($a['tipo']), 'frequencia' => $t($a['frequencia']), 'duracao' => $t($a['duracao']),
                ], array_filter($f['atividades'], fn ($a) => trim($a['tipo'] ?? '') !== ''))),
            ],
            'sono_habitos' => [
                'sono_horas' => $num($f['sono_horas']),
                'sono_horario' => $t($f['sono_horario']),
                'sono_reparador' => $f['sono_reparador'],
                'estresse' => $f['estresse'],
                'hidratacao' => $t($f['hidratacao']),
                'intestino' => $t($f['intestino']),
            ],
            'alimentacao' => [
                'alergias' => ['resposta' => $f['alergias'], 'texto' => $f['alergias'] === 'Sim' ? $t($f['alergias_texto']) : null],
                'padrao_alimentar' => $f['padrao_alimentar'],
                'aversoes' => ['resposta' => $f['aversoes'], 'texto' => $f['aversoes'] === 'Sim' ? $t($f['aversoes_texto']) : null],
                'compulsao' => ['nao_tem' => (bool) $f['compulsao_nao_tem'], 'texto' => $f['compulsao_nao_tem'] ? null : $t($f['compulsao_texto'])],
                'orcamento' => $f['orcamento'],
                'culinaria' => $f['culinaria'],
            ],
            'saude' => [
                'condicoes' => array_values(array_intersect(self::CONDICOES, $f['condicoes'])),
                'condicoes_outras' => trim((string) $f['condicoes_outras']),
                'medicamentos' => ['nao_toma' => (bool) $f['medicamentos_nao_toma'], 'lista' => $f['medicamentos_nao_toma'] ? [] : $itens($f['medicamentos'])],
                'suplementos' => ['nao_usa' => (bool) $f['suplementos_nao_usa'], 'lista' => $f['suplementos_nao_usa'] ? [] : $itens($f['suplementos'])],
                'historico_familiar' => array_values(array_intersect(self::HISTORICO_FAMILIAR, $f['historico_familiar'])),
                'historico_familiar_obs' => trim((string) $f['historico_familiar_obs']),
            ],
            'observacoes' => ['sem_observacoes' => (bool) $f['observacoes_sem'], 'texto' => $f['observacoes_sem'] ? null : $t($f['observacoes_texto'])],
            // Arquivos chegam na etapa 3 do desenvolvimento (uploads privados)
            'arquivos' => ['sem_exames' => false, 'sem_bio' => false, 'sem_outros' => false, 'lista' => []],
        ];
    }
}
