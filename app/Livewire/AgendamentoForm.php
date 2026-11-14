<?php

namespace App\Livewire;

use App\Models\Lead;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class AgendamentoForm extends Component
{
    // Variáveis que estarão ligadas aos inputs do formulário
    public $nome = '';
    public $telefone = '';
    public $data_preferencia = '';

    /** Origem do visitante (UTM do link), lida ao abrir a página e travada contra alteração. */
    #[\Livewire\Attributes\Locked]
    public ?array $utm = null;

    public function mount(): void
    {
        $limpar = fn ($v, $max) => is_string($v) && trim($v) !== ''
            ? \Illuminate\Support\Str::limit(\Illuminate\Support\Str::lower(preg_replace('/[^\pL\pN _.-]/u', '', trim($v))), $max, '')
            : null;

        $this->utm = [
            'origem' => $limpar(request()->query('utm_source'), 50),
            'meio' => $limpar(request()->query('utm_medium'), 50),
            'campanha' => $limpar(request()->query('utm_campaign'), 80),
        ];
    }

    // Regras de validação simples
    protected $rules = [
        'nome' => 'required|min:3|max:100',
        'telefone' => 'required|min:10|max:20', // Ex: (11) 99999-9999
        'data_preferencia' => 'required|max:150',
    ];

    public function salvar()
    {
        // 1. Validar os dados
        $this->validate();

        // Anti-spam: até 3 pedidos por IP a cada 10 minutos
        $chave = 'agendamento|'.request()->ip();

        if (RateLimiter::tooManyAttempts($chave, 3)) {
            $this->addError('nome', 'Muitos pedidos seguidos. Tente novamente em alguns minutos ou chame no WhatsApp.');

            return;
        }

        RateLimiter::hit($chave, 600);

        // 2. Salvar no Banco de Dados
        Lead::create([
            'nome' => $this->nome,
            'telefone' => $this->telefone,
            'data_preferencia' => $this->data_preferencia,
            'origem' => $this->utm['origem'] ?? null,
            'meio' => $this->utm['meio'] ?? null,
            'campanha' => $this->utm['campanha'] ?? null,
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