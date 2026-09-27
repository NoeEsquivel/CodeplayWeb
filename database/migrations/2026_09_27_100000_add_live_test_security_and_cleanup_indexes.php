<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_sesiones', function (Blueprint $table) {
            $table->index(['estado', 'updated_at'], 'ts_estado_actualizacion_idx');
            $table->index(['estado', 'disponible_hasta'], 'ts_estado_disponibilidad_idx');
            $table->index(['colegio_id', 'clase_id', 'tipo', 'estado'], 'ts_aislamiento_clase_idx');
        });
        Schema::table('test_sesion_participantes', function (Blueprint $table) {
            $table->index(['estado', 'ultima_actividad_at'], 'tsp_estado_actividad_idx');
        });
        Schema::table('test_sesion_respuestas', function (Blueprint $table) {
            $table->index(['sesion_pregunta_id', 'correcta', 'orden_respuesta'], 'tsr_resultado_orden_idx');
        });
    }

    public function down(): void
    {
        Schema::table('test_sesion_respuestas', fn (Blueprint $table) => $table->dropIndex('tsr_resultado_orden_idx'));
        Schema::table('test_sesion_participantes', fn (Blueprint $table) => $table->dropIndex('tsp_estado_actividad_idx'));
        Schema::table('test_sesiones', function (Blueprint $table) {
            $table->dropIndex('ts_estado_actualizacion_idx');
            $table->dropIndex('ts_estado_disponibilidad_idx');
            $table->dropIndex('ts_aislamiento_clase_idx');
        });
    }
};
