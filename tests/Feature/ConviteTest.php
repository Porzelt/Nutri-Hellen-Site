<?php

namespace Tests\Feature;

use App\Livewire\Admin\Convites;
use App\Models\Convite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ConviteTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_cria_convite_com_token_longo_e_validade_no_fim_do_dia(): void
    {
        // 06/11/2026 10:00 em Brasília
        Carbon::setTestNow(Carbon::parse('2026-11-06 10:00:00', Convite::FUSO));

        $convite = Convite::criar('primeira', 'Maria da Silva', 'maria-da-silva', 7);

        $this->assertGreaterThanOrEqual(40, strlen($convite->token));
        $this->assertSame(
            '2026-11-13 23:59:59',
            $convite->expira_em->timezone(Convite::FUSO)->format('Y-m-d H:i:s')
        );
        $this->assertSame('criado', $convite->status());
        $this->assertStringEndsWith('/anamnese/'.$convite->token, $convite->url());
        $this->assertTrue(Convite::peloToken($convite->token)->is($convite));
        $this->assertNull(Convite::peloToken('token-que-nao-existe'));
    }

    public function test_dados_do_paciente_e_token_ficam_criptografados_no_banco(): void
    {
        $convite = Convite::criar('retorno', 'Maria da Silva', 'maria-da-silva');

        $linha = DB::table('convites')->where('id', $convite->id)->first();

        $this->assertStringNotContainsString('Maria', $linha->nome_paciente);
        $this->assertStringNotContainsString('maria-da-silva', $linha->paciente_ref);
        $this->assertStringNotContainsString($convite->token, $linha->token);
        $this->assertSame(hash('sha256', $convite->token), $linha->token_hash);
    }

    public function test_status_expirado_e_respondido(): void
    {
        $convite = Convite::criar('primeira', validadeDias: 1);

        Carbon::setTestNow(now()->addDays(3));
        $this->assertSame('expirado', $convite->fresh()->status());
        $this->assertFalse($convite->fresh()->utilizavel());
        $this->assertSame(1, Convite::comStatus('expirado')->count());

        Carbon::setTestNow();
        $convite->respondido_em = now();
        $convite->save();
        $this->assertSame('respondido', $convite->fresh()->status());
        $this->assertSame(1, Convite::comStatus('respondido')->count());
    }

    public function test_painel_exige_login(): void
    {
        $this->get('/dashboard/convites')->assertRedirect('/login');
    }

    public function test_painel_cria_convite_e_mostra_link(): void
    {
        $this->actingAs(User::factory()->create());

        $componente = Livewire::test(Convites::class)
            ->set('tipo', 'retorno')
            ->set('nome_paciente', 'João Souza')
            ->set('validade_dias', 10)
            ->call('criar')
            ->assertHasNoErrors();

        $convite = Convite::sole();
        $this->assertSame('retorno', $convite->tipo);
        $this->assertSame('João Souza', $convite->nome_paciente);
        $componente->assertSet('linkCriado', $convite->url())
            ->assertSee('João Souza')
            ->assertSee('Aguardando');
    }

    public function test_painel_valida_tipo_e_validade(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([0, 31] as $dias) {
            Livewire::test(Convites::class)
                ->set('validade_dias', $dias)
                ->call('criar')
                ->assertHasErrors(['validade_dias' => 'between']);
        }

        Livewire::test(Convites::class)
            ->set('tipo', 'outro')
            ->call('criar')
            ->assertHasErrors(['tipo']);

        $this->assertSame(0, Convite::count());
    }

    public function test_painel_cancela_convite(): void
    {
        $this->actingAs(User::factory()->create());
        $convite = Convite::criar('primeira', 'Ana');

        Livewire::test(Convites::class)
            ->call('cancelar', $convite->id);

        $this->assertSame(0, Convite::count());
        $this->assertNull(Convite::peloToken($convite->token));
    }

    public function test_painel_filtra_por_status(): void
    {
        $this->actingAs(User::factory()->create());
        Convite::criar('primeira', 'Ativo da Silva');
        $respondido = Convite::criar('primeira', 'Respondido Souza');
        $respondido->respondido_em = now();
        $respondido->save();

        Livewire::test(Convites::class)
            ->set('filtro', 'respondido')
            ->assertSee('Respondido Souza')
            ->assertDontSee('Ativo da Silva');
    }
}
