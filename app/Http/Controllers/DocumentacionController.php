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
            'css1' => 'documentacion.doc_css1',
            'css2' => 'documentacion.doc_css2',
            'js1' => 'documentacion.doc_js1',
            'js2' => 'documentacion.doc_js2',
            'bootstrap1' => 'documentacion.doc_bootstrap1',
            'bootstrap2' => 'documentacion.doc_bootstrap2',
            'bootstrap3' => 'documentacion.doc_bootstrap3',
            'bootstrap4' => 'documentacion.doc_bootstrap4',
            'php1' => 'documentacion.doc_php1',
            'php2' => 'documentacion.doc_php2',
            'php3' => 'documentacion.doc_php3',
            'php4' => 'documentacion.doc_php4',
            'proyecto1' => 'documentacion.doc_proyecto1',
            'proyecto2' => 'documentacion.doc_proyecto2',
        ];

        if (array_key_exists($documento, $documentos)) {
            return view("documentacion.{$documentos[$documento]}");
        }

        abort(404, 'Documento no encontrado');
    }
}