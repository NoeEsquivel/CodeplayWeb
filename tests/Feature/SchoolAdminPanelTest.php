<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchoolAdminPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Se requiere pdo_sqlite.');
        }

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        foreach ([
            '2025_09_30_064434_create_usuarios_table.php',
            '2025_10_01_063305_add_cambio_clave_requerido_to_usuarios_table.php',
            '2025_10_02_083703_add_ultimo_acceso_to_usuarios_table.php',
            '2026_09_24_120000_create_colegios_and_expand_user_roles.php',
            '2026_09_24_121000_add_email_to_usuarios_table.php',
            '2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php',
            '2026_09_24_123000_create_clases_and_memberships.php',
            '2026_09_25_091000_add_ciclo_escolar_to_clases.php',
            '2026_09_25_100000_add_user_status_and_admin_audit.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_school_administrator_lists_and_creates_users_only_in_own_college(): void
    {
        [$propio, $externo] = $this->colleges();
        $admin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Admin Propio', $propio->id);
        $this->user(Usuario::ROL_PROFESOR, 'Profesor Propio', $propio->id);
        $this->user(Usuario::ROL_PROFESOR, 'Profesor Externo', $externo->id);

        $this->withSession(['usuario_id' => $admin->id])
            ->getJson('/api/colegio/usuarios-por-rol/Profesor')
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Profesor Propio'])
            ->assertJsonMissing(['nombre' => 'Profesor Externo']);

        $this->postJson('/api/colegio/usuarios', [
            'nombre' => 'Profesor Creado',
            'email' => 'creado@colegio.edu',
            'rol' => Usuario::ROL_PROFESOR,
            'colegio_id' => $externo->id,
            'clave' => 'Temporal123',
        ])->assertCreated()
            ->assertJsonPath('usuario.colegio_id', $propio->id);

        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Profesor Creado', 'colegio_id' => $propio->id, 'rol' => Usuario::ROL_PROFESOR,
        ]);
    }

    public function test_school_administrator_cannot_create_master_or_professor_without_email(): void
    {
        [$colegio] = $this->colleges();
        $admin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Admin Roles', $colegio->id);

        $this->withSession(['usuario_id' => $admin->id])
            ->postJson('/api/colegio/usuarios', [
                'nombre' => 'Master Prohibido', 'rol' => Usuario::ROL_MASTER, 'clave' => 'Temporal123',
            ])->assertUnprocessable();

        $this->postJson('/api/colegio/usuarios', [
            'nombre' => 'Profesor Sin Correo', 'rol' => Usuario::ROL_PROFESOR, 'clave' => 'Temporal123',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('usuarios', ['nombre' => 'Master Prohibido']);
    }

    public function test_school_administrator_creates_classes_and_assigns_local_members(): void
    {
        [$colegio] = $this->colleges();
        $admin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Admin Clases', $colegio->id);
        $profesor = $this->user(Usuario::ROL_PROFESOR, 'Profesor Clase', $colegio->id);
        $alumno = $this->user(Usuario::ROL_ALUMNO, 'Alumno Clase', $colegio->id);

        $response = $this->withSession(['usuario_id' => $admin->id])
            ->postJson('/api/colegio/clases', [
                'nombre' => 'Programación A', 'ciclo_escolar' => '2026', 'descripcion' => 'Grupo principal',
            ])->assertCreated()
            ->assertJsonPath('clase.colegio_id', $colegio->id);

        $claseId = $response->json('clase.id');
        $this->postJson("/api/colegio/clases/{$claseId}/profesores/{$profesor->id}")->assertOk();
        $this->postJson("/api/colegio/clases/{$claseId}/alumnos/{$alumno->id}")->assertOk();

        $this->assertDatabaseHas('clase_profesor', ['clase_id' => $claseId, 'usuario_id' => $profesor->id]);
        $this->assertDatabaseHas('clase_alumno', ['clase_id' => $claseId, 'usuario_id' => $alumno->id]);
        $this->assertDatabaseHas('registros_administrativos', ['accion' => 'clase.creada', 'colegio_id' => $colegio->id]);
    }

    public function test_school_administrator_cannot_read_or_modify_another_college(): void
    {
        [$propio, $externo] = $this->colleges();
        $admin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Admin Aislado', $propio->id);
        $profesorExterno = $this->user(Usuario::ROL_PROFESOR, 'Profesor Ajeno', $externo->id);
        $alumnoExterno = $this->user(Usuario::ROL_ALUMNO, 'Alumno Ajeno', $externo->id);
        $claseExterna = Clase::where('colegio_id', $externo->id)->firstOrFail();
        $clasePropia = Clase::where('colegio_id', $propio->id)->firstOrFail();

        $this->withSession(['usuario_id' => $admin->id])
            ->getJson('/api/colegio/clases')
            ->assertOk()
            ->assertJsonMissing(['colegio_id' => $externo->id]);

        $this->putJson("/api/colegio/clases/{$claseExterna->id}", ['nombre' => 'Clase Invadida'])
            ->assertNotFound();
        $this->postJson("/api/colegio/clases/{$clasePropia->id}/profesores/{$profesorExterno->id}")
            ->assertUnprocessable();
        $this->postJson("/api/colegio/clases/{$clasePropia->id}/alumnos/{$alumnoExterno->id}")
            ->assertUnprocessable();
        $this->postJson("/api/colegio/usuarios/{$profesorExterno->id}/reset-password", [
            'nueva_clave' => 'Nueva1234',
        ])->assertForbidden();
    }

    public function test_school_administrator_cannot_change_own_college_or_use_master_endpoints(): void
    {
        [$propio, $externo] = $this->colleges();
        $admin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Admin Inamovible', $propio->id);

        $this->withSession(['usuario_id' => $admin->id])
            ->patchJson("/api/usuarios/{$admin->id}/colegio", ['colegio_id' => $externo->id])
            ->assertForbidden();

        $this->getJson('/api/colegios')->assertForbidden();
        $this->postJson('/api/colegios', ['nombre' => 'Colegio Prohibido'])->assertForbidden();
        $this->assertSame($propio->id, $admin->fresh()->colegio_id);
    }

    public function test_summary_contains_only_own_college_statistics(): void
    {
        [$propio, $externo] = $this->colleges();
        $admin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Admin Estadísticas', $propio->id);
        $this->user(Usuario::ROL_PROFESOR, 'Profesor Estadística', $propio->id);
        $this->user(Usuario::ROL_ALUMNO, 'Alumno Estadística', $propio->id, 120);
        $this->user(Usuario::ROL_ALUMNO, 'Alumno Externo Puntos', $externo->id, 9999);

        $this->withSession(['usuario_id' => $admin->id])
            ->getJson('/api/colegio/resumen')
            ->assertOk()
            ->assertJsonPath('colegio.id', $propio->id)
            ->assertJsonPath('usuarios.profesores', 1)
            ->assertJsonPath('usuarios.alumnos', 1)
            ->assertJsonPath('progreso.puntos_totales', 120)
            ->assertJsonMissing(['nombre' => 'Alumno Externo Puntos']);
    }

    public function test_other_roles_cannot_use_school_administrator_api(): void
    {
        [$colegio] = $this->colleges();
        $profesor = $this->user(Usuario::ROL_PROFESOR, 'Profesor Sin Panel', $colegio->id);
        $alumno = $this->user(Usuario::ROL_ALUMNO, 'Alumno Sin Panel', $colegio->id);

        $this->withSession(['usuario_id' => $profesor->id])
            ->getJson('/api/colegio/resumen')->assertForbidden();
        $this->flushSession();
        $this->withSession(['usuario_id' => $alumno->id])
            ->getJson('/api/colegio/clases')->assertForbidden();
    }

    private function colleges(): array
    {
        $first = Colegio::firstOrFail();
        $second = Colegio::create(['nombre' => 'Colegio Externo', 'codigo' => 'EXTERNO', 'activo' => true]);
        Clase::create([
            'colegio_id' => $second->id, 'nombre' => 'Clase General', 'codigo' => 'GENERAL',
            'ciclo_escolar' => '2026', 'activa' => true,
        ]);
        return [$first, $second];
    }

    private function user(string $role, string $name, int $collegeId, int $points = 0): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId,
            'nombre' => $name,
            'email' => in_array($role, [Usuario::ROL_PROFESOR, Usuario::ROL_ADMINISTRADOR_COLEGIO], true)
                ? strtolower(str_replace(' ', '.', $name)).'@example.test' : null,
            'clave' => Hash::make('Clave1234'),
            'rol' => $role,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => $points,
            'puntos_usados' => 0,
        ]);
    }
}
