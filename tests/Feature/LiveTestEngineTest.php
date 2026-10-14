<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestSessionService;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LiveTestEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_server_synchronizes_question_order_results_and_score_for_every_player(): void
    {
        [$host, $friend, , $bank] = $this->scenario();
        $session = $this->liveSession($host, $bank, ['max_participantes' => 2]);
        $engine = app(LiveTestEngineService::class);
        $engine->abrirSala($host, $session);
        $hostJoin = $engine->unir($host, 'mesa-secreta');
        $engine->unir($friend, $session->codigo_sala, 'mesa-secreta');
        $engine->iniciar($host, $session);

        $hostState = $engine->estado($host, $session);
        $friendState = $engine->estado($friend, $session);
        $this->assertSame($hostState['pregunta']['id'], $friendState['pregunta']['id']);
        $this->assertSame('activa', $hostState['fase']);
        $this->assertNull($hostState['pregunta']['respuestas_correctas']);

        $engine->responder($host, $session, 'correcta');
        $engine->responder($friend, $session, 'correcta');
        $result = $engine->estado($friend, $session);

        $this->assertSame('resultado', $result['fase']);
        $this->assertTrue($result['mi_resultado']['correcta']);
        $this->assertSame(50, $result['mi_resultado']['puntos_sumados']);
        $this->assertSame(100, $result['clasificacion'][0]['puntos']);
        $this->assertSame(50, $result['clasificacion'][1]['puntos']);
        $this->assertNotNull($result['pregunta']['respuestas_correctas']);

        try {
            $engine->responder($host, $session, 'correcta');
            $this->fail('Se aceptó una segunda respuesta para la misma pregunta.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('ya no está abierta', $e->getMessage());
        }

        $reconnected = $engine->reconectar($host, $session, $hostJoin['reconexion_token']);
        $this->assertSame($host->id, $reconnected['participante']->usuario_id);
        $this->assertNotSame($hostJoin['reconexion_token'], $reconnected['reconexion_token']);
    }

    public function test_room_enforces_limit_school_membership_secret_and_safe_host_close(): void
    {
        [$host, $friend, $third, $bank, $outsider] = $this->scenario();
        $session = $this->liveSession($host, $bank, ['max_participantes' => 2]);
        $engine = app(LiveTestEngineService::class);
        $engine->abrirSala($host, $session);
        $engine->unir($host, $session->codigo_sala, 'mesa-secreta');

        foreach ([
            fn () => $engine->unir($friend, $session->codigo_sala, 'incorrecta'),
            fn () => $engine->unir($outsider, $session->codigo_sala, 'mesa-secreta'),
        ] as $blocked) {
            try { $blocked(); $this->fail('Se permitió entrar sin autorización.'); }
            catch (\DomainException) { $this->assertTrue(true); }
        }

        $engine->unir($friend, $session->codigo_sala, 'mesa-secreta');
        try { $engine->unir($third, $session->codigo_sala, 'mesa-secreta'); $this->fail('Se excedió el límite.'); }
        catch (\DomainException $e) { $this->assertStringContainsString('máximo', $e->getMessage()); }

        try { $engine->cerrar($friend, $session); $this->fail('Un invitado cerró la sala.'); }
        catch (\DomainException) { $this->assertTrue(true); }

        $closed = $engine->cerrar($host, $session);
        $this->assertSame('cerrada', $closed->estado);
        $this->assertNotNull($closed->cerrada_at);
    }

    public function test_late_answers_are_rejected_and_result_is_published_before_advancing(): void
    {
        [$host, , , $bank] = $this->scenario(1);
        Carbon::setTestNow(Carbon::parse('2026-09-26 10:00:00'));
        $session = $this->liveSession($host, $bank, ['max_participantes' => 1, 'tiempo_pregunta' => 5]);
        $engine = app(LiveTestEngineService::class);
        $engine->abrirSala($host, $session);
        $engine->unir($host, $session->codigo_sala, 'mesa-secreta');
        $engine->iniciar($host, $session);

        Carbon::setTestNow(now()->addSeconds(6));
        try { $engine->responder($host, $session, 'correcta'); $this->fail('Se aceptó una respuesta tardía.'); }
        catch (\DomainException $e) { $this->assertStringContainsString('ya no está abierta', $e->getMessage()); }

        $result = $engine->estado($host, $session);
        $this->assertSame('resultado', $result['fase']);
        $this->assertFalse($result['mi_resultado']['respondio']);
        $this->assertSame(0, $result['mi_resultado']['puntos_sumados']);

        Carbon::setTestNow(now()->addSeconds(3));
        $finished = $engine->estado($host, $session);
        $this->assertSame('finalizada', $finished['fase']);
    }

    public function test_questions_repeat_until_target_is_reached(): void
    {
        [$host, , , $bank] = $this->scenario(1);
        Carbon::setTestNow(Carbon::parse('2026-09-26 12:00:00'));
        $session = $this->liveSession($host, $bank, [
            'max_participantes' => 1,
            'puntaje_objetivo' => 200,
            'segundos_resultado' => 2,
        ]);
        $engine = app(LiveTestEngineService::class);
        $engine->abrirSala($host, $session);
        $engine->unir($host, $session->codigo_sala, 'mesa-secreta');
        $engine->iniciar($host, $session);
        $engine->responder($host, $session, 'correcta');

        Carbon::setTestNow(now()->addSeconds(3));
        $roundTwo = $engine->estado($host, $session);
        $this->assertSame('activa', $roundTwo['fase']);
        $this->assertSame(2, $roundTwo['sesion']['ronda']);
        $this->assertSame(100, $roundTwo['mi_resultado']['puntos_totales']);

        $engine->responder($host, $session, 'correcta');
        Carbon::setTestNow(now()->addSeconds(3));
        $finished = $engine->estado($host, $session);
        $this->assertSame('finalizada', $finished['fase']);
        $this->assertSame(200, $finished['mi_resultado']['puntos_totales']);
    }

    public function test_engine_rejects_invalid_start_answer_state_pause_and_resume_transitions(): void
    {
        [$host, $friend, , $bank] = $this->scenario();
        $engine = app(LiveTestEngineService::class);
        $assertDomain = function (callable $action, string $message): void {
            try {
                $action();
                $this->fail('La operación inválida fue aceptada.');
            } catch (\DomainException $exception) {
                $this->assertSame($message, $exception->getMessage());
            }
        };

        $unopened = $this->liveSession($host, $bank);
        $assertDomain(fn () => $engine->iniciar($host, $unopened), 'La sala no está en espera.');
        $engine->abrirSala($host, $unopened);
        $assertDomain(fn () => $engine->iniciar($host, $unopened), 'Debe ingresar al menos un participante.');
        $assertDomain(fn () => $engine->pausar($host, $unopened), 'Solo se puede pausar una partida activa.');
        $assertDomain(fn () => $engine->reanudar($host, $unopened), 'La partida no está pausada.');

        $future = $this->liveSession($host, $bank, ['disponible_desde' => now()->addHour()]);
        $engine->abrirSala($host, $future);
        $assertDomain(fn () => $engine->iniciar($host, $future), 'La actividad aún no está disponible.');

        $expired = $this->liveSession($host, $bank, ['disponible_hasta' => now()->subMinute()]);
        $engine->abrirSala($host, $expired);
        $assertDomain(fn () => $engine->iniciar($host, $expired), 'La actividad ya no está disponible.');

        $empty = $this->liveSession($host, $bank);
        $engine->abrirSala($host, $empty);
        $engine->unir($host, $empty->codigo_sala, 'mesa-secreta');
        $empty->bancos()->detach();
        $assertDomain(fn () => $engine->iniciar($host, $empty->fresh()), 'Los bancos seleccionados no tienen preguntas disponibles.');

        $active = $this->liveSession($host, $bank);
        $engine->abrirSala($host, $active);
        $engine->unir($host, $active->codigo_sala, 'mesa-secreta');
        $engine->iniciar($host, $active);
        $assertDomain(fn () => $engine->estado($friend, $active), 'No perteneces a esta sesión.');

        $exam = $this->liveSession($host, $bank, ['modalidad' => 'examen']);
        $engine->abrirSala($host, $exam);
        $engine->iniciar($host, $exam);
        $assertDomain(fn () => $engine->responder($friend, $exam, 'correcta'), 'Las respuestas del examen usan el guardado individual.');
    }
    public function test_teacher_controls_class_room_and_only_enrolled_students_can_join(): void
    {
        [$student, , , , , $class] = $this->scenario(1);
        $teacher = $this->user('Profesor de sala', $student->colegio_id, Usuario::ROL_PROFESOR);
        $notEnrolled = $this->user('Alumno sin clase', $student->colegio_id);
        $class->agregarProfesor($teacher);

        $banks = app(TestBankService::class);
        $bank = $banks->crear($teacher, ['nombre' => 'Banco docente', 'materia' => 'HTML']);
        $banks->agregarPregunta($teacher, $bank, [
            'tipo' => 'respuesta_directa', 'enunciado' => 'Pregunta docente',
            'respuestas_correctas' => ['correcta'],
        ]);
        $banks->publicar($teacher, $bank);
        $session = app(LiveTestSessionService::class)->crear($teacher, [
            'tipo' => 'clase', 'modalidad' => 'trivia', 'clase_id' => $class->id,
            'tiempo_pregunta' => 20, 'max_participantes' => 30,
            'configuracion' => ['segundos_resultado' => 2],
        ], [$bank->fresh()]);

        $engine = app(LiveTestEngineService::class);
        $engine->abrirSala($teacher, $session);
        $engine->unir($student, $session->codigo_sala);
        try { $engine->unir($notEnrolled, $session->codigo_sala); $this->fail('Entró un alumno ajeno a la clase.'); }
        catch (\DomainException) { $this->assertTrue(true); }

        $engine->iniciar($teacher, $session);
        $teacherState = $engine->estado($teacher, $session);
        $studentState = $engine->estado($student, $session);
        $this->assertNull($teacherState['mi_resultado']);
        $this->assertSame($teacherState['pregunta']['id'], $studentState['pregunta']['id']);
    }

    private function scenario(int $questions = 2): array
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $college = Colegio::create(['nombre' => 'Colegio '.$suffix, 'codigo' => 'LIVE-'.$suffix, 'activo' => true]);
        $otherCollege = Colegio::create(['nombre' => 'Otro '.$suffix, 'codigo' => 'OUT-'.$suffix, 'activo' => true]);
        $class = Clase::create([
            'colegio_id' => $college->id, 'nombre' => 'Clase '.$suffix, 'codigo' => 'C-'.$suffix,
            'ciclo_escolar' => '2026', 'activa' => true,
        ]);
        $host = $this->user('Anfitrión '.$suffix, $college->id);
        $friend = $this->user('Amigo '.$suffix, $college->id);
        $third = $this->user('Tercero '.$suffix, $college->id);
        $outsider = $this->user('Externo '.$suffix, $otherCollege->id);
        foreach ([$host, $friend, $third] as $student) $class->agregarAlumno($student);

        $bankService = app(TestBankService::class);
        $bank = $bankService->crear($host, ['nombre' => 'Banco en vivo '.$suffix, 'materia' => 'Programación']);
        foreach (range(1, $questions) as $number) {
            $bankService->agregarPregunta($host, $bank, [
                'tipo' => 'respuesta_directa',
                'enunciado' => 'Pregunta '.$number,
                'respuestas_correctas' => ['correcta'],
                'tiempo_limite' => 20,
            ]);
        }
        $bankService->publicar($host, $bank);

        return [$host, $friend, $third, $bank->fresh(), $outsider, $class];
    }

    private function liveSession(Usuario $host, TestBanco $bank, array $overrides = []): TestSesion
    {
        $data = array_merge([
            'tipo' => 'amigos',
            'modalidad' => 'trivia',
            'palabra_clave' => 'mesa-secreta',
            'max_participantes' => 30,
            'tiempo_pregunta' => 20,
            'configuracion' => ['segundos_resultado' => 2],
        ], $overrides);
        if (isset($overrides['segundos_resultado'])) {
            $data['configuracion'] = ['segundos_resultado' => $overrides['segundos_resultado']];
            unset($data['segundos_resultado']);
        }

        return app(LiveTestSessionService::class)->crear($host, $data, [$bank]);
    }

    private function user(string $name, int $collegeId, string $role = Usuario::ROL_ALUMNO): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId,
            'nombre' => $name,
            'email' => $role === Usuario::ROL_ALUMNO ? null : uniqid('teacher-', true).'@example.test',
            'email_verified_at' => $role === Usuario::ROL_ALUMNO ? null : now(),
            'clave' => Hash::make('Clave1234'),
            'rol' => $role,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ]);
    }
}
