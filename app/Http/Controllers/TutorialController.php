<?php

namespace App\Http\Controllers;

use App\Services\TutorialPrerequisiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TutorialController extends Controller
{
    public function index()
    {
        return view('tutoriales.tutoriales');
    }

    public function show($slug, TutorialPrerequisiteService $prerequisites)
    {
        // Obtener el tutorial específico
        $tutorial = DB::table('tutoriales')->where('slug', $slug)->first();

        if (! $tutorial) {
            abort(404, 'Tutorial no encontrado');
        }

        // Verificar si el usuario ya completó este tutorial
        $usuarioId = session('usuario_id');
        $progreso = DB::table('progreso_tutorial')
            ->where('usuario_id', $usuarioId)
            ->where('tutorial_id', $tutorial->id)
            ->first();

        // Cargar la vista específica del tutorial
        $vista = 'tutoriales.'.$slug;

        if (! view()->exists($vista)) {
            return view('tutoriales.en-desarrollo', compact('slug'));
        }

        $videoAccess = $prerequisites->status($slug, (int) $usuarioId);

        return view($vista, compact('tutorial', 'progreso', 'videoAccess'));
    }

    public function video(Request $request, string $video, TutorialPrerequisiteService $prerequisites): StreamedResponse
    {
        $slug = $prerequisites->slugForVideo($video);
        abort_if($slug === null, 404);

        $access = $prerequisites->status($slug, (int) $request->session()->get('usuario_id'));
        $pending = array_column(array_filter($access['requirements'], fn ($item) => ! $item['complete']), 'title');
        abort_unless($access['unlocked'], 403,
            'Para desbloquear este video, visita la documentación y realiza al menos un test de cada tema pendiente: '.implode(', ', $pending).'.');

        // Los videos restringidos se guardan fuera de la carpeta pública.
        $path = $slug === 'primer-pagina-web'
            ? public_path('videos'.DIRECTORY_SEPARATOR.$video)
            : resource_path('videos/tutoriales/'.$video);
        abort_unless(is_file($path) && is_readable($path), 404);

        $size = filesize($path);
        $start = 0;
        $end = $size - 1;
        $status = 200;
        $range = $request->header('Range');

        if ($range !== null) {
            if (str_contains($range, ',') || ! preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $matches)) {
                abort(416, 'Rango de video no válido', ['Content-Range' => "bytes */{$size}"]);
            }

            if ($matches[1] === '' && $matches[2] === '') {
                abort(416, 'Rango de video no válido', ['Content-Range' => "bytes */{$size}"]);
            }

            if ($matches[1] === '') {
                $suffixLength = min((int) $matches[2], $size);
                $start = $size - $suffixLength;
            } else {
                $start = (int) $matches[1];
                if ($matches[2] !== '') {
                    $end = min((int) $matches[2], $end);
                }
            }

            if ($start < 0 || $start > $end || $start >= $size) {
                abort(416, 'Rango de video no disponible', ['Content-Range' => "bytes */{$size}"]);
            }

            $status = 206;
        }

        $length = $end - $start + 1;
        $headers = [
            'Accept-Ranges' => 'bytes',
            'Content-Type' => 'video/mp4',
            'Content-Length' => (string) $length,
            'Content-Disposition' => 'inline; filename="'.$video.'"',
            'Cache-Control' => 'private, no-store',
        ];

        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        return response()->stream(function () use ($path, $start, $length) {
            $stream = fopen($path, 'rb');
            if ($stream === false) {
                return;
            }

            fseek($stream, $start);
            $remaining = $length;

            while ($remaining > 0 && ! feof($stream) && connection_aborted() === 0) {
                $chunk = fread($stream, min(1024 * 1024, $remaining));
                if ($chunk === false || $chunk === '') {
                    break;
                }

                echo $chunk;
                $remaining -= strlen($chunk);
                flush();
            }

            fclose($stream);
        }, $status, $headers);
    }

    public function completar(Request $request, $slug)
    {
        $usuarioId = session('usuario_id');

        if (! $usuarioId) {
            return response()->json([
                'success' => false,
                'error' => 'Usuario no autenticado',
            ]);
        }

        // Obtener el tutorial
        $tutorial = DB::table('tutoriales')->where('slug', $slug)->first();

        if (! $tutorial) {
            return response()->json([
                'success' => false,
                'error' => 'Tutorial no encontrado',
            ]);
        }

        $access = app(TutorialPrerequisiteService::class)->status($slug, (int) $usuarioId);
        if (! $access['unlocked']) {
            $pending = array_column(array_filter($access['requirements'], fn ($item) => ! $item['complete']), 'title');

            return response()->json([
                'success' => false,
                'error' => 'Para completar este tutorial, visita la documentación y realiza al menos un test de cada tema pendiente: '.implode(', ', $pending).'.',
                'requirements' => $access['requirements'],
            ], 403);
        }

        // Verificar si ya completó el tutorial
        $progresoExistente = DB::table('progreso_tutorial')
            ->where('usuario_id', $usuarioId)
            ->where('tutorial_id', $tutorial->id)
            ->first();

        if ($progresoExistente && $progresoExistente->estado === 'completado') {
            return response()->json([
                'success' => false,
                'error' => 'Ya completaste este tutorial',
            ]);
        }

        DB::transaction(function () use ($usuarioId, $tutorial, $progresoExistente) {
            if ($progresoExistente) {
                // Actualizar estado existente
                DB::table('progreso_tutorial')
                    ->where('id', $progresoExistente->id)
                    ->update([
                        'estado' => 'completado',
                        'updated_at' => now(),
                    ]);
            } else {
                // Crear nuevo registro
                DB::table('progreso_tutorial')->insert([
                    'usuario_id' => $usuarioId,
                    'tutorial_id' => $tutorial->id,
                    'estado' => 'completado',
                    'fecha' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sumar puntos al usuario
            DB::table('usuarios')
                ->where('id', $usuarioId)
                ->increment('puntos_totales', $tutorial->puntos_otorgados);

            // Actualizar sesión
            $usuario = DB::table('usuarios')->where('id', $usuarioId)->first();
            $puntosDisponibles = ($usuario->puntos_totales ?? 0) - ($usuario->puntos_usados ?? 0);

            session([
                'puntos_disponibles' => $puntosDisponibles,
                'puntos_totales' => $usuario->puntos_totales ?? 0,
            ]);
        });

        return response()->json([
            'success' => true,
            'puntos_ganados' => $tutorial->puntos_otorgados,
            'puntos_totales' => session('puntos_disponibles'),
        ]);
    }

    public function getProgreso()
    {
        $usuarioId = session('usuario_id');

        if (! $usuarioId) {
            return response()->json([
                'success' => false,
                'progreso' => 0,
                'tutorialesCompletados' => 0,
                'totalTutoriales' => 10,
            ]);
        }

        // Obtener tutoriales completados
        $tutorialesCompletados = DB::table('progreso_tutorial')
            ->where('usuario_id', $usuarioId)
            ->where('estado', 'completado')
            ->count();

        $totalTutoriales = 10; // Total de tutoriales en el sistema
        $progresoPorcentaje = round(($tutorialesCompletados / $totalTutoriales) * 100);

        return response()->json([
            'success' => true,
            'progreso' => $progresoPorcentaje,
            'tutorialesCompletados' => $tutorialesCompletados,
            'totalTutoriales' => $totalTutoriales,
            'puntos' => session('puntos_disponibles') ?? 0,
        ]);
    }
}
