<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('test_sesiones','archivada_at')) Schema::table('test_sesiones', function (Blueprint $table) {
            $table->timestamp('archivada_at')->nullable()->after('cerrada_at');
            $table->foreignId('archivada_por_usuario_id')->nullable()->after('archivada_at')->constrained('usuarios')->nullOnDelete();
            $table->timestamp('eliminada_at')->nullable()->after('archivada_por_usuario_id');
            $table->index(['colegio_id','clase_id','archivada_at'], 'ts_colegio_clase_archivo_idx');
        });
        if (!Schema::hasColumn('test_resultados_sesion','intento')) Schema::table('test_resultados_sesion', function (Blueprint $table) {
            $table->unsignedInteger('intento')->default(1)->after('participante_id');
            $table->unsignedInteger('duracion_segundos')->nullable()->after('puntos_test');
        });
        $indexes=collect($this->indexNames('test_resultados_sesion'));
        if(!$indexes->contains('tr_sesion_fk_idx')) Schema::table('test_resultados_sesion',fn(Blueprint $table)=>$table->index('sesion_id','tr_sesion_fk_idx'));
        if($indexes->contains('tr_sesion_participante_uq')) Schema::table('test_resultados_sesion',fn(Blueprint $table)=>$table->dropUnique('tr_sesion_participante_uq'));
        $indexes=collect($this->indexNames('test_resultados_sesion'));
        if(!$indexes->contains('tr_sesion_participante_intento_uq')) Schema::table('test_resultados_sesion',fn(Blueprint $table)=>$table->unique(['sesion_id','participante_id','intento'],'tr_sesion_participante_intento_uq'));
        if(!$indexes->contains('tr_colegio_clase_entrega_idx')) Schema::table('test_resultados_sesion',fn(Blueprint $table)=>$table->index(['colegio_id','clase_id','entregada_at'],'tr_colegio_clase_entrega_idx'));
        if (!Schema::hasTable('test_historial_respuestas')) Schema::create('test_historial_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('test_sesiones')->cascadeOnDelete();
            $table->foreignId('participante_id')->nullable()->constrained('test_sesion_participantes')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('colegio_id')->nullable()->constrained('colegios')->restrictOnDelete();
            $table->foreignId('clase_id')->nullable()->constrained('clases')->nullOnDelete();
            $table->foreignId('profesor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('banco_id')->nullable()->constrained('test_bancos')->nullOnDelete();
            $table->unsignedBigInteger('sesion_pregunta_original_id')->nullable();
            $table->unsignedInteger('intento')->default(1);
            $table->unsignedInteger('ronda')->default(1);
            $table->unsignedInteger('orden')->default(1);
            $table->text('pregunta');
            $table->string('tipo_pregunta',30);
            $table->json('opciones')->nullable();
            $table->json('respuestas_correctas');
            $table->json('respuesta_alumno')->nullable();
            $table->boolean('correcta')->default(false);
            $table->integer('puntos_otorgados')->default(0);
            $table->unsignedInteger('puntos_maximos')->default(1);
            $table->unsignedInteger('tiempo_respuesta_ms')->nullable();
            $table->timestamp('respondida_at')->nullable();
            $table->timestamps();
            $table->unique(['sesion_id','participante_id','sesion_pregunta_original_id','intento'], 'thr_sesion_part_preg_intento_uq');
            $table->index(['colegio_id','clase_id','profesor_id'], 'thr_scope_idx');
            $table->index(['usuario_id','sesion_id','intento'], 'thr_alumno_sesion_idx');
            $table->index(['banco_id','created_at'], 'thr_banco_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_historial_respuestas');
        Schema::table('test_resultados_sesion', function (Blueprint $table) {
            $table->dropIndex('tr_colegio_clase_entrega_idx');$table->dropUnique('tr_sesion_participante_intento_uq');
            $table->dropColumn(['intento','duracion_segundos']);$table->unique(['sesion_id','participante_id'],'tr_sesion_participante_uq');
        });
        Schema::table('test_sesiones', function (Blueprint $table) {
            $table->dropIndex('ts_colegio_clase_archivo_idx');$table->dropConstrainedForeignId('archivada_por_usuario_id');
            $table->dropColumn(['archivada_at','eliminada_at']);
        });
    }

    private function indexNames(string $table): array
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))->pluck('name')->filter()->unique()->values()->all();
        }

        return collect(DB::select("SHOW INDEX FROM {$table}"))->pluck('Key_name')->filter()->unique()->values()->all();
    }
};
