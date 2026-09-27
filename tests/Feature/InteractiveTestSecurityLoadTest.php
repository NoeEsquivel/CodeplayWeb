<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestSesion;
use App\Models\TestSesionRespuesta;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestScoringService;
use App\Services\LiveTestSessionService;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InteractiveTestSecurityLoadTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_thirty_students_answer_the_same_question_with_server_order_and_score(): void
    {
        [$college,$teacher,$class]=$this->school('Carga');
        $bank=$this->bank($teacher,'Banco carga');
        $session=$this->classSession($teacher,$class,$bank,30);
        $engine=app(LiveTestEngineService::class);
        $students=[];
        for($i=1;$i<=30;$i++) {
            $student=$this->user(Usuario::ROL_ALUMNO,"Alumno carga {$i} ".uniqid(),$college->id);
            $class->agregarAlumno($student);$engine->unir($student,$session->codigo_sala,null,'clase');$students[]=$student;
        }
        $engine->iniciar($teacher,$session);
        foreach($students as $student) $engine->responder($student,$session->fresh(),'correcta');

        $emission=$session->fresh()->preguntas()->firstOrFail();
        $answers=$emission->respuestas()->orderBy('orden_respuesta')->get();
        $this->assertCount(30,$answers);
        $this->assertSame(range(1,30),$answers->pluck('orden_respuesta')->all());
        $scoring=app(LiveTestScoringService::class);
        foreach($answers as $index=>$answer) {
            $this->assertSame($scoring->puntosPorOrden(true,$index+1,30),$answer->puntos_otorgados);
            $this->assertGreaterThanOrEqual(0,$answer->tiempo_respuesta_ms);
        }
        $this->assertSame('resultado',$emission->fresh()->estado);
    }

    public function test_server_rejects_score_time_duplicate_and_late_answer_manipulation(): void
    {
        [$college,$teacher,$class]=$this->school('Manipulación');
        $first=$this->user(Usuario::ROL_ALUMNO,'Primero '.uniqid(),$college->id);
        $second=$this->user(Usuario::ROL_ALUMNO,'Segundo '.uniqid(),$college->id);
        $class->agregarAlumno($first);$class->agregarAlumno($second);
        $session=$this->classSession($teacher,$class,$this->bank($teacher,'Banco seguro'),2);
        $engine=app(LiveTestEngineService::class);$engine->unir($first,$session->codigo_sala,null,'clase');$engine->unir($second,$session->codigo_sala,null,'clase');$engine->iniciar($teacher,$session);

        $payload=['respuesta'=>'correcta','puntos_otorgados'=>999999,'tiempo_respuesta_ms'=>-500,'orden_respuesta'=>99,'correcta'=>false];
        $this->withSession($this->sessionFor($first))->postJson(route('tests.live.answer',$session),$payload)->assertOk();
        $emission=$session->fresh()->preguntas()->firstOrFail();
        $stored=$emission->respuestas()->whereHas('participante',fn($query)=>$query->where('usuario_id',$first->id))->firstOrFail();
        $this->assertSame(100,$stored->puntos_otorgados);
        $this->assertSame(1,$stored->orden_respuesta);
        $this->assertTrue($stored->correcta);
        $this->assertGreaterThanOrEqual(0,$stored->tiempo_respuesta_ms);
        $this->postJson(route('tests.live.answer',$session),$payload)->assertUnprocessable();
        $this->assertSame(1,$emission->respuestas()->count());

        $emission->forceFill(['cierra_at'=>now()->subSecond()])->save();
        $this->withSession($this->sessionFor($second))->postJson(route('tests.live.answer',$session),$payload)->assertUnprocessable();
        $this->assertSame(1,$emission->respuestas()->count());
        $this->assertSame(0,$session->participantes()->where('usuario_id',$second->id)->value('puntos_test'));
    }

    public function test_roles_colleges_classes_expired_codes_and_reconnection_are_isolated(): void
    {
        [$collegeA,$teacher,$classA]=$this->school('A');
        [$collegeB,$foreignTeacher,$classB]=$this->school('B');
        $studentA=$this->user(Usuario::ROL_ALUMNO,'Alumno A '.uniqid(),$collegeA->id);
        $sameCollegeOtherClass=Clase::create(['colegio_id'=>$collegeA->id,'nombre'=>'Otra clase','codigo'=>'OC-'.uniqid(),'ciclo_escolar'=>'2026','activa'=>true]);
        $studentOtherClass=$this->user(Usuario::ROL_ALUMNO,'Alumno otra clase '.uniqid(),$collegeA->id);
        $foreignStudent=$this->user(Usuario::ROL_ALUMNO,'Alumno B '.uniqid(),$collegeB->id);
        $classA->agregarAlumno($studentA);$sameCollegeOtherClass->agregarAlumno($studentOtherClass);$classB->agregarAlumno($foreignStudent);
        $session=$this->classSession($teacher,$classA,$this->bank($teacher,'Banco aislado'),30);
        $engine=app(LiveTestEngineService::class);
        $joined=$engine->unir($studentA,$session->codigo_sala,null,'clase');
        $this->assertDomainFailure(fn()=>$engine->unir($studentOtherClass,$session->codigo_sala,null,'clase'),'No perteneces a la clase');
        $this->assertDomainFailure(fn()=>$engine->unir($foreignStudent,$session->codigo_sala,null,'clase'),'no pertenece a tu colegio');

        $reconnected=$engine->reconectar($studentA,$session,$joined['reconexion_token']);
        $this->assertNotSame($joined['reconexion_token'],$reconnected['reconexion_token']);
        $this->assertDomainFailure(fn()=>$engine->reconectar($studentA,$session,$joined['reconexion_token']),'no es válido');
        $engine->cerrar($teacher,$session->fresh());
        $this->assertDomainFailure(fn()=>$engine->reconectar($studentA,$session->fresh(),$reconnected['reconexion_token']),'ya fue cerrada');

        $expired=$this->classSession($teacher,$classA,$this->bank($teacher,'Banco vencido'),30,['disponible_hasta'=>now()->subMinute()]);
        $this->assertDomainFailure(fn()=>$engine->unir($studentA,$expired->codigo_sala,null,'clase'),'terminó');

        $admin=$this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO,'Administrador '.uniqid(),$collegeA->id,'admin'.uniqid().'@test.local');
        $master=$this->user(Usuario::ROL_MASTER,'Master '.uniqid(),null,'master'.uniqid().'@test.local');
        $this->withSession($this->sessionFor($studentA))->get(route('alumno.tests'))->assertOk();
        $this->get(route('inicio.profesor'))->assertForbidden();
        $this->withSession($this->sessionFor($teacher))->get(route('profesor.tests.index'))->assertOk();
        $this->get(route('alumno.tests'))->assertForbidden();
        $this->withSession($this->sessionFor($admin))->get(route('inicio.administrador-colegio'))->assertOk();
        $this->postJson(route('tests.live.create'),[])->assertForbidden();
        $this->withSession($this->sessionFor($master))->get(route('inicio.master'))->assertOk();
        $this->postJson(route('tests.live.create'),[])->assertForbidden();
        $this->assertNotSame($foreignTeacher->colegio_id,$teacher->colegio_id);
    }

    public function test_cleanup_closes_abandoned_sessions_and_expires_old_credentials_without_deleting_history(): void
    {
        Carbon::setTestNow('2026-09-27 10:00:00');
        [$college,$teacher,$class]=$this->school('Limpieza');
        $student=$this->user(Usuario::ROL_ALUMNO,'Alumno limpieza '.uniqid(),$college->id);$class->agregarAlumno($student);
        $engine=app(LiveTestEngineService::class);
        $abandoned=$this->classSession($teacher,$class,$this->bank($teacher,'Banco abandonado'),30);
        $join=$engine->unir($student,$abandoned->codigo_sala,null,'clase');
        DB::table('test_sesion_participantes')->where('sesion_id',$abandoned->id)->update(['ultima_actividad_at'=>now()->subHours(30)]);
        DB::table('test_sesiones')->where('id',$abandoned->id)->update(['updated_at'=>now()->subHours(30)]);

        $old=$this->classSession($teacher,$class,$this->bank($teacher,'Banco histórico'),30);
        $oldJoin=$engine->unir($student,$old->codigo_sala,null,'clase');
        $old->forceFill(['estado'=>'cerrada','cerrada_at'=>now()->subDays(400),'updated_at'=>now()->subDays(400)])->save(['timestamps'=>false]);

        $this->artisan('tests:limpiar-sesiones',['--inactividad'=>24,'--retencion'=>365])
            ->expectsOutputToContain('Sesiones abandonadas cerradas: 1')->assertSuccessful();
        $this->assertSame('cerrada',$abandoned->fresh()->estado);
        $this->assertNotNull($abandoned->fresh()->cerrada_at);
        $this->assertNull($old->fresh()->codigo_sala);
        $this->assertNull($old->fresh()->palabra_clave_hash);
        $this->assertNotNull($old->fresh()->archivada_at);
        $this->assertDomainFailure(fn()=>$engine->reconectar($student,$old->fresh(),$oldJoin['reconexion_token']),'ya fue cerrada');
        $this->assertDatabaseHas('test_sesion_participantes',['sesion_id'=>$abandoned->id,'estado'=>'finalizado']);
        $this->assertNotEmpty($join['reconexion_token']);
    }

    public function test_indexes_and_existing_student_modules_remain_available_and_private(): void
    {
        $indexes=collect(DB::select('SHOW INDEX FROM test_sesiones'))->pluck('Key_name');
        foreach(['ts_estado_actualizacion_idx','ts_estado_disponibilidad_idx','ts_aislamiento_clase_idx'] as $index) $this->assertContains($index,$indexes);
        $this->assertContains('tsp_estado_actividad_idx',collect(DB::select('SHOW INDEX FROM test_sesion_participantes'))->pluck('Key_name'));
        $this->assertContains('tsr_resultado_orden_idx',collect(DB::select('SHOW INDEX FROM test_sesion_respuestas'))->pluck('Key_name'));

        [$college]=$this->school('Regresión');
        $student=$this->user(Usuario::ROL_ALUMNO,'Alumno módulos '.uniqid(),$college->id);
        $other=$this->user(Usuario::ROL_ALUMNO,'Alumno privado '.uniqid(),$college->id);
        $this->withSession($this->sessionFor($student));
        $this->get(route('alumno.documentacion'))->assertOk();
        $this->get(route('alumno.documentacion.show','html1'))->assertOk();
        $this->get('/alumno/tutoriales')->assertOk();
        $this->get(route('alumno.desafios'))->assertOk();
        $this->get(route('alumno.aventura'))->assertOk();
        $this->get(route('alumno.progreso'))->assertOk();
        $this->assertDatabaseHas('progreso_documentacion',['usuario_id'=>$student->id,'documento'=>'html1']);
        $this->assertDatabaseMissing('progreso_documentacion',['usuario_id'=>$other->id,'documento'=>'html1']);
    }

    private function school(string $label): array
    {
        $suffix=str_replace('.','',uniqid('',true));$short=substr(hash('sha256',$label.$suffix),0,12);
        $college=Colegio::create(['nombre'=>'Colegio '.$label.' '.$suffix,'codigo'=>'S-'.$short,'activo'=>true]);
        $class=Clase::create(['colegio_id'=>$college->id,'nombre'=>'Clase '.$label,'codigo'=>'C-'.$short,'ciclo_escolar'=>'2026','activa'=>true]);
        $teacher=$this->user(Usuario::ROL_PROFESOR,'Profesor '.$label.' '.$suffix,$college->id,'p'.$suffix.'@test.local');$class->agregarProfesor($teacher);
        return [$college,$teacher,$class];
    }

    private function bank(Usuario $owner,string $name)
    {
        $service=app(TestBankService::class);$bank=$service->crear($owner,['nombre'=>$name,'materia'=>'Seguridad']);
        $service->agregarPregunta($owner,$bank,['tipo'=>'respuesta_directa','enunciado'=>'Respuesta segura','respuestas_correctas'=>['correcta'],'puntos'=>10]);
        $service->publicar($owner,$bank);return $bank->fresh();
    }

    private function classSession(Usuario $teacher,Clase $class,$bank,int $max,array $extra=[]): TestSesion
    {
        $data=array_merge(['tipo'=>'clase','modalidad'=>'trivia','clase_id'=>$class->id,'tiempo_pregunta'=>60,
            'puntaje_objetivo'=>null,'max_participantes'=>$max,'configuracion'=>['orden_aleatorio'=>false,'segundos_resultado'=>2]],$extra);
        $session=app(LiveTestSessionService::class)->crear($teacher,$data,[$bank]);
        return app(LiveTestEngineService::class)->abrirSala($teacher,$session);
    }

    private function user(string $role,string $name,?int $collegeId,?string $email=null): Usuario
    {
        $user=Usuario::create(['colegio_id'=>$collegeId,'nombre'=>$name,'email'=>$email,'email_verified_at'=>$email?now():null,
            'clave'=>Hash::make('Clave1234'),'rol'=>$role,'activo'=>true,'cambio_clave_requerido'=>false]);
        if($role===Usuario::ROL_MASTER)$user->twoFactorAuthentication()->create([
            'secret'=>'JBSWY3DPEHPK3PXP','recovery_codes'=>['codigo-prueba'],'confirmed_at'=>now(),
        ]);
        return $user;
    }

    private function sessionFor(Usuario $user): array
    {
        $data=['usuario_id'=>$user->id,'usuario_session_version'=>$user->fresh()->session_version];
        if($user->esMaster())$data['two_factor_verified_user_id']=$user->id;
        return $data;
    }

    private function assertDomainFailure(callable $call,string $fragment): void
    {
        try{$call();$this->fail('La operación insegura fue aceptada.');}
        catch(\DomainException $exception){$this->assertStringContainsStringIgnoringCase($fragment,$exception->getMessage());}
    }
}
