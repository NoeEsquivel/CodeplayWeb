<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentacionController extends Controller
{
    public function index()
    {
        return view('documentacion.index');
    }

    public function show($documento)
    {
        // Lista de documentos disponibles - ACTUALIZADA con la nueva estructura
        $documentos = [
            'intro1' => 'introduccion.que_es_programacion',
            'intro2' => 'introduccion.que_hace_programador',
            'intro3' => 'introduccion.grandes_programas_ideas',
            'intro4' => 'introduccion.programar_importante_futuro',
            'html1' => 'fundamentos_html.introduccion_html',
            'html2' => 'fundamentos_html.etiquetas_fundamentales',
            'html3' => 'fundamentos_html.enlaces_imagenes',
            'html4' => 'fundamentos_html.formularios_html',
            'css1' => 'fundamentos_css.introduccion_css',
            'css2' => 'fundamentos_css.colores_fuentes_fondos',
            'css3' => 'fundamentos_css.espacios_bordes_cajas',
            'css4' => 'fundamentos_css.disenos_flexibles',
            'js1' => 'javascript_basico.que_es_javascript',
            'js2' => 'javascript_basico.variables_decisiones',
            'js3' => 'javascript_basico.botones_eventos',
            'js4' => 'javascript_basico.mini_quiz_javascript',
            'mundo1' => 'mundos_programacion.tipos_lenguajes',
            'mundo2' => 'mundos_programacion.frontend_backend_bases_datos',
            'mundo3' => 'mundos_programacion.programacion_software',
            'mundo4' => 'mundos_programacion.programacion_robots_componentes',
            'mundo5' => 'mundos_programacion.videojuegos_interactividad',
            'proyecto1' => 'proyectos_integradores.pagina_web_personal',
            'proyecto2' => 'proyectos_integradores.menu_videojuego',
            'proyecto3' => 'proyectos_integradores.tarjeta_interactiva',
            'proyecto4' => 'proyectos_integradores.mini_quiz_javascript',
            'proyecto5' => 'proyectos_integradores.juego_culebrita',
        ];

        if (array_key_exists($documento, $documentos)) {
            if (Schema::hasTable('progreso_documentacion') && session('usuario_id')) {
                $clave = ['usuario_id' => (int) session('usuario_id'), 'documento' => $documento];
                DB::table('progreso_documentacion')->insertOrIgnore($clave + [
                    'visto_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('progreso_documentacion')->where($clave)->update([
                    'visto_at' => now(), 'updated_at' => now(),
                ]);
            }

            return view("documentacion.{$documentos[$documento]}");
        }

        $modulosEnPreparacion = [
        ];

        if (array_key_exists($documento, $modulosEnPreparacion)) {
            return view('documentacion.modulo_en_preparacion', [
                'modulo' => $modulosEnPreparacion[$documento],
            ]);
        }

        abort(404, 'Documento no encontrado');
    }
}
