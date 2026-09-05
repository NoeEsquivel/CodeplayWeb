<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HTMLFormsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentacion')->updateOrInsert(
            ['titulo' => 'Formularios HTML'],
            [
                'contenido' => 'Documentacion sobre formularios, campos, etiquetas, opciones y envio de datos con HTML.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $docId = DB::table('documentacion')
            ->where('titulo', 'Formularios HTML')
            ->value('id');

        DB::table('tests')->updateOrInsert(
            ['modulo' => 'html4'],
            [
                'documentacion_id' => $docId,
                'titulo' => 'Test de Formularios HTML',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $testId = DB::table('tests')
            ->where('modulo', 'html4')
            ->value('id');

        DB::table('preguntas_test')
            ->where('test_id', $testId)
            ->delete();

        $preguntas = [
            [
                'pregunta' => '¿Que etiqueta agrupa los campos de un formulario?',
                'opcion_a' => '<input>',
                'opcion_b' => '<form>',
                'opcion_c' => '<label>',
                'opcion_d' => '<button>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Que etiqueta se usa para describir un campo al usuario?',
                'opcion_a' => '<label>',
                'opcion_b' => '<option>',
                'opcion_c' => '<textarea>',
                'opcion_d' => '<select>',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Que atributo conecta un label con el id de un input?',
                'opcion_a' => 'name',
                'opcion_b' => 'type',
                'opcion_c' => 'for',
                'opcion_d' => 'method',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Para que sirve el atributo name en un campo?',
                'opcion_a' => 'Para cambiar el color del campo',
                'opcion_b' => 'Para identificar el dato cuando el formulario se envia',
                'opcion_c' => 'Para abrir otra pagina',
                'opcion_d' => 'Para insertar una imagen',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Que input es mas adecuado para pedir un correo electronico?',
                'opcion_a' => '<input type="text">',
                'opcion_b' => '<input type="number">',
                'opcion_c' => '<input type="email">',
                'opcion_d' => '<input type="checkbox">',
                'respuesta_correcta' => 'c',
            ],
            [
                'pregunta' => '¿Que tipo de input oculta los caracteres escritos?',
                'opcion_a' => 'password',
                'opcion_b' => 'email',
                'opcion_c' => 'radio',
                'opcion_d' => 'submit',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Que etiqueta permite elegir una opcion de una lista desplegable?',
                'opcion_a' => '<textarea>',
                'opcion_b' => '<select>',
                'opcion_c' => '<button>',
                'opcion_d' => '<form>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Que etiqueta sirve para escribir mensajes largos?',
                'opcion_a' => '<input type="text">',
                'opcion_b' => '<textarea>',
                'opcion_c' => '<option>',
                'opcion_d' => '<label>',
                'respuesta_correcta' => 'b',
            ],
            [
                'pregunta' => '¿Que atributo indica a donde se enviaran los datos del formulario?',
                'opcion_a' => 'action',
                'opcion_b' => 'required',
                'opcion_c' => 'id',
                'opcion_d' => 'placeholder',
                'respuesta_correcta' => 'a',
            ],
            [
                'pregunta' => '¿Para que sirve el atributo required?',
                'opcion_a' => 'Para hacer que un campo sea obligatorio',
                'opcion_b' => 'Para borrar el formulario',
                'opcion_c' => 'Para abrir el formulario en otra pestana',
                'opcion_d' => 'Para convertir texto en imagen',
                'respuesta_correcta' => 'a',
            ],
        ];

        foreach ($preguntas as $pregunta) {
            DB::table('preguntas_test')->insert(array_merge($pregunta, [
                'test_id' => $testId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Seeder de formularios HTML completado con 10 preguntas.');
    }
}
