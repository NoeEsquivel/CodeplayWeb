<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestBanco;
use App\Models\TestHistorialRespuesta;
use App\Models\TestResultadoSesion;
use App\Models\TestSesion;
use App\Models\TestSesionParticipante;
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

    public function test_teacher_completes_the_bank_and_question_crud_flow(): void
    {
        [$teacher] = $this->scenario();
        $service = app(TestBankService::class);
        $bank = $service->crear($teacher, ['nombre' => 'Banco CRUD', 'materia' => 'Programación']);
        $this->withSession($this->sessionFor($teacher));

        $this->post(route('profesor.tests.banks.publish', $bank))->assertRedirect()->assertSessionHas('error');
        $this->put(route('profesor.tests.banks.update', $bank), [
            'nombre' => 'Banco CRUD actualizado', 'materia' => 'JavaScript', 'descripcion' => 'Validación integral',
        ])->assertRedirect()->assertSessionHas('success');

        $this->post(route('profesor.tests.questions.store', $bank), [
            'tipo' => 'opcion_unica', 'enunciado' => '¿Cuál opción es correcta?',
            'opciones' => ['a' => 'Primera', 'b' => 'Segunda'], 'respuesta_unica' => 'a',
            'tiempo_limite' => 20, 'puntos' => 5,
        ])->assertRedirect()->assertSessionHas('success');
        $question = $bank->preguntas()->firstOrFail();
        $this->put(route('profesor.tests.questions.update', [$bank, $question]), [
            'tipo' => 'respuesta_directa', 'enunciado' => 'Escribe dos formas válidas',
            'respuestas_escritas' => "respuesta uno\nrespuesta dos", 'tiempo_limite' => 30, 'puntos' => 8,
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame(['respuesta uno', 'respuesta dos'], $question->fresh()->respuestas_correctas);

        $this->post(route('profesor.tests.questions.store', $bank), [
            'tipo' => 'verdadero_falso', 'enunciado' => 'JavaScript se ejecuta en el navegador',
            'respuesta_booleana' => '1', 'tiempo_limite' => 15, 'puntos' => 3,
        ])->assertRedirect()->assertSessionHas('success');
        $second = $bank->preguntas()->whereKeyNot($question->id)->firstOrFail();
        $this->delete(route('profesor.tests.questions.destroy', [$bank, $second]))
            ->assertRedirect()->assertSessionHas('success');
        $this->post(route('profesor.tests.banks.publish', $bank))->assertRedirect()->assertSessionHas('success');
        $this->assertSame('publicado', $bank->fresh()->estado);

        $this->delete(route('profesor.tests.banks.destroy', $bank))->assertRedirect()->assertSessionHas('success');
        $this->assertSoftDeleted('test_bancos', ['id' => $bank->id]);
    }

    public function test_bank_service_rejects_cross_bank_and_invalid_question_data(): void
    {
        [$teacher] = $this->scenario();
        $service = app(TestBankService::class);
        $bank = $service->crear($teacher, ['nombre' => 'Validaciones A', 'materia' => 'HTML']);
        $other = $service->crear($teacher, ['nombre' => 'Validaciones B', 'materia' => 'CSS']);
        $question = $service->agregarPregunta($teacher, $other, [
            'tipo' => 'respuesta_directa', 'enunciado' => 'Pregunta externa', 'respuestas_correctas' => ['ok'],
        ]);

        foreach ([
            fn () => $service->actualizarPregunta($teacher, $bank, $question, ['tipo' => 'respuesta_directa', 'enunciado' => 'x', 'respuestas_correctas' => ['x']]),
            fn () => $service->eliminarPregunta($teacher, $bank, $question),
            fn () => $service->reordenar($teacher, $other, []),
            fn () => $service->agregarPregunta($teacher, $bank, ['tipo' => 'desconocido', 'enunciado' => 'x', 'respuestas_correctas' => ['x']]),
            fn () => $service->agregarPregunta($teacher, $bank, ['tipo' => 'respuesta_directa', 'enunciado' => '', 'respuestas_correctas' => []]),
            fn () => $service->agregarPregunta($teacher, $bank, ['tipo' => 'opcion_unica', 'enunciado' => 'x', 'opciones' => ['a' => 'A'], 'respuestas_correctas' => ['a']]),
            fn () => $service->agregarPregunta($teacher, $bank, ['tipo' => 'opcion_unica', 'enunciado' => 'x', 'opciones' => ['a' => 'A', 'b' => 'B'], 'respuestas_correctas' => ['z']]),
            fn () => $service->agregarPregunta($teacher, $bank, ['tipo' => 'verdadero_falso', 'enunciado' => 'x', 'respuestas_correctas' => ['quizá']]),
            fn () => $service->agregarPregunta($teacher, $bank, ['tipo' => 'respuesta_directa', 'enunciado' => 'x', 'respuestas_correctas' => range(1, 21)]),
        ] as $operation) {
            try {
                $operation();
                $this->fail('La validación debía rechazar la operación.');
            } catch (\DomainException|\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
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

    public function test_teacher_actions_return_friendly_errors_and_can_remove_class_assignment(): void
    {
        [$teacher, , $class] = $this->scenario();
        $service = app(TestBankService::class);
        $bank = $service->crear($teacher, ['nombre' => 'Banco bloqueado', 'materia' => 'HTML']);
        $question = $service->agregarPregunta($teacher, $bank, [
            'tipo' => 'respuesta_directa', 'enunciado' => 'Pregunta publicada', 'respuestas_correctas' => ['correcta'],
        ]);
        $service->publicar($teacher, $bank);
        $this->withSession($this->sessionFor($teacher));

        $this->post(route('profesor.tests.banks.assign', $bank), ['clase_ids' => [$class->id]])->assertRedirect();
        $this->post(route('profesor.tests.banks.assign', $bank), ['clase_ids' => []])->assertRedirect();
        $this->assertDatabaseMissing('test_banco_clase', ['banco_id' => $bank->id, 'clase_id' => $class->id]);

        $questionData = [
            'tipo' => 'respuesta_directa', 'enunciado' => 'No debe cambiar',
            'respuestas_escritas' => '', 'tiempo_limite' => 20, 'puntos' => 1,
        ];
        $this->post(route('profesor.tests.questions.store', $bank), $questionData)
            ->assertRedirect()->assertSessionHas('error');
        $this->put(route('profesor.tests.questions.update', [$bank, $question]), $questionData)
            ->assertRedirect()->assertSessionHas('error');

        $this->post(route('profesor.tests.sessions.store'), [
            'titulo' => 'Banco inexistente', 'clase_id' => $class->id,
            'banco_ids' => [999999999], 'modalidad' => 'examen',
        ])->assertRedirect()->assertSessionHas('error', 'Uno de los bancos no está disponible.');

        $bank->clasesAsignadas()->syncWithoutDetaching([$class->id => ['asignado_por_usuario_id' => $teacher->id, 'activo' => true]]);
        $this->post(route('profesor.tests.sessions.store'), [
            'titulo' => 'Sesión para acción inválida', 'clase_id' => $class->id,
            'banco_ids' => [$bank->id], 'modalidad' => 'examen',
        ])->assertRedirect();
        $session = TestSesion::where('anfitrion_usuario_id', $teacher->id)->latest('id')->firstOrFail();
        $this->post(route('profesor.tests.sessions.action', [$session, 'pause']))
            ->assertRedirect()->assertSessionHas('error', 'Solo se puede pausar una partida activa.');
    }
    public function test_selected_bank_is_automatically_assigned_when_creating_a_session(): void
    {
        [$teacher, , $ownClass] = $this->scenario();
        $service = app(TestBankService::class);
        $bank = $service->crear($teacher, ['nombre'=>'Examen sin paso extra','materia'=>'HTML']);
        $service->agregarPregunta($teacher,$bank,['tipo'=>'verdadero_falso','enunciado'=>'HTML estructura contenido','respuestas_correctas'=>['1']]);
        $service->publicar($teacher,$bank);

        $this->assertDatabaseMissing('test_banco_clase', ['banco_id'=>$bank->id,'clase_id'=>$ownClass->id]);

        $this->withSession($this->sessionFor($teacher))->post(route('profesor.tests.sessions.store'), [
            'titulo'=>'Examen automático','clase_id'=>$ownClass->id,'banco_ids'=>[$bank->id],'modalidad'=>'examen',
            'puntos_pregunta'=>[$bank->preguntas()->firstOrFail()->id=>10],
        ])->assertRedirect();

        $this->assertDatabaseHas('test_banco_clase', [
            'banco_id'=>$bank->id,'clase_id'=>$ownClass->id,'asignado_por_usuario_id'=>$teacher->id,'activo'=>true,
        ]);
        $this->assertDatabaseHas('test_sesiones', [
            'anfitrion_usuario_id'=>$teacher->id,'clase_id'=>$ownClass->id,'modalidad'=>'examen',
        ]);
        $session=TestSesion::where('anfitrion_usuario_id',$teacher->id)->where('modalidad','examen')->latest('id')->firstOrFail();
        $this->get(route('profesor.tests.sessions.show',$session))->assertOk()
            ->assertSee('Las respuestas correctas permanecerán ocultas para los alumnos')
            ->assertSee('Las entregas pendientes se cerrarán')
            ->assertDontSee('@else');
    }

    public function test_session_dates_use_the_teachers_browser_timezone(): void
    {
        [$teacher,,$ownClass]=$this->scenario();$service=app(TestBankService::class);
        $bank=$service->crear($teacher,['nombre'=>'Horario local','materia'=>'HTML']);
        $service->agregarPregunta($teacher,$bank,['tipo'=>'verdadero_falso','enunciado'=>'HTML usa etiquetas','respuestas_correctas'=>['1']]);$service->publicar($teacher,$bank);

        $this->withSession($this->sessionFor($teacher))->post(route('profesor.tests.sessions.store'),[
            'titulo'=>'Examen en Guatemala','clase_id'=>$ownClass->id,'banco_ids'=>[$bank->id],'modalidad'=>'examen',
            'zona_horaria_profesor'=>'America/Guatemala','disponible_desde'=>'2026-09-27T22:33','disponible_hasta'=>'2026-09-27T23:33',
        ])->assertRedirect();

        $session=TestSesion::where('anfitrion_usuario_id',$teacher->id)->latest('id')->firstOrFail();
        $this->assertSame('2026-09-28 04:33',$session->disponible_desde->format('Y-m-d H:i'));
        $this->assertSame('2026-09-28 05:33',$session->disponible_hasta->format('Y-m-d H:i'));
        $this->assertSame('America/Guatemala',$session->configuracion['zona_horaria_profesor']);
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
        do{$foreignCode='Z'.strtoupper(substr(md5(uniqid('',true)),0,5));}while(TestSesion::where('codigo_sala',$foreignCode)->exists());
        $foreignSession=TestSesion::create(['colegio_id'=>$other->colegio_id,'clase_id'=>$foreignClass->id,
            'anfitrion_usuario_id'=>$other->id,'codigo_sala'=>$foreignCode,'tipo'=>'clase',
            'modalidad'=>'trivia','estado'=>'espera','tiempo_pregunta'=>20,'max_participantes'=>30]);
        $this->get(route('profesor.tests.sessions.show',$foreignSession))->assertNotFound();
        $this->expectException(\DomainException::class);
        $engine->unir($outsider,$session->codigo_sala,null,'clase');
    }

    public function test_teacher_can_permanently_delete_only_finished_history_from_own_class(): void
    {
        [$teacher,$other,$ownClass,$foreignClass,$student]=$this->scenario();
        $finished=$this->classSession($teacher,$ownClass,'cerrada');
        $participant=TestSesionParticipante::create([
            'sesion_id'=>$finished->id,'usuario_id'=>$student->id,'nombre_visible'=>$student->nombre,
            'reconexion_token_hash'=>hash('sha256','finished-'.$finished->id),'estado'=>'finalizado',
            'es_anfitrion'=>false,'puntos_test'=>80,'unido_at'=>now(),'finalizado_at'=>now(),
        ]);
        $result=TestResultadoSesion::create([
            'sesion_id'=>$finished->id,'participante_id'=>$participant->id,'intento'=>1,'usuario_id'=>$student->id,
            'colegio_id'=>$teacher->colegio_id,'clase_id'=>$ownClass->id,'nombre_alumno'=>$student->nombre,
            'modalidad'=>'examen','total_preguntas'=>1,'correctas'=>1,'incorrectas'=>0,'calificacion'=>100,
            'puntos_test'=>0,'estado'=>'entregado','iniciada_at'=>now()->subMinute(),'entregada_at'=>now(),
        ]);
        $history=TestHistorialRespuesta::create([
            'sesion_id'=>$finished->id,'participante_id'=>$participant->id,'usuario_id'=>$student->id,
            'colegio_id'=>$teacher->colegio_id,'clase_id'=>$ownClass->id,'profesor_id'=>$teacher->id,
            'intento'=>1,'ronda'=>1,'orden'=>1,'pregunta'=>'¿Pregunta?','tipo_pregunta'=>'verdadero_falso',
            'respuestas_correctas'=>['1'],'respuesta_alumno'=>['1'],'correcta'=>true,'puntos_otorgados'=>10,
            'puntos_maximos'=>10,'respondida_at'=>now(),
        ]);

        $this->withSession($this->sessionFor($teacher))->get(route('profesor.tests.index').'#sesiones-recientes')
            ->assertOk()->assertSee('Eliminar historial')->assertSee('ya no podrás consultar ni imprimir un resumen');
        $this->delete(route('profesor.tests.sessions.destroy-history',$finished))->assertRedirect();
        $this->assertDatabaseMissing('test_sesiones',['id'=>$finished->id]);
        $this->assertDatabaseMissing('test_sesion_participantes',['id'=>$participant->id]);
        $this->assertDatabaseMissing('test_resultados_sesion',['id'=>$result->id]);
        $this->assertDatabaseMissing('test_historial_respuestas',['id'=>$history->id]);

        $active=$this->classSession($teacher,$ownClass,'activa');
        $this->delete(route('profesor.tests.sessions.destroy-history',$active))->assertUnprocessable();
        $this->assertDatabaseHas('test_sesiones',['id'=>$active->id]);

        $foreign=$this->classSession($other,$foreignClass,'cerrada');
        $this->delete(route('profesor.tests.sessions.destroy-history',$foreign))->assertNotFound();
        $this->assertDatabaseHas('test_sesiones',['id'=>$foreign->id]);
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

    private function classSession(Usuario $teacher,Clase $class,string $state): TestSesion
    {
        do{$code=strtoupper(substr(md5(uniqid('',true)),0,6));}while(TestSesion::where('codigo_sala',$code)->exists());
        return TestSesion::create([
            'colegio_id'=>$teacher->colegio_id,'clase_id'=>$class->id,'anfitrion_usuario_id'=>$teacher->id,
            'codigo_sala'=>$code,'tipo'=>'clase','modalidad'=>'examen','estado'=>$state,
            'tiempo_pregunta'=>null,'max_participantes'=>30,'cerrada_at'=>$state==='cerrada'?now():null,
        ]);
    }

    private function sessionFor(Usuario $user): array
    {
        return ['usuario_id'=>$user->id,'usuario_session_version'=>$user->fresh()->session_version];
    }
}
