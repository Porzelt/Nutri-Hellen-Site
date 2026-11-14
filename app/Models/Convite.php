<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Convite de anamnese: um link único para um paciente (contrato INTEGRACAO-CONSULTORIO.md, seções 2 e 3).
 */
class Convite extends Model
{
    use HasUuids;

    public const TIPOS = ['primeira', 'retorno'];

    public const VALIDADE_PADRAO = 7;

    public const VALIDADE_MIN = 1;

    public const VALIDADE_MAX = 30;

    /** Fuso usado para "fim do dia" da validade (contrato, Mudanças propostas 1). */
    public const FUSO = 'America/Sao_Paulo';

    protected $fillable = ['tipo', 'nome_paciente', 'paciente_ref', 'expira_em'];

    protected $hidden = ['token', 'token_hash'];

    protected function casts(): array
    {
        return [
            'nome_paciente' => 'encrypted',
            'paciente_ref' => 'encrypted',
            'token' => 'encrypted',
            'expira_em' => 'datetime',
            'respondido_em' => 'datetime',
        ];
    }

    /**
     * Cria o convite com token aleatório. A validade termina às 23:59:59 (Brasília) do último dia.
     */
    public static function criar(string $tipo, ?string $nomePaciente = null, ?string $pacienteRef = null, int $validadeDias = self::VALIDADE_PADRAO): self
    {
        $token = Str::random(48);

        $convite = new self([
            'tipo' => $tipo,
            'nome_paciente' => filled($nomePaciente) ? trim($nomePaciente) : null,
            'paciente_ref' => self::formatoDePasta($pacienteRef),
            'expira_em' => Carbon::now(self::FUSO)->addDays($validadeDias)->endOfDay()->utc(),
        ]);
        $convite->token = $token;
        $convite->token_hash = self::hashDoToken($token);
        $convite->save();

        return $convite;
    }

    protected static function booted(): void
    {
        // Cancelar/apagar o convite apaga a resposta e os arquivos (banco e disco)
        static::deleting(fn (Convite $c) => $c->resposta()->first()?->delete());
    }

    public function resposta(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Resposta::class);
    }

    /** "Maria da Silva 2" -> "maria-da-silva-2": nome de pasta do app local (sem acento, espaço vira hífen). */
    public static function formatoDePasta(?string $texto): ?string
    {
        $pasta = Str::slug((string) $texto, '-', 'pt_BR');

        return $pasta !== '' ? $pasta : null;
    }

    public static function hashDoToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** Busca um convite pelo token do link (sem revelar se existe). */
    public static function peloToken(string $token): ?self
    {
        return self::where('token_hash', self::hashDoToken($token))->first();
    }

    public function url(): string
    {
        return url('/anamnese/'.$this->token);
    }

    public function expirado(): bool
    {
        return $this->expira_em->isPast();
    }

    /** criado | respondido | expirado (contrato, seção 10). */
    public function status(): string
    {
        if ($this->respondido_em) {
            return 'respondido';
        }

        return $this->expirado() ? 'expirado' : 'criado';
    }

    /** O link só funciona antes do envio e dentro da validade. */
    public function utilizavel(): bool
    {
        return $this->status() === 'criado';
    }

    public function scopeComStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'respondido' => $query->whereNotNull('respondido_em'),
            'expirado' => $query->whereNull('respondido_em')->where('expira_em', '<=', now()),
            'criado' => $query->whereNull('respondido_em')->where('expira_em', '>', now()),
            default => $query,
        };
    }
}
