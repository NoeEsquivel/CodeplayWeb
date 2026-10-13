<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CODIGO_COLEGIO = 'INED-MARIA-LUISA';

    private const NOMBRE_CLASE = 'Clase General';

    private const CODIGO_CLASE = 'GENERAL';

    public function up(): void
    {
        Schema::create('clases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colegio_id')
                ->constrained('colegios')
                ->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->string('codigo', 50);
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['colegio_id', 'nombre']);
            $table->unique(['colegio_id', 'codigo']);
        });

        Schema::create('clase_profesor', function (Blueprint $table) {
            $table->foreignId('clase_id')
                ->constrained('clases')
                ->cascadeOnDelete();
            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['clase_id', 'usuario_id']);
        });

        Schema::create('clase_alumno', function (Blueprint $table) {
            $table->foreignId('clase_id')
                ->constrained('clases')
                ->cascadeOnDelete();
            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['clase_id', 'usuario_id']);
        });

        $colegioId = DB::table('colegios')
            ->where('codigo', self::CODIGO_COLEGIO)
            ->value('id');

        if (! $colegioId) {
            return;
        }

        $claseId = DB::table('clases')->insertGetId([
            'colegio_id' => $colegioId,
            'nombre' => self::NOMBRE_CLASE,
            'codigo' => self::CODIGO_CLASE,
            'descripcion' => 'Clase predeterminada para los usuarios existentes de INED MARIA LUISA.',
            'activa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ahora = now();

        $profesores = DB::table('usuarios')
            ->where('colegio_id', $colegioId)
            ->where('rol', 'Profesor')
            ->pluck('id')
            ->map(fn ($usuarioId) => [
                'clase_id' => $claseId,
                'usuario_id' => $usuarioId,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ])
            ->all();

        if ($profesores !== []) {
            DB::table('clase_profesor')->insertOrIgnore($profesores);
        }

        $alumnos = DB::table('usuarios')
            ->where('colegio_id', $colegioId)
            ->where('rol', 'Alumno')
            ->pluck('id')
            ->map(fn ($usuarioId) => [
                'clase_id' => $claseId,
                'usuario_id' => $usuarioId,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ])
            ->all();

        if ($alumnos !== []) {
            DB::table('clase_alumno')->insertOrIgnore($alumnos);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clase_alumno');
        Schema::dropIfExists('clase_profesor');
        Schema::dropIfExists('clases');
    }
};
