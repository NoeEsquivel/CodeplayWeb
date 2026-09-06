<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoboticsComponentsTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Programacion de Robots y Componentes'],
            [
                'contenido' => 'Documentacion conceptual sobre programacion de robots, placas reales, sensores, actuadores, entradas, salidas, variantes y proyectos fisicos basicos.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')->where('titulo', 'Programacion de Robots y Componentes')->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'mundo4'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Programacion de Robots y Componentes',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')->where('modulo', 'mundo4')->value('id');
        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que significa programar robots y componentes?', 'opcion_a' => 'Crear instrucciones que controlan piezas fisicas como sensores, luces o motores', 'opcion_b' => 'Cambiar solamente el color de una pagina', 'opcion_c' => 'Escribir textos sin ejecutar nada', 'opcion_d' => 'Guardar imagenes en una carpeta', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual es el ciclo basico de muchos proyectos de robotica?', 'opcion_a' => 'Entrada, proceso y salida', 'opcion_b' => 'Color, fuente y margen', 'opcion_c' => 'Titulo, parrafo e imagen', 'opcion_d' => 'Commit, push y pull solamente', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es una placa en robotica educativa?', 'opcion_a' => 'Una pequeña computadora que ejecuta instrucciones y se conecta con componentes', 'opcion_b' => 'Una tabla de CSS', 'opcion_c' => 'Un tipo de enlace HTML', 'opcion_d' => 'Un archivo de imagen', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que diferencia general hay entre Raspberry Pi Pico y Raspberry Pi 5?', 'opcion_a' => 'Pico es microcontrolador; Raspberry Pi 5 es una computadora pequeña mas completa', 'opcion_b' => 'Pico solo sirve para escribir documentos y Raspberry Pi 5 solo para HTML', 'opcion_c' => 'No hay ninguna diferencia', 'opcion_d' => 'Raspberry Pi 5 no puede conectarse a nada', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que variante de Arduino UNO R4 es útil para proyectos conectados con WiFi/Bluetooth?', 'opcion_a' => 'UNO R4 WiFi', 'opcion_b' => 'UNO R4 Sin Pantalla HTML', 'opcion_c' => 'UNO CSS', 'opcion_d' => 'UNO SQL', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que hace un sensor?', 'opcion_a' => 'Detecta informacion del ambiente y la convierte en datos', 'opcion_b' => 'Decora botones con colores', 'opcion_c' => 'Guarda usuarios en una base de datos', 'opcion_d' => 'Crea etiquetas h1', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual de estos es un ejemplo de sensor?', 'opcion_a' => 'Sensor ultrasonico de distancia', 'opcion_b' => 'Etiqueta div', 'opcion_c' => 'Fuente Arial', 'opcion_d' => 'Commit de Git', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es un actuador?', 'opcion_a' => 'Un componente que realiza una accion fisica o visible', 'opcion_b' => 'Una variable que guarda texto', 'opcion_c' => 'Un estilo de borde', 'opcion_d' => 'Una ruta web', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual de estos es un actuador?', 'opcion_a' => 'Servomotor', 'opcion_b' => 'Sensor de luz', 'opcion_c' => 'Campo de texto HTML', 'opcion_d' => 'Base de datos', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Para que sirve una protoboard?', 'opcion_a' => 'Para armar circuitos de prueba sin soldar', 'opcion_b' => 'Para guardar contrasenas', 'opcion_c' => 'Para crear commits automaticos', 'opcion_d' => 'Para cambiar la fuente de una pagina', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de programacion de robots y componentes actualizado con 10 preguntas.');
    }
}
