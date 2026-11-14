<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'nome',
        'telefone',
        'data_preferencia',
        'observacoes',
        'contatado',
        'origem',
        'meio',
        'campanha',
    ];

    /** Texto curto para o painel: "instagram / bio" ou "direto". */
    public function origemLegivel(): string
    {
        if (! $this->origem) {
            return 'direto';
        }

        return $this->origem.($this->meio ? ' / '.$this->meio : '').($this->campanha ? ' ('.$this->campanha.')' : '');
    }
}
