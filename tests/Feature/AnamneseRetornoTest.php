<?php

namespace Tests\Feature;

use App\Livewire\Anamnese\Formulario;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class AnamneseRetornoTest extends TestCase
{
    use RefreshDatabase;

    private function abrir(Convite $convite): Testable
    {
        return Livewire::test(Formulario::class, ['token' => $convite->token]);
    }

    public function test_retorno_tem_5_etapas_e_nome_pre_preenchido(): void
    {
        $convite = Convite::criar('retorno', 'Raniele Barreto', 'raniele-barreto-oliveira');

        $this->abrir($convite)
            ->assertDontSee('Formulário em preparação')
            ->set('aceite', true)->call('aceitar')
            ->assertSet('etapa', 1)
            ->assertSet('f.nome', 'Raniele Barreto')
            ->assertSee('Etapa 1 de 5')
            ->assertSee('Como foi seguir o plano alimentar?');
    }

    public function test_fluxo_completo_gera_json_da_secao_6_3(): void
    {
        Storage::fake('anamnese');
        $convite = Convite::criar('retorno', 'Raniele', 'raniele-barreto-oliveira');

        $t = $this->abrir($convite)->set('aceite', true)->call('aceitar');

        // 1. Como foi o plano
        $t->call('avancar')->assertHasErrors(['f.peso_kg', 'f.adesao']);
        $t->set('f.nome', 'Raniele Barreto Oliveira')->set('f.peso_kg', '85,0')->set('f.adesao', 'Boa')
            ->set('f.dificuldades', 'fim de semana')->set('f.mudancas', 'mais disposição')
            ->call('avancar')->assertHasNoErrors()->assertSet('etapa', 2);

        // 2. Rotina e saúde (tudo opcional)
        $t->set('f.atividade', 'caminhada 3x por semana')->set('f.sono', '7 horas')
            ->set('f.intestino', 'normal')->set('f.hidratacao', '1 a 2 litros')
            ->call('avancar')->assertHasNoErrors()->assertSet('etapa', 3);

        // 3. Medicamentos e suplementos
        $t->set('f.medicamentos_nao_toma', true)
            ->set('f.suplementos', [['nome' => 'vitamina D', 'dose' => '2000 UI', 'horario' => 'almoço', 'nao_informado' => false]])
            ->call('avancar')->assertHasNoErrors()->assertSet('etapa', 4);

        // 4. Arquivos
        $t->set('uploads.bio', [UploadedFile::fake()->image('bio.jpg', 40, 40)])
            ->set('f.sem_exames', true)->set('f.sem_outros', true)
            ->call('avancar')->assertHasNoErrors()->assertSet('etapa', 5);

        // 5. Observações e envio
        $t->set('f.observacoes_sem', true)->call('enviar')->assertHasNoErrors()->assertSet('enviado', true);

        $resposta = Resposta::sole();
        $this->assertSame('retorno', $resposta->tipo);
        $this->assertSame('respondido', $convite->fresh()->status());

        $d = $resposta->dados;
        $this->assertSame(['identificacao', 'retorno', 'saude', 'observacoes', 'arquivos'], array_keys($d));
        $this->assertEquals(['nome' => 'Raniele Barreto Oliveira', 'peso_kg' => 85.0], $d['identificacao']);
        $this->assertSame([
            'adesao' => 'Boa', 'dificuldades' => 'fim de semana', 'mudancas' => 'mais disposição',
            'atividade' => 'caminhada 3x por semana', 'sono' => '7 horas', 'intestino' => 'normal',
            'hidratacao' => '1 a 2 litros', 'condicoes_novas' => '',
        ], $d['retorno']);
        $this->assertSame(['nao_toma' => true, 'lista' => []], $d['saude']['medicamentos']);
        $this->assertSame([['nome' => 'vitamina D', 'dose' => '2000 UI', 'horario' => 'almoço', 'nao_informado' => false]], $d['saude']['suplementos']['lista']);
        $this->assertSame(['sem_observacoes' => true, 'texto' => null], $d['observacoes']);
        $this->assertTrue($d['arquivos']['sem_exames']);
        $this->assertFalse($d['arquivos']['sem_bio']);
        $this->assertSame('bio', $d['arquivos']['lista'][0]['categoria']);

        // Link desativado
        $this->get($convite->url())->assertSee('Link indisponível');
    }

    public function test_adesao_so_aceita_opcoes_do_contrato(): void
    {
        $convite = Convite::criar('retorno', 'Ana');

        $this->abrir($convite)->set('aceite', true)->call('aceitar')
            ->set('f.peso_kg', '70')->set('f.adesao', 'Ótima')
            ->call('avancar')
            ->assertHasErrors(['f.adesao' => 'in']);
    }
}
