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

    public const VERSAO_ESQUEMA = '1.0';

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
}
