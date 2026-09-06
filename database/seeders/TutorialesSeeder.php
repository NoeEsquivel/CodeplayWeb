<?php

namespace Database\Seeders;

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
            ],
            [
                'titulo' => 'Añadiendo Estilos CSS',
                'slug' => 'estilos-css',
                'dificultad' => 'facil',
                'puntos_otorgados' => 30,
            ],
            [
                'titulo' => 'Creando Botones Interactivos',
                'slug' => 'botones-interactivos',
                'dificultad' => 'facil',
                'puntos_otorgados' => 25,
            ],
            [
                'titulo' => 'Navegación entre Páginas',
                'slug' => 'navegacion-paginas',
                'dificultad' => 'facil',
                'puntos_otorgados' => 25,
            ],
        ];

        foreach ($tutoriales as $tutorial) {
            DB::transaction(function () use ($tutorial) {
                $legacyTutorial = DB::table('tutoriales')
                    ->where('titulo', $tutorial['titulo'])
                    ->where(function ($query) {
                        $query->whereNull('slug')->orWhere('slug', '');
                    })
                    ->first();

                $tutorialWithSlug = DB::table('tutoriales')
                    ->where('slug', $tutorial['slug'])
                    ->first();

                if ($legacyTutorial && $tutorialWithSlug && $legacyTutorial->id !== $tutorialWithSlug->id) {
                    DB::table('progreso_tutorial')
                        ->where('tutorial_id', $tutorialWithSlug->id)
                        ->update(['tutorial_id' => $legacyTutorial->id]);

                    DB::table('tutoriales')
                        ->where('id', $tutorialWithSlug->id)
                        ->delete();

                    $tutorialWithSlug = null;
                }

                if ($legacyTutorial) {
                    DB::table('tutoriales')
                        ->where('id', $legacyTutorial->id)
                        ->update(array_merge($tutorial, ['updated_at' => now()]));

                    return;
                }

                DB::table('tutoriales')->updateOrInsert(
                    ['slug' => $tutorial['slug']],
                    array_merge($tutorial, [
                        'created_at' => $tutorialWithSlug->created_at ?? now(),
                        'updated_at' => now(),
                    ])
                );
            });
        }

        $this->command->info('Tutoriales creados o actualizados exitosamente!');
    }
}
