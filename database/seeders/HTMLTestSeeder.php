<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HTMLTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primero necesitamos un registro en documentacion para relacionar
        $docId = DB::table('documentacion')->insertGetId([
            'titulo' => 'Introducción a HTML',
            'contenido' => 'Documentación de introducción a HTML',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear el test
        $testId = DB::table('tests')->insertGetId([
            'documentacion_id' => $docId,
            'modulo' => 'html1',
            'titulo' => 'Test de Introducción a HTML',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Banco de 10 preguntas
        $preguntas = [
            [
                'pregunta' => '¿Qué significa HTML?',
                'opcion_a' => 'Hyper Text Markup Language',
                'opcion_b' => 'High Tech Modern Language',
                'opcion_c' => 'Home Tool Markup Language',
                'opcion_d' => 'Hyper Transfer Markup Language',
                'respuesta_correcta' => 'a'
            ],
            [
                'pregunta' => '¿Cuál es la etiqueta correcta para el encabezado más grande?',
                'opcion_a' => '<heading>',
                'opcion_b' => '<h6>',
                'opcion_c' => '<h1>',
                'opcion_d' => '<head>',
                'respuesta_correcta' => 'c'
            ],
            [
                'pregunta' => '¿Qué etiqueta se usa para crear un párrafo?',
                'opcion_a' => '<paragraph>',
                'opcion_b' => '<p>',
                'opcion_c' => '<para>',
                'opcion_d' => '<text>',
                'respuesta_correcta' => 'b'
            ],
            [
                'pregunta' => '¿Dónde se coloca el título de la página web?',
                'opcion_a' => 'En la etiqueta <body>',
                'opcion_b' => 'En la etiqueta <head>',
                'opcion_c' => 'En la etiqueta <title>',
                'opcion_d' => 'Tanto b como c son correctas',
                'respuesta_correcta' => 'd'
            ],
            [
                'pregunta' => '¿Cuál es la estructura básica de un documento HTML?',
                'opcion_a' => '<html><body><head>',
                'opcion_b' => '<!DOCTYPE html><html><head><body>',
                'opcion_c' => '<html><head><body>',
                'opcion_d' => '<!DOCTYPE html><html><head></head><body></body></html>',
                'respuesta_correcta' => 'd'
            ],
            [
                'pregunta' => '¿Qué etiqueta se usa para insertar una imagen?',
                'opcion_a' => '<image>',
                'opcion_b' => '<pic>',
                'opcion_c' => '<img>',
                'opcion_d' => '<picture>',
                'respuesta_correcta' => 'c'
            ],
            [
                'pregunta' => '¿Para qué sirve la etiqueta <div>?',
                'opcion_a' => 'Para crear divisiones o secciones',
                'opcion_b' => 'Para hacer divisiones matemáticas',
                'opcion_c' => 'Para crear listas',
                'opcion_d' => 'Para insertar videos',
                'respuesta_correcta' => 'a'
            ],
            [
                'pregunta' => '¿Cuál es la etiqueta correcta para un enlace?',
                'opcion_a' => '<link>',
                'opcion_b' => '<a>',
                'opcion_c' => '<href>',
                'opcion_d' => '<anchor>',
                'respuesta_correcta' => 'b'
            ],
            [
                'pregunta' => '¿Qué significa la etiqueta <!DOCTYPE html>?',
                'opcion_a' => 'Define el tipo de documento como HTML5',
                'opcion_b' => 'Es un comentario',
                'opcion_c' => 'Define la versión de CSS',
                'opcion_d' => 'Inicia el documento HTML',
                'respuesta_correcta' => 'a'
            ],
            [
                'pregunta' => '¿Dónde va el contenido visible de la página web?',
                'opcion_a' => 'En la etiqueta <head>',
                'opcion_b' => 'En la etiqueta <body>',
                'opcion_c' => 'En la etiqueta <main>',
                'opcion_d' => 'En la etiqueta <content>',
                'respuesta_correcta' => 'b'
            ]
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert([
                'test_id' => $testId,
                ...$pregunta,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeder de HTML completado con 10 preguntas!');
    }
}