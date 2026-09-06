<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JavaScriptIntroTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => '¿Que es JavaScript?'],
            [
                'contenido' => 'Documentacion introductoria sobre JavaScript, eventos, interactividad, cambios en HTML, script y consola del navegador.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', '¿Que es JavaScript?')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'js1'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de ¿Que es JavaScript?',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'js1')
            ->value('id');

        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Para que sirve JavaScript en una pagina web?', 'opcion_a' => 'Para agregar interactividad y respuestas a acciones del usuario', 'opcion_b' => 'Para crear solamente titulos HTML', 'opcion_c' => 'Para reemplazar todas las imagenes', 'opcion_d' => 'Para borrar la base de datos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que papel cumple HTML en una pagina?', 'opcion_a' => 'Define la estructura y los elementos', 'opcion_b' => 'Escucha clics del usuario', 'opcion_c' => 'Cambia contrasenas automaticamente', 'opcion_d' => 'Compila el servidor', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que papel cumple CSS?', 'opcion_a' => 'Da estilo visual a los elementos', 'opcion_b' => 'Guarda respuestas de tests', 'opcion_c' => 'Crea variables de JavaScript', 'opcion_d' => 'Ejecuta consultas SQL', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es un evento en JavaScript?', 'opcion_a' => 'Una accion que ocurre en la pagina, como un clic', 'opcion_b' => 'Una carpeta de imagenes', 'opcion_c' => 'Un color hexadecimal', 'opcion_d' => 'Un borde redondeado', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que evento se usa normalmente para responder cuando el usuario presiona un boton?', 'opcion_a' => 'click', 'opcion_b' => 'border', 'opcion_c' => 'font', 'opcion_d' => 'table', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve addEventListener?', 'opcion_a' => 'Para escuchar un evento y ejecutar codigo cuando ocurra', 'opcion_b' => 'Para cambiar una imagen por CSS', 'opcion_c' => 'Para crear una tabla en HTML', 'opcion_d' => 'Para instalar Laravel', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que hace document.getElementById("mensaje")?', 'opcion_a' => 'Busca un elemento HTML con id mensaje', 'opcion_b' => 'Crea una base de datos llamada mensaje', 'opcion_c' => 'Cambia todos los fondos a amarillo', 'opcion_d' => 'Elimina un archivo CSS', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que propiedad puede cambiar el texto de un elemento?', 'opcion_a' => 'textContent', 'opcion_b' => 'border-radius', 'opcion_c' => 'font-family', 'opcion_d' => 'background-size', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Donde se puede escribir JavaScript dentro de una pagina HTML para practicar?', 'opcion_a' => 'Dentro de una etiqueta script', 'opcion_b' => 'Dentro de una etiqueta img', 'opcion_c' => 'Solo dentro de una base de datos', 'opcion_d' => 'En el atributo alt de una imagen', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve console.log()?', 'opcion_a' => 'Para mostrar mensajes o valores en la consola del navegador', 'opcion_b' => 'Para centrar botones con Flexbox', 'opcion_c' => 'Para crear bordes de color', 'opcion_d' => 'Para subir archivos a GitHub', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de introduccion a JavaScript completado con 10 preguntas.');
    }
}
