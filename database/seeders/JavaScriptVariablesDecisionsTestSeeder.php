<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JavaScriptVariablesDecisionsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Variables y Decisiones'],
            [
                'contenido' => 'Documentacion sobre variables let y const, tipos de datos, cambios de valores, comparaciones e instrucciones if else en JavaScript.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Variables y Decisiones')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'js2'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Variables y Decisiones',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'js2')
            ->value('id');

        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que es una variable en JavaScript?', 'opcion_a' => 'Un espacio con nombre para guardar un dato', 'opcion_b' => 'Una imagen de fondo', 'opcion_c' => 'Una etiqueta exclusiva de HTML', 'opcion_d' => 'Un tipo de borde en CSS', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cuando conviene usar let?', 'opcion_a' => 'Cuando el valor puede cambiar', 'opcion_b' => 'Cuando queremos crear una imagen', 'opcion_c' => 'Cuando escribimos una ruta de Laravel', 'opcion_d' => 'Cuando no queremos guardar datos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cuando conviene usar const?', 'opcion_a' => 'Cuando el valor no deberia cambiar durante esa parte del programa', 'opcion_b' => 'Cuando el valor cambia en cada segundo obligatoriamente', 'opcion_c' => 'Solo para crear botones', 'opcion_d' => 'Solo para estilos CSS', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual ejemplo guarda un texto?', 'opcion_a' => "let nombre = 'Luna';", 'opcion_b' => 'let vida = 100;', 'opcion_c' => 'let activo = true;', 'opcion_d' => 'width: 100px;', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual ejemplo guarda un numero?', 'opcion_a' => "let jugador = 'Ana';", 'opcion_b' => 'let puntos = 50;', 'opcion_c' => 'let tieneLlave = false;', 'opcion_d' => 'background: blue;', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Que valores puede tener un booleano?', 'opcion_a' => 'true o false', 'opcion_b' => 'rojo o azul', 'opcion_c' => 'arriba o abajo solamente', 'opcion_d' => 'HTML o CSS', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que operador compara si dos valores son iguales?', 'opcion_a' => '===', 'opcion_b' => '+++', 'opcion_c' => '=>=>', 'opcion_d' => '!===' , 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que significa !== en una comparacion?', 'opcion_a' => 'Diferente de', 'opcion_b' => 'Mayor que', 'opcion_c' => 'Menor que', 'opcion_d' => 'Color de fondo', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve if?', 'opcion_a' => 'Para ejecutar codigo si una condicion se cumple', 'opcion_b' => 'Para cambiar la fuente de una pagina', 'opcion_c' => 'Para crear una base de datos', 'opcion_d' => 'Para insertar una imagen HTML', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve else?', 'opcion_a' => 'Para ejecutar otro codigo si la condicion del if no se cumple', 'opcion_b' => 'Para sumar padding', 'opcion_c' => 'Para activar Flexbox', 'opcion_d' => 'Para abrir GitHub', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de variables y decisiones completado con 10 preguntas.');
    }
}
