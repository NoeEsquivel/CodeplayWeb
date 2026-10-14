<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\Clase;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Se requiere pdo_sqlite.');
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        foreach ([
            '2025_09_30_064434_create_usuarios_table.php',
            '2025_10_01_063305_add_cambio_clave_requerido_to_usuarios_table.php',
            '2025_10_02_083703_add_ultimo_acceso_to_usuarios_table.php',
            '2026_09_08_120000_create_aventura_preparacion_tables.php',
            '2026_09_24_120000_create_colegios_and_expand_user_roles.php',
            '2026_09_25_100000_add_user_status_and_admin_audit.php',
            '2026_09_24_121000_add_email_to_usuarios_table.php',
            '2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php',
            '2026_09_24_123000_create_clases_and_memberships.php',
            '2026_09_25_091000_add_ciclo_escolar_to_clases.php',
            '2026_09_27_130000_add_full_names_and_adventure_alias.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_protected_routes_reject_guests_and_deleted_sessions(): void
    {
        $this->get('/inicio-master')->assertRedirect('/login');

        $this->withSession(['usuario_id' => 999999])
            ->getJson('/api/usuarios-por-rol/Alumno')
            ->assertUnauthorized()
            ->assertJson(['success' => false]);
    }

    public function test_each_role_can_only_open_its_own_area(): void
    {
        $alumno = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Uno');
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Uno');
        $administradorColegio = $this->usuario(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Administrador Escolar Uno');
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Uno');

        $this->withSession(['usuario_id' => $alumno->id])
            ->getJson('/usuario/puntos')->assertOk();
        $this->get('/inicio-administrador')->assertForbidden();
        $this->get('/mi-cuenta/actualizar-clave')->assertForbidden();

        $this->flushSession();
        $this->withSession(['usuario_id' => $profesor->id])
            ->get('/inicio-profesor')->assertOk()
            ->assertSee('Actualizar contraseña');
        $this->get('/mi-cuenta/actualizar-clave')
            ->assertOk()
            ->assertSee('Actualiza tu contraseña');
        $this->get('/inicio-administrador')->assertOk();
        $this->get('/alumno/documentacion')->assertForbidden();
        $this->get('/inicio-administrador-colegio')->assertForbidden();

        $this->flushSession();
        $this->withSession(['usuario_id' => $administradorColegio->id])
            ->get('/inicio-administrador-colegio')
            ->assertOk()
            ->assertSee('Panel del Colegio')
            ->assertDontSee('Resumen del colegio')
            ->assertSee('Estado general del colegio')
            ->assertSee('Menú del Colegio')
            ->assertSee('Actualizar contraseña')
            ->assertSee('Cerrar sesión')
            ->assertSee('panel-profesor-codeplay.png');
        $this->get('/inicio-administrador')->assertForbidden();
        $this->get('/inicio-master')->assertForbidden();

        $this->flushSession();
        $this->withSession(['usuario_id' => $master->id])
            ->get('/inicio-master')->assertOk();
        $this->get('/inicio-administrador')->assertForbidden();
    }

    public function test_professor_can_manage_only_students(): void
    {
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Dos');
        $otroProfesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Tres');
        $alumno = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Dos');
        $clase = Clase::where('codigo', 'GENERAL')->firstOrFail();
        $clase->agregarProfesor($profesor);
        $clase->agregarAlumno($alumno);

        $this->withSession(['usuario_id' => $profesor->id])
            ->getJson('/api/usuarios-por-rol/Alumno')
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Alumno Dos'])
            ->assertJsonMissing(['clave' => $alumno->clave]);

        $this->getJson('/api/usuarios-por-rol/Profesor')->assertForbidden();
        $this->getJson('/api/usuarios-por-rol/AdministradorColegio')->assertForbidden();

        $this->postJson('/api/usuarios', [
            'nombre' => 'Profesor Prohibido',
            'email' => 'prohibido@colegio.edu',
            'rol' => Usuario::ROL_PROFESOR,
            'clave' => 'Clave1234',
        ])->assertForbidden();

        $this->postJson("/api/usuarios/{$otroProfesor->id}/reset-password", [
            'nueva_clave' => 'Nueva1234',
        ])->assertForbidden();

        $this->deleteJson("/api/usuarios/{$otroProfesor->id}")
            ->assertForbidden();

        $this->postJson('/api/usuarios', [
            'nombre' => 'Alumno Nuevo',
            'rol' => Usuario::ROL_ALUMNO,
            'clase_id' => $clase->id,
            'clave' => 'Temporal123',
        ])->assertCreated()
            ->assertJsonPath('usuario.rol', Usuario::ROL_ALUMNO)
            ->assertJsonMissingPath('usuario.clave');

        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Alumno Nuevo',
            'rol' => Usuario::ROL_ALUMNO,
            'cambio_clave_requerido' => 1,
        ]);
    }

    public function test_master_manages_supported_roles_but_never_master_accounts(): void
    {
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Dos');
        $otroMaster = $this->usuario(Usuario::ROL_MASTER, 'Master Tres');
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Cuatro');

        $this->withSession(['usuario_id' => $master->id])
            ->getJson('/api/usuarios-por-rol/Profesor')
            ->assertOk()
            ->assertJsonFragment(['nombre' => $profesor->nombre])
            ->assertJsonMissingPath('0.clave');

        $this->postJson('/api/usuarios', [
            'nombre' => 'Administrador Escolar Dos',
            'email' => 'administrador@colegio.edu',
            'colegio_id' => Colegio::firstOrFail()->id,
            'rol' => Usuario::ROL_ADMINISTRADOR_COLEGIO,
            'clave' => 'Segura1234',
        ])->assertCreated()
            ->assertJsonPath('usuario.rol', Usuario::ROL_ADMINISTRADOR_COLEGIO);

        $this->getJson('/api/usuarios-por-rol/Master')->assertNotFound();

        $this->postJson('/api/usuarios', [
            'nombre' => 'Otro Master',
            'rol' => Usuario::ROL_MASTER,
            'clave' => 'Segura1234',
        ])->assertUnprocessable();

        $this->postJson("/api/usuarios/{$otroMaster->id}/reset-password", [
            'nueva_clave' => 'Nueva1234',
        ])->assertForbidden();

        $this->deleteJson("/api/usuarios/{$otroMaster->id}")
            ->assertForbidden();
    }

    public function test_email_is_required_for_new_professors_and_school_administrators(): void
    {
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Correos');

        $this->withSession(['usuario_id' => $master->id])
            ->postJson('/api/usuarios', [
                'nombre' => 'Profesor Sin Correo',
                'rol' => Usuario::ROL_PROFESOR,
                'clave' => 'Temporal123',
            ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/usuarios', [
            'nombre' => 'Profesor Con Correo',
            'email' => '  PROFESOR@COLEGIO.EDU ',
            'colegio_id' => Colegio::firstOrFail()->id,
            'rol' => Usuario::ROL_PROFESOR,
            'clave' => 'Temporal123',
        ])->assertCreated()
            ->assertJsonPath('usuario.email', 'profesor@colegio.edu');
    }
    public function test_duplicate_username_returns_the_first_available_suggestion(): void
    {
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Cinco');
        $clase = Clase::where('codigo', 'GENERAL')->firstOrFail();
        $clase->agregarProfesor($profesor);
        $this->usuario(Usuario::ROL_ALUMNO, 'EEsquivel');
        $this->usuario(Usuario::ROL_ALUMNO, 'EEsquivel2');

        $this->withSession(['usuario_id' => $profesor->id])
            ->postJson('/api/usuarios', [
                'nombre_completo' => 'Elvin Noe Esquivel Cabrera',
                'nombre' => 'EEsquivel',
                'rol' => Usuario::ROL_ALUMNO,
                'clase_id' => $clase->id,
                'clave' => 'Temporal123',
            ])->assertUnprocessable()
            ->assertJsonPath('code', 'USERNAME_TAKEN')
            ->assertJsonPath('suggested_username', 'EEsquivel3')
            ->assertJsonPath('errors.nombre.0', 'El nombre de usuario ya existe. Puedes usar «EEsquivel3».');

        $this->assertDatabaseCount('usuarios', 3);
    }

    public function test_college_can_contain_users_while_master_can_remain_global(): void
    {
        $colegio = Colegio::create([
            'nombre' => 'Colegio de Prueba',
            'codigo' => 'PRUEBA',
            'activo' => true,
        ]);
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Colegio', false, $colegio->id);
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Global');

        $this->assertTrue($profesor->colegio->is($colegio));
        $this->assertTrue($colegio->usuarios->contains($profesor));
        $this->assertNull($master->colegio_id);
    }

    public function test_password_is_hidden_from_every_user_serialization(): void
    {
        $usuario = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Oculto');

        $this->assertArrayNotHasKey('clave', $usuario->toArray());
        $this->assertStringNotContainsString($usuario->clave, $usuario->toJson());
    }

    public function test_authentication_rejects_invalid_inactive_and_unknown_role_accounts(): void
    {
        $this->post(route('login.verify'), ['nombre' => 'No existe', 'clave' => 'Incorrecta'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $inactive = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Inactivo Login');
        $inactive->forceFill(['activo' => false])->save();
        $this->post(route('login.verify'), ['nombre' => $inactive->nombre, 'clave' => 'Clave1234'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $unknown = $this->usuario(Usuario::ROL_ALUMNO, 'Usuario Rol Desconocido');
        $unknown->forceFill(['rol' => 'Desconocido'])->save();
        $this->post(route('login.verify'), ['nombre' => $unknown->nombre, 'clave' => 'Clave1234'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Rol no válido');
        $this->assertNull(session('usuario_id'));
    }

    public function test_password_change_rejects_an_incorrect_current_password(): void
    {
        $student = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Cambia Clave', true);
        $this->withSession([
            'usuario_id' => $student->id,
            'usuario_session_version' => $student->session_version,
            'cambio_clave_requerido' => true,
        ])->post(route('cambio.clave.procesar'), [
            'clave_actual' => 'ClaveEquivocada9',
            'nueva_clave' => 'NuevaClave9',
            'nueva_clave_confirmation' => 'NuevaClave9',
        ])->assertRedirect()->assertSessionHas('error', 'La contraseña actual es incorrecta');

        $this->assertTrue(Hash::check('Clave1234', $student->fresh()->clave));
    }
    public function test_temporary_password_blocks_access_until_it_is_changed(): void
    {
        $alumno = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Temporal', true);

        $this->withSession(['usuario_id' => $alumno->id])
            ->get('/alumno/tests')
            ->assertRedirect('/cambio-clave-obligatorio');

        $this->get('/cambio-clave-obligatorio')->assertOk();
    }

    private function usuario(
        string $rol,
        string $nombre,
        bool $cambioRequerido = false,
        ?int $colegioId = null
    ): Usuario {
        if ($colegioId === null && $rol !== Usuario::ROL_MASTER) {
            $colegioId = Colegio::query()->value('id');
        }

        return Usuario::create([
            'colegio_id' => $colegioId,
            'nombre' => $nombre,
            'clave' => Hash::make('Clave1234'),
            'rol' => $rol,
            'cambio_clave_requerido' => $cambioRequerido,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ]);
    }
}
