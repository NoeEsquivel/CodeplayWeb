<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentLuxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Se requiere pdo_sqlite.');
        }
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_lux_appears_on_student_pages_and_welcome_is_saved_once_per_student(): void
    {
        $student = $this->user(Usuario::ROL_ALUMNO, 'Alumno Lux');
        $other = $this->user(Usuario::ROL_ALUMNO, 'Otro Lux');

        $this->withSession($this->sessionFor($student))
            ->get('/inicio-alumno')
            ->assertOk()
            ->assertSee('id="codeplay-lux"', false)
            ->assertSee('data-welcome-pending="1"', false);
        foreach (['/alumno/documentacion', '/alumno/tutoriales', '/alumno/desafios', '/alumno/tests'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('id="codeplay-lux"', false)
                ->assertSee('data-welcome-pending="0"', false);
        }

        $this->postJson('/alumno/lux/bienvenida-vista')
            ->assertOk()->assertJsonPath('ok', true)->assertJsonPath('show', true);
        $firstSeenAt = DB::table('usuarios')->where('id', $student->id)->value('lux_bienvenida_vista_at');
        $this->assertNotNull($firstSeenAt);

        $this->postJson('/alumno/lux/bienvenida-vista')->assertOk()->assertJsonPath('show', false);
        $this->assertSame($firstSeenAt, DB::table('usuarios')->where('id', $student->id)->value('lux_bienvenida_vista_at'));
        $this->get('/inicio-alumno')->assertOk()->assertSee('data-welcome-pending="0"', false);
        $this->get('/alumno/tests')->assertOk()->assertSee('id="codeplay-lux"', false);
        $this->assertNull(DB::table('usuarios')->where('id', $other->id)->value('lux_bienvenida_vista_at'));
    }

    public function test_professor_cannot_mark_lux_welcome_or_see_student_widget(): void
    {
        $professor = $this->user(Usuario::ROL_PROFESOR, 'Profesor sin Lux');
        $this->withSession($this->sessionFor($professor))
            ->postJson('/alumno/lux/bienvenida-vista')
            ->assertForbidden();
        $this->get('/inicio-profesor')->assertOk()->assertDontSee('id="codeplay-lux"', false);
    }

    private function user(string $role, string $name): Usuario
    {
        return Usuario::create([
            'colegio_id' => Colegio::firstOrFail()->id,
            'nombre' => $name,
            'email' => $role === Usuario::ROL_ALUMNO ? null : 'profesor-lux@example.test',
            'email_verified_at' => $role === Usuario::ROL_ALUMNO ? null : now(),
            'clave' => Hash::make('Clave1234'),
            'rol' => $role,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ]);
    }

    private function sessionFor(Usuario $user): array
    {
        return ['usuario_id' => $user->id, 'usuario_session_version' => $user->fresh()->session_version];
    }
}
