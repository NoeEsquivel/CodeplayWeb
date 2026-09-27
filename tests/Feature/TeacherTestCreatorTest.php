<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherTestCreatorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_teacher_manages_own_banks_and_cannot_edit_another_teachers_bank(): void
    {
        [$teacher, $other] = $this->scenario();
        $this->withSession($this->sessionFor($teacher))
            ->get(route('profesor.tests.index'))->assertOk()->assertSee('Tests y evaluaciones');

        $this->post(route('profesor.tests.banks.store'), [
            'nombre' => 'Banco del profesor', 'materia' => 'HTML', 'descripcion' => 'Repaso',
        ])->assertRedirect();
        $own = TestBanco::where('creador_usuario_id', $teacher->id)->latest('id')->firstOrFail();
        $this->assertSame('profesor', $own->origen);

        $system=TestBanco::create(['colegio_id'=>null,'creador_usuario_id'=>null,'origen'=>'sistema','categoria'=>'html',
            'materia'=>'HTML','estado'=>'publicado','clave_sistema'=>'teacher-'.$own->id,'nombre'=>'Oficial','activo'=>true]);
        $officialQuestion=$system->preguntas()->create(['tipo'=>'respuesta_directa','enunciado'=>'Pregunta oficial',
            'respuestas_correctas'=>['respuesta'],'tiempo_limite'=>20,'orden'=>1,'activa'=>true]);
        $this->post(route('profesor.tests.questions.import-system',$own),['pregunta_ids'=>[$officialQuestion->id]])->assertRedirect();
        $this->assertDatabaseHas('test_banco_preguntas',['banco_id'=>$own->id,'enunciado'=>'Pregunta oficial']);
        $this->assertSame('Pregunta oficial',$officialQuestion->fresh()->enunciado);

        $foreign = app(TestBankService::class)->crear($other, ['nombre' => 'Privado', 'materia' => 'CSS']);
        $this->get(route('profesor.tests.banks.edit', $foreign))->assertNotFound();
        $this->put(route('profesor.tests.banks.update', $foreign), ['nombre'=>'Intrusión','materia'=>'CSS'])->assertNotFound();
        $this->assertSame('Privado', $foreign->fresh()->nombre);
    }

    public function test_teacher_assigns_banks_only_to_own_classes_and_creates_scheduled_session(): void
    {
        [$teacher, , $ownClass, $foreignClass] = $this->scenario();
        $service = app(TestBankService::class);
        $bank = $service->crear($teacher, ['nombre'=>'Evaluación docente','materia'=>'JavaScript']);
        $service->agregarPregunta($teacher, $bank, ['tipo'=>'respuesta_directa','enunciado'=>'¿Resultado?','respuestas_correctas'=>['42']]);
        $service->publicar($teacher, $bank);
        $this->withSession($this->sessionFor($teacher));

        $this->post(route('profesor.tests.banks.assign', $bank), ['clase_ids'=>[$foreignClass->id]])->assertNotFound();
        $this->post(route('profesor.tests.banks.assign', $bank), [
            'clase_ids'=>[$ownClass->id], 'disponible_desde'=>now()->subMinute()->format('Y-m-d H:i:s'),
            'disponible_hasta'=>now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect();
        $this->assertDatabaseHas('test_banco_clase', ['banco_id'=>$bank->id,'clase_id'=>$ownClass->id,'asignado_por_usuario_id'=>$teacher->id]);

        $this->post(route('profesor.tests.sessions.store'), [
            'titulo'=>'Examen programado','clase_id'=>$ownClass->id, 'banco_ids'=>[$bank->id], 'modalidad'=>'examen',
            'duracion_examen_minutos'=>45,'puntos_pregunta'=>[$bank->preguntas()->firstOrFail()->id=>12],
            'disponible_desde'=>now()->subMinute()->format('Y-m-d H:i:s'), 'disponible_hasta'=>now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect();
        $session = TestSesion::where('anfitrion_usuario_id',$teacher->id)->latest('id')->firstOrFail();
        $this->assertSame('examen', $session->modalidad);
        $this->assertSame('espera', $session->estado);
        $this->assertSame($ownClass->id, $session->clase_id);
        $this->assertSame(45,$session->configuracion['duracion_examen_minutos']);
        $this->assertSame(12,$session->configuracion['puntos_pregunta'][(string)$bank->preguntas()->firstOrFail()->id]);

        $this->post(route('profesor.tests.sessions.store'), [
            'titulo'=>'Trivia ajena','clase_id'=>$foreignClass->id, 'banco_ids'=>[$bank->id], 'modalidad'=>'trivia', 'tiempo_pregunta'=>20,
        ])->assertNotFound();
    }

    public function test_only_students_from_selected_class_can_join_and_teacher_cannot_monitor_foreign_session(): void
    {
        [$teacher, $other, $ownClass, $foreignClass, $student, $outsider] = $this->scenario();
        $service = app(TestBankService::class);
        $bank = $service->crear($teacher, ['nombre'=>'Trivia segura','materia'=>'CSS']);
        $service->agregarPregunta($teacher,$bank,['tipo'=>'verdadero_falso','enunciado'=>'CSS da estilo','respuestas_correctas'=>['1']]);
        $service->publicar($teacher,$bank);
        $bank->clasesAsignadas()->attach($ownClass->id, ['asignado_por_usuario_id'=>$teacher->id,'activo'=>true]);
        $this->withSession($this->sessionFor($teacher))->post(route('profesor.tests.sessions.store'), [
            'titulo'=>'Trivia segura','clase_id'=>$ownClass->id,'banco_ids'=>[$bank->id],'modalidad'=>'trivia','tiempo_pregunta'=>20,'puntaje_objetivo'=>100,
        ])->assertRedirect();
        $session = TestSesion::where('anfitrion_usuario_id',$teacher->id)->latest('id')->firstOrFail();
        $engine = app(LiveTestEngineService::class);
        $engine->unir($student,$session->codigo_sala,null,'clase');
        $this->withSession($this->sessionFor($teacher))->get(route('profesor.tests.sessions.show',$session))
            ->assertOk()->assertSee($student->nombre)->assertSee('Participantes y progreso');
        $foreignSession=TestSesion::create(['colegio_id'=>$other->colegio_id,'clase_id'=>$foreignClass->id,
            'anfitrion_usuario_id'=>$other->id,'codigo_sala'=>'Z'.substr($session->codigo_sala,1),'tipo'=>'clase',
            'modalidad'=>'trivia','estado'=>'espera','tiempo_pregunta'=>20,'max_participantes'=>30]);
        $this->get(route('profesor.tests.sessions.show',$foreignSession))->assertNotFound();
        $this->expectException(\DomainException::class);
        $engine->unir($outsider,$session->codigo_sala,null,'clase');
    }

    private function scenario(): array
    {
        $suffix=str_replace('.','',uniqid('',true));
        $college=Colegio::create(['nombre'=>'Colegio '.$suffix,'codigo'=>'TC-'.$suffix,'activo'=>true]);
        $otherCollege=Colegio::create(['nombre'=>'Otro '.$suffix,'codigo'=>'TO-'.$suffix,'activo'=>true]);
        $ownClass=Clase::create(['colegio_id'=>$college->id,'nombre'=>'Clase propia','codigo'=>'CP-'.$suffix,'ciclo_escolar'=>'2026','activa'=>true]);
        $foreignClass=Clase::create(['colegio_id'=>$otherCollege->id,'nombre'=>'Clase ajena','codigo'=>'CA-'.$suffix,'ciclo_escolar'=>'2026','activa'=>true]);
        $teacher=$this->user('Profesor '.$suffix,Usuario::ROL_PROFESOR,$college->id,'p'.$suffix.'@example.test');
        $other=$this->user('Otro profesor '.$suffix,Usuario::ROL_PROFESOR,$otherCollege->id,'o'.$suffix.'@example.test');
        $student=$this->user('Alumno '.$suffix,Usuario::ROL_ALUMNO,$college->id,null);
        $outsider=$this->user('Ajeno '.$suffix,Usuario::ROL_ALUMNO,$otherCollege->id,null);
        $ownClass->agregarProfesor($teacher); $ownClass->agregarAlumno($student);
        $foreignClass->agregarProfesor($other); $foreignClass->agregarAlumno($outsider);
        return [$teacher,$other,$ownClass,$foreignClass,$student,$outsider];
    }

    private function user(string $name,string $role,int $collegeId,?string $email): Usuario
    {
        return Usuario::create(['colegio_id'=>$collegeId,'nombre'=>$name,'email'=>$email,'email_verified_at'=>$email?now():null,
            'clave'=>Hash::make('Clave1234'),'rol'=>$role,'activo'=>true,'cambio_clave_requerido'=>false,'puntos_totales'=>0,'puntos_usados'=>0]);
    }

    private function sessionFor(Usuario $user): array
    {
        return ['usuario_id'=>$user->id,'usuario_session_version'=>$user->fresh()->session_version];
    }
}
