<?php

namespace Tests\Feature;

use App\Livewire\AgendamentoForm;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LandingDivulgacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_mostra_crn_local_e_instagram(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('CRN-3 73749')
            ->assertSee('Parada Inglesa, São Paulo · presencial e online')
            ->assertSee('https://www.instagram.com/hellenfatima.nutri/', false)
            ->assertSee('@hellenfatima.nutri');
    }

    public function test_agendamento_guarda_a_origem_do_link_da_bio(): void
    {
        Livewire::withQueryParams(['utm_source' => 'Instagram', 'utm_medium' => 'bio', 'utm_campaign' => 'lancamento'])
            ->test(AgendamentoForm::class)
            ->set('nome', 'Maria Teste')->set('telefone', '11999998888')->set('data_preferencia', 'terça à tarde')
            ->call('salvar');

        $lead = Lead::sole();
        $this->assertSame('instagram', $lead->origem);
        $this->assertSame('bio', $lead->meio);
        $this->assertSame('lancamento', $lead->campanha);
        $this->assertSame('instagram / bio (lancamento)', $lead->origemLegivel());
    }

    public function test_sem_utm_a_origem_e_direto_e_nao_aceita_lixo(): void
    {
        Livewire::test(AgendamentoForm::class)
            ->set('nome', 'Ana Teste')->set('telefone', '11999998888')->set('data_preferencia', 'manhã')
            ->call('salvar');
        $this->assertSame('direto', Lead::sole()->origemLegivel());

        Lead::query()->delete();
        Livewire::withQueryParams(['utm_source' => '<script>alert(1)</script>'.str_repeat('x', 100)])
            ->test(AgendamentoForm::class)
            ->set('nome', 'Bia Teste')->set('telefone', '11999998888')->set('data_preferencia', 'manhã')
            ->call('salvar');
        $origem = Lead::sole()->origem;
        $this->assertStringNotContainsString('<', $origem);
        $this->assertLessThanOrEqual(50, strlen($origem));
    }

    public function test_painel_mostra_a_origem(): void
    {
        Lead::create(['nome' => 'Maria', 'telefone' => '11999998888', 'origem' => 'instagram', 'meio' => 'bio']);
        Lead::create(['nome' => 'Joana', 'telefone' => '11999997777']);

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertSee('instagram / bio')
            ->assertSee('direto')
            ->assertSee('do Instagram: <strong>1</strong>', false);
    }
}
