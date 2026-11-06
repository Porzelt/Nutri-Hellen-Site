<?php

namespace Tests\Feature;

use App\Anamnese\PrimeiraConsulta;
use App\Livewire\Anamnese\Formulario;
use App\Models\Arquivo;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class AnamneseArquivosTest extends TestCase
{
    use RefreshDatabase;

    private const PDF = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n";

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('anamnese');
    }

    /** Abre o link, aceita e vai direto para a etapa de arquivos (via rascunho). */
    private function naEtapaDeArquivos(): array
    {
        $convite = Convite::criar('primeira');
        Resposta::create([
            'convite_id' => $convite->id, 'tipo' => 'primeira', 'etapa' => PrimeiraConsulta::ETAPA_ARQUIVOS,
            'consentimento_versao' => 'x', 'consentimento_aceito_em' => now(),
            'dados' => ['rascunho' => PrimeiraConsulta::estadoInicial()],
        ]);

        $t = Livewire::test(Formulario::class, ['token' => $convite->token])
            ->assertSet('etapa', PrimeiraConsulta::ETAPA_ARQUIVOS);

        return [$convite, $t];
    }

    private function pdf(string $nome = 'hemograma.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nome, self::PDF);
    }

    public function test_anexa_pdf_e_foto_criptografados_com_nome_aleatorio(): void
    {
        [, $t] = $this->naEtapaDeArquivos();

        $t->set('uploads.exames', [$this->pdf('Maria hemograma.pdf')])
            ->set('uploads.bio', [UploadedFile::fake()->image('bio.png', 50, 50)])
            ->assertHasNoErrors()
            ->assertSee('Maria hemograma.pdf');

        $exame = Arquivo::where('categoria', 'exames')->sole();
        $this->assertSame('application/pdf', $exame->mime);
        $this->assertSame(strlen(self::PDF), $exame->tamanho_bytes);
        $this->assertSame('image/png', Arquivo::where('categoria', 'bio')->sole()->mime);

        // No disco: nome aleatório e conteúdo criptografado
        $this->assertStringNotContainsString('hemograma', $exame->caminho);
        $bruto = Storage::disk('anamnese')->get($exame->caminho);
        $this->assertStringNotContainsString('%PDF', $bruto);
        $this->assertSame(self::PDF, $exame->conteudo());

        // Nome original criptografado no banco
        $this->assertStringNotContainsString('hemograma', \DB::table('arquivos')->value('nome_original'));
    }

    public function test_recusa_tipo_falso_pela_extensao(): void
    {
        [, $t] = $this->naEtapaDeArquivos();

        // Texto com extensão .pdf: o tipo real não é PDF
        $t->set('uploads.exames', [UploadedFile::fake()->createWithContent('exame.pdf', 'só um texto qualquer')])
            ->assertHasErrors('arquivos_exames');

        $this->assertSame(0, Arquivo::count());
        $this->assertSame([], Storage::disk('anamnese')->allFiles());
    }

    public function test_recusa_arquivo_maior_que_15mb(): void
    {
        [, $t] = $this->naEtapaDeArquivos();

        $t->set('uploads.exames', [UploadedFile::fake()->create('grande.pdf', 16 * 1024, 'application/pdf')])
            ->assertHasErrors();

        $this->assertSame(0, Arquivo::count());
    }

    public function test_limite_de_15_arquivos(): void
    {
        [, $t] = $this->naEtapaDeArquivos();

        $t->set('uploads.exames', array_map(fn ($i) => $this->pdf("e$i.pdf"), range(1, 16)))
            ->assertHasErrors('arquivos_exames');

        $this->assertSame(15, Arquivo::count());
    }

    public function test_cada_categoria_exige_arquivo_ou_nao_tenho(): void
    {
        [, $t] = $this->naEtapaDeArquivos();

        $t->call('avancar')
            ->assertHasErrors(['arquivos_exames', 'arquivos_bio', 'arquivos_outros'])
            ->assertSet('etapa', PrimeiraConsulta::ETAPA_ARQUIVOS);

        $t->set('uploads.exames', [$this->pdf()])
            ->set('f.sem_bio', true)->set('f.sem_outros', true)
            ->call('avancar')
            ->assertHasNoErrors()
            ->assertSet('etapa', PrimeiraConsulta::ETAPA_ARQUIVOS + 1);
    }

    public function test_remover_arquivo_apaga_do_disco(): void
    {
        [, $t] = $this->naEtapaDeArquivos();
        $t->set('uploads.exames', [$this->pdf()]);
        $arquivo = Arquivo::sole();

        $t->call('removerArquivo', $arquivo->id);

        $this->assertSame(0, Arquivo::count());
        Storage::disk('anamnese')->assertMissing($arquivo->caminho);
    }

    public function test_cancelar_convite_apaga_resposta_e_arquivos_do_disco(): void
    {
        [$convite, $t] = $this->naEtapaDeArquivos();
        $t->set('uploads.exames', [$this->pdf()]);
        $this->assertCount(1, Storage::disk('anamnese')->allFiles());

        $convite->delete();

        $this->assertSame(0, Resposta::count());
        $this->assertSame(0, Arquivo::count());
        $this->assertSame([], Storage::disk('anamnese')->allFiles());
    }

    public function test_envio_inclui_lista_de_arquivos_e_indicadores_no_json(): void
    {
        [$convite, $t] = $this->naEtapaDeArquivos();
        $t->set('uploads.exames', [$this->pdf()])
            ->set('f.sem_bio', true)->set('f.sem_outros', true)
            ->call('avancar');

        // Completa as demais etapas direto no rascunho e envia
        $f = array_replace(PrimeiraConsulta::estadoInicial(), [
            'nome' => 'Ana', 'nascimento' => '1990-01-01', 'sexo' => 'Feminino', 'altura_cm' => '160', 'peso_kg' => '60',
            'objetivo' => 'Emagrecimento', 'metas_nao_tem' => true, 'atividade_nao_pratica' => true,
            'sono_reparador' => 'Sim', 'estresse' => 'Baixo', 'alergias' => 'Não', 'aversoes' => 'Não', 'padrao_alimentar' => 'Onívoro',
            'compulsao_nao_tem' => true, 'orcamento' => 'Médio', 'culinaria' => 'Boa',
            'medicamentos_nao_toma' => true, 'suplementos_nao_usa' => true,
            'sem_bio' => true, 'sem_outros' => true, 'observacoes_sem' => true,
        ]);
        $t->set('f', $f)->call('enviar')->assertHasNoErrors()->assertSet('enviado', true);

        $arquivos = Resposta::sole()->dados['arquivos'];
        $this->assertFalse($arquivos['sem_exames']);
        $this->assertTrue($arquivos['sem_bio']);
        $this->assertTrue($arquivos['sem_outros']);
        $this->assertCount(1, $arquivos['lista']);
        $this->assertSame(['id', 'categoria', 'nome_original', 'mime', 'tamanho_bytes'], array_keys($arquivos['lista'][0]));
        $this->assertSame('exames', $arquivos['lista'][0]['categoria']);
        $this->assertSame('hemograma.pdf', $arquivos['lista'][0]['nome_original']);
    }
}
