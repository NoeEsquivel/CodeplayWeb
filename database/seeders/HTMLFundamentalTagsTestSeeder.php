<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HTMLFundamentalTagsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Etiquetas Fundamentales'],
            [
                'contenido' => 'Documentacion sobre encabezados, parrafos, contenedores, listas y etiquetas de enfasis en HTML.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Etiquetas Fundamentales')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'html2'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Etiquetas Fundamentales',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'html2')
            ->value('id');

        DB::table('preguntas_test')
            ->where('test_id', $testId)
            ->delete();

        $preguntas = [
            [
                'pregunta' => '¿Cuál etiqueta representa el título principal de una página?',
                'opcion_a' => '<h6>',
                'opcion_b' => '<h1>',
                'opcion_c' => '<p>',
                'opcion_d' => '<title>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Qué etiqueta se usa para escribir un párrafo de texto?',
                'opcion_a' => '<text>',
                'opcion_b' => '<paragraph>',
                'opcion_c' => '<p>',
                'opcion_d' => '<span>',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Cuál es la forma correcta de ordenar encabezados en una página?',
                'opcion_a' => 'Usar h1 y luego saltar directo a h5',
                'opcion_b' => 'Usar cualquier encabezado solo por su tamaño',
                'opcion_c' => 'Usar h2, h3 y siguientes según la jerarquía del contenido',
                'opcion_d' => 'Usar siempre h6 porque es el más pequeño',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Para qué sirve principalmente la etiqueta <div>?',
                'opcion_a' => 'Para insertar una imagen',
                'opcion_b' => 'Para agrupar contenido en una sección o bloque',
                'opcion_c' => 'Para crear un enlace',
                'opcion_d' => 'Para marcar una palabra dentro de una línea',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Qué diferencia principal hay entre <div> y <span>?',
                'opcion_a' => '<div> es de bloque y <span> trabaja en línea',
                'opcion_b' => '<span> solo puede contener imágenes',
                'opcion_c' => '<div> solo se usa dentro de <head>',
                'opcion_d' => 'No existe ninguna diferencia',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Qué etiqueta debes usar para una lista sin orden específico?',
                'opcion_a' => '<ol>',
                'opcion_b' => '<li>',
                'opcion_c' => '<ul>',
                'opcion_d' => '<list>',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Qué etiqueta representa cada elemento dentro de una lista?',
                'opcion_a' => '<item>',
                'opcion_b' => '<li>',
                'opcion_c' => '<ul>',
                'opcion_d' => '<ol>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Cuándo conviene usar una lista ordenada <ol>?',
                'opcion_a' => 'Cuando los elementos deben seguir pasos o un orden',
                'opcion_b' => 'Cuando se quiere resaltar una sola palabra',
                'opcion_c' => 'Cuando se quiere insertar una línea horizontal',
                'opcion_d' => 'Cuando se quiere escribir el título del navegador',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Qué etiqueta indica que un texto es importante?',
                'opcion_a' => '<em>',
                'opcion_b' => '<br>',
                'opcion_c' => '<strong>',
                'opcion_d' => '<hr>',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Qué hacen las etiquetas <br> y <hr>?',
                'opcion_a' => '<br> crea un salto de línea y <hr> dibuja una línea separadora',
                'opcion_b' => '<br> crea una lista y <hr> crea un párrafo',
                'opcion_c' => '<br> pone negrita y <hr> pone cursiva',
                'opcion_d' => 'Ambas crean encabezados',
                'respuesta_correcta' => 'a',
            ],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de etiquetas fundamentales HTML completado con 10 preguntas.');
    }
}
