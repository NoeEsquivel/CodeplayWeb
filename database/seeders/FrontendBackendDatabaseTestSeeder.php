<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrontendBackendDatabaseTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Frontend, Backend y Bases de Datos'],
            [
                'contenido' => 'Documentacion conceptual sobre frontend, backend, bases de datos y el flujo de informacion entre usuario, navegador, servidor y datos guardados.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')->where('titulo', 'Frontend, Backend y Bases de Datos')->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'mundo2'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Frontend, Backend y Bases de Datos',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')->where('modulo', 'mundo2')->value('id');
        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que es el frontend?', 'opcion_a' => 'La parte visible e interactiva que el usuario ve y toca', 'opcion_b' => 'Una copia de seguridad de la base de datos', 'opcion_c' => 'Un motor fisico para robots', 'opcion_d' => 'Una contrasena temporal', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que tecnologias aparecen mucho en el frontend web?', 'opcion_a' => 'HTML, CSS y JavaScript', 'opcion_b' => 'Solo SQL', 'opcion_c' => 'Solo MySQL', 'opcion_d' => 'Solo archivos comprimidos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es el backend?', 'opcion_a' => 'La parte que trabaja en el servidor procesando reglas y respuestas', 'opcion_b' => 'El color principal de una pagina', 'opcion_c' => 'Una etiqueta para insertar imagenes', 'opcion_d' => 'El borde de una tarjeta', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual es un ejemplo de tarea del backend?', 'opcion_a' => 'Validar usuario y contrasena', 'opcion_b' => 'Cambiar el color de un boton con CSS', 'opcion_c' => 'Mostrar una imagen con alt', 'opcion_d' => 'Ajustar el margin de una tarjeta', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve una base de datos?', 'opcion_a' => 'Para guardar y consultar informacion de forma organizada', 'opcion_b' => 'Para decorar botones', 'opcion_c' => 'Para dibujar iconos', 'opcion_d' => 'Para reemplazar el monitor', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual de estos datos podria guardarse en la base de datos de CodePlay?', 'opcion_a' => 'Resultados de tests y puntos del usuario', 'opcion_b' => 'El brillo fisico del monitor', 'opcion_c' => 'El color de la pared', 'opcion_d' => 'La hora del reloj de pulsera', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que ocurre cuando un usuario hace clic en una accion importante?', 'opcion_a' => 'Puede iniciar un flujo entre frontend, backend y base de datos', 'opcion_b' => 'Siempre se apaga el servidor', 'opcion_c' => 'Se elimina HTML', 'opcion_d' => 'CSS se convierte en SQL', 'respuesta_correcta' => 'a'],
            ['pregunta' => 'En un login, ¿que parte muestra el formulario?', 'opcion_a' => 'Frontend', 'opcion_b' => 'Base de datos directamente', 'opcion_c' => 'Cable de red', 'opcion_d' => 'Disco duro externo', 'respuesta_correcta' => 'a'],
            ['pregunta' => 'En un login, ¿que parte revisa si los datos son correctos?', 'opcion_a' => 'Backend', 'opcion_b' => 'Solo el color del boton', 'opcion_c' => 'La imagen de fondo', 'opcion_d' => 'El texto alternativo', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Por que es util entender frontend, backend y base de datos?', 'opcion_a' => 'Porque ayuda a ver una aplicacion como un sistema de partes conectadas', 'opcion_b' => 'Porque evita aprender conceptos basicos', 'opcion_c' => 'Porque elimina la necesidad de guardar datos', 'opcion_d' => 'Porque hace que todos los lenguajes sean iguales', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de frontend, backend y bases de datos completado con 10 preguntas.');
    }
}
