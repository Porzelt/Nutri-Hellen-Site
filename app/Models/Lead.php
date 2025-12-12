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
        'contatado'
    ];
}
