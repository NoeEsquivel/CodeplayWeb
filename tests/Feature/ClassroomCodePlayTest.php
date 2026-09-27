<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestResultadoSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestSessionService;
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
        $questions=$bank->preguntas()->orderBy('orden')->get();
        $session=$this->classSession($teacher,$class,$bank,'examen','Examen con puntaje',[
            'duracion_examen_minutos'=>60,'puntos_pregunta'=>[(string)$questions[0]->id=>3,(string)$questions[1]->id=>7],
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
        $this->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertSee('La calificación permanecerá oculta');
        $this->post(route('alumno.tests.clase.examen.submit',$session))->assertRedirect();

        $result=TestResultadoSesion::where('sesion_id',$session->id)->where('usuario_id',$student->id)->firstOrFail();
        $this->assertSame('entregado',$result->estado);
        $this->assertSame('30.00',$result->calificacion);
        $this->assertSame(3,$result->puntos_test);
        $this->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertSee('30.00')->assertSee('Examen entregado');
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
        $this->withSession($this->sessionFor($student))->get(route('alumno.tests.clase.examen',$session))->assertOk();
        Carbon::setTestNow(now()->addMinutes(2));
        $this->get(route('alumno.tests.clase.examen',$session))->assertOk()->assertSee('Examen entregado');
        $this->assertDatabaseHas('test_resultados_sesion',['sesion_id'=>$session->id,'usuario_id'=>$student->id,'estado'=>'entregado']);
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
