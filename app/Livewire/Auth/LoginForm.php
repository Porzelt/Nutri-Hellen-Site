<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        // Tenta autenticar o usuário
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            
            // Regenera a sessão para evitar ataques de fixação (Segurança)
            session()->regenerate();

            // Redireciona para o Dashboard (que criaremos em breve)
            return redirect()->intended(route('dashboard'));
        }

        // Se falhar, retorna erro para o campo de email
        $this->addError('email', 'As credenciais fornecidas não conferem.');
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}