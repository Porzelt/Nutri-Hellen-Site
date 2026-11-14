<?php

namespace Tests\Feature;

use App\Livewire\Anamnese\Formulario;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class AnamnesePacienteTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function abrir(Convite $convite): Testable
    {
        return Livewire::test(Formulario::class, ['token' => $convite->token]);
    }

    /** Preenche e avança todas as etapas com um paciente completo. */
    private function preencherTudo(Testable $t, array $extra = []): Testable
    {
        $t->set('aceite', true)->call('aceitar')->assertSet('etapa', 1);

        $etapas = [
            1 => ['nome' => 'Maria da Silva', 'nascimento' => '1990-05-20', 'sexo' => 'Feminino', 'altura_cm' => '164', 'peso_kg' => '82,0',
                'gestante' => true, 'semanas_gestacao' => '14', 'peso_pre_gestacional_kg' => '84'],
            2 => ['profissao' => 'Auxiliar administrativo', 'horario_trabalho' => 'Seg a sex, 8h às 18h', 'objetivo' => 'Emagrecimento',
                'metas_texto' => 'melhorar a disposição', 'atividades' => [['tipo' => 'caminhada', 'frequencia' => '3x/semana', 'duracao' => '30 min']]],
            3 => ['sono_horas' => '7', 'sono_horario' => '00h às 07h', 'sono_reparador' => 'Às vezes', 'estresse' => 'Muito alto',
                'hidratacao' => '1 a 2 litros', 'intestino' => '1 vez ao dia'],
            4 => ['alergias' => 'Não', 'padrao_alimentar' => 'Onívoro', 'aversoes' => 'Sim', 'aversoes_texto' => 'jiló e melão',
                'compulsao_texto' => 'doce à noite', 'orcamento' => 'Baixo', 'culinaria' => 'Básica'],
            5 => ['condicoes' => ['Dislipidemia (colesterol / triglicerídeos)'],
                'medicamentos' => [['nome' => 'levotiroxina', 'dose' => '', 'horario' => '', 'nao_informado' => true]],
                'suplementos' => [['nome' => 'ácido fólico', 'dose' => '5 mg', 'horario' => 'manhã', 'nao_informado' => false]],
                'historico_familiar_situacao' => 'tem', 'historico_familiar' => ['Diabetes', 'Hipertensão'], 'historico_familiar_obs' => 'mãe'],
            6 => ['sem_exames' => true, 'sem_bio' => true, 'sem_outros' => true],
            7 => ['observacoes_texto' => 'Trabalho em pé o dia todo'],
        ];

        foreach ($etapas as $etapa => $campos) {
            if (isset($extra['antes'][$etapa])) {
                ($extra['antes'][$etapa])($t);
            }
            foreach (array_merge($campos, $extra[$etapa] ?? []) as $campo => $valor) {
                $t->set("f.$campo", $valor);
            }
            $etapa < 7
                ? $t->call('avancar')->assertHasNoErrors()->assertSet('etapa', $etapa + 1)
                : $t->call('enviar')->assertHasNoErrors();
        }

        return $t;
    }

    public function test_link_invalido_mostra_mensagem_generica(): void
    {
        $this->get('/anamnese/'.str_repeat('a', 48))
            ->assertOk()
            ->assertSee('Link indisponível')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_link_expirado_mostra_mensagem_generica(): void
    {
        $convite = Convite::criar('primeira', validadeDias: 1);
        Carbon::setTestNow(now()->addDays(3));

        $this->get($convite->url())->assertSee('Link indisponível')->assertDontSee('Antes de começar');
    }

    public function test_pagina_tem_noindex_csp_e_consentimento_versionado(): void
    {
        $convite = Convite::criar('primeira');

        $resposta = $this->get($convite->url())
            ->assertOk()
            ->assertSee('Antes de começar')
            ->assertDontSee('RASCUNHO')
            ->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false)
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $csp = $resposta->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_exige_aceite_do_consentimento(): void
    {
        $convite = Convite::criar('primeira');

        $this->abrir($convite)->call('aceitar')->assertHasErrors('aceite')->assertSet('etapa', 0);
        $this->assertSame(0, Resposta::count());
    }

    public function test_fluxo_completo_gera_json_do_contrato_e_desativa_o_link(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-06 14:00:00'));
        $convite = Convite::criar('primeira', 'Maria');

        $this->preencherTudo($this->abrir($convite))->assertSet('enviado', true)->assertSee('Recebi suas respostas')
            ->assertSee('Obrigada por preencher, Maria. Eu vou analisar tudo antes da sua consulta.');

        $resposta = Resposta::sole();
        $this->assertNotNull($resposta->enviado_em);
        $this->assertSame(config('consentimento.versao'), $resposta->consentimento_versao);
        $this->assertNotNull($convite->fresh()->respondido_em);
        $this->assertSame('respondido', $convite->fresh()->status());

        $d = $resposta->dados;
        // assertEquals: no JSON 82.0 e 82 são o mesmo número (a API da etapa 5 preserva o ".0")
        $this->assertEquals([
            'nome' => 'Maria da Silva', 'nascimento' => '1990-05-20', 'idade_informada' => null, 'sexo' => 'Feminino',
            'altura_cm' => 164, 'peso_kg' => 82.0, 'gestante' => true, 'semanas_gestacao' => 14, 'peso_pre_gestacional_kg' => 84.0,
        ], $d['identificacao']);
        $this->assertSame(['nao_tem' => false, 'texto' => 'melhorar a disposição'], $d['rotina']['metas_secundarias']);
        $this->assertSame([['tipo' => 'caminhada', 'frequencia' => '3x/semana', 'duracao' => '30 min']], $d['atividade_fisica']['lista']);
        $this->assertEquals(7, $d['sono_habitos']['sono_horas']);
        $this->assertSame(['resposta' => 'Não', 'texto' => null], $d['alimentacao']['alergias']);
        $this->assertSame(['resposta' => 'Sim', 'texto' => 'jiló e melão'], $d['alimentacao']['aversoes']);
        $this->assertSame([['nome' => 'levotiroxina', 'dose' => null, 'horario' => null, 'nao_informado' => true]], $d['saude']['medicamentos']['lista']);
        $this->assertSame('5 mg', $d['saude']['suplementos']['lista'][0]['dose']);
        $this->assertSame(['Diabetes', 'Hipertensão'], $d['saude']['historico_familiar']);
        $this->assertFalse($d['saude']['historico_familiar_nao_tem']);
        $this->assertFalse($d['saude']['historico_familiar_nao_sabe']);
        $this->assertFalse($d['saude']['sem_condicoes']);
        $this->assertSame('1 a 2 litros', $d['sono_habitos']['hidratacao']);
        $this->assertSame(['sem_observacoes' => false, 'texto' => 'Trabalho em pé o dia todo'], $d['observacoes']);

        // Link deixa de funcionar depois do envio
        $this->get($convite->url())->assertSee('Link indisponível');
    }

    public function test_saude_exige_condicao_ou_nao_tenho_e_situacao_da_familia(): void
    {
        $convite = Convite::criar('primeira');
        Resposta::create([
            'convite_id' => $convite->id, 'tipo' => 'primeira', 'etapa' => 5,
            'consentimento_versao' => 'x', 'consentimento_aceito_em' => now(),
            'dados' => ['rascunho' => \App\Anamnese\PrimeiraConsulta::estadoInicial()],
        ]);

        $t = $this->abrir($convite)
            ->set('f.medicamentos_nao_toma', true)->set('f.suplementos_nao_usa', true)
            ->call('avancar')
            ->assertHasErrors(['f.condicoes' => 'required_without', 'f.historico_familiar_situacao' => 'required'])
            ->assertSet('etapa', 5);

        // "Sim, há casos" sem marcar nenhuma doença
        $t->set('f.sem_condicoes', true)->set('f.historico_familiar_situacao', 'tem')
            ->call('avancar')
            ->assertHasErrors(['f.historico_familiar' => 'required_without']);

        $t->set('f.historico_familiar_situacao', 'nao_tem')
            ->call('avancar')
            ->assertHasNoErrors()
            ->assertSet('etapa', 6);
    }

    public function test_agua_so_aceita_as_opcoes_em_litros(): void
    {
        $convite = Convite::criar('primeira');
        Resposta::create([
            'convite_id' => $convite->id, 'tipo' => 'primeira', 'etapa' => 3,
            'consentimento_versao' => 'x', 'consentimento_aceito_em' => now(),
            'dados' => ['rascunho' => \App\Anamnese\PrimeiraConsulta::estadoInicial()],
        ]);

        $this->abrir($convite)
            ->assertSee('Quantos litros de água bebe por dia?')
            ->set('f.sono_reparador', 'Sim')->set('f.estresse', 'Baixo')
            ->set('f.hidratacao', '1')
            ->call('avancar')
            ->assertHasErrors(['f.hidratacao' => 'in'])
            ->set('f.hidratacao', 'Mais de 3 litros')
            ->call('avancar')
            ->assertHasNoErrors()
            ->assertSet('etapa', 4);
    }

    public function test_mostra_dica_de_padrao_alimentar(): void
    {
        $convite = Convite::criar('primeira');
        Resposta::create([
            'convite_id' => $convite->id, 'tipo' => 'primeira', 'etapa' => 4,
            'consentimento_versao' => 'x', 'consentimento_aceito_em' => now(),
            'dados' => ['rascunho' => \App\Anamnese\PrimeiraConsulta::estadoInicial()],
        ]);

        $this->abrir($convite)->assertSee('Onívoro: come de tudo, inclusive carnes.');
    }

    public function test_sem_gestacao_e_com_idade_em_vez_de_nascimento(): void
    {
        $convite = Convite::criar('primeira');

        $this->preencherTudo($this->abrir($convite), [
            1 => ['sexo' => 'Masculino', 'usa_idade' => true, 'idade_informada' => '45', 'nascimento' => ''],
            2 => ['atividade_nao_pratica' => true, 'metas_nao_tem' => true],
            4 => ['compulsao_nao_tem' => true],
            5 => ['medicamentos_nao_toma' => true, 'sem_condicoes' => true, 'condicoes' => ['Hipertensão'], 'historico_familiar_situacao' => 'nao_sabe'],
            7 => ['observacoes_sem' => true],
        ]);

        $d = Resposta::sole()->dados;
        // "Não tenho problemas de saúde" ignora o que tinha sido marcado antes
        $this->assertTrue($d['saude']['sem_condicoes']);
        $this->assertSame([], $d['saude']['condicoes']);
        // "Não sei" no histórico familiar: lista vazia
        $this->assertTrue($d['saude']['historico_familiar_nao_sabe']);
        $this->assertFalse($d['saude']['historico_familiar_nao_tem']);
        $this->assertSame([], $d['saude']['historico_familiar']);
        $this->assertSame('', $d['saude']['historico_familiar_obs']);
        $this->assertNull($d['identificacao']['nascimento']);
        $this->assertSame(45, $d['identificacao']['idade_informada']);
        $this->assertFalse($d['identificacao']['gestante']);
        $this->assertNull($d['identificacao']['semanas_gestacao']);
        $this->assertSame(['nao_pratica' => true, 'lista' => []], $d['atividade_fisica']);
        $this->assertSame(['nao_tem' => true, 'texto' => null], $d['rotina']['metas_secundarias']);
        $this->assertSame(['nao_toma' => true, 'lista' => []], $d['saude']['medicamentos']);
        $this->assertSame(['sem_observacoes' => true, 'texto' => null], $d['observacoes']);
    }

    public function test_validacoes_da_primeira_etapa(): void
    {
        $convite = Convite::criar('primeira');

        $t = $this->abrir($convite)->set('aceite', true)->call('aceitar')
            ->set('f.nome', 'Ana')->set('f.sexo', 'Feminino')->set('f.nascimento', '1990-01-01')
            ->set('f.altura_cm', '300')->set('f.peso_kg', '70,55')
            ->call('avancar')
            ->assertHasErrors(['f.altura_cm', 'f.peso_kg'])
            ->assertSet('etapa', 1);

        // Gestante exige semanas e peso pré-gestacional
        $t->set('f.altura_cm', '160')->set('f.peso_kg', '70,5')->set('f.gestante', true)
            ->call('avancar')
            ->assertHasErrors(['f.semanas_gestacao', 'f.peso_pre_gestacional_kg']);
    }

    public function test_rascunho_e_retomado_ao_voltar_ao_link(): void
    {
        $convite = Convite::criar('primeira');

        $this->abrir($convite)->set('aceite', true)->call('aceitar')
            ->set('f.nome', 'Maria')->set('f.sexo', 'Feminino')->set('f.nascimento', '1990-01-01')
            ->set('f.altura_cm', '160')->set('f.peso_kg', '70')
            ->call('avancar')->assertSet('etapa', 2);

        $resposta = Resposta::sole();
        $this->assertNull($resposta->enviado_em);
        $this->assertSame('criado', $convite->fresh()->status());

        $this->abrir($convite)
            ->assertSet('etapa', 2)
            ->assertSet('f.nome', 'Maria')
            ->assertSet('aceite', true);
    }

    public function test_respostas_ficam_criptografadas_no_banco(): void
    {
        $convite = Convite::criar('primeira');
        $this->preencherTudo($this->abrir($convite));

        $bruto = DB::table('respostas')->value('dados');
        $this->assertStringNotContainsString('Maria', $bruto);
        $this->assertStringNotContainsString('levotiroxina', $bruto);
    }

    public function test_link_cancelado_no_meio_do_preenchimento_para_de_funcionar(): void
    {
        $convite = Convite::criar('primeira');
        $t = $this->abrir($convite)->set('aceite', true)->call('aceitar');

        $convite->delete();

        $t->set('f.nome', 'Maria')->call('avancar')->assertSet('conviteId', null)->assertSee('Link indisponível');
        $this->assertSame(0, Resposta::count()); // apagada junto (cascade)
    }

    public function test_nao_aceita_campo_fora_das_opcoes_do_contrato(): void
    {
        $convite = Convite::criar('primeira');

        $this->abrir($convite)->set('aceite', true)->call('aceitar')
            ->set('f.nome', 'Ana')->set('f.sexo', 'Outro')->set('f.nascimento', '1990-01-01')
            ->set('f.altura_cm', '160')->set('f.peso_kg', '60')
            ->call('avancar')
            ->assertHasErrors(['f.sexo' => 'in']);
    }
}
