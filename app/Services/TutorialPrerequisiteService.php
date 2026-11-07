<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TutorialPrerequisiteService
{
    private const TUTORIALS = [
        'primer-pagina-web' => ['video' => 'Primera_pagina.mp4', 'documents' => []],
        'estilos-css' => ['video' => 'Estilos_css.mp4', 'documents' => ['css1', 'css2']],
        'botones-interactivos' => ['video' => 'Botones_interactivos.mp4', 'documents' => ['js3']],
        'navegacion-paginas' => ['video' => 'Navegacion_paginas.mp4', 'documents' => ['html3']],
        'introduccion-bootstrap' => ['video' => 'Introduccion_bootstrap.mp4', 'documents' => ['html2', 'css1']],
        'formularios-web' => ['video' => 'Formularios_web.mp4', 'documents' => ['html4']],
        'javascript-basico' => ['video' => 'Javascript_basico.mp4', 'documents' => ['js1', 'js2']],
        'entorno-desarrollo' => ['video' => 'Entorno_desarrollo.mp4', 'documents' => ['mundo3']],
        'diseno-responsivo' => ['video' => 'Diseno_responsivo.mp4', 'documents' => ['css3', 'css4']],
        'proyecto-completo' => ['video' => 'Proyecto_completo.mp4', 'documents' => ['html2', 'css4', 'js3']],
    ];

    private const DOCUMENTS = [
        'html2' => 'Etiquetas Fundamentales',
        'html3' => 'Enlaces e Imágenes',
        'html4' => 'Formularios HTML',
        'css1' => 'Introducción a CSS',
        'css2' => 'Colores, Fuentes y Fondos',
        'css3' => 'Espacios, Bordes y Cajas',
        'css4' => 'Diseños Flexibles',
        'js1' => '¿Qué es JavaScript?',
        'js2' => 'Variables y Decisiones',
        'js3' => 'Botones y Eventos',
        'mundo3' => 'Programación de Software',
    ];

    public function slugForVideo(string $video): ?string
    {
        foreach (self::TUTORIALS as $slug => $tutorial) {
            if ($tutorial['video'] === $video) {
                return $slug;
            }
        }

        return null;
    }

    public function status(string $slug, int $userId): array
    {
        $modules = self::TUTORIALS[$slug]['documents'] ?? [];

        if ($modules === []) {
            return ['unlocked' => true, 'requirements' => []];
        }

        $visited = DB::table('progreso_documentacion')
            ->where('usuario_id', $userId)
            ->whereIn('documento', $modules)
            ->pluck('documento')->flip();

        // Un intento registrado cuenta, sin importar la nota ni el tiempo transcurrido.
        $attempted = DB::table('resultados_test')
            ->join('tests', 'tests.id', '=', 'resultados_test.test_id')
            ->where('resultados_test.usuario_id', $userId)
            ->whereIn('tests.modulo', $modules)
            ->distinct()->pluck('tests.modulo')->flip();

        $requirements = array_map(fn ($module) => [
            'module' => $module,
            'title' => self::DOCUMENTS[$module],
            'visited' => $visited->has($module),
            'attempted' => $attempted->has($module),
            'complete' => $visited->has($module) && $attempted->has($module),
        ], $modules);

        return [
            'unlocked' => ! in_array(false, array_column($requirements, 'complete'), true),
            'requirements' => $requirements,
        ];
    }
}
