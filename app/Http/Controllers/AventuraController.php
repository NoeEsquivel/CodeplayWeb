<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AventuraController extends Controller
{
    public function index()
    {
        $usuarioId = session('usuario_id');
        $desafiosCompletados = 0;
        
        if ($usuarioId) {
            $desafiosCompletados = DB::table('desafio_recompensas')
                ->where('usuario_id', $usuarioId)
                ->where('completado_primera_vez', true)
                ->count();
        }
        
        return view('aventura.index', compact('desafiosCompletados'));
    }
}