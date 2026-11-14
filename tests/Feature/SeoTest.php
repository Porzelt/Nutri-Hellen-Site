<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_tem_titulo_descricao_e_previa_de_compartilhamento(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Nutricionista Hellen Fátima em São Paulo · Emagrecimento e reeducação alimentar</title>', $html);
        $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]*CRN-3 73749[^"]*Parada Inglesa/', $html);
        $this->assertStringContainsString('<meta property="og:image" content="'.url('/img/compartilhar.jpg').'">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.url('/').'">', $html);
        $this->assertStringNotContainsString('noindex', $html);
        $this->assertFileExists(public_path('img/compartilhar.jpg'));
    }

    public function test_dados_estruturados_de_nutricionista(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $html, $m);
        $dados = json_decode($m[1] ?? '', true);

        $this->assertSame('Dietitian', $dados['@type']);
        $this->assertSame('CRN-3 73749', $dados['identifier']['value']);
        $this->assertSame('São Paulo', $dados['address']['addressLocality']);
        $this->assertSame(['https://www.instagram.com/hellenfatima.nutri/'], $dados['sameAs']);
    }

    public function test_paginas_internas_nao_sao_indexadas(): void
    {
        $this->get('/login')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertSee('noindex, nofollow', false);
    }

    public function test_sitemap_lista_so_paginas_publicas(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('privacidade'), false)
            ->assertDontSee('dashboard')
            ->assertDontSee('anamnese');
    }
}
