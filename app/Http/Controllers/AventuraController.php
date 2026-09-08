<?php

namespace App\Http\Controllers;

use App\Services\AventuraService;
use Illuminate\Http\Request;

class AventuraController extends Controller
{
    private function actualizarSesion(array $estado): void
    {
        session(['puntos_disponibles' => $estado['puntos']['disponibles'], 'puntos_usados' => $estado['puntos']['ganados'] - $estado['puntos']['disponibles']]);
    }

    public function index(AventuraService $aventura)
    {
        $estado = $aventura->estado((int) session('usuario_id'));
        $this->actualizarSesion($estado);
        return view('aventura.Index', compact('estado'));
    }

    public function guardar(Request $request, AventuraService $aventura)
    {
        $datos = $request->validate([
            'expedicion_id' => ['required', 'integer', 'min:1'],
            'version' => ['required', 'integer', 'min:0'],
            'equipo' => ['required', 'array:arma,armadura,hechizos,pociones'],
            'equipo.arma' => ['present', 'nullable', 'string', 'max:80'],
            'equipo.armadura' => ['present', 'nullable', 'string', 'max:80'],
            'equipo.hechizos' => ['present', 'array', 'max:2'],
            'equipo.hechizos.*' => ['required', 'string', 'max:80', 'distinct:strict'],
            'equipo.pociones' => ['present', 'array', 'max:4'],
            'equipo.pociones.*' => ['required', 'array:id,cantidad'],
            'equipo.pociones.*.id' => ['required', 'string', 'max:80', 'distinct:strict'],
            'equipo.pociones.*.cantidad' => ['required', 'integer', 'min:1', 'max:20'],
        ]);
        foreach ($datos['equipo']['pociones'] as &$pocion) $pocion['cantidad'] = (int) $pocion['cantidad'];
        unset($pocion);
        $estado = $aventura->guardar((int) session('usuario_id'), (int) $datos['expedicion_id'], (int) $datos['version'], $datos['equipo']);
        $this->actualizarSesion($estado);
        return response()->json(['success' => true, 'estado' => $estado]);
    }
}
