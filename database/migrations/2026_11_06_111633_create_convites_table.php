<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tipo', 20); // primeira | retorno
            $table->text('nome_paciente')->nullable(); // criptografado
            $table->text('paciente_ref')->nullable(); // criptografado (pode conter o nome do paciente)
            $table->text('token'); // criptografado, para o painel poder copiar o link de novo
            $table->char('token_hash', 64)->unique(); // sha256 do token, usado para achar o convite pelo link
            $table->timestamp('expira_em')->index();
            $table->timestamp('respondido_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convites');
    }
};
