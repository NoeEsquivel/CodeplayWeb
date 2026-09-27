<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestHistorialRespuesta;
use App\Models\TestResultadoSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestSessionService;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherGradebookTest extends TestCase
{
    use DatabaseTransactions;

    public function test_exam_history_keeps_written_answers_scores_and_printable_detail(): void
    {
        [$teacher,$student,$class,$bank]=$this->scenario();
        $questions=$bank->preguntas()->orderBy('orden')->get();
        $session=$this->classSession($teacher,$class,$bank,'examen','Examen imprimible',[
            'puntos_pregunta'=>[(string)$questions[0]->id=>3,(string)$questions[1]->id=>7],
        ]);
        $engine=app(LiveTestEngineService::class);
        $engine->unir($student,$session->codigo_sala,null,'clase');
        $engine->iniciar($teacher,$session);
        $emissions=$session->fresh()->preguntas()->orderBy('orden')->get();

        $this->withSession($this->sessionFor($student))
            ->post(route('alumno.tests.clase.examen.save',[$session,$emissions[0]]),['respuesta'=>'correcta'])->assertRedirect();
        $this->post(route('alumno.tests.clase.examen.save',[$session,$emissions[1]]),['respuesta'=>'respuesta escrita incorrecta'])->assertRedirect();
        $this->post(route('alumno.tests.clase.examen.submit',$session))->assertRedirect();

        $result=TestResultadoSesion::where('sesion_id',$session->id)->where('usuario_id',$student->id)->firstOrFail();
        $this->assertSame('30.00',$result->calificacion);
        $this->assertSame(1,$result->correctas);
        $this->assertSame(1,$result->incorrectas);
        $this->assertSame(2,TestHistorialRespuesta::where('sesion_id',$session->id)->where('usuario_id',$student->id)->count());

        $this->withSession($this->sessionFor($teacher))->get(route('profesor.calificaciones.detail',$result))
            ->assertOk()->assertSee('Examen imprimible')->assertSee($student->nombre)
            ->assertSee('respuesta escrita incorrecta')->assertSee('30.00')->assertSee('Respuesta correcta');
    }

    public function test_reports_filters_exports_and_prints_only_the_teachers_class(): void
    {
        [$teacher,$student,$class,$bank]=$this->scenario();
        $session=$this->classSession($teacher,$class,$bank,'examen','Actividad propia');
        $participant=app(LiveTestEngineService::class)->unir($student,$session->codigo_sala,null,'clase')['participante'];
        $own=TestResultadoSesion::create($this->resultData($session,$participant,$student,'Actividad propia'));

        $foreignCollege=Colegio::create(['nombre'=>'Colegio ajeno '.uniqid(),'codigo'=>'X-'.uniqid(),'activo'=>true]);
        $foreignTeacher=$this->user('Profesor ajeno '.uniqid(),Usuario::ROL_PROFESOR,$foreignCollege->id,'ajeno'.uniqid().'@test.local');
        $foreignStudent=$this->user('Alumno secreto '.uniqid(),Usuario::ROL_ALUMNO,$foreignCollege->id,null);
        $foreignClass=Clase::create(['colegio_id'=>$foreignCollege->id,'nombre'=>'Clase secreta','codigo'=>'Z-'.uniqid(),'ciclo_escolar'=>'2026','activa'=>true]);
        $foreignClass->agregarProfesor($foreignTeacher);$foreignClass->agregarAlumno($foreignStudent);
        $foreignBank=$this->bank($foreignTeacher,'Banco secreto');
        $foreignSession=$this->classSession($foreignTeacher,$foreignClass,$foreignBank,'examen','Actividad secreta');
        $foreignParticipant=app(LiveTestEngineService::class)->unir($foreignStudent,$foreignSession->codigo_sala,null,'clase')['participante'];
        $foreignResult=TestResultadoSesion::create($this->resultData($foreignSession,$foreignParticipant,$foreignStudent,'Actividad secreta'));

        $teacherSession=$this->sessionFor($teacher);
        $this->withSession($teacherSession)->get(route('profesor.calificaciones.index',['clase_id'=>$class->id,'dimension'=>'alumno']))
            ->assertOk()->assertSee('Actividad propia')->assertSee($student->nombre)
            ->assertDontSee('Actividad secreta')->assertDontSee($foreignStudent->nombre);
        $this->get(route('profesor.calificaciones.print-class',['clase_id'=>$class->id]))
            ->assertOk()->assertSee($class->nombre)->assertSee('Actividad propia')->assertDontSee('Actividad secreta');
        $this->get(route('profesor.calificaciones.export',['clase_id'=>$class->id]))
            ->assertOk()->assertHeader('content-type','text/csv; charset=UTF-8')
            ->assertSee('Actividad propia')->assertDontSee('Actividad secreta');
        $this->get(route('profesor.calificaciones.detail',$foreignResult))->assertNotFound();
        $this->assertNotNull($own);
    }

    public function test_archiving_and_repeating_preserve_previous_attempt_history(): void
    {
        [$teacher,$student,$class,$bank]=$this->scenario();
        $session=$this->classSession($teacher,$class,$bank,'examen','Examen repetible');
        $engine=app(LiveTestEngineService::class);$engine->unir($student,$session->codigo_sala,null,'clase');$engine->iniciar($teacher,$session);
        $emission=$session->fresh()->preguntas()->orderBy('orden')->firstOrFail();
        $this->withSession($this->sessionFor($student))->post(route('alumno.tests.clase.examen.save',[$session,$emission]),['respuesta'=>'correcta'])->assertRedirect();
        $this->post(route('alumno.tests.clase.examen.submit',$session))->assertRedirect();
        $engine->cerrar($teacher,$session->fresh());
        $oldCount=TestHistorialRespuesta::where('sesion_id',$session->id)->where('intento',1)->count();
        $this->assertGreaterThan(0,$oldCount);

        $engine->repetir($teacher,$session->fresh(),true);
        $this->assertSame($oldCount,TestHistorialRespuesta::where('sesion_id',$session->id)->where('intento',1)->count());
        $this->assertDatabaseHas('test_historial_respuestas',['sesion_id'=>$session->id,'intento'=>1,'correcta'=>false,'respuesta_alumno'=>null]);
        $this->assertSame(2,(int)$session->fresh()->configuracion['intento_actual']);

        $this->withSession($this->sessionFor($teacher))->post(route('profesor.calificaciones.archive',$session))->assertRedirect();
        $this->assertNotNull($session->fresh()->archivada_at);
        $this->post(route('profesor.calificaciones.restore',$session))->assertRedirect();
        $this->assertNull($session->fresh()->archivada_at);
        $engine->cerrar($teacher,$session->fresh());
        $this->delete(route('profesor.calificaciones.destroy',$session))->assertRedirect(route('profesor.calificaciones.index'));
        $this->assertNotNull($session->fresh()->eliminada_at);
        $this->assertSame($oldCount,TestHistorialRespuesta::where('sesion_id',$session->id)->where('intento',1)->count());
    }

    private function scenario(): array
    {
        $s=str_replace('.','',uniqid('',true));$college=Colegio::create(['nombre'=>'Colegio '.$s,'codigo'=>'G-'.$s,'activo'=>true]);
        $class=Clase::create(['colegio_id'=>$college->id,'nombre'=>'Cuarto A','codigo'=>'C-'.$s,'ciclo_escolar'=>'2026','activa'=>true]);
        $teacher=$this->user('Profesor '.$s,Usuario::ROL_PROFESOR,$college->id,'p'.$s.'@test.local');
        $student=$this->user('Alumno '.$s,Usuario::ROL_ALUMNO,$college->id,null);
        $class->agregarProfesor($teacher);$class->agregarAlumno($student);
        return [$teacher,$student,$class,$this->bank($teacher,'Banco '.$s)];
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

    private function resultData($session,$participant,Usuario $student,string $title): array
    {
        return ['sesion_id'=>$session->id,'participante_id'=>$participant->id,'intento'=>1,'usuario_id'=>$student->id,
            'colegio_id'=>$session->colegio_id,'clase_id'=>$session->clase_id,'nombre_alumno'=>$student->nombre,
            'modalidad'=>$session->modalidad,'total_preguntas'=>2,'correctas'=>1,'incorrectas'=>1,'calificacion'=>50,
            'puntos_test'=>5,'duracion_segundos'=>45,'estado'=>'entregado','iniciada_at'=>now()->subSeconds(45),'entregada_at'=>now()];
    }

    private function user(string $name,string $role,int $college,?string $email): Usuario
    {return Usuario::create(['colegio_id'=>$college,'nombre'=>$name,'email'=>$email,'email_verified_at'=>$email?now():null,'clave'=>Hash::make('Clave1234'),'rol'=>$role,'activo'=>true,'cambio_clave_requerido'=>false]);}
    private function sessionFor(Usuario $user): array{return ['usuario_id'=>$user->id,'usuario_session_version'=>$user->fresh()->session_version];}
}
