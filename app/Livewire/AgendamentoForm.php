<?php

namespace App\Livewire;

use App\Models\Lead;
use Livewire\Component;

class AgendamentoForm extends Component
{
    // Variáveis que estarão ligadas aos inputs do formulário
    public $nome = '';
    public $telefone = '';
    public $data_preferencia = '';

    // Regras de validação simples
    protected $rules = [
        'nome' => 'required|min:3',
        'telefone' => 'required|min:10', // Ex: (11) 99999-9999
        'data_preferencia' => 'required',
    ];

    public function salvar()
    {
        // 1. Validar os dados
        $this->validate();

        // 2. Salvar no Banco de Dados
        Lead::create([
            'nome' => $this->nome,
            'telefone' => $this->telefone,
            'data_preferencia' => $this->data_preferencia,
        ]);

        // 3. Gerar Link do WhatsApp
        
        $numeroNutri = '5511967261034';
        
        $mensagem = urlencode("Olá! Me chamo {$this->nome}. Gostaria de agendar uma consulta. Minha preferência é: {$this->data_preferencia}.");
        
        $linkWhatsApp = "https://wa.me/{$numeroNutri}?text={$mensagem}";

        // 4. Redirecionar o usuário
        return redirect()->away($linkWhatsApp);
    }

    public function render()
    {
        return view('livewire.agendamento-form');
    }
}