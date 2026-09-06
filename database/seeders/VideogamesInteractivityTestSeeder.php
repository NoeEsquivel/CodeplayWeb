<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VideogamesInteractivityTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Videojuegos e Interactividad'],
            [
                'contenido' => 'Documentacion conceptual sobre videojuegos, interactividad, juegos pequeños, juegos complejos, ciclo de juego, roles, motores, prototipos, pruebas y publicacion.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')->where('titulo', 'Videojuegos e Interactividad')->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'mundo5'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Videojuegos e Interactividad',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')->where('modulo', 'mundo5')->value('id');
        DB::table('preguntas_test')->where('test_id', $testId)->delete();

        $preguntas = [
            ['pregunta' => '¿Que hace que un videojuego sea interactivo?', 'opcion_a' => 'El jugador realiza acciones y el juego responde', 'opcion_b' => 'Solo tener una imagen bonita', 'opcion_c' => 'No permitir ninguna decision', 'opcion_d' => 'Ser siempre una pelicula', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Cual es un ejemplo de juego pequeño que puede crearse rapidamente?', 'opcion_a' => 'Un mini quiz o una culebrita sencilla', 'opcion_b' => 'Un mundo abierto con miles de misiones', 'opcion_c' => 'Un juego online masivo completo', 'opcion_d' => 'Una consola fisica nueva', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Por que conviene empezar con juegos pequeños?', 'opcion_a' => 'Porque permiten terminar, aprender y mejorar una idea clara', 'opcion_b' => 'Porque evitan aprender reglas', 'opcion_c' => 'Porque no usan interaccion', 'opcion_d' => 'Porque nunca tienen errores', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es una colision en un videojuego?', 'opcion_a' => 'Cuando dos objetos se tocan o se cruzan', 'opcion_b' => 'Cuando se cambia una fuente', 'opcion_c' => 'Cuando se abre GitHub', 'opcion_d' => 'Cuando se borra una imagen', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es retroalimentacion en un videojuego?', 'opcion_a' => 'La forma en que el juego muestra que una accion tuvo efecto', 'opcion_b' => 'Una tabla de base de datos', 'opcion_c' => 'Un lenguaje para servidores', 'opcion_d' => 'Un tipo de cable', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que suele hacer el ciclo del juego muchas veces por segundo?', 'opcion_a' => 'Leer entradas, actualizar reglas, detectar colisiones y dibujar la pantalla', 'opcion_b' => 'Borrar el proyecto y reiniciar Windows', 'opcion_c' => 'Cambiar contrasenas automaticamente', 'opcion_d' => 'Crear una base de datos nueva en cada cuadro', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que es un prototipo de videojuego?', 'opcion_a' => 'Una version simple para probar si la idea funciona', 'opcion_b' => 'La version final con todo perfecto', 'opcion_c' => 'Un archivo que no se puede jugar', 'opcion_d' => 'Una imagen sin reglas', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que rol diseña reglas, niveles y experiencia del jugador?', 'opcion_a' => 'Game designer', 'opcion_b' => 'Electricista de edificios', 'opcion_c' => 'Base de datos', 'opcion_d' => 'Navegador web', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Que hace un tester en un equipo de videojuegos?', 'opcion_a' => 'Busca errores y problemas de experiencia', 'opcion_b' => 'Solo dibuja personajes', 'opcion_c' => 'Solo compone musica', 'opcion_d' => 'Solo instala cables', 'respuesta_correcta' => 'a'],
            ['pregunta' => '¿Por que un videojuego grande puede necesitar un equipo?', 'opcion_a' => 'Porque conecta muchos sistemas como arte, sonido, reglas, niveles, pruebas y produccion', 'opcion_b' => 'Porque no tiene reglas', 'opcion_c' => 'Porque siempre se termina en una tarde', 'opcion_d' => 'Porque no requiere coordinacion', 'respuesta_correcta' => 'a'],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de videojuegos e interactividad completado con 10 preguntas.');
    }
}
