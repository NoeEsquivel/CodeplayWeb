<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\TestAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentProgressController extends Controller
{
    private const DOCUMENTATION_TOTAL = 22;
    private const ADVENTURE_TOTAL = 50;

    public function __invoke(Request $request, TestAvailabilityService $availability)
    {
        $student = $request->attributes->get('usuario_actual');
        abort_unless($student instanceof Usuario && $student->esAlumno(), 403);

        $viewedDocuments = Schema::hasTable('progreso_documentacion')
            ? DB::table('progreso_documentacion')->where('usuario_id', $student->id)
                ->pluck('visto_at', 'documento')
            : collect();

        $tutorialTotal = Schema::hasTable('tutoriales') ? DB::table('tutoriales')->count() : 0;
        $tutorialDone = Schema::hasTable('progreso_tutorial')
            ? DB::table('progreso_tutorial')->where('usuario_id', $student->id)
                ->where('estado', 'completado')->distinct()->count('tutorial_id')
            : 0;

        $challengeTotal = Schema::hasTable('niveles_desafio') ? DB::table('niveles_desafio')->count() : 0;
        if ($challengeTotal === 0) $challengeTotal = 20;
        $challengeDone = Schema::hasTable('desafio_recompensas')
            ? DB::table('desafio_recompensas')->where('usuario_id', $student->id)
                ->where('completado_primera_vez', true)->distinct()->count('nivel_desafio')
            : 0;

        $character = Schema::hasTable('aventura_personajes')
            ? DB::table('aventura_personajes')->where('usuario_id', $student->id)->first()
            : null;
        $adventureScene = Schema::hasTable('aventura_expediciones') && Schema::hasColumn('aventura_expediciones', 'escenario')
            ? (int) (DB::table('aventura_expediciones')->where('usuario_id', $student->id)->max('escenario') ?? 0)
            : 0;
        $victories = Schema::hasTable('aventura_expediciones')
            ? DB::table('aventura_expediciones')->where('usuario_id', $student->id)->where('estado', 'victoria')->count()
            : 0;
        if ($victories > 0) $adventureScene = self::ADVENTURE_TOTAL;

        $tests = $availability->catalogForUser($student->id)->map(function (array $test) use ($viewedDocuments) {
            $test['documento_revisado'] = $viewedDocuments->has($test['modulo']);
            $test['documento_visto_at'] = $viewedDocuments->get($test['modulo']);
            return $test;
        });

        $modules = [
            'documentacion' => $this->progress($viewedDocuments->count(), self::DOCUMENTATION_TOTAL),
            'tutoriales' => $this->progress($tutorialDone, $tutorialTotal),
            'desafios' => $this->progress($challengeDone, $challengeTotal),
            'aventura' => $this->progress($adventureScene, self::ADVENTURE_TOTAL),
        ];
        $overall = round(collect($modules)->avg('porcentaje'), 1);

        return view('alumno.progreso', [
            'student' => $student,
            'pointsAvailable' => max(0, (int) $student->puntos_totales - (int) $student->puntos_usados),
            'modules' => $modules,
            'overall' => $overall,
            'tests' => $tests,
            'availableTests' => $tests->where('disponible', true)->count(),
            'testAttempts' => $tests->sum('intentos'),
            'testAverage' => $tests->whereNotNull('promedio')->avg('promedio'),
            'adventureLevel' => (int) ($character->nivel ?? 0),
            'adventureVictories' => $victories,
        ]);
    }

    private function progress(int $completed, int $total): array
    {
        $completed = max(0, min($completed, max($total, $completed)));
        return [
            'completados' => $completed,
            'total' => $total,
            'porcentaje' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }
}
