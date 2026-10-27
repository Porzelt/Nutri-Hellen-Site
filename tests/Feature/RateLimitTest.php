<?php

namespace Tests\Feature;

use App\Livewire\AgendamentoForm;
use App\Livewire\Auth\LoginForm;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login bloqueia após 5 senhas erradas, mesmo que a 6ª esteja certa
     */
    public function test_login_bloqueia_apos_cinco_tentativas_erradas(): void
    {
        User::factory()->create(['email' => 'admin@teste.com', 'password' => bcrypt('senha-certa')]);

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(LoginForm::class)
                ->set('email', 'admin@teste.com')
                ->set('password', 'errada')
                ->call('login');
        }

        Livewire::test(LoginForm::class)
            ->set('email', 'admin@teste.com')
            ->set('password', 'senha-certa')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    /**
     * Formulário de agendamento aceita no máximo 3 pedidos por IP em 10 minutos
     */
    public function test_agendamento_limita_pedidos_por_ip(): void
    {
        for ($i = 0; $i < 4; $i++) {
            Livewire::test(AgendamentoForm::class)
                ->set('nome', 'Paciente '.$i)
                ->set('telefone', '11999997777')
                ->set('data_preferencia', 'Quarta-feira')
                ->call('salvar');
        }

        $this->assertSame(3, Lead::count());
    }

    /**
     * Campos longos demais são recusados
     */
    public function test_agendamento_recusa_campos_longos(): void
    {
        Livewire::test(AgendamentoForm::class)
            ->set('nome', str_repeat('a', 101))
            ->set('telefone', '11999997777')
            ->set('data_preferencia', 'Quarta-feira')
            ->call('salvar')
            ->assertHasErrors('nome');
    }
}
