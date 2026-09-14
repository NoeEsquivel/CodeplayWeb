<?php

namespace App\Http\Controllers;

use App\Services\AventuraService;
use App\Services\AventuraExploracion;
use App\Services\AventuraCombate;
use App\Models\AventuraExpedicion;
use Illuminate\Http\Request;

class AventuraController extends Controller
{
    public function finalAventura(int $expedicion)
    {
        $partida=AventuraExpedicion::where('usuario_id',session('usuario_id'))->findOrFail($expedicion);
        $victoria=\App\Services\AventuraFinal::victoria((int)session('usuario_id'),$expedicion);
        abort_unless($victoria,404);
        return view('aventura.final',compact('partida','victoria'));
    }

    public function completar(Request $request, AventuraService $aventura)
    {
        $datos=$request->validate(['expedicion_id'=>['required','integer','min:1'],'version'=>['required','integer','min:0']]);
        $aventura->completar((int)session('usuario_id'),(int)$datos['expedicion_id'],(int)$datos['version']);
        $this->actualizarSesion($aventura->estado((int)session('usuario_id')));
        return redirect()->route('alumno.aventura');
    }
    public function gremio(Request $request, \App\Services\AventuraGremio $gremio)
    {
        $datos=$request->validate([
            'expedicion_id'=>['required','integer','min:1'],'version'=>['required','integer','min:0'],
            'accion'=>['required','in:abrir,aceptar,salir,tienda,cerrar_tienda,comprar,responder,cobrar,abandonar,equipar'],
            'encargo'=>['nullable','string','max:40'],'origen'=>['nullable','integer','between:1,50'],
            'respuesta'=>['nullable','in:a,b,c,d'],'reemplazar'=>['nullable','string','max:80'],'oferta'=>['nullable','string','max:80'],
        ]);
        $gremio->actuar((int)session('usuario_id'),(int)$datos['expedicion_id'],(int)$datos['version'],$datos);
        return response()->json(['url'=>route('alumno.aventura.mapa').'#story-title']);
    }

    public function evento(Request $request, \App\Services\AventuraEventos $eventos)
    {
        $datos = $request->validate([
            'expedicion_id'=>['required','integer','min:1'], 'version'=>['required','integer','min:0'],
            'accion'=>['required','in:descubrir,preguntar,ayudar,ignorar,abrir,responder,equipar,mejorar_hechizo,continuar'],
            'respuesta'=>['nullable','in:a,b,c,d'], 'reemplazar'=>['nullable','string','max:80'], 'hechizo'=>['nullable','string','max:80'],
        ]);
        $resultado = $eventos->actuar((int)session('usuario_id'),(int)$datos['expedicion_id'],(int)$datos['version'],$datos);
        return response()->json(['url'=>$resultado === 'derrota'
            ? route('alumno.aventura.derrota',['expedicion'=>$datos['expedicion_id']])
            : route('alumno.aventura.mapa').'#story-title']);
    }

    public function reiniciar(Request $request, AventuraService $aventura)
    {
        $datos = $request->validate(['expedicion_id' => ['required', 'integer', 'min:1'], 'version' => ['required', 'integer', 'min:0']]);
        $aventura->reiniciar((int) session('usuario_id'), (int) $datos['expedicion_id'], (int) $datos['version']);
        $this->actualizarSesion($aventura->estado((int) session('usuario_id')));
        return response()->json(['url' => route('alumno.aventura')]);
    }

    public function combate(Request $request, AventuraCombate $combate)
    {
        $datos = $request->validate([
            'expedicion_id'=>['required','integer','min:1'], 'version'=>['required','integer','min:0'],
            'accion'=>['required','in:abrir,atacar,defender,hechizo,pocion,responder,continuar'],
            'objetivo'=>['nullable','integer','between:0,9'], 'objeto'=>['nullable','string','max:80'],
            'respuesta'=>['nullable','in:a,b,c,d'],
            'tiempo_agotado'=>['nullable','boolean'],
        ]);
        $resultado = $combate->actuar((int)session('usuario_id'), (int)$datos['expedicion_id'], (int)$datos['version'], $datos);
        if(\App\Services\AventuraFinal::victoria((int)session('usuario_id'),(int)$datos['expedicion_id'])) return response()->json(['cinematica_final'=>(bool)config('aventura-cinematics.ignar_victory_url'),'secuencia'=>$combate->secuencia(),'url'=>route('alumno.aventura.final',['expedicion'=>$datos['expedicion_id']])]);
        return response()->json(['secuencia'=>$combate->secuencia(),'url'=>$resultado === 'derrota'
            ? route('alumno.aventura.derrota',['expedicion'=>$datos['expedicion_id']])
            : route('alumno.aventura.mapa').'#story-title']);
    }

    public function derrota(int $expedicion)
    {
        $partida = AventuraExpedicion::where('usuario_id',session('usuario_id'))->where('estado','derrota')->findOrFail($expedicion);
        $personaje = \App\Models\AventuraPersonaje::where('usuario_id',session('usuario_id'))->firstOrFail();
        return view('aventura.derrota',compact('partida','personaje'));
    }

    public function mapa(Request $request, AventuraService $aventura, AventuraExploracion $exploracion)
    {
        $datos = $request->validate(['region' => ['nullable', 'integer', 'between:1,5']]);
        $estado = $aventura->estado((int) session('usuario_id'));
        if(\App\Services\AventuraFinal::victoria((int)session('usuario_id'),$estado['expedicion']['id'])) return redirect()->route('alumno.aventura.final',['expedicion'=>$estado['expedicion']['id']]);
        $mundo = $exploracion->vista($estado, isset($datos['region']) ? (int) $datos['region'] : null);
        return view('aventura.mapa', compact('estado', 'mundo'));
    }

    public function iniciar(Request $request, AventuraExploracion $exploracion)
    {
        $datos = $request->validate(['expedicion_id' => ['required', 'integer', 'min:1'], 'version' => ['required', 'integer', 'min:0']]);
        $exploracion->iniciar((int) session('usuario_id'), (int) $datos['expedicion_id'], (int) $datos['version']);
        return response()->json(['url' => route('alumno.aventura.mapa')]);
    }

    public function camino(Request $request, AventuraExploracion $exploracion)
    {
        $datos = $request->validate(['expedicion_id' => ['required', 'integer', 'min:1'], 'version' => ['required', 'integer', 'min:0'], 'camino' => ['required', 'integer', 'between:0,2']]);
        $exploracion->elegir((int) session('usuario_id'), (int) $datos['expedicion_id'], (int) $datos['version'], (int) $datos['camino']);
        return response()->json(['url' => route('alumno.aventura.mapa')]);
    }

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
