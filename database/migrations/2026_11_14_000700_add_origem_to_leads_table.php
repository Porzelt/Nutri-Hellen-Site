<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // De onde veio o pedido de agendamento (parâmetros UTM do link, ex.: bio do Instagram)
        Schema::table('leads', function (Blueprint $table) {
            $table->string('origem', 50)->nullable()->after('contatado');   // utm_source
            $table->string('meio', 50)->nullable()->after('origem');        // utm_medium
            $table->string('campanha', 80)->nullable()->after('meio');      // utm_campaign
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['origem', 'meio', 'campanha']);
        });
    }
};
