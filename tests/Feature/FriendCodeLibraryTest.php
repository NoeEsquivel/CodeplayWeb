<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FriendCodeLibraryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_friend_can_preview_safe_metadata_add_once_and_remove_from_library(): void
    {
        [$owner, $friend, $bank, $code] = $this->sharedFixture();

        $this->withSession($this->sessionFor($friend))
            ->post(route('alumno.codigo-amigo.lookup'), ['codigo' => $code])
            ->assertOk()
            ->assertSee($owner->nombre)
            ->assertSee($bank->nombre)
            ->assertSee($bank->descripcion)
            ->assertSee('1 preguntas')
            ->assertDontSee($owner->email)
            ->assertDontSee('respuestas_correctas');

        $this->post(route('alumno.codigo-amigo.add'), ['codigo' => $code])
            ->assertRedirect(route('alumno.codigo-amigo.index'));
        $this->assertSame(1, DB::table('test_biblioteca_usuario')
            ->where('banco_id', $bank->id)->where('usuario_id', $friend->id)->where('agregado_por_codigo', true)->count());

        $this->post(route('alumno.codigo-amigo.add'), ['codigo' => $code])
            ->assertRedirect(route('alumno.codigo-amigo.index'));
        $this->assertSame(1, DB::table('test_biblioteca_usuario')
            ->where('banco_id', $bank->id)->where('usuario_id', $friend->id)->count());

        $this->get(route('alumno.codigo-amigo.index'))->assertOk()->assertSee($bank->nombre)->assertSee('Solo lectura');
        $this->delete(route('alumno.codigo-amigo.remove', $bank))->assertRedirect();
        $this->assertDatabaseMissing('test_biblioteca_usuario', ['banco_id' => $bank->id, 'usuario_id' => $friend->id]);
        $this->assertDatabaseHas('test_bancos', ['id' => $bank->id, 'creador_usuario_id' => $owner->id, 'deleted_at' => null]);
    }

    public function test_shared_bank_can_be_practiced_but_only_copy_is_editable_by_friend(): void
    {
        [$owner, $friend, $bank, $code] = $this->sharedFixture();
        app(TestBankService::class)->agregarPorCodigo($friend, $code);

        $this->withSession($this->sessionFor($friend))
            ->post(route('alumno.codigo-amigo.practice', $bank))
            ->assertRedirect();
        $session = TestSesion::where('anfitrion_usuario_id', $friend->id)->latest('id')->firstOrFail();
        $this->assertSame($bank->id, (int) $session->configuracion['banco_id']);
        $this->assertSame(1, $session->preguntas()->count());

        $this->get(route('alumno.bancos.edit', $bank))->assertNotFound();
        $this->put(route('alumno.bancos.update', $bank), ['nombre' => 'Intento', 'materia' => 'Ataque'])->assertNotFound();

        $this->post(route('alumno.codigo-amigo.copy', $bank))->assertRedirect();
        $copy = TestBanco::where('creador_usuario_id', $friend->id)->latest('id')->firstOrFail();
        $this->assertSame('borrador', $copy->estado);
        $this->assertSame('Copia de '.$bank->nombre, $copy->nombre);
        $this->assertSame(1, $copy->preguntas()->count());
        $this->assertNull($copy->codigo_compartir);

        $this->put(route('alumno.bancos.update', $copy), [
            'nombre' => 'Mi versión editable', 'materia' => $copy->materia, 'descripcion' => 'Copia independiente',
        ])->assertRedirect();
        $this->assertSame('Mi versión editable', $copy->fresh()->nombre);
        $this->assertSame($bank->nombre, $bank->fresh()->nombre);
        $this->assertSame($owner->id, $bank->fresh()->creador_usuario_id);
    }

    public function test_creator_can_disable_code_without_granting_edit_permission_or_removing_existing_library_entry(): void
    {
        [$owner, $friend, $bank, $code] = $this->sharedFixture();
        app(TestBankService::class)->agregarPorCodigo($friend, $code);

        $this->withSession($this->sessionFor($owner))
            ->post(route('alumno.bancos.stop-sharing', $bank))
            ->assertRedirect();
        $this->assertFalse($bank->fresh()->compartible);

        $newClassmate = $this->student('Nuevo compañero', $owner->colegio_id);
        $class = $owner->clasesComoAlumno()->firstOrFail();
        $class->agregarAlumno($newClassmate);
        $this->flushSession();
        $this->withSession($this->sessionFor($newClassmate))
            ->post(route('alumno.codigo-amigo.lookup'), ['codigo' => $code])
            ->assertOk()
            ->assertSee('El código no está disponible.');

        $this->flushSession();
        $this->withSession($this->sessionFor($friend))
            ->get(route('alumno.codigo-amigo.index'))
            ->assertOk()
            ->assertSee($bank->nombre);
        $this->post(route('alumno.codigo-amigo.practice', $bank))->assertRedirect();
        $this->get(route('alumno.bancos.edit', $bank))->assertNotFound();
    }

    private function sharedFixture(): array
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $college = Colegio::create(['nombre' => 'Colegio '.$suffix, 'codigo' => 'FC-'.$suffix, 'activo' => true]);
        $class = Clase::create(['colegio_id' => $college->id, 'nombre' => 'Clase '.$suffix, 'codigo' => 'C-'.$suffix, 'ciclo_escolar' => '2026', 'activa' => true]);
        $owner = $this->student('Autor', $college->id, 'autor'.$suffix.'@example.test');
        $friend = $this->student('Amigo', $college->id);
        $class->agregarAlumno($owner);
        $class->agregarAlumno($friend);

        $service = app(TestBankService::class);
        $bank = $service->crear($owner, ['nombre' => 'Banco compartido', 'descripcion' => 'Descripción pública', 'materia' => 'HTML']);
        $service->agregarPregunta($owner, $bank, [
            'tipo' => 'opcion_unica', 'enunciado' => '¿Cuál es HTML?', 'opciones' => ['a' => 'HTML', 'b' => 'CSS'],
            'respuestas_correctas' => ['a'], 'explicacion' => 'La respuesta es HTML.', 'tiempo_limite' => 20,
        ]);
        $service->publicar($owner, $bank);
        $code = $service->habilitarCodigo($owner, $bank->fresh());

        return [$owner, $friend, $bank->fresh(), $code];
    }

    private function student(string $name, int $collegeId, ?string $email = null): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId, 'nombre' => $name.' '.uniqid(), 'email' => $email,
            'clave' => Hash::make('Clave1234'), 'rol' => Usuario::ROL_ALUMNO, 'activo' => true,
            'cambio_clave_requerido' => false, 'puntos_totales' => 0, 'puntos_usados' => 0,
        ]);
    }

    private function sessionFor(Usuario $student): array
    {
        return ['usuario_id' => $student->id, 'usuario_session_version' => $student->fresh()->session_version];
    }
}

