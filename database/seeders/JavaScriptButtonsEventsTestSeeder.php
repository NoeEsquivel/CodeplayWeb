<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JavaScriptButtonsEventsTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Botones y Eventos'],
            ['contenido' => 'Documentacion sobre eventos de JavaScript, botones, seleccion de elementos, cambios de texto, estilos, clases, input y toggle.', 'updated_at' => now(), 'created_at' => now()]
        );

        $docId = DB::table('documentacion')->where('titulo', 'Botones y Eventos')->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'js3'],
            ['documentacion_id' => $docId, 'titulo' => 'Test de Botones y Eventos', 'updated_at' => now(), 'created_at' => now()]
        );

        $testId = DB::table('tests')->where('modulo', 'js3')->value('id');
        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que es un evento en JavaScript?', 'opcion_a' => 'Una accion que ocurre en la pagina', 'opcion_b' => 'Una imagen SVG', 'opcion_c' => 'Un color de CSS', 'opcion_d' => 'Una tabla de MySQL', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que evento ocurre cuando el usuario presiona un boton?', 'opcion_a' => 'input', 'opcion_b' => 'click', 'opcion_c' => 'border', 'opcion_d' => 'font', 'respuesta_correcta' => 'b'],
            ['pregunta' => '¿Para que sirve addEventListener?', 'opcion_a' => 'Para escuchar un evento y ejecutar codigo', 'opcion_b' => 'Para crear una base de datos', 'opcion_c' => 'Para cambiar el nombre del archivo', 'opcion_d' => 'Para subir commits', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que hace document.getElementById("boton")?', 'opcion_a' => 'Busca el elemento con id boton', 'opcion_b' => 'Crea una imagen llamada boton', 'opcion_c' => 'Borra todos los botones', 'opcion_d' => 'Cambia la ruta principal', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve querySelector?', 'opcion_a' => 'Para buscar un elemento usando un selector parecido a CSS', 'opcion_b' => 'Para crear una migracion', 'opcion_c' => 'Para cambiar el tamano de PHP', 'opcion_d' => 'Para instalar Bootstrap', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad permite cambiar el texto de un elemento?', 'opcion_a' => 'textContent', 'opcion_b' => 'border-radius', 'opcion_c' => 'background-size', 'opcion_d' => 'font-family', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que permite hacer classList.add?', 'opcion_a' => 'Agregar una clase CSS a un elemento', 'opcion_b' => 'Eliminar la base de datos', 'opcion_c' => 'Crear un archivo HTML nuevo', 'opcion_d' => 'Cambiar la contrasena del usuario', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que hace classList.toggle?', 'opcion_a' => 'Agrega una clase si no existe o la quita si ya existe', 'opcion_b' => 'Convierte CSS en HTML', 'opcion_c' => 'Borra todas las clases', 'opcion_d' => 'Cambia el servidor', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que evento sirve para responder mientras el usuario escribe?', 'opcion_a' => 'input', 'opcion_b' => 'submit-db', 'opcion_c' => 'margin', 'opcion_d' => 'hover-only', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que significa boton.addEventListener("click", function() { ... })?', 'opcion_a' => 'Cuando el boton reciba clic, ejecuta esa funcion', 'opcion_b' => 'Cuando cargue CSS, borra el boton', 'opcion_c' => 'Cuando exista una imagen, abre GitHub', 'opcion_d' => 'Cuando termine MySQL, crea una clase', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, ['test_id' => $testId, 'created_at' => now(), 'updated_at' => now()]));
        }

        $this->command->info('Seeder de botones y eventos completado con 10 preguntas.');
    }
}
