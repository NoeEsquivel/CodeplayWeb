<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CSSIntroTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Introduccion a CSS'],
            [
                'contenido' => 'Documentacion introductoria sobre CSS, reglas, selectores, propiedades, valores y cascada.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Introduccion a CSS')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'css1'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Introduccion a CSS',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'css1')
            ->value('id');

        DB::table('preguntas_test')
            ->where('test_id', $testId)
            ->delete();

        $preguntas = [
            [
                'pregunta' => '¿Para que sirve CSS en una pagina web?',
                'opcion_a' => 'Para crear la estructura principal del HTML',
                'opcion_b' => 'Para cambiar colores, tamanos, fondos y estilos visuales',
                'opcion_c' => 'Para guardar datos en una base de datos',
                'opcion_d' => 'Para abrir archivos de video automaticamente',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Que significa CSS?',
                'opcion_a' => 'Creative Server System',
                'opcion_b' => 'Computer Style Script',
                'opcion_c' => 'Cascading Style Sheets',
                'opcion_d' => 'Code Structure System',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => 'En una regla CSS, ¿que es el selector?',
                'opcion_a' => 'El elemento o grupo de elementos que recibira el estilo',
                'opcion_b' => 'El color final de la pagina',
                'opcion_c' => 'El archivo donde se guarda la imagen',
                'opcion_d' => 'El boton que envia un formulario',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => 'En la regla h1 { color: blue; }, ¿cual es la propiedad?',
                'opcion_a' => 'h1',
                'opcion_b' => 'color',
                'opcion_c' => 'blue',
                'opcion_d' => '{}',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => 'En la regla h1 { color: blue; }, ¿cual es el valor?',
                'opcion_a' => 'h1',
                'opcion_b' => 'color',
                'opcion_c' => 'blue',
                'opcion_d' => 'style',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Cual selector apunta a todos los parrafos?',
                'opcion_a' => '.p',
                'opcion_b' => '#p',
                'opcion_c' => 'p',
                'opcion_d' => '*p',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Cual selector apunta a una clase llamada destacado?',
                'opcion_a' => '#destacado',
                'opcion_b' => '.destacado',
                'opcion_c' => 'destacado',
                'opcion_d' => '<destacado>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Cual selector apunta a un elemento con id="principal"?',
                'opcion_a' => '#principal',
                'opcion_b' => '.principal',
                'opcion_c' => 'principal',
                'opcion_d' => 'id.principal',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Cual forma de usar CSS es mas ordenada para un proyecto con varias paginas?',
                'opcion_a' => 'Usar un archivo CSS externo',
                'opcion_b' => 'Repetir style en cada etiqueta',
                'opcion_c' => 'Escribir CSS dentro de cada parrafo',
                'opcion_d' => 'No usar selectores',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Que idea describe mejor la cascada en CSS?',
                'opcion_a' => 'CSS solo puede tener una regla por pagina',
                'opcion_b' => 'El navegador decide que regla gana cuando varias afectan lo mismo',
                'opcion_c' => 'Todas las reglas se ignoran si hay imagenes',
                'opcion_d' => 'CSS solo funciona con formularios',
                'respuesta_correcta' => 'b',
            ],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de introduccion a CSS completado con 10 preguntas.');
    }
}
