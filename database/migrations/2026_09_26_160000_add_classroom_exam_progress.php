<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_banco_preguntas', function (Blueprint $table) {
            $table->unsignedSmallInteger('puntos')->default(1)->after('tiempo_limite');
        });
        Schema::table('test_sesion_preguntas', function (Blueprint $table) {
            $table->unsignedSmallInteger('puntos_maximos')->default(1)->after('orden');
        });
        Schema::table('test_sesion_participantes', function (Blueprint $table) {
            $table->timestamp('examen_iniciado_at')->nullable()->after('ultima_actividad_at');
            $table->timestamp('examen_limite_at')->nullable()->after('examen_iniciado_at');
            $table->index(['sesion_id', 'examen_limite_at'], 'tsp_sesion_examen_limite_idx');
        });
    }

    public function down(): void
    {
        Schema::table('test_sesion_participantes', function (Blueprint $table) {
            $table->dropIndex('tsp_sesion_examen_limite_idx');
            $table->dropColumn(['examen_iniciado_at', 'examen_limite_at']);
        });
        Schema::table('test_sesion_preguntas', fn (Blueprint $table) => $table->dropColumn('puntos_maximos'));
        Schema::table('test_banco_preguntas', fn (Blueprint $table) => $table->dropColumn('puntos'));
    }
};
