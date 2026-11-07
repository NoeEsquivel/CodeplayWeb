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
        $bodyEnd = $this->closingTagPosition($html, 'body');
        if ($bodyEnd === null || str_contains($html, 'id="codeplay-lux"')) {
            return $response;
        }

        $routeName = (string) $request->route()?->getName();
        $welcomePending = $routeName === 'inicio.alumno'
            && ! DB::table('usuarios')->where('id', $usuario->id)
                ->whereNotNull('lux_bienvenida_vista_at')->exists();

        $style = '<link rel="stylesheet" href="'.e(asset('css/student-lux.css')).'">';
        $headEnd = $this->closingTagPosition($html, 'head');
        if ($headEnd !== null) {
            $html = substr_replace($html, $style, $headEnd, 0);
            $bodyEnd = $this->closingTagPosition($html, 'body');
        }
        $widget = view('partials.student-lux', compact('routeName', 'welcomePending'))->render();
        if ($headEnd === null) {
            $widget = $style.$widget;
        }
        $response->setContent(substr_replace($html, $widget, $bodyEnd, 0));
        $response->headers->remove('Content-Length');

        return $response;
    }

    private function closingTagPosition(string $html, string $tag): ?int
    {
        // Los editores y scripts pueden contener documentos HTML completos.
        // Ignorar su contenido, los comentarios y los atributos evita insertar Lux en esos ejemplos.
        $pattern = <<<'REGEX'
~(?:<!--.*?-->|<(script|style|textarea|title)\b(?:[^>"']|"[^"]*"|'[^']*')*>.*?</\1\s*>)(*SKIP)(*F)|</%s\s*>|<(?:[^>"']|"[^"]*"|'[^']*')*>(*SKIP)(*F)~is
REGEX;

        if (preg_match(sprintf($pattern, preg_quote($tag, '~')), $html, $match, PREG_OFFSET_CAPTURE) !== 1) {
            return null;
        }

        return $match[0][1];
    }
}
