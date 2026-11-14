<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arquivos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resposta_id')->constrained('respostas')->cascadeOnDelete();
            $table->string('categoria', 10); // exames | bio | outros
            $table->text('nome_original'); // criptografado (pode conter o nome do paciente)
            $table->string('mime', 50);
            $table->unsignedBigInteger('tamanho_bytes');
            $table->string('caminho', 100); // nome aleatório no disco privado "anamnese"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arquivos');
    }
};
