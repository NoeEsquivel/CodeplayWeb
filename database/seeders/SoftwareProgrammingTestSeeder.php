<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SoftwareProgrammingTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Programacion de Software'],
            [
                'contenido' => 'Documentacion conceptual sobre programacion de software, programas web, aplicaciones instaladas, apps moviles, editores de codigo, pruebas, depuracion y versiones.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')->where('titulo', 'Programacion de Software')->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'mundo3'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Programacion de Software',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')->where('modulo', 'mundo3')->value('id');
        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que significa programar software?', 'opcion_a' => 'Crear instrucciones para que una maquina realice una tarea util', 'opcion_b' => 'Solo cambiar el color de una pantalla', 'opcion_c' => 'Escribir texto sin objetivo', 'opcion_d' => 'Usar internet sin crear nada', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual es un ejemplo de programa web?', 'opcion_a' => 'Una plataforma educativa usada desde el navegador', 'opcion_b' => 'Un martillo', 'opcion_c' => 'Una silla', 'opcion_d' => 'Un cable HDMI', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que caracteriza a un programa instalado en PC?', 'opcion_a' => 'Se instala en la computadora y puede trabajar con sus recursos', 'opcion_b' => 'Solo existe como una pagina HTML sin archivos', 'opcion_c' => 'No puede tener ventanas', 'opcion_d' => 'Siempre es una base de datos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que caracteriza a una app movil?', 'opcion_a' => 'Esta pensada para celular o tablet y puede usar funciones como camara o notificaciones', 'opcion_b' => 'Solo funciona en servidores sin pantalla', 'opcion_c' => 'Es siempre una hoja de calculo', 'opcion_d' => 'No puede recibir clics ni toques', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve un editor de codigo o IDE?', 'opcion_a' => 'Para escribir, organizar y revisar codigo', 'opcion_b' => 'Para cargar gasolina', 'opcion_c' => 'Para cambiar cables de red', 'opcion_d' => 'Para borrar todos los programas', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual de estas herramientas es un editor de codigo con apoyo de IA?', 'opcion_a' => 'Cursor', 'opcion_b' => 'MySQL', 'opcion_c' => 'HTML', 'opcion_d' => 'CSS', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual herramienta es un IDE muy usado para C# y .NET?', 'opcion_a' => 'Visual Studio', 'opcion_b' => 'SQL', 'opcion_c' => 'Navegador solamente', 'opcion_d' => 'Bloc de papel', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Por que se prueba el software?', 'opcion_a' => 'Para descubrir errores y confirmar que funciona como se espera', 'opcion_b' => 'Para evitar guardar versiones', 'opcion_c' => 'Para quitar todos los botones', 'opcion_d' => 'Para impedir que el usuario entre', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que significa depurar?', 'opcion_a' => 'Investigar y corregir errores del programa', 'opcion_b' => 'Cambiar el nombre de una imagen', 'opcion_c' => 'Apagar el monitor', 'opcion_d' => 'Crear una cuenta sin contrasena', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que ayudan Git y GitHub?', 'opcion_a' => 'Para guardar versiones del proyecto y colaborar', 'opcion_b' => 'Para reemplazar todos los lenguajes', 'opcion_c' => 'Para dibujar bordes redondos', 'opcion_d' => 'Para eliminar pruebas', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de programacion de software completado con 10 preguntas.');
    }
}
