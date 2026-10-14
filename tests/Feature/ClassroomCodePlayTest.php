<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestResultadoSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestSessionService;
use App\Services\ClassroomExamLifecycleService;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClassroomCodePlayTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }

    public function test_student_sees_only_active_sessions_from_own_classes_with_teacher_and_activity(): void
    {
        [$teacher,$student,$class,$outsider,$foreignClass,$bank]=$this->scenario();
        $own=$this->classSession($teacher,$class,$bank,'trivia','Trivia HTML');
        $foreignBank=$this->bank($outsider,'Banco ajeno');
        $foreign=$this->classSession($outsider,$foreignClass,$foreignBank,'trivia','Actividad secreta');

        $this->withSession($this->sessionFor($student))->get(route('alumno.tests.clase'))
            ->assertOk()->assertSee('Trivia HTML')->assertSee($class->nombre)->assertSee($teacher->nombre)
            ->assertDontSee('Actividad secreta');
        $this->post(route('alumno.tests.clase.enter',$foreign))->assertNotFound();
        $this->get(route('alumno.tests.clase.trivia',$foreign))->assertNotFound();
    }

    public function test_exam_saves_individual_progress_hides_grade_until_submit_and_uses_teacher_points(): void
    {
        [$teacher,$student,$class,,, $bank]=$this->scenario();
        $second=$this->user('Alumno pendiente '.uniqid(),Usuario::ROL_ALUMNO,$teacher->colegio_id,null);$class->agregarAlumno($second);
        $questions=$bank->preguntas()->orderBy('orden')->get();
        $session=$this->classSession($teacher,$class,$bank,'examen','Examen con puntaje',[
            'duracion_examen_minutos'=>60,'resumen_disponible_minutos'=>60,'puntos_pregunta'=>[(string)$questions[0]->id=>3,(string)$questions[1]->id=>7],
        ]);
        $engine=app(LiveTestEngineService::class);
        $engine->unir($student,$session->codigo_sala,null,'clase');
        $this->withSession($this->sessionFor($teacher))->post(route('profesor.tests.sessions.action',[$session,'start']))->assertRedirect();
        $session->refresh();
        $this->assertSame([3,7],$session->preguntas()->orderBy('orden')->pluck('puntos_maximos')->all());

        $this->withSession($this->sessionFor($student))->get(route('alumno.tests.clase.examen',$session))
            ->assertOk()->assertSee($questions[0]->enunciado)->assertDontSee('Calificación final');
        $emissions=$session->preguntas()->orderBy('orden')->get();
        $this->post(route('alumno.tests.clase.examen.save',[$session,$emissions[0]]),['respuesta'=>'correcta'])->assertRedirect();
        $this->post(route('alumno.tests.clase.examen.save',[$session,$emissions[1]]),['respuesta'=>'incorrecta'])->assertRedirect();
        $this->get(route('alumno.tests.clase.examen',['sesion'=>$session,'pregunta'=>$emissions[0]->id]))
            ->assertOk()->assertSee('Actualizar respuesta')->assertSee('Siguiente')->assertSee('value="correcta"',false);
        $this->post(route('alumno.tests.clase.examen.save',[$session,$emissions[0]]),['respuesta'=>'incorrecta'])->assertRedirect();
        $this->assertDatabaseHas('test_sesion_respuestas',['sesion_pregunta_id'=>$emissions[0]->id,'correcta'=>false]);
        $this->post(route('alumno.tests.clase.examen.save',[$session,$emissions[0]]),['respuesta'=>'correcta'])->assertRedirect();
        $this->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertSee('calificación permanecerá oculta');
        $this->post(route('alumno.tests.clase.examen.submit',$session))->assertRedirect();

        $result=TestResultadoSesion::where('sesion_id',$session->id)->where('usuario_id',$student->id)->firstOrFail();
        $this->assertSame('entregado',$result->estado);
        $this->assertSame('30.00',$result->calificacion);
        $this->assertSame(3,$result->puntos_test);
        $this->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertDontSee('30.00')->assertSee('Resultados protegidos hasta el cierre')->assertSee('¡Entrega completada!');
        $this->get(route('alumno.tests.clase.examen.summary',$session))->assertNotFound();

        $this->withSession($this->sessionFor($teacher))->post(route('profesor.tests.sessions.action',[$session,'finish']))->assertRedirect();
        $this->withSession($this->sessionFor($student))->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertSee('30.00')->assertSee('Ver resumen de mis respuestas');
        $this->get(route('alumno.tests.clase.examen.summary',$session))->assertOk()->assertSee('Pregunta uno')->assertSee('Correcta')->assertSee('Pregunta dos')->assertSee('Incorrecta');

        Carbon::setTestNow($session->fresh()->cerrada_at->copy()->addMinutes(61));
        $this->get(route('alumno.tests.clase.examen.summary',$session))->assertNotFound();
    }

    public function test_scheduled_exam_starts_and_closes_automatically(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-27 08:00:00'));
        [$teacher,,$class,,, $bank]=$this->scenario();
        $bank->clasesAsignadas()->syncWithoutDetaching([$class->id=>['asignado_por_usuario_id'=>$teacher->id,'activo'=>true]]);
        $session=app(LiveTestSessionService::class)->crear($teacher,[
            'tipo'=>'clase','modalidad'=>'examen','clase_id'=>$class->id,'max_participantes'=>30,
            'disponible_desde'=>now()->addMinutes(10),'disponible_hasta'=>now()->addMinutes(40),
            'configuracion'=>['titulo'=>'Examen programado','orden_aleatorio'=>false,'resumen_disponible_minutos'=>60],
        ],[$bank]);
        app(LiveTestEngineService::class)->abrirSala($teacher,$session);
        $lifecycle=app(ClassroomExamLifecycleService::class);

        $this->assertSame('espera',$lifecycle->synchronize($session)->estado);
        Carbon::setTestNow(now()->addMinutes(10));
        $this->assertSame('activa',$lifecycle->synchronize($session)->estado);
        Carbon::setTestNow(now()->addMinutes(30));
        $this->assertSame('cerrada',$lifecycle->synchronize($session)->estado);
    }

    public function test_exam_closes_when_every_student_in_the_class_has_submitted(): void
    {
        [$teacher,$student,$class,,, $bank]=$this->scenario();
        $second=$this->user('Último alumno '.uniqid(),Usuario::ROL_ALUMNO,$teacher->colegio_id,null);$class->agregarAlumno($second);
        $session=$this->classSession($teacher,$class,$bank,'examen','Cierre por entrega total',['resumen_disponible_minutos'=>60]);
        $engine=app(LiveTestEngineService::class);$engine->iniciar($teacher,$session);
        $engine->unir($student,$session->codigo_sala,null,'clase');$engine->unir($second,$session->codigo_sala,null,'clase');

        app(\App\Services\ClassroomExamService::class)->submit($student,$session->fresh());
        app(ClassroomExamLifecycleService::class)->synchronize($session->fresh());
        $this->assertSame('activa',$session->fresh()->estado);
        app(\App\Services\ClassroomExamService::class)->submit($second,$session->fresh());
        app(ClassroomExamLifecycleService::class)->synchronize($session->fresh());
        $this->assertSame(2,$session->participantes()->where('estado','finalizado')->count());
        $this->assertSame(2,$class->alumnos()->where('usuarios.activo',true)->count());
        $this->assertSame('cerrada',$session->fresh()->estado);
        $this->assertNotNull($session->fresh()->cerrada_at);
    }

    public function test_active_exam_allows_late_enrolled_join_and_keeps_each_students_answers_separate(): void
    {
        [$teacher,$student,$class,,, $bank]=$this->scenario();
        $second=$this->user('Segundo '.uniqid(),Usuario::ROL_ALUMNO,$teacher->colegio_id,null); $class->agregarAlumno($second);
        $session=$this->classSession($teacher,$class,$bank,'examen','Examen activo');
        $engine=app(LiveTestEngineService::class); $engine->iniciar($teacher,$session);
        $engine->unir($student,$session->codigo_sala,null,'clase');
        $engine->unir($second,$session->codigo_sala,null,'clase');
        $this->assertSame(2,$session->participantes()->count());
        $this->withSession($this->sessionFor($student))->get(route('alumno.tests.clase.examen',$session))->assertOk();
        $this->withSession($this->sessionFor($second))->get(route('alumno.tests.clase.examen',$session))->assertOk();
    }

    public function test_exam_is_submitted_automatically_when_individual_deadline_expires(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-26 10:00:00'));
        [$teacher,$student,$class,,, $bank]=$this->scenario();
        $session=$this->classSession($teacher,$class,$bank,'examen','Examen cronometrado',['duracion_examen_minutos'=>1]);
        $engine=app(LiveTestEngineService::class);$engine->iniciar($teacher,$session);$engine->unir($student,$session->codigo_sala,null,'clase');
        $this->withSession($this->sessionFor($student))->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertSee('Tiempo restante');
        Carbon::setTestNow(now()->addMinutes(2));
        $this->artisan('tests:sincronizar-examenes')->assertSuccessful();
        $this->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertSee('Examen entregado');
        $this->assertDatabaseHas('test_resultados_sesion',['sesion_id'=>$session->id,'usuario_id'=>$student->id,'estado'=>'entregado','correctas'=>0,'incorrectas'=>2]);
        $this->get(route('alumno.tests.clase.examen.summary',$session))->assertOk()->assertSee('Sin respuesta')->assertSee('Incorrecta');
    }

    public function test_exam_countdown_uses_the_earliest_of_individual_duration_and_scheduled_close(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-27 13:30:00'));
        [$teacher,$student,$class,,, $bank]=$this->scenario();
        $bank->clasesAsignadas()->syncWithoutDetaching([$class->id=>['asignado_por_usuario_id'=>$teacher->id,'activo'=>true]]);

        $withoutIndividualLimit=app(LiveTestSessionService::class)->crear($teacher,[
            'tipo'=>'clase','modalidad'=>'examen','clase_id'=>$class->id,'max_participantes'=>30,
            'disponible_hasta'=>now()->addMinutes(30),
            'configuracion'=>['titulo'=>'Cierra a las dos','orden_aleatorio'=>false],
        ],[$bank]);
        $engine=app(LiveTestEngineService::class);$engine->abrirSala($teacher,$withoutIndividualLimit);$engine->iniciar($teacher,$withoutIndividualLimit);
        $engine->unir($student,$withoutIndividualLimit->codigo_sala,null,'clase');
        $this->withSession($this->sessionFor($student))->get(route('alumno.tests.clase.examen',$withoutIndividualLimit))
            ->assertOk()->assertSee('Tiempo restante')->assertSee('2026-09-27T14:00:00',false);
        $this->assertSame('2026-09-27 14:00:00',$withoutIndividualLimit->participantes()->where('usuario_id',$student->id)->firstOrFail()->examen_limite_at->format('Y-m-d H:i:s'));

        $oneHourButClosesSooner=app(LiveTestSessionService::class)->crear($teacher,[
            'tipo'=>'clase','modalidad'=>'examen','clase_id'=>$class->id,'max_participantes'=>30,
            'disponible_hasta'=>now()->addMinutes(30),
            'configuracion'=>['titulo'=>'Una hora con cierre cercano','orden_aleatorio'=>false,'duracion_examen_minutos'=>60],
        ],[$bank]);
        $engine->abrirSala($teacher,$oneHourButClosesSooner);$engine->iniciar($teacher,$oneHourButClosesSooner);
        $engine->unir($student,$oneHourButClosesSooner->codigo_sala,null,'clase');
        $this->get(route('alumno.tests.clase.examen',$oneHourButClosesSooner))
            ->assertOk()->assertSee('Tiempo restante')->assertSee('2026-09-27T14:00:00',false);
        $this->assertSame('2026-09-27 14:00:00',$oneHourButClosesSooner->participantes()->where('usuario_id',$student->id)->firstOrFail()->examen_limite_at->format('Y-m-d H:i:s'));
    }

    public function test_student_can_join_and_reopen_an_available_trivia_from_the_classroom_screen(): void
    {
        [$teacher, $student, $class, , , $bank] = $this->scenario();
        $session = $this->classSession($teacher, $class, $bank, 'trivia', 'Trivia para ingresar');

        $studentSession = $this->sessionFor($student);
        $this->withSession($studentSession)
            ->post(route('alumno.tests.clase.enter', $session))
            ->assertRedirect(route('alumno.tests.clase.trivia', $session));

        $this->assertDatabaseHas('test_sesion_participantes', [
            'sesion_id' => $session->id,
            'usuario_id' => $student->id,
        ]);
        $this->assertNotNull(session("live_test_tokens.{$session->id}"));

        $this->withSession($studentSession)
            ->get(route('alumno.tests.clase.trivia', $session))
            ->assertOk()
            ->assertSee('Trivia para ingresar');

        $this->withSession($studentSession)
            ->post(route('alumno.tests.clase.enter', $session))
            ->assertRedirect(route('alumno.tests.clase.trivia', $session));
        $this->assertSame(1, $session->participantes()->where('usuario_id', $student->id)->count());

        $session->forceFill(['disponible_desde' => now()->addHour()])->save();
        $this->withSession($studentSession)
            ->post(route('alumno.tests.clase.enter', $session))
            ->assertNotFound();

        $session->forceFill(['disponible_desde' => null, 'modalidad' => 'examen'])->save();
        $this->withSession($studentSession)
            ->get(route('alumno.tests.clase.trivia', $session))
            ->assertNotFound();
    }
    public function test_exam_service_rejects_foreign_questions_invalid_states_and_duplicate_submissions(): void
    {
        [$teacher, $student, $class, , , $bank] = $this->scenario();
        $session = $this->classSession($teacher, $class, $bank, 'examen', 'Validaciones del examen');
        $engine = app(LiveTestEngineService::class);
        $engine->unir($student, $session->codigo_sala, null, 'clase');
        $service = app(\App\Services\ClassroomExamService::class);

        $waiting = $service->estado($student, $session->fresh());
        $this->assertNull($waiting['pregunta']);
        $this->assertFalse($waiting['entregado']);

        $engine->iniciar($teacher, $session);
        $session->refresh();
        $foreignSession = $this->classSession($teacher, $class, $bank, 'examen', 'Otro examen');
        $engine->iniciar($teacher, $foreignSession);
        $foreignEmission = $foreignSession->preguntas()->firstOrFail();

        try {
            $service->estado($student, $session, $foreignEmission->id);
            $this->fail('Debió rechazar una pregunta perteneciente a otro examen.');
        } catch (\DomainException $exception) {
            $this->assertSame('La pregunta seleccionada no pertenece a este examen.', $exception->getMessage());
        }

        try {
            $service->save($student, $session, $foreignEmission, 'respuesta');
            $this->fail('Debió rechazar una emisión perteneciente a otra sesión.');
        } catch (\DomainException $exception) {
            $this->assertSame('La pregunta no pertenece a este examen.', $exception->getMessage());
        }

        try {
            $service->estado($teacher, $session);
            $this->fail('Debió rechazar al profesor como participante alumno.');
        } catch (\DomainException $exception) {
            $this->assertSame('No perteneces a la clase de este examen.', $exception->getMessage());
        }

        $result = $service->submit($student, $session);
        $this->assertSame($result->id, $service->submit($student, $session)->id);

        try {
            $service->save($student, $session->fresh(), $session->preguntas()->firstOrFail(), 'respuesta');
            $this->fail('Debió impedir cambios después de la entrega.');
        } catch (\DomainException $exception) {
            $this->assertSame('El examen ya fue entregado.', $exception->getMessage());
        }

        $service->submitPending($foreignSession->forceFill(['modalidad' => 'trivia']));
        $this->assertSame('activa', $foreignSession->fresh()->estado);
    }
    public function test_classroom_controller_handles_exam_navigation_join_errors_and_missing_participation(): void
    {
        [$teacher, $student, $class, , , $bank] = $this->scenario();
        $studentSession = $this->sessionFor($student);
        $engine = app(LiveTestEngineService::class);
        $exam = $this->classSession($teacher, $class, $bank, 'examen', 'Examen por controlador');

        $this->withSession($studentSession)
            ->get(route('alumno.tests.clase'))
            ->assertOk()
            ->assertSee('Examen por controlador');
        $this->post(route('alumno.tests.clase.enter', $exam))
            ->assertRedirect(route('alumno.tests.clase.examen', $exam));
        $engine->iniciar($teacher, $exam);
        $emissions = $exam->fresh()->preguntas()->orderBy('orden')->get();

        $this->get(route('alumno.tests.clase.examen', ['sesion' => $exam, 'pregunta' => 999999999]))
            ->assertNotFound();
        $this->post(route('alumno.tests.clase.examen.save', [$exam, $emissions[0]]), [
            'respuesta' => 'correcta',
            'destino' => $emissions[1]->id,
        ])->assertRedirect(route('alumno.tests.clase.examen', [
            'sesion' => $exam,
            'pregunta' => $emissions[1]->id,
        ]));

        $notJoined = $this->classSession($teacher, $class, $bank, 'examen', 'Sin participación');
        $engine->iniciar($teacher, $notJoined);
        $this->post(route('alumno.tests.clase.examen.submit', $notJoined))
            ->assertRedirect()->assertSessionHas('error', 'Primero debes ingresar a la sesión.');

        $second = $this->user('Alumno que ocupa la sala '.uniqid(), Usuario::ROL_ALUMNO, $teacher->colegio_id, null);
        $class->agregarAlumno($second);
        $full = $this->classSession($teacher, $class, $bank, 'trivia', 'Sala completa');
        $full->forceFill(['max_participantes' => 1])->save();
        $engine->unir($second, $full->codigo_sala, null, 'clase');
        $this->post(route('alumno.tests.clase.enter', $full))
            ->assertRedirect()->assertSessionHas('error');
    }
    public function test_live_polling_is_limited_per_account_instead_of_shared_ip(): void
    {
        [$teacher,$student,$class,,, $bank]=$this->scenario();
        $second=$this->user('Segundo sondeo '.uniqid(),Usuario::ROL_ALUMNO,$teacher->colegio_id,null);$class->agregarAlumno($second);
        $session=$this->classSession($teacher,$class,$bank,'trivia','Sondeo simultáneo');
        $engine=app(LiveTestEngineService::class);$engine->unir($student,$session->codigo_sala,null,'clase');$engine->unir($second,$session->codigo_sala,null,'clase');
        for($i=0;$i<65;$i++) {
            $this->withSession($this->sessionFor($student))->getJson(route('tests.live.state',$session))->assertOk();
            $this->withSession($this->sessionFor($second))->getJson(route('tests.live.state',$session))->assertOk();
        }
    }

    private function scenario(): array
    {
        $s=str_replace('.','',uniqid('',true));$college=Colegio::create(['nombre'=>'Colegio '.$s,'codigo'=>'CC-'.$s,'activo'=>true]);
        $other=Colegio::create(['nombre'=>'Otro '.$s,'codigo'=>'CO-'.$s,'activo'=>true]);
        $class=Clase::create(['colegio_id'=>$college->id,'nombre'=>'Primero A','codigo'=>'A-'.$s,'ciclo_escolar'=>'2026','activa'=>true]);
        $foreign=Clase::create(['colegio_id'=>$other->id,'nombre'=>'Clase ajena','codigo'=>'B-'.$s,'ciclo_escolar'=>'2026','activa'=>true]);
        $teacher=$this->user('Profesor '.$s,Usuario::ROL_PROFESOR,$college->id,'p'.$s.'@test.local');
        $outsider=$this->user('Profesor ajeno '.$s,Usuario::ROL_PROFESOR,$other->id,'o'.$s.'@test.local');
        $student=$this->user('Alumno '.$s,Usuario::ROL_ALUMNO,$college->id,null);
        $class->agregarProfesor($teacher);$class->agregarAlumno($student);$foreign->agregarProfesor($outsider);
        return [$teacher,$student,$class,$outsider,$foreign,$this->bank($teacher,'Banco de clase')];
    }

    private function bank(Usuario $teacher,string $name)
    {
        $service=app(TestBankService::class);$bank=$service->crear($teacher,['nombre'=>$name,'materia'=>'HTML']);
        $service->agregarPregunta($teacher,$bank,['tipo'=>'respuesta_directa','enunciado'=>'Pregunta uno','respuestas_correctas'=>['correcta'],'puntos'=>3]);
        $service->agregarPregunta($teacher,$bank,['tipo'=>'respuesta_directa','enunciado'=>'Pregunta dos','respuestas_correctas'=>['correcta'],'puntos'=>7]);
        $service->publicar($teacher,$bank);return $bank->fresh();
    }

    private function classSession(Usuario $teacher,Clase $class,$bank,string $mode,string $title,array $extra=[])
    {
        $bank->clasesAsignadas()->syncWithoutDetaching([$class->id=>['asignado_por_usuario_id'=>$teacher->id,'activo'=>true]]);
        $session=app(LiveTestSessionService::class)->crear($teacher,['tipo'=>'clase','modalidad'=>$mode,'clase_id'=>$class->id,
            'tiempo_pregunta'=>20,'puntaje_objetivo'=>$mode==='trivia'?100:null,'max_participantes'=>30,
            'configuracion'=>array_merge(['titulo'=>$title,'orden_aleatorio'=>false,'segundos_resultado'=>2],$extra)],[$bank]);
        app(LiveTestEngineService::class)->abrirSala($teacher,$session);return $session->fresh();
    }

    private function user(string $name,string $role,int $college,?string $email): Usuario
    {return Usuario::create(['colegio_id'=>$college,'nombre'=>$name,'email'=>$email,'email_verified_at'=>$email?now():null,'clave'=>Hash::make('Clave1234'),'rol'=>$role,'activo'=>true,'cambio_clave_requerido'=>false]);}
    private function sessionFor(Usuario $user): array{return ['usuario_id'=>$user->id,'usuario_session_version'=>$user->fresh()->session_version];}
}
