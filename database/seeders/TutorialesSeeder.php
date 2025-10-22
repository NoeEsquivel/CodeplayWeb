<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorialesSeeder extends Seeder
{
    public function run()
    {
        $tutoriales = [
            [
                'titulo' => 'Tu Primera Página Web',
                'slug' => 'primer-pagina-web',
                'dificultad' => 'facil',
                'puntos_otorgados' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Agregar más tutoriales aquí después
        ];

        foreach ($tutoriales as $tutorial) {
            DB::table('tutoriales')->insert($tutorial);
        }

        $this->command->info('Tutoriales creados exitosamente!');
    }
}