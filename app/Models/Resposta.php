<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Resposta do paciente a um convite. Enquanto enviado_em for null, é rascunho.
 * Os dados clínicos ficam num único JSON criptografado (contrato, seção 4).
 */
class Resposta extends Model
{
    use HasUuids;

    public const VERSAO_ESQUEMA = '1.1';

    protected $table = 'respostas';

    protected $fillable = ['convite_id', 'tipo', 'dados', 'etapa', 'consentimento_versao', 'consentimento_aceito_em', 'enviado_em'];

    protected function casts(): array
    {
        return [
            'dados' => 'encrypted:array',
            'etapa' => 'integer',
            'consentimento_aceito_em' => 'datetime',
            'enviado_em' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Apaga os arquivos do disco (o cascade do banco não passa pelo disco)
        static::deleting(fn (Resposta $r) => $r->arquivos()->get()->each->delete());
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(Arquivo::class);
    }

    public function convite(): BelongsTo
    {
        return $this->belongsTo(Convite::class);
    }

    public function enviada(): bool
    {
        return $this->enviado_em !== null;
    }

    /** Data no formato do contrato: ISO 8601 no horário de Brasília (ex.: 2026-10-27T14:03:00-03:00). */
    public static function iso(?\DateTimeInterface $data): ?string
    {
        return $data ? \Illuminate\Support\Carbon::instance($data)->timezone(Convite::FUSO)->toIso8601String() : null;
    }

    /** Item da listagem de pendentes (contrato, seção 5). */
    public function paraLista(): array
    {
        return [
            'id' => $this->id,
            'convite_id' => $this->convite_id,
            'tipo' => $this->tipo,
            'nome_paciente' => $this->convite?->nome_paciente,
            'paciente_ref' => $this->convite?->paciente_ref,
            'enviado_em' => self::iso($this->enviado_em),
            'qtd_arquivos' => $this->arquivos_count ?? $this->arquivos()->count(),
        ];
    }

    /** Resposta completa no esquema da seção 6 (6.1 ou 6.3). */
    public function paraContrato(): array
    {
        $dados = $this->dados;

        // Números decimais voltam do JSON como inteiros (82.0 -> 82): devolve como decimal
        foreach (['identificacao.peso_kg', 'identificacao.peso_pre_gestacional_kg', 'sono_habitos.sono_horas'] as $campo) {
            $valor = data_get($dados, $campo);
            if (is_int($valor)) {
                data_set($dados, $campo, (float) $valor);
            }
        }

        return [
            'id' => $this->id,
            'versao_esquema' => self::VERSAO_ESQUEMA,
            'tipo' => $this->tipo,
            'convite_id' => $this->convite_id,
            'paciente_ref' => $this->convite?->paciente_ref,
            'enviado_em' => self::iso($this->enviado_em),
            'consentimento' => [
                'aceito' => $this->consentimento_aceito_em !== null,
                'texto_versao' => $this->consentimento_versao,
                'aceito_em' => self::iso($this->consentimento_aceito_em),
            ],
        ] + $dados;
    }
}
