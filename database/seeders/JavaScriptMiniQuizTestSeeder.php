<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JavaScriptMiniQuizTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Mini Quiz con JavaScript'],
            ['contenido' => 'Documentacion sobre la logica de un mini quiz con JavaScript: preguntas, opciones, arreglos, indice, comparaciones, puntaje y resultado.', 'updated_at' => now(), 'created_at' => now()]
        );

        $docId = DB::table('documentacion')->where('titulo', 'Mini Quiz con JavaScript')->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'js4'],
            ['documentacion_id' => $docId, 'titulo' => 'Test de Mini Quiz con JavaScript', 'updated_at' => now(), 'created_at' => now()]
        );

        $testId = DB::table('tests')->where('modulo', 'js4')->value('id');
        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que ideas combina un mini quiz con JavaScript?', 'opcion_a' => 'Preguntas, opciones, respuestas, puntaje y resultado', 'opcion_b' => 'Solo colores de fondo', 'opcion_c' => 'Solo etiquetas de imagen', 'opcion_d' => 'Solo rutas de Laravel', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve un objeto de pregunta?', 'opcion_a' => 'Para guardar texto, opciones y respuesta correcta juntos', 'opcion_b' => 'Para crear una fuente de CSS', 'opcion_c' => 'Para borrar un boton', 'opcion_d' => 'Para cambiar el nombre del servidor', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve un arreglo de preguntas?', 'opcion_a' => 'Para guardar varias preguntas en una sola variable', 'opcion_b' => 'Para guardar solo una imagen', 'opcion_c' => 'Para reemplazar HTML', 'opcion_d' => 'Para cambiar contrasenas', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que representa el indice actual en un quiz?', 'opcion_a' => 'La pregunta que se esta mostrando', 'opcion_b' => 'El color del fondo', 'opcion_c' => 'La ruta de una imagen', 'opcion_d' => 'El tamano del boton', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que hace preguntas[actual]?', 'opcion_a' => 'Obtiene la pregunta ubicada en la posicion actual', 'opcion_b' => 'Borra todas las preguntas', 'opcion_c' => 'Crea un nuevo usuario', 'opcion_d' => 'Cambia una clase CSS', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad se puede usar para mostrar el texto de una pregunta en HTML?', 'opcion_a' => 'textContent', 'opcion_b' => 'border-color', 'opcion_c' => 'font-size', 'opcion_d' => 'background-image', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Como se comprueba si una respuesta es correcta?', 'opcion_a' => 'Comparando la opcion elegida con la respuesta correcta', 'opcion_b' => 'Cambiando el fondo a azul', 'opcion_c' => 'Recargando siempre la pagina', 'opcion_d' => 'Quitando todos los botones', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que variable suele guardar los puntos ganados?', 'opcion_a' => 'puntaje', 'opcion_b' => 'background', 'opcion_c' => 'border', 'opcion_d' => 'archivo', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que se puede hacer despues de responder una pregunta?', 'opcion_a' => 'Aumentar el indice para pasar a la siguiente', 'opcion_b' => 'Eliminar JavaScript', 'opcion_c' => 'Cambiar Laravel por HTML', 'opcion_d' => 'Borrar el navegador', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que deberia pasar cuando ya no hay mas preguntas?', 'opcion_a' => 'Mostrar el resultado final', 'opcion_b' => 'Cerrar la computadora', 'opcion_c' => 'Crear una etiqueta img', 'opcion_d' => 'Borrar el puntaje siempre', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, ['test_id' => $testId, 'created_at' => now(), 'updated_at' => now()]));
        }

        $this->command->info('Seeder de mini quiz con JavaScript completado con 10 preguntas.');
    }
}
