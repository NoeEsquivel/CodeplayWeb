<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_bancos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colegio_id')->nullable()->constrained('colegios')->restrictOnDelete();
            $table->foreignId('creador_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('origen', 20);
            $table->string('categoria', 40)->default('personalizado');
            $table->string('clave_sistema', 80)->nullable()->unique();
            $table->string('nombre', 160);
            $table->text('descripcion')->nullable();
            $table->string('codigo_compartir', 16)->nullable()->unique();
            $table->boolean('compartible')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['colegio_id', 'origen', 'activo'], 'tb_colegio_origen_activo_idx');
            $table->index(['creador_usuario_id', 'activo'], 'tb_creador_activo_idx');
        });

        Schema::create('test_banco_preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banco_id')->constrained('test_bancos')->cascadeOnDelete();
            $table->text('enunciado');
            $table->string('tipo', 30);
            $table->json('opciones')->nullable();
            $table->json('respuestas_correctas');
            $table->text('explicacion')->nullable();
            $table->unsignedSmallInteger('tiempo_limite')->default(20);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->index(['banco_id', 'activa', 'orden'], 'tbp_banco_activa_orden_idx');
        });

        Schema::create('test_biblioteca_usuario', function (Blueprint $table) {
            $table->foreignId('banco_id')->constrained('test_bancos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->boolean('agregado_por_codigo')->default(false);
            $table->timestamps();
            $table->primary(['banco_id', 'usuario_id']);
            $table->index(['usuario_id', 'created_at'], 'tbu_usuario_fecha_idx');
        });

        Schema::create('test_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colegio_id')->nullable()->constrained('colegios')->restrictOnDelete();
            $table->foreignId('clase_id')->nullable()->constrained('clases')->nullOnDelete();
            $table->foreignId('anfitrion_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('codigo_sala', 12)->nullable()->unique();
            $table->string('palabra_clave_hash')->nullable();
            $table->string('tipo', 20);
            $table->string('modalidad', 20);
            $table->string('estado', 20)->default('configuracion');
            $table->unsignedSmallInteger('tiempo_pregunta')->nullable()->default(20);
            $table->unsignedInteger('puntaje_objetivo')->nullable();
            $table->unsignedTinyInteger('max_participantes')->default(30);
            $table->unsignedInteger('pregunta_actual_indice')->default(0);
            $table->unsignedInteger('version')->default(0);
            $table->json('configuracion')->nullable();
            $table->timestamp('iniciada_at')->nullable();
            $table->timestamp('finalizada_at')->nullable();
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();
            $table->index(['colegio_id', 'tipo', 'estado'], 'ts_colegio_tipo_estado_idx');
            $table->index(['clase_id', 'estado'], 'ts_clase_estado_idx');
            $table->index(['anfitrion_usuario_id', 'estado'], 'ts_anfitrion_estado_idx');
        });

        Schema::create('test_sesion_banco', function (Blueprint $table) {
            $table->foreignId('sesion_id')->constrained('test_sesiones')->cascadeOnDelete();
            $table->foreignId('banco_id')->constrained('test_bancos')->restrictOnDelete();
            $table->unsignedInteger('orden')->default(0);
            $table->primary(['sesion_id', 'banco_id']);
        });

        Schema::create('test_sesion_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('test_sesiones')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('nombre_visible', 120);
            $table->string('reconexion_token_hash', 64)->unique();
            $table->string('estado', 20)->default('conectado');
            $table->boolean('es_anfitrion')->default(false);
            $table->integer('puntos_test')->default(0);
            $table->timestamp('unido_at')->nullable();
            $table->timestamp('ultima_actividad_at')->nullable();
            $table->timestamp('finalizado_at')->nullable();
            $table->timestamps();
            $table->unique(['sesion_id', 'usuario_id']);
            $table->index(['sesion_id', 'estado', 'puntos_test'], 'tsp_sesion_estado_puntos_idx');
        });

        Schema::create('test_sesion_preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('test_sesiones')->cascadeOnDelete();
            $table->foreignId('pregunta_id')->constrained('test_banco_preguntas')->restrictOnDelete();
            $table->unsignedInteger('ronda')->default(1);
            $table->unsignedInteger('orden');
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('abierta_at')->nullable();
            $table->timestamp('cierra_at')->nullable();
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();
            $table->unique(['sesion_id', 'ronda', 'orden']);
            $table->index(['sesion_id', 'estado'], 'tsq_sesion_estado_idx');
        });

        Schema::create('test_sesion_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_pregunta_id')->constrained('test_sesion_preguntas')->cascadeOnDelete();
            $table->foreignId('participante_id')->constrained('test_sesion_participantes')->cascadeOnDelete();
            $table->json('respuesta');
            $table->boolean('correcta')->nullable();
            $table->unsignedInteger('tiempo_respuesta_ms')->nullable();
            $table->unsignedSmallInteger('orden_respuesta')->nullable();
            $table->integer('puntos_otorgados')->default(0);
            $table->timestamp('respondida_at')->nullable();
            $table->timestamps();
            $table->unique(['sesion_pregunta_id', 'participante_id'], 'tsr_pregunta_participante_uq');
            $table->index(['participante_id', 'respondida_at'], 'tsr_participante_fecha_idx');
        });

        Schema::create('test_resultados_sesion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('test_sesiones')->cascadeOnDelete();
            $table->foreignId('participante_id')->constrained('test_sesion_participantes')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('colegio_id')->nullable()->constrained('colegios')->restrictOnDelete();
            $table->foreignId('clase_id')->nullable()->constrained('clases')->nullOnDelete();
            $table->string('nombre_alumno', 120);
            $table->string('modalidad', 20);
            $table->unsignedInteger('total_preguntas')->default(0);
            $table->unsignedInteger('correctas')->default(0);
            $table->unsignedInteger('incorrectas')->default(0);
            $table->decimal('calificacion', 6, 2)->default(0);
            $table->integer('puntos_test')->default(0);
            $table->string('estado', 20)->default('en_progreso');
            $table->timestamp('iniciada_at')->nullable();
            $table->timestamp('entregada_at')->nullable();
            $table->timestamp('revisada_at')->nullable();
            $table->timestamps();
            $table->unique(['sesion_id', 'participante_id'], 'tr_sesion_participante_uq');
            $table->index(['colegio_id', 'clase_id', 'modalidad'], 'tr_colegio_clase_modalidad_idx');
            $table->index(['usuario_id', 'created_at'], 'tr_usuario_fecha_idx');
        });

        $this->importarBancoActual();
    }

    private function importarBancoActual(): void
    {
        if (!Schema::hasTable('tests') || !Schema::hasTable('preguntas_test')) return;

        $ahora = now();
        DB::table('tests')->orderBy('id')->get()->each(function ($test) use ($ahora) {
            $clave = $test->modulo ?? ('test-'.$test->id);
            $prefijo = strtolower((string) preg_replace('/[^a-z]+.*/i', '', $clave));
            $categoria = match ($prefijo) {
                'html' => 'html', 'css' => 'css', 'js' => 'javascript',
                'mundo' => 'mundos', default => 'general',
            };

            $bancoId = DB::table('test_bancos')->insertGetId([
                'colegio_id' => null, 'creador_usuario_id' => null, 'origen' => 'sistema',
                'categoria' => $categoria, 'clave_sistema' => $clave, 'nombre' => $test->titulo,
                'descripcion' => 'Banco oficial importado del sistema actual.', 'codigo_compartir' => null,
                'compartible' => false, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora,
            ]);

            foreach (DB::table('preguntas_test')->where('test_id', $test->id)->orderBy('id')->get() as $indice => $pregunta) {
                DB::table('test_banco_preguntas')->insert([
                    'banco_id' => $bancoId, 'enunciado' => $pregunta->pregunta, 'tipo' => 'opcion_unica',
                    'opciones' => json_encode(['a' => $pregunta->opcion_a, 'b' => $pregunta->opcion_b, 'c' => $pregunta->opcion_c, 'd' => $pregunta->opcion_d], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'respuestas_correctas' => json_encode([$pregunta->respuesta_correcta], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'explicacion' => null, 'tiempo_limite' => 20, 'orden' => $indice + 1, 'activa' => true,
                    'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_resultados_sesion');
        Schema::dropIfExists('test_sesion_respuestas');
        Schema::dropIfExists('test_sesion_preguntas');
        Schema::dropIfExists('test_sesion_participantes');
        Schema::dropIfExists('test_sesion_banco');
        Schema::dropIfExists('test_sesiones');
        Schema::dropIfExists('test_biblioteca_usuario');
        Schema::dropIfExists('test_banco_preguntas');
        Schema::dropIfExists('test_bancos');
    }
};

