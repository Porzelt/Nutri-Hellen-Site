<?php

namespace Tests\Feature;

use App\Livewire\AgendamentoForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PacienteFlowTest extends TestCase
{
    // Usa essa trait para limpar o banco de testes a cada execução
    use RefreshDatabase;

    /**
     * Cenário 1: A Vitrine (Landing Page)
     */
    public function test_a_pagina_inicial_deve_carregar_corretamente(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Nutrição com afeto'); // Verifica texto chave
    }

    /**
     * Cenário 2: Segurança (Dashboard Protegido)
     */
    public function test_visitantes_nao_logados_nao_podem_acessar_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * Cenário 3: O Coração do Negócio (Salvar Lead)
     */
    public function test_o_formulario_deve_salvar_o_lead_no_banco_de_dados(): void
    {
        // Simula o componente Livewire recebendo dados
        Livewire::test(AgendamentoForm::class)
            ->set('nome', 'Paciente Robô PHPUnit')
            ->set('telefone', '11999997777')
            ->set('data_preferencia', 'Quarta-feira')
            ->call('salvar'); // Dispara a ação

        // Verifica no banco de dados
        $this->assertDatabaseHas('leads', [
            'nome' => 'Paciente Robô PHPUnit',
            'telefone' => '11999997777',
            'data_preferencia' => 'Quarta-feira'
        ]);
    }
}