<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InjectStudentLux
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200
            || ! str_contains(strtolower((string) $response->headers->get('Content-Type')), 'text/html')) {
            return $response;
        }

        $usuario = $request->attributes->get('usuario_actual');
        if (! $usuario instanceof Usuario || ! $usuario->esAlumno()) {
            return $response;
        }

        $html = (string) $response->getContent();
        $bodyEnd = strripos($html, '</body>');
        if ($bodyEnd === false || str_contains($html, 'id="codeplay-lux"')) {
            return $response;
        }

        $routeName = (string) $request->route()?->getName();
        $welcomePending = $routeName === 'inicio.alumno'
            && ! DB::table('usuarios')->where('id', $usuario->id)
                ->whereNotNull('lux_bienvenida_vista_at')->exists();

        $style = '<link rel="stylesheet" href="'.e(asset('css/student-lux.css')).'">';
        $headEnd = strripos($html, '</head>');
        if ($headEnd !== false) {
            $html = substr_replace($html, $style, $headEnd, 0);
            $bodyEnd = strripos($html, '</body>');
        }
        $widget = view('partials.student-lux', compact('routeName', 'welcomePending'))->render();
        if ($headEnd === false) {
            $widget = $style.$widget;
        }
        $response->setContent(substr_replace($html, $widget, $bodyEnd, 0));
        $response->headers->remove('Content-Length');

        return $response;
    }
}
