<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CSSBoxModelTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Espacios, Bordes y Cajas'],
            [
                'contenido' => 'Documentacion sobre modelo de caja, margin, padding, border, border-radius, ancho, alto y box-sizing en CSS.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Espacios, Bordes y Cajas')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'css3'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Espacios, Bordes y Cajas',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'css3')
            ->value('id');

        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => 'En CSS, ¿como podemos imaginar la mayoria de elementos HTML?', 'opcion_a' => 'Como cajas con contenido, espacio, borde y margen', 'opcion_b' => 'Como bases de datos pequenas', 'opcion_c' => 'Como archivos invisibles', 'opcion_d' => 'Como contrasenas temporales', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que parte del modelo de caja representa el contenido visible como texto o imagen?', 'opcion_a' => 'Margin', 'opcion_b' => 'Contenido', 'opcion_c' => 'Border', 'opcion_d' => 'Servidor', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Para que sirve margin?', 'opcion_a' => 'Para crear espacio externo entre elementos', 'opcion_b' => 'Para cambiar la fuente del texto', 'opcion_c' => 'Para guardar imagenes', 'opcion_d' => 'Para cambiar la ruta de una pagina', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve padding?', 'opcion_a' => 'Para crear espacio interno entre el contenido y el borde', 'opcion_b' => 'Para borrar un boton', 'opcion_c' => 'Para crear una base de datos', 'opcion_d' => 'Para repetir una imagen', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad dibuja una linea alrededor de un elemento?', 'opcion_a' => 'font-size', 'opcion_b' => 'margin', 'opcion_c' => 'border', 'opcion_d' => 'background-image', 'respuesta_correcta' => 'c'],
            ['pregunta' => '¿Que hace border-radius?', 'opcion_a' => 'Redondea las esquinas de una caja', 'opcion_b' => 'Cambia el texto a mayusculas', 'opcion_c' => 'Aumenta los puntos del usuario', 'opcion_d' => 'Crea una etiqueta HTML nueva', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad controla el ancho de un elemento?', 'opcion_a' => 'height', 'opcion_b' => 'width', 'opcion_c' => 'padding-top', 'opcion_d' => 'border-color', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Que propiedad controla la altura de un elemento?', 'opcion_a' => 'height', 'opcion_b' => 'width', 'opcion_c' => 'margin-left', 'opcion_d' => 'font-family', 'respuesta_correcta' => 'a'],
            ['pregunta' => 'En padding: 12px 24px, ¿que representa el segundo valor?', 'opcion_a' => 'Arriba y abajo', 'opcion_b' => 'Izquierda y derecha', 'opcion_c' => 'Color del texto', 'opcion_d' => 'Tipo de letra', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Por que se usa box-sizing: border-box?', 'opcion_a' => 'Para que el ancho total incluya contenido, padding y borde', 'opcion_b' => 'Para convertir CSS en HTML', 'opcion_c' => 'Para ocultar todas las imagenes', 'opcion_d' => 'Para ejecutar JavaScript automaticamente', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de espacios, bordes y cajas completado con 10 preguntas.');
    }
}
