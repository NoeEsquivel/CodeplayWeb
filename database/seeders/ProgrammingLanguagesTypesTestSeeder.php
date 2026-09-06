<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgrammingLanguagesTypesTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Tipos de Lenguajes'],
            [
                'contenido' => 'Documentacion conceptual sobre diferencias entre HTML, CSS, JavaScript, Python, Java, PHP, SQL y otros lenguajes segun su ambito de uso.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')->where('titulo', 'Tipos de Lenguajes')->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'mundo1'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Tipos de Lenguajes',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')->where('modulo', 'mundo1')->value('id');
        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Por que existen distintos lenguajes de programacion?', 'opcion_a' => 'Porque cada uno puede resolver mejor ciertos tipos de problemas', 'opcion_b' => 'Porque todos hacen exactamente lo mismo', 'opcion_c' => 'Porque HTML reemplaza a todos', 'opcion_d' => 'Porque CSS guarda bases de datos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve principalmente HTML?', 'opcion_a' => 'Para estructurar el contenido de una pagina web', 'opcion_b' => 'Para consultar bases de datos', 'opcion_c' => 'Para mover motores', 'opcion_d' => 'Para entrenar inteligencia artificial', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve principalmente CSS?', 'opcion_a' => 'Para controlar la apariencia visual de una pagina', 'opcion_b' => 'Para crear usuarios en MySQL', 'opcion_c' => 'Para encender sensores', 'opcion_d' => 'Para compilar aplicaciones Android', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve JavaScript en una pagina web?', 'opcion_a' => 'Para agregar interactividad y comportamiento', 'opcion_b' => 'Para escribir solo textos largos', 'opcion_c' => 'Para reemplazar la base de datos', 'opcion_d' => 'Para crear carpetas del sistema', 'respuesta_correcta' => 'a'],
            ['pregunta' => 'En la comparacion simple, ¿que hace HTML y que hace JavaScript?', 'opcion_a' => 'HTML coloca elementos y JavaScript decide como reaccionan', 'opcion_b' => 'HTML entrena IA y JavaScript pinta paredes', 'opcion_c' => 'HTML consulta SQL y JavaScript guarda archivos', 'opcion_d' => 'HTML mueve robots y JavaScript crea tornillos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que lenguaje se usa mucho para backend web y puede trabajar en el servidor?', 'opcion_a' => 'PHP', 'opcion_b' => 'CSS', 'opcion_c' => 'HTML', 'opcion_d' => 'SVG', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve SQL?', 'opcion_a' => 'Para consultar, guardar y ordenar informacion en bases de datos', 'opcion_b' => 'Para cambiar colores de botones', 'opcion_c' => 'Para crear solo animaciones', 'opcion_d' => 'Para dibujar iconos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual trio es muy comun para empezar creando paginas web?', 'opcion_a' => 'HTML, CSS y JavaScript', 'opcion_b' => 'SQL, R y MATLAB', 'opcion_c' => 'C++, Kotlin y Swift solamente', 'opcion_d' => 'PHP, SQL y CSS sin HTML', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿En que destaca Python?', 'opcion_a' => 'Automatizacion, datos, inteligencia artificial y aprendizaje sencillo', 'opcion_b' => 'Unicamente crear etiquetas HTML', 'opcion_c' => 'Solo cambiar colores CSS', 'opcion_d' => 'Solo dibujar imagenes SVG', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que lenguaje se usa mucho en aplicaciones grandes y tambien en Android?', 'opcion_a' => 'Java', 'opcion_b' => 'HTML', 'opcion_c' => 'CSS', 'opcion_d' => 'SQL', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que lenguaje es muy comun para crear videojuegos con Unity?', 'opcion_a' => 'C#', 'opcion_b' => 'HTML', 'opcion_c' => 'SQL', 'opcion_d' => 'PHP', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que lenguaje destaca por velocidad y control en videojuegos o sistemas de alto rendimiento?', 'opcion_a' => 'C++', 'opcion_b' => 'CSS', 'opcion_c' => 'HTML', 'opcion_d' => 'Markdown', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que lenguaje se usa para crear apps del ecosistema Apple como iPhone o iPad?', 'opcion_a' => 'Swift', 'opcion_b' => 'SQL', 'opcion_c' => 'CSS', 'opcion_d' => 'HTML solamente', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que lenguaje es moderno y comun para crear aplicaciones Android?', 'opcion_a' => 'Kotlin', 'opcion_b' => 'R', 'opcion_c' => 'SVG', 'opcion_d' => 'CSS', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿En que area aparece mucho R?', 'opcion_a' => 'Analisis estadistico y datos', 'opcion_b' => 'Solo estilos de botones', 'opcion_c' => 'Solo formularios HTML', 'opcion_d' => 'Solo rutas web', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿En que area aparece MATLAB con frecuencia?', 'opcion_a' => 'Ingenieria, matematicas, simulaciones y señales', 'opcion_b' => 'Crear menus HTML basicos', 'opcion_c' => 'Diseñar fuentes CSS', 'opcion_d' => 'Escribir enlaces web', 'respuesta_correcta' => 'a'],
            ['pregunta' => 'En robotica educativa, ¿que lenguajes pueden aparecer con frecuencia?', 'opcion_a' => 'C, C++ y Python', 'opcion_b' => 'HTML y CSS solamente', 'opcion_c' => 'SQL y SVG solamente', 'opcion_d' => 'R y CSS solamente', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que significa frontend?', 'opcion_a' => 'La parte que el usuario ve y toca', 'opcion_b' => 'La contrasena maestra', 'opcion_c' => 'El motor fisico de un robot', 'opcion_d' => 'Una tabla oculta siempre', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que significa backend?', 'opcion_a' => 'La parte que trabaja en el servidor detras de la pagina', 'opcion_b' => 'El color del texto', 'opcion_c' => 'El borde de una tarjeta', 'opcion_d' => 'La etiqueta para imagenes', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual es una buena pregunta para elegir lenguaje?', 'opcion_a' => '¿Que quiero construir?', 'opcion_b' => '¿Cual tiene el nombre mas corto?', 'opcion_c' => '¿Cual evita aprender logica?', 'opcion_d' => '¿Cual elimina todo HTML?', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de tipos de lenguajes completado con 20 preguntas.');
    }
}
