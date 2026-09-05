<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            'html1' => 'fundamentos_html.introduccion_html',
            'html2' => 'fundamentos_html.etiquetas_fundamentales',
            'html3' => 'fundamentos_html.enlaces_imagenes',
            'html4' => 'fundamentos_html.formularios_html',
            'css1' => 'fundamentos_css.introduccion_css',
        ];

        if (array_key_exists($documento, $documentos)) {
            return view("documentacion.{$documentos[$documento]}");
        }

        $modulosEnPreparacion = [
            'css2' => ['numero' => 6, 'titulo' => 'Colores, Fuentes y Fondos', 'descripcion' => 'Primeros cambios visuales para personalizar una pagina'],
            'css3' => ['numero' => 7, 'titulo' => 'Espacios, Bordes y Cajas', 'descripcion' => 'Como se acomodan los elementos usando margin, padding y border'],
            'css4' => ['numero' => 8, 'titulo' => 'Diseños Flexibles', 'descripcion' => 'Organizar tarjetas, menus y secciones con Flexbox'],
            'js1' => ['numero' => 9, 'titulo' => '¿Que es JavaScript?', 'descripcion' => 'La parte que permite que una pagina responda y cambie'],
            'js2' => ['numero' => 10, 'titulo' => 'Variables y Decisiones', 'descripcion' => 'Guardar datos, comparar respuestas y usar condiciones simples'],
            'js3' => ['numero' => 11, 'titulo' => 'Botones y Eventos', 'descripcion' => 'Hacer que una pagina reaccione a clics y acciones del usuario'],
            'js4' => ['numero' => 12, 'titulo' => 'Mini Quiz con JavaScript', 'descripcion' => 'Primer ejercicio interactivo con preguntas, respuestas y puntaje'],
            'mundo1' => ['numero' => 13, 'titulo' => 'Tipos de Lenguajes', 'descripcion' => 'Diferencias entre HTML, CSS, JavaScript, Python, Java, PHP y otros'],
            'mundo2' => ['numero' => 14, 'titulo' => 'Frontend, Backend y Bases de Datos', 'descripcion' => 'Que parte ve el usuario y que parte trabaja por detras'],
            'mundo3' => ['numero' => 15, 'titulo' => 'Programacion de Software', 'descripcion' => 'Como se crean aplicaciones para computadora y celular'],
            'mundo4' => ['numero' => 16, 'titulo' => 'Programacion de Robots y Componentes', 'descripcion' => 'Sensores, motores, placas y codigo que mueve cosas reales'],
            'mundo5' => ['numero' => 17, 'titulo' => 'Videojuegos e Interactividad', 'descripcion' => 'Personajes, reglas, colisiones, puntos y controles'],
            'proyecto1' => ['numero' => 18, 'titulo' => 'Pagina Web Personal', 'descripcion' => 'Crear una pagina propia con HTML y CSS'],
            'proyecto2' => ['numero' => 19, 'titulo' => 'Menu de VideoJuego', 'descripcion' => 'Diseñar una pantalla inicial con opciones y estilo gamer'],
            'proyecto3' => ['numero' => 20, 'titulo' => 'Tarjeta Interactiva', 'descripcion' => 'Crear una tarjeta que cambie al hacer clic'],
            'proyecto4' => ['numero' => 21, 'titulo' => 'Mini Quiz con JavaScript', 'descripcion' => 'Construir un juego corto de preguntas con puntaje'],
            'proyecto5' => ['numero' => 22, 'titulo' => 'Creemos un juego "La culebrita"', 'descripcion' => 'Crear una version simple del juego de la culebrita'],
        ];

        if (array_key_exists($documento, $modulosEnPreparacion)) {
            return view('documentacion.modulo_en_preparacion', [
                'modulo' => $modulosEnPreparacion[$documento],
            ]);
        }

        abort(404, 'Documento no encontrado');
    }
}
