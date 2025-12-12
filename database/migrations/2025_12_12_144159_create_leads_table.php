<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('leads', function (Blueprint $table) {
        $table->id();
        $table->string('nome');
        $table->string('telefone'); // Vamos salvar com DDD
        $table->string('data_preferencia')->nullable(); // Texto livre: "Terça a tarde", "Dia 20", etc.
        $table->text('observacoes')->nullable(); // Caso o paciente queira dizer algo
        $table->boolean('contatado')->default(false); // Para o painel admin depois
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
