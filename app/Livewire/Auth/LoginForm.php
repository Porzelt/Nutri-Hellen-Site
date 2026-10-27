<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

        // Bloqueia força bruta: 5 tentativas por email+IP, depois espera
        $chave = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($chave, 5)) {
            $segundos = RateLimiter::availableIn($chave);
            $this->addError('email', "Muitas tentativas. Tente novamente em {$segundos} segundos.");

            return;
        }

        // Tenta autenticar o usuário
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::clear($chave);

            // Regenera a sessão para evitar ataques de fixação (Segurança)
            session()->regenerate();

            // Redireciona para o Dashboard (que criaremos em breve)
            return redirect()->intended(route('dashboard'));
        }

        // Se falhar, conta a tentativa (janela de 5 min) e retorna erro para o campo de email
        RateLimiter::hit($chave, 300);
        $this->addError('email', 'As credenciais fornecidas não conferem.');
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}