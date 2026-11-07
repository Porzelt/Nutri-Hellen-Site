<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\Convite;
use App\Models\Resposta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnamneseLimpezaTest extends TestCase
{
    use RefreshDatabase;

    private const PDF = "%PDF-1.4\n%%EOF\n";

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('anamnese');
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function comArquivo(Convite $convite, ?Carbon $enviadoEm = null): Resposta
    {
        $resposta = Resposta::create([
            'convite_id' => $convite->id, 'tipo' => $convite->tipo,
            'consentimento_versao' => 'x', 'consentimento_aceito_em' => now(),
            'dados' => ['rascunho' => []], 'enviado_em' => $enviadoEm,
        ]);
        Arquivo::guardar($resposta, 'exames', UploadedFile::fake()->createWithContent('e.pdf', self::PDF), 'application/pdf');

        if ($enviadoEm) {
            $convite->respondido_em = $enviadoEm;
            $convite->save();
        }

        return $resposta;
    }

    public function test_apaga_convite_expirado_com_rascunho_e_arquivos(): void
    {
        $expirado = Convite::criar('primeira', validadeDias: 1);
        $this->comArquivo($expirado);
        $ativo = Convite::criar('primeira', validadeDias: 10);

        Carbon::setTestNow(now()->addDays(3));
        $this->artisan('anamnese:limpar')->assertSuccessful();

        $this->assertNull(Convite::find($expirado->id));
        $this->assertNotNull(Convite::find($ativo->id));
        $this->assertSame(0, Resposta::count() + Arquivo::count());
        $this->assertSame([], Storage::disk('anamnese')->allFiles());
    }

    public function test_apaga_resposta_enviada_e_nao_importada_apos_7_dias(): void
    {
        $velho = Convite::criar('primeira');
        $this->comArquivo($velho, now()->subDays(8));
        $recente = Convite::criar('primeira');
        $this->comArquivo($recente, now()->subDays(6));

        $this->artisan('anamnese:limpar')->assertSuccessful();

        $this->assertNull(Convite::find($velho->id));
        $this->assertNotNull(Convite::find($recente->id));
        $this->assertSame(1, Resposta::count());
        $this->assertSame(1, Arquivo::count());
        $this->assertCount(1, Storage::disk('anamnese')->allFiles());
    }

    public function test_apaga_arquivo_solto_no_disco_e_temporario_antigo(): void
    {
        $convite = Convite::criar('primeira');
        $this->comArquivo($convite); // arquivo válido, deve ficar

        Storage::disk('anamnese')->put('solto.bin', 'x');
        Storage::disk('local')->put('livewire-tmp/abc.pdf', 'x');

        Carbon::setTestNow(now()->addDays(2));
        // o convite (7 dias) ainda vale
        $this->artisan('anamnese:limpar')->assertSuccessful();

        Storage::disk('anamnese')->assertMissing('solto.bin');
        Storage::disk('local')->assertMissing('livewire-tmp/abc.pdf');
        $this->assertCount(1, Storage::disk('anamnese')->allFiles());
    }

    public function test_esta_agendada_diariamente(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('anamnese:limpar')->assertSuccessful();
    }

    public function test_politica_de_privacidade_e_links(): void
    {
        $this->get('/privacidade')->assertOk()
            ->assertSee('Política de privacidade')
            ->assertSee('apagadas automaticamente em até 7 dias', false)
            ->assertSee('Não pedimos CPF, RG nem endereço.');

        $this->get('/')->assertSee(route('privacidade'));
        $this->get(Convite::criar('primeira')->url())->assertSee(route('privacidade'));
    }
}
