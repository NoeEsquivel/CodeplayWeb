<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_sesion_respuestas', function (Blueprint $table) {
            $table->unique(
                ['sesion_pregunta_id', 'orden_respuesta'],
                'tsr_pregunta_orden_uq'
            );
        });

        Schema::table('test_sesion_preguntas', function (Blueprint $table) {
            $table->index(
                ['sesion_id', 'estado', 'cierra_at'],
                'tsq_sesion_estado_cierre_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('test_sesion_respuestas', function (Blueprint $table) {
            $table->dropUnique('tsr_pregunta_orden_uq');
        });

        Schema::table('test_sesion_preguntas', function (Blueprint $table) {
            $table->dropIndex('tsq_sesion_estado_cierre_idx');
        });
    }
};
