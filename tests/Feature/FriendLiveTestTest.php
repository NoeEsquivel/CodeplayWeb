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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FriendLiveTestTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_host_can_select_own_shared_and_system_banks_and_create_waiting_room(): void
    {
        [$host, , $own, $shared, $system] = $this->scenario();
        $this->withSession($this->sessionFor($host))
            ->get(route('alumno.tests.amigos.index'))
            ->assertOk()
            ->assertSee('Ser anfitrión')
            ->assertSee($own->nombre)
            ->assertSee($shared->nombre)
            ->assertSee($system->nombre);

        $response = $this->post(route('alumno.tests.amigos.store'), [
            'banco_ids' => [$own->id, $shared->id, $system->id],
            'palabra_clave' => 'dragones-unidos',
            'tiempo_pregunta' => 25,
            'puntaje_objetivo' => 500,
            'orden_aleatorio' => 1,
        ]);

        $room = TestSesion::where('anfitrion_usuario_id', $host->id)->latest('id')->firstOrFail();
        $response->assertRedirect(route('alumno.tests.amigos.room', $room));
        $this->assertSame('espera', $room->estado);
        $this->assertSame(30, $room->max_participantes);
        $this->assertSame(3, $room->bancos()->count());
        $this->assertTrue((bool) $room->configuracion['orden_aleatorio']);
        $this->assertTrue(Hash::check('dragones-unidos', $room->palabra_clave_hash));
        $this->assertDatabaseHas('test_sesion_participantes', [
            'sesion_id' => $room->id, 'usuario_id' => $host->id, 'es_anfitrion' => true,
        ]);
    }

    public function test_friend_joins_classroom_and_host_can_start_pause_resume_and_finish(): void
    {
        [$host, $friend, $bank] = $this->simpleScenario();
        $room = $this->createRoom($host, $bank, 300);

        $this->withSession($this->sessionFor($friend));
        $join = $this->post(route('alumno.tests.amigos.join'), ['acceso' => 'mesa-verde']);
        $join->assertRedirect(route('alumno.tests.amigos.room', $room));
        $join->assertSessionHas("live_test_tokens.{$room->id}");
        $this->get(route('alumno.tests.amigos.room', $room))
            ->assertOk()->assertSee('Nuestro salón')->assertSee('2</strong> / 30 participantes', false);
        $this->get(route('alumno.tests.amigos.configure', $room))
            ->assertRedirect(route('alumno.tests.amigos.room', $room));

        $this->withSession($this->sessionFor($host));
        $this->post(route('alumno.tests.amigos.start', $room))->assertRedirect();
        $this->assertSame('activa', $room->fresh()->estado);
        $closingBeforePause = $room->preguntas()->where('estado', 'abierta')->firstOrFail()->cierra_at;

        $this->post(route('alumno.tests.amigos.pause', $room))->assertRedirect();
        $this->assertSame('pausada', $room->fresh()->estado);
        Carbon::setTestNow(now()->addSeconds(10));
        $this->post(route('alumno.tests.amigos.resume', $room))->assertRedirect();
        $this->assertSame('activa', $room->fresh()->estado);
        $this->assertTrue($room->preguntas()->where('estado', 'abierta')->firstOrFail()->cierra_at->greaterThan($closingBeforePause));

        $this->post(route('alumno.tests.amigos.finish', $room))->assertRedirect();
        $this->assertSame('cerrada', $room->fresh()->estado);
    }

    public function test_winner_is_presented_and_room_can_repeat_or_be_reconfigured_without_losing_players(): void
    {
        [$host, , $bank] = $this->simpleScenario();
        Carbon::setTestNow(Carbon::parse('2026-09-26 15:00:00'));
        $room = $this->createRoom($host, $bank, 100);
        $engine = app(LiveTestEngineService::class);
        $engine->iniciar($host, $room);
        $engine->responder($host, $room, 'correcta');
        Carbon::setTestNow(now()->addSeconds(6));
        $state = $engine->estado($host, $room);

        $this->assertSame('finalizada', $state['fase']);
        $this->assertSame($host->nombre, $state['ganador']['nombre']);
        $this->assertSame(100, $state['ganador']['puntos']);

        $this->withSession($this->sessionFor($host));
        $this->get(route('alumno.tests.amigos.room', $room))->assertOk()->assertSee('Jugar otra vez');
        $this->post(route('alumno.tests.amigos.repeat', $room))->assertRedirect();
        $this->assertSame('espera', $room->fresh()->estado);
        $this->assertSame(0, $room->participantes()->firstOrFail()->puntos_test);
        $this->assertSame(0, $room->preguntas()->count());

        $this->post(route('alumno.tests.amigos.finish', $room))->assertRedirect();
        $this->get(route('alumno.tests.amigos.configure', $room))
            ->assertOk()->assertSee('Reconfigurar partida');
        $this->assertSame('configuracion', $room->fresh()->estado);
        $this->put(route('alumno.tests.amigos.update', $room), [
            'banco_ids' => [$bank->id],
            'palabra_clave' => 'nueva-mesa',
            'tiempo_pregunta' => 35,
            'puntaje_objetivo' => 250,
        ])->assertRedirect(route('alumno.tests.amigos.room', $room));

        $room->refresh();
        $this->assertSame('espera', $room->estado);
        $this->assertSame(35, $room->tiempo_pregunta);
        $this->assertSame(250, $room->puntaje_objetivo);
        $this->assertFalse((bool) $room->configuracion['orden_aleatorio']);
        $this->assertSame(1, $room->participantes()->count());
        $this->assertTrue(Hash::check('nueva-mesa', $room->palabra_clave_hash));
        $engine->iniciar($host, $room);
        $this->assertSame(
            $bank->preguntas()->orderBy('orden')->firstOrFail()->id,
            $room->preguntas()->where('estado', 'abierta')->firstOrFail()->pregunta_id,
        );
    }

    private function scenario(): array
    {
        [$host, $friend, $own] = $this->simpleScenario();
        $service = app(TestBankService::class);
        $shared = $service->crear($friend, ['nombre' => 'Banco compartido', 'materia' => 'CSS']);
        $service->agregarPregunta($friend, $shared, [
            'tipo' => 'respuesta_directa', 'enunciado' => 'Pregunta compartida', 'respuestas_correctas' => ['correcta'],
        ]);
        $service->publicar($friend, $shared);
        $shared->usuariosBiblioteca()->syncWithoutDetaching([$host->id => ['agregado_por_codigo' => true]]);

        $system = TestBanco::create([
            'colegio_id' => null, 'creador_usuario_id' => null, 'origen' => 'sistema', 'categoria' => 'html',
            'materia' => 'HTML', 'estado' => 'publicado', 'clave_sistema' => 'live-'.uniqid(),
            'nombre' => 'Banco oficial', 'descripcion' => 'Sistema', 'compartible' => false, 'activo' => true,
        ]);
        $system->preguntas()->create([
            'tipo' => 'respuesta_directa', 'enunciado' => 'Pregunta oficial', 'respuestas_correctas' => ['correcta'],
            'tiempo_limite' => 20, 'orden' => 1, 'activa' => true,
        ]);
        return [$host, $friend, $own, $shared->fresh(), $system->fresh()];
    }

    private function simpleScenario(): array
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $college = Colegio::create(['nombre' => 'Colegio '.$suffix, 'codigo' => 'FR-'.$suffix, 'activo' => true]);
        $class = Clase::create([
            'colegio_id' => $college->id, 'nombre' => 'Clase '.$suffix, 'codigo' => 'C-'.$suffix,
            'ciclo_escolar' => '2026', 'activa' => true,
        ]);
        $host = $this->student('Anfitrión '.$suffix, $college->id);
        $friend = $this->student('Amigo '.$suffix, $college->id);
        $class->agregarAlumno($host); $class->agregarAlumno($friend);
        $service = app(TestBankService::class);
        $bank = $service->crear($host, ['nombre' => 'Banco propio '.$suffix, 'materia' => 'HTML']);
        $service->agregarPregunta($host, $bank, [
            'tipo' => 'respuesta_directa', 'enunciado' => 'Pregunta propia', 'respuestas_correctas' => ['correcta'],
        ]);
        $service->publicar($host, $bank);
        return [$host, $friend, $bank->fresh()];
    }

    private function createRoom(Usuario $host, TestBanco $bank, int $target): TestSesion
    {
        $this->withSession($this->sessionFor($host));
        $this->post(route('alumno.tests.amigos.store'), [
            'banco_ids' => [$bank->id], 'palabra_clave' => 'mesa-verde',
            'tiempo_pregunta' => 20, 'puntaje_objetivo' => $target, 'orden_aleatorio' => 1,
        ])->assertRedirect();
        return TestSesion::where('anfitrion_usuario_id', $host->id)->latest('id')->firstOrFail();
    }

    private function student(string $name, int $collegeId): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId, 'nombre' => $name, 'email' => null,
            'clave' => Hash::make('Clave1234'), 'rol' => Usuario::ROL_ALUMNO, 'activo' => true,
            'cambio_clave_requerido' => false, 'puntos_totales' => 0, 'puntos_usados' => 0,
        ]);
    }

    private function sessionFor(Usuario $student): array
    {
        return ['usuario_id' => $student->id, 'usuario_session_version' => $student->fresh()->session_version];
    }
}
