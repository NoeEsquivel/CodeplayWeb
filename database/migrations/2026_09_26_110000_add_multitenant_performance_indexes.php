<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->index(['colegio_id', 'rol', 'activo'], 'usuarios_colegio_rol_activo_idx');
        });

        Schema::table('clases', function (Blueprint $table) {
            $table->index(['colegio_id', 'activa'], 'clases_colegio_activa_idx');
        });

        Schema::table('clase_profesor', function (Blueprint $table) {
            $table->index(['usuario_id', 'clase_id'], 'clase_profesor_usuario_clase_idx');
        });

        Schema::table('clase_alumno', function (Blueprint $table) {
            $table->index(['usuario_id', 'clase_id'], 'clase_alumno_usuario_clase_idx');
        });

        Schema::table('progreso_tutorial', function (Blueprint $table) {
            $table->index(['usuario_id', 'estado'], 'progreso_tutorial_usuario_estado_idx');
        });

        Schema::table('progreso_niveles', function (Blueprint $table) {
            $table->index(['usuario_id', 'estado'], 'progreso_niveles_usuario_estado_idx');
        });

        Schema::table('desafio_recompensas', function (Blueprint $table) {
            $table->index(['usuario_id', 'completado_primera_vez'], 'desafio_usuario_completado_idx');
        });

        Schema::table('resultados_test', function (Blueprint $table) {
            $table->index(['usuario_id', 'created_at'], 'resultados_usuario_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::table('resultados_test', fn (Blueprint $table) => $table->dropIndex('resultados_usuario_fecha_idx'));
        Schema::table('desafio_recompensas', fn (Blueprint $table) => $table->dropIndex('desafio_usuario_completado_idx'));
        Schema::table('progreso_niveles', fn (Blueprint $table) => $table->dropIndex('progreso_niveles_usuario_estado_idx'));
        Schema::table('progreso_tutorial', fn (Blueprint $table) => $table->dropIndex('progreso_tutorial_usuario_estado_idx'));
        Schema::table('clase_alumno', fn (Blueprint $table) => $table->dropIndex('clase_alumno_usuario_clase_idx'));
        Schema::table('clase_profesor', fn (Blueprint $table) => $table->dropIndex('clase_profesor_usuario_clase_idx'));
        Schema::table('clases', fn (Blueprint $table) => $table->dropIndex('clases_colegio_activa_idx'));
        Schema::table('usuarios', fn (Blueprint $table) => $table->dropIndex('usuarios_colegio_rol_activo_idx'));
    }
};
