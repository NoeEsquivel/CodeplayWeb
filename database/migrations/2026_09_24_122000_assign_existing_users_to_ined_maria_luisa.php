<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NOMBRE_COLEGIO = 'INED MARIA LUISA';

    private const CODIGO_COLEGIO = 'INED-MARIA-LUISA';

    public function up(): void
    {
        $colegio = DB::table('colegios')
            ->where('codigo', self::CODIGO_COLEGIO)
            ->orWhere('nombre', self::NOMBRE_COLEGIO)
            ->first();

        if ($colegio) {
            $colegioId = (int) $colegio->id;

            DB::table('colegios')
                ->where('id', $colegioId)
                ->update([
                    'nombre' => self::NOMBRE_COLEGIO,
                    'codigo' => self::CODIGO_COLEGIO,
                    'activo' => true,
                    'updated_at' => now(),
                ]);
        } else {
            $colegioId = (int) DB::table('colegios')->insertGetId([
                'nombre' => self::NOMBRE_COLEGIO,
                'codigo' => self::CODIGO_COLEGIO,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('usuarios')
            ->whereIn('rol', ['Alumno', 'Profesor', 'AdministradorColegio'])
            ->whereNull('colegio_id')
            ->update(['colegio_id' => $colegioId]);

        DB::table('usuarios')
            ->where('rol', 'Master')
            ->update(['colegio_id' => null]);
    }

    public function down(): void
    {
        $colegioId = DB::table('colegios')
            ->where('codigo', self::CODIGO_COLEGIO)
            ->value('id');

        if (! $colegioId) {
            return;
        }

        DB::table('usuarios')
            ->where('colegio_id', $colegioId)
            ->update(['colegio_id' => null]);

        DB::table('colegios')
            ->where('id', $colegioId)
            ->delete();
    }
};
