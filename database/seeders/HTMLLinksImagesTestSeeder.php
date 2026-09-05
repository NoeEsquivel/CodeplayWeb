<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HTMLLinksImagesTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Enlaces e Imagenes'],
            [
                'contenido' => 'Documentacion sobre enlaces, imagenes, atributos href, target, src, alt y rutas locales.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Enlaces e Imagenes')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'html3'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Enlaces e Imagenes',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'html3')
            ->value('id');

        DB::table('preguntas_test')
            ->where('test_id', $testId)
            ->delete();

        $preguntas = [
            [
                'pregunta' => '¿Que etiqueta se usa para crear un enlace en HTML?',
                'opcion_a' => '<link>',
                'opcion_b' => '<a>',
                'opcion_c' => '<href>',
                'opcion_d' => '<url>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Que atributo indica el destino de un enlace?',
                'opcion_a' => 'src',
                'opcion_b' => 'alt',
                'opcion_c' => 'href',
                'opcion_d' => 'target',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Cual ejemplo crea correctamente un enlace local?',
                'opcion_a' => '<a href="contacto.html">Contacto</a>',
                'opcion_b' => '<link src="contacto.html">Contacto</link>',
                'opcion_c' => '<a src="contacto.html">Contacto</a>',
                'opcion_d' => '<href="contacto.html">Contacto</href>',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Para que sirve target="_blank" en un enlace?',
                'opcion_a' => 'Para mostrar una imagen rota',
                'opcion_b' => 'Para abrir el enlace en una pestana nueva',
                'opcion_c' => 'Para cambiar el color del enlace',
                'opcion_d' => 'Para cerrar la pagina actual',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Que etiqueta se usa para mostrar una imagen?',
                'opcion_a' => '<picture-only>',
                'opcion_b' => '<image>',
                'opcion_c' => '<img>',
                'opcion_d' => '<photo>',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Que atributo de <img> contiene la ruta del archivo de imagen?',
                'opcion_a' => 'href',
                'opcion_b' => 'src',
                'opcion_c' => 'target',
                'opcion_d' => 'title',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Para que sirve el atributo alt en una imagen?',
                'opcion_a' => 'Para describir la imagen con texto',
                'opcion_b' => 'Para abrir la imagen en otra pestana',
                'opcion_c' => 'Para convertir la imagen en video',
                'opcion_d' => 'Para ocultar la imagen siempre',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Cual ruta apunta a una imagen local dentro de una carpeta Imagenes?',
                'opcion_a' => 'https://sitio.com/foto.png',
                'opcion_b' => 'Imagenes/foto.png',
                'opcion_c' => 'target="_blank"',
                'opcion_d' => '<a>foto.png</a>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Como se hace que una imagen funcione como enlace?',
                'opcion_a' => 'Poniendo la etiqueta <img> dentro de una etiqueta <a>',
                'opcion_b' => 'Poniendo <a> dentro del atributo alt',
                'opcion_c' => 'Usando href dentro de <img> sin <a>',
                'opcion_d' => 'Cambiando src por target',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => 'En una vista Blade de Laravel, ¿que helper se usa para llamar archivos publicos como imagenes?',
                'opcion_a' => 'route()',
                'opcion_b' => 'view()',
                'opcion_c' => 'asset()',
                'opcion_d' => 'session()',
                'respuesta_correcta' => 'c',
            ],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de enlaces e imagenes HTML completado con 10 preguntas.');
    }
}
