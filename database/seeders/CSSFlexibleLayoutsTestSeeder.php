<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CSSFlexibleLayoutsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Diseños Flexibles'],
            [
                'contenido' => 'Documentacion sobre Flexbox, contenedores, hijos, direccion, alineacion, separacion y ajuste de elementos en CSS.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Diseños Flexibles')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'css4'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Diseños Flexibles',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'css4')
            ->value('id');

        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Para que sirve Flexbox en CSS?', 'opcion_a' => 'Para organizar elementos de forma flexible dentro de un contenedor', 'opcion_b' => 'Para guardar contrasenas', 'opcion_c' => 'Para crear tablas en MySQL', 'opcion_d' => 'Para cambiar archivos PHP automaticamente', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad activa Flexbox en un contenedor?', 'opcion_a' => 'flex-start', 'opcion_b' => 'display: flex', 'opcion_c' => 'position: image', 'opcion_d' => 'font-flex', 'respuesta_correcta' => 'b'],
            ['pregunta' => 'En Flexbox, ¿quien controla como se acomodan los elementos hijos?', 'opcion_a' => 'El contenedor padre', 'opcion_b' => 'La base de datos', 'opcion_c' => 'El navegador sin CSS', 'opcion_d' => 'Un archivo de imagen', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad define si los elementos van en fila o columna?', 'opcion_a' => 'gap', 'opcion_b' => 'align-items', 'opcion_c' => 'flex-direction', 'opcion_d' => 'border-radius', 'respuesta_correcta' => 'c'],
            ['pregunta' => '¿Que valor de flex-direction acomoda los elementos de izquierda a derecha?', 'opcion_a' => 'column', 'opcion_b' => 'row', 'opcion_c' => 'wrap', 'opcion_d' => 'center', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Que propiedad suele usarse para centrar elementos horizontalmente en una fila?', 'opcion_a' => 'justify-content', 'opcion_b' => 'font-size', 'opcion_c' => 'background-image', 'opcion_d' => 'border-color', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad ayuda a alinear elementos sobre la direccion cruzada?', 'opcion_a' => 'align-items', 'opcion_b' => 'gap', 'opcion_c' => 'font-family', 'opcion_d' => 'width', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve gap?', 'opcion_a' => 'Para crear espacio uniforme entre los elementos hijos', 'opcion_b' => 'Para borrar el fondo', 'opcion_c' => 'Para hacer que el texto sea negrita', 'opcion_d' => 'Para cambiar el nombre de la ruta', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad permite que los elementos bajen a otra linea si no caben?', 'opcion_a' => 'flex-wrap', 'opcion_b' => 'font-weight', 'opcion_c' => 'text-align', 'opcion_d' => 'background-size', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual combinacion centra un elemento dentro de un contenedor Flexbox?', 'opcion_a' => 'justify-content: center; align-items: center;', 'opcion_b' => 'color: center; font-size: center;', 'opcion_c' => 'border: center; margin: blue;', 'opcion_d' => 'image: center; data: center;', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de diseños flexibles completado con 10 preguntas.');
    }
}
