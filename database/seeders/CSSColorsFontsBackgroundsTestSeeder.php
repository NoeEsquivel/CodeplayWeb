<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CSSColorsFontsBackgroundsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Colores, Fuentes y Fondos'],
            [
                'contenido' => 'Documentacion sobre colores, fuentes, fondos, contrastes y decisiones visuales basicas en CSS.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Colores, Fuentes y Fondos')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'css2'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Colores, Fuentes y Fondos',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'css2')
            ->value('id');

        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que propiedad CSS cambia el color del texto?', 'opcion_a' => 'background-color', 'opcion_b' => 'font-family', 'opcion_c' => 'color', 'opcion_d' => 'background-image', 'respuesta_correcta' => 'c'],
            ['pregunta' => '¿Que propiedad cambia el color de fondo de un elemento?', 'opcion_a' => 'background-color', 'opcion_b' => 'font-size', 'opcion_c' => 'text-color', 'opcion_d' => 'image-color', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual de estos es un color hexadecimal valido?', 'opcion_a' => 'color(rojo)', 'opcion_b' => '#00ff9d', 'opcion_c' => 'font-blue', 'opcion_d' => 'rgb-blue', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Para que sirve font-family?', 'opcion_a' => 'Para cambiar el tipo de letra', 'opcion_b' => 'Para colocar una imagen de fondo', 'opcion_c' => 'Para crear un enlace', 'opcion_d' => 'Para enviar un formulario', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad cambia el tamano del texto?', 'opcion_a' => 'font-weight', 'opcion_b' => 'font-size', 'opcion_c' => 'font-color', 'opcion_d' => 'background-size', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Que propiedad suele usarse para hacer un texto mas grueso o en negrita?', 'opcion_a' => 'font-weight', 'opcion_b' => 'background-weight', 'opcion_c' => 'color-bold', 'opcion_d' => 'text-image', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad permite usar una imagen como fondo?', 'opcion_a' => 'image-src', 'opcion_b' => 'background-image', 'opcion_c' => 'font-image', 'opcion_d' => 'color-image', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Para que sirve background-size: cover?', 'opcion_a' => 'Para hacer que una imagen de fondo cubra el area disponible', 'opcion_b' => 'Para borrar el fondo', 'opcion_c' => 'Para cambiar el color del texto', 'opcion_d' => 'Para esconder una etiqueta HTML', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es un degradado en CSS?', 'opcion_a' => 'Una mezcla o transicion entre colores', 'opcion_b' => 'Una etiqueta nueva de HTML', 'opcion_c' => 'Una base de datos de imagenes', 'opcion_d' => 'Un error del navegador', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Por que es importante el contraste?', 'opcion_a' => 'Porque hace que el texto sea mas facil de leer sobre el fondo', 'opcion_b' => 'Porque elimina la necesidad de usar HTML', 'opcion_c' => 'Porque convierte CSS en JavaScript', 'opcion_d' => 'Porque guarda contrasenas', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de colores, fuentes y fondos completado con 10 preguntas.');
    }
}
