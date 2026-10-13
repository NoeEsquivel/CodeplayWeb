<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyTestBankImporter
{
    public function importar(): array
    {
        if (! Schema::hasTable('tests') || ! Schema::hasTable('preguntas_test') || ! Schema::hasTable('test_bancos')) {
            return ['bancos' => 0, 'preguntas' => 0];
        }

        $bancos = 0;
        $preguntas = 0;
        foreach (DB::table('tests')->orderBy('id')->get() as $test) {
            $clave = $test->modulo ?? ('test-'.$test->id);
            $bancoId = DB::table('test_bancos')->where('clave_sistema', $clave)->value('id');
            if (! $bancoId) {
                $prefijo = strtolower((string) preg_replace('/[^a-z]+.*/i', '', $clave));
                $categoria = match ($prefijo) {
                    'html' => 'html', 'css' => 'css', 'js' => 'javascript',
                    'mundo' => 'mundos', default => 'general',
                };
                $bancoId = DB::table('test_bancos')->insertGetId([
                    'colegio_id' => null, 'creador_usuario_id' => null, 'origen' => 'sistema',
                    'categoria' => $categoria, 'clave_sistema' => $clave, 'nombre' => $test->titulo,
                    'descripcion' => 'Banco oficial importado del sistema actual.', 'codigo_compartir' => null,
                    'compartible' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $bancos++;
            }

            if (DB::table('test_banco_preguntas')->where('banco_id', $bancoId)->exists()) {
                continue;
            }
            foreach (DB::table('preguntas_test')->where('test_id', $test->id)->orderBy('id')->get() as $indice => $pregunta) {
                DB::table('test_banco_preguntas')->insert([
                    'banco_id' => $bancoId, 'enunciado' => $pregunta->pregunta, 'tipo' => 'opcion_unica',
                    'opciones' => json_encode(['a' => $pregunta->opcion_a, 'b' => $pregunta->opcion_b, 'c' => $pregunta->opcion_c, 'd' => $pregunta->opcion_d], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'respuestas_correctas' => json_encode([$pregunta->respuesta_correcta], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'explicacion' => null, 'tiempo_limite' => 20, 'orden' => $indice + 1, 'activa' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $preguntas++;
            }
        }

        return compact('bancos', 'preguntas');
    }
}
