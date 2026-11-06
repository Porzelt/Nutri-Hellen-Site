<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Anexo da anamnese (contrato, seção 7). Fica no disco privado "anamnese",
 * criptografado, com nome aleatório; o nome original é só metadado (criptografado).
 */
class Arquivo extends Model
{
    use HasUuids;

    public const CATEGORIAS = ['exames', 'bio', 'outros'];

    /** Tipo real (conferido pelo conteúdo) => extensão. */
    public const TIPOS = [
        'application/pdf' => 'pdf',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heic',
    ];

    public const MAX_KB_ARQUIVO = 15 * 1024;

    public const MAX_ARQUIVOS = 15;

    public const MAX_BYTES_TOTAL = 60 * 1024 * 1024;

    protected $fillable = ['resposta_id', 'categoria', 'nome_original', 'mime', 'tamanho_bytes', 'caminho'];

    protected function casts(): array
    {
        return [
            'nome_original' => 'encrypted',
            'tamanho_bytes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Apagar a linha apaga o arquivo do disco
        static::deleting(fn (Arquivo $a) => Storage::disk('anamnese')->delete($a->caminho));
    }

    public function resposta(): BelongsTo
    {
        return $this->belongsTo(Resposta::class);
    }

    public static function guardar(Resposta $resposta, string $categoria, UploadedFile $upload, string $mimeReal): self
    {
        $caminho = Str::random(40).'.bin';
        Storage::disk('anamnese')->put($caminho, Crypt::encryptString($upload->get()));

        // Nome original sem pastas e com tamanho limitado
        $nome = Str::limit(basename(str_replace('\\', '/', $upload->getClientOriginalName())), 150, '');

        return self::create([
            'resposta_id' => $resposta->id,
            'categoria' => $categoria,
            'nome_original' => $nome !== '' ? $nome : 'arquivo.'.self::TIPOS[$mimeReal],
            'mime' => $mimeReal === 'image/heif' ? 'image/heic' : $mimeReal,
            'tamanho_bytes' => $upload->getSize(),
            'caminho' => $caminho,
        ]);
    }

    /** Conteúdo original (descriptografado), para o download da API. */
    public function conteudo(): string
    {
        return Crypt::decryptString(Storage::disk('anamnese')->get($this->caminho));
    }

    /** Item da lista "arquivos.lista" do contrato. */
    public function paraContrato(): array
    {
        return [
            'id' => $this->id,
            'categoria' => $this->categoria,
            'nome_original' => $this->nome_original,
            'mime' => $this->mime,
            'tamanho_bytes' => $this->tamanho_bytes,
        ];
    }
}
