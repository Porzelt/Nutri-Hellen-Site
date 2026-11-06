<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respostas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('convite_id')->unique()->constrained('convites')->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->longText('dados')->nullable(); // JSON criptografado (esquema da seção 6 do contrato)
            $table->unsignedTinyInteger('etapa')->default(0); // onde o paciente parou (rascunho)
            $table->string('consentimento_versao', 20)->nullable();
            $table->timestamp('consentimento_aceito_em')->nullable();
            $table->timestamp('enviado_em')->nullable()->index(); // null = rascunho
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respostas');
    }
};
