<?php

namespace Tests\Feature;

use App\Anamnese\PrimeiraConsulta;
use App\Models\Arquivo;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnamneseApiTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-de-teste-da-api-com-mais-de-quarenta-caracteres-1234';

    private const PDF = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n";

    protected function setUp(): void
    {
        parent::setUp();
        config(['anamnese.api_token_hash' => hash('sha256', self::TOKEN)]);
        Storage::fake('anamnese');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function api(): self
    {
        return $this->withHeader('Authorization', 'Bearer '.self::TOKEN);
    }

    /** Cria uma resposta de primeira consulta já enviada, com um PDF. */
    private function respostaEnviada(array $extra = []): Resposta
    {
        $convite = Convite::criar('primeira', 'Maria da Silva', 'maria-da-silva');
        $resposta = Resposta::create([
            'convite_id' => $convite->id, 'tipo' => 'primeira',
            'consentimento_versao' => '2026-11-06', 'consentimento_aceito_em' => now()->subMinutes(5),
        ]);
        $arquivo = Arquivo::guardar($resposta, 'exames', UploadedFile::fake()->createWithContent('hemograma.pdf', self::PDF), 'application/pdf');

        $f = array_replace(PrimeiraConsulta::estadoInicial(), [
            'nome' => 'Maria da Silva', 'nascimento' => '1990-05-20', 'sexo' => 'Feminino', 'altura_cm' => '164', 'peso_kg' => '82',
            'gestante' => true, 'semanas_gestacao' => '14', 'peso_pre_gestacional_kg' => '84',
            'objetivo' => 'Emagrecimento', 'metas_nao_tem' => true, 'atividade_nao_pratica' => true,
            'sono_horas' => '7', 'sono_reparador' => 'Sim', 'estresse' => 'Baixo', 'hidratacao' => '1 a 2 litros',
            'alergias' => 'Não', 'aversoes' => 'Não', 'padrao_alimentar' => 'Onívoro', 'compulsao_nao_tem' => true,
            'orcamento' => 'Médio', 'culinaria' => 'Boa', 'sem_condicoes' => true, 'historico_familiar_situacao' => 'nao_sabe',
            'medicamentos_nao_toma' => true, 'suplementos_nao_usa' => true, 'sem_bio' => true, 'sem_outros' => true,
            'observacoes_sem' => true,
        ], $extra);

        $resposta->dados = PrimeiraConsulta::montar($f, [$arquivo->paraContrato()]);
        $resposta->enviado_em = now();
        $resposta->save();
        $convite->respondido_em = now();
        $convite->save();

        return $resposta->fresh();
    }

    public function test_exige_token(): void
    {
        $this->getJson('/api/v1/saude')->assertStatus(401)->assertExactJson(['erro' => 'não autorizado']);
        $this->withHeader('Authorization', 'Bearer errado')->getJson('/api/v1/saude')->assertStatus(401);
    }

    public function test_sem_token_configurado_a_api_fica_fechada(): void
    {
        config(['anamnese.api_token_hash' => null]);
        $this->api()->getJson('/api/v1/saude')->assertStatus(401);
    }

    public function test_saude(): void
    {
        $this->api()->getJson('/api/v1/saude')->assertOk()->assertExactJson(['ok' => true, 'versao_esquema' => '1.2']);
    }

    public function test_cria_convite_pela_api_e_ele_aparece_no_painel(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-06 10:00:00', Convite::FUSO));

        $r = $this->api()->postJson('/api/v1/convites', [
            'tipo' => 'retorno', 'nome_paciente' => 'Raniele', 'paciente_ref' => 'raniele-barreto-oliveira', 'validade_dias' => 3,
        ])->assertCreated()->assertJsonStructure(['id', 'url', 'expira_em']);

        $this->assertSame('2026-11-09T23:59:59-03:00', $r->json('expira_em'));
        $convite = Convite::findOrFail($r->json('id'));
        $this->assertSame('raniele-barreto-oliveira', $convite->paciente_ref);
        $this->assertSame($convite->url(), $r->json('url'));

        // Aparece no painel
        $this->actingAs(\App\Models\User::factory()->create())->get('/dashboard/convites')->assertSee('Raniele');
    }

    public function test_cria_convite_com_validade_padrao_e_valida_campos(): void
    {
        $this->api()->postJson('/api/v1/convites', ['tipo' => 'primeira'])->assertCreated();

        $this->api()->postJson('/api/v1/convites', ['tipo' => 'outro'])->assertStatus(422)->assertJsonStructure(['erro']);
        $this->api()->postJson('/api/v1/convites', ['tipo' => 'primeira', 'validade_dias' => 31])->assertStatus(422);
        $this->assertSame(1, Convite::count());
    }

    public function test_cancela_convite_e_apaga_resposta_e_arquivos(): void
    {
        $resposta = $this->respostaEnviada();

        $this->api()->deleteJson('/api/v1/convites/'.$resposta->convite_id)->assertNoContent();

        $this->assertSame(0, Convite::count() + Resposta::count() + Arquivo::count());
        $this->assertSame([], Storage::disk('anamnese')->allFiles());
        $this->api()->deleteJson('/api/v1/convites/'.$resposta->convite_id)->assertNotFound()->assertExactJson(['erro' => 'não encontrado']);
    }

    public function test_lista_so_respostas_enviadas(): void
    {
        $enviada = $this->respostaEnviada();

        // Rascunho de outro convite: não aparece
        $outro = Convite::criar('primeira', 'Rascunho');
        Resposta::create(['convite_id' => $outro->id, 'tipo' => 'primeira', 'dados' => ['rascunho' => []]]);

        $this->api()->getJson('/api/v1/respostas?status=pendente')
            ->assertOk()
            ->assertExactJson(['dados' => [[
                'id' => $enviada->id,
                'convite_id' => $enviada->convite_id,
                'tipo' => 'primeira',
                'nome_paciente' => 'Maria da Silva',
                'paciente_ref' => 'maria-da-silva',
                'enviado_em' => Resposta::iso($enviada->enviado_em),
                'qtd_arquivos' => 1,
            ]]]);
    }

    public function test_resposta_completa_no_esquema_do_contrato(): void
    {
        $resposta = $this->respostaEnviada();

        $r = $this->api()->getJson('/api/v1/respostas/'.$resposta->id)->assertOk();
        $json = $r->json();

        $this->assertSame(
            ['id', 'versao_esquema', 'tipo', 'convite_id', 'paciente_ref', 'enviado_em', 'consentimento',
                'identificacao', 'rotina', 'atividade_fisica', 'sono_habitos', 'alimentacao', 'saude', 'observacoes', 'arquivos'],
            array_keys($json)
        );
        $this->assertSame('1.2', $json['versao_esquema']);
        $this->assertSame('maria-da-silva', $json['paciente_ref']);
        $this->assertTrue($json['consentimento']['aceito']);
        $this->assertSame('2026-11-06', $json['consentimento']['texto_versao']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d-03:00$/', $json['enviado_em']);

        // Decimais com ".0", como no exemplo do contrato
        $this->assertStringContainsString('"peso_kg":82.0', $r->getContent());
        $this->assertStringContainsString('"peso_pre_gestacional_kg":84.0', $r->getContent());
        $this->assertStringContainsString('"sono_horas":7.0', $r->getContent());

        $this->assertSame(['id', 'categoria', 'nome_original', 'mime', 'tamanho_bytes'], array_keys($json['arquivos']['lista'][0]));
        $this->assertTrue($json['saude']['sem_condicoes']);
        $this->assertTrue($json['saude']['historico_familiar_nao_sabe']);
    }

    public function test_rascunho_nao_sai_pela_api(): void
    {
        $convite = Convite::criar('primeira');
        $rascunho = Resposta::create(['convite_id' => $convite->id, 'tipo' => 'primeira', 'dados' => ['rascunho' => []]]);

        $this->api()->getJson('/api/v1/respostas/'.$rascunho->id)->assertNotFound();
    }

    public function test_baixa_arquivo_descriptografado_com_tipo_correto(): void
    {
        $resposta = $this->respostaEnviada();
        $arquivoId = $resposta->dados['arquivos']['lista'][0]['id'];

        $r = $this->api()->get("/api/v1/respostas/{$resposta->id}/arquivos/{$arquivoId}")->assertOk();

        $this->assertSame(self::PDF, $r->getContent());
        $this->assertSame('application/pdf', $r->headers->get('Content-Type'));
        $this->assertStringContainsString("filename*=UTF-8''hemograma.pdf", $r->headers->get('Content-Disposition'));

        // Arquivo de outra resposta: 404
        $outra = $this->respostaEnviada();
        $this->api()->get("/api/v1/respostas/{$outra->id}/arquivos/{$arquivoId}")->assertNotFound();
    }

    public function test_importado_apaga_tudo_e_e_idempotente(): void
    {
        $resposta = $this->respostaEnviada();
        $this->assertCount(1, Storage::disk('anamnese')->allFiles());

        $this->api()->postJson("/api/v1/respostas/{$resposta->id}/importado")->assertNoContent();

        $this->assertSame(0, Resposta::count());
        $this->assertSame(0, Arquivo::count());
        $this->assertSame(0, Convite::count());
        $this->assertSame([], Storage::disk('anamnese')->allFiles());

        // Segunda chamada: 204 também
        $this->api()->postJson("/api/v1/respostas/{$resposta->id}/importado")->assertNoContent();
    }

    public function test_id_invalido_e_rota_inexistente_respondem_404_em_json(): void
    {
        $this->api()->getJson('/api/v1/respostas/nao-e-uuid')->assertNotFound()->assertExactJson(['erro' => 'não encontrado']);
        $this->api()->getJson('/api/v1/respostas/'.\Illuminate\Support\Str::uuid())->assertNotFound();
    }

    public function test_limite_de_requisicoes(): void
    {
        config(['anamnese.api_limite_por_minuto' => 3]);

        for ($i = 0; $i < 3; $i++) {
            $this->api()->getJson('/api/v1/saude')->assertOk();
        }
        $this->api()->getJson('/api/v1/saude')->assertStatus(429)->assertExactJson(['erro' => 'muitas requisições']);
    }
}
