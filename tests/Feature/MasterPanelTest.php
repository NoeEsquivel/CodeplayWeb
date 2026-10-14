<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterPanelTest extends TestCase
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
            '2026_09_24_121000_add_email_to_usuarios_table.php',
            '2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php',
            '2026_09_24_123000_create_clases_and_memberships.php',
            '2026_09_25_091000_add_ciclo_escolar_to_clases.php',
            '2026_09_25_100000_add_user_status_and_admin_audit.php',
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

    public function test_only_master_can_manage_colleges(): void
    {
        $colegio = Colegio::firstOrFail();
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Colegios');
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Sin Permiso', $colegio->id);

        $this->withSession(['usuario_id' => $profesor->id])
            ->getJson('/api/colegios')
            ->assertForbidden();

        $this->flushSession();
        $this->withSession(['usuario_id' => $master->id])
            ->getJson('/api/colegios')
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'INED MARIA LUISA']);
    }

    public function test_master_creates_college_with_a_general_class_and_can_delete_it_while_empty(): void
    {
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Creador');

        $response = $this->withSession(['usuario_id' => $master->id])
            ->postJson('/api/colegios', ['nombre' => '  Colegio Nuevo   Horizonte  '])
            ->assertCreated()
            ->assertJsonPath('colegio.nombre', 'Colegio Nuevo Horizonte')
            ->assertJsonPath('colegio.codigo', 'COLEGIO-NUEVO-HORIZONTE');

        $colegioId = $response->json('colegio.id');
        $this->assertDatabaseHas('clases', [
            'colegio_id' => $colegioId,
            'nombre' => 'Clase General',
            'codigo' => 'GENERAL',
        ]);

        $this->deleteJson("/api/colegios/{$colegioId}")
            ->assertOk();
        $this->assertDatabaseMissing('colegios', ['id' => $colegioId]);
        $this->assertDatabaseMissing('clases', ['colegio_id' => $colegioId]);
    }

    public function test_master_creates_school_administrator_inside_selected_college(): void
    {
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Administradores');
        $colegio = Colegio::firstOrFail();

        $this->withSession(['usuario_id' => $master->id])
            ->postJson('/api/usuarios', [
                'nombre' => 'Dirección INED',
                'email' => 'direccion@ined.edu',
                'rol' => Usuario::ROL_ADMINISTRADOR_COLEGIO,
                'colegio_id' => $colegio->id,
                'clave' => 'Temporal123',
            ])->assertCreated()
            ->assertJsonPath('usuario.rol', Usuario::ROL_ADMINISTRADOR_COLEGIO)
            ->assertJsonPath('usuario.colegio.id', $colegio->id);

        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Dirección INED',
            'email' => 'direccion@ined.edu',
            'rol' => Usuario::ROL_ADMINISTRADOR_COLEGIO,
            'colegio_id' => $colegio->id,
        ]);
    }

    public function test_new_professors_and_students_join_the_selected_colleges_general_class(): void
    {
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Asignaciones');
        $colegio = Colegio::firstOrFail();
        $clase = Clase::where('colegio_id', $colegio->id)->where('codigo', 'GENERAL')->firstOrFail();

        $profesorResponse = $this->withSession(['usuario_id' => $master->id])
            ->postJson('/api/usuarios', [
                'nombre' => 'Profesor Asignado',
                'email' => 'asignado@ined.edu',
                'rol' => Usuario::ROL_PROFESOR,
                'colegio_id' => $colegio->id,
                'clave' => 'Temporal123',
            ])->assertCreated();

        $profesor = Usuario::findOrFail($profesorResponse->json('usuario.id'));
        $this->assertTrue($profesor->clasesComoProfesor->contains($clase));

        $alumnoResponse = $this->postJson('/api/usuarios', [
            'nombre' => 'Alumno Asignado',
            'rol' => Usuario::ROL_ALUMNO,
            'colegio_id' => $colegio->id,
            'clave' => 'Temporal123',
        ])->assertCreated();

        $alumno = Usuario::findOrFail($alumnoResponse->json('usuario.id'));
        $this->assertTrue($alumno->clasesComoAlumno->contains($clase));
    }

    public function test_professor_inherits_its_college_for_students_and_cannot_manage_another_college(): void
    {
        $ined = Colegio::firstOrFail();
        $otro = Colegio::create([
            'nombre' => 'Colegio Externo',
            'codigo' => 'EXTERNO',
            'activo' => true,
        ]);
        Clase::create([
            'colegio_id' => $otro->id,
            'nombre' => 'Clase General',
            'codigo' => 'GENERAL',
            'activa' => true,
        ]);
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor INED', $ined->id);
        $alumnoExterno = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Externo', $otro->id);
        $claseIned = Clase::where('colegio_id', $ined->id)->where('codigo', 'GENERAL')->firstOrFail();
        $claseIned->agregarProfesor($profesor);

        $response = $this->withSession(['usuario_id' => $profesor->id])
            ->postJson('/api/usuarios', [
                'nombre' => 'Alumno del Profesor',
                'rol' => Usuario::ROL_ALUMNO,
                'colegio_id' => $otro->id,
                'clase_id' => $claseIned->id,
                'clave' => 'Temporal123',
            ])->assertCreated();

        $this->assertSame($ined->id, $response->json('usuario.colegio_id'));
        $this->getJson('/api/usuarios-por-rol/Alumno')
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Alumno del Profesor'])
            ->assertJsonMissing(['nombre' => $alumnoExterno->nombre]);
        $this->postJson("/api/usuarios/{$alumnoExterno->id}/reset-password", [
            'nueva_clave' => 'Nueva1234',
        ])->assertForbidden();
    }

    public function test_occupied_college_cannot_be_deleted_but_can_be_deactivated(): void
    {
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Estado');
        $colegio = Colegio::firstOrFail();
        $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Ocupante', $colegio->id);

        $this->withSession(['usuario_id' => $master->id])
            ->deleteJson("/api/colegios/{$colegio->id}")
            ->assertStatus(409)
            ->assertJsonPath('code', 'COLLEGE_IN_USE');

        $this->putJson("/api/colegios/{$colegio->id}", ['activo' => false])
            ->assertOk()
            ->assertJsonPath('colegio.activo', false);

        $this->postJson('/api/usuarios', [
            'nombre' => 'Alumno Bloqueado',
            'rol' => Usuario::ROL_ALUMNO,
            'colegio_id' => $colegio->id,
            'clave' => 'Temporal123',
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'COLLEGE_INACTIVE');
    }

    public function test_master_can_consult_the_complete_hierarchy_of_a_college(): void
    {
        $colegio = Colegio::firstOrFail();
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Jerarquía');
        $administrador = $this->usuario(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Director Jerarquía', $colegio->id);
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Jerarquía', $colegio->id);
        $alumno = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Jerarquía', $colegio->id);
        $clase = Clase::where('colegio_id', $colegio->id)->where('codigo', 'GENERAL')->firstOrFail();
        $clase->agregarProfesor($profesor);
        $clase->agregarAlumno($alumno);

        $this->withSession(['usuario_id' => $master->id])
            ->getJson("/api/colegios/{$colegio->id}/resumen")
            ->assertOk()
            ->assertJsonFragment(['nombre' => $administrador->nombre])
            ->assertJsonFragment(['nombre' => $profesor->nombre])
            ->assertJsonFragment(['nombre' => $alumno->nombre])
            ->assertJsonPath('clases.0.profesores_count', 1)
            ->assertJsonPath('clases.0.alumnos_count', 1);
    }

    public function test_master_moves_a_user_and_reassigns_the_general_class(): void
    {
        $origen = Colegio::firstOrFail();
        $destino = Colegio::create(['nombre' => 'Colegio Destino', 'codigo' => 'DESTINO', 'activo' => true]);
        $claseDestino = Clase::create([
            'colegio_id' => $destino->id, 'nombre' => 'Clase General', 'codigo' => 'GENERAL', 'activa' => true,
        ]);
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Traslado');
        $alumno = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Traslado', $origen->id);
        Clase::where('colegio_id', $origen->id)->where('codigo', 'GENERAL')->firstOrFail()->agregarAlumno($alumno);

        $this->withSession(['usuario_id' => $master->id])
            ->patchJson("/api/usuarios/{$alumno->id}/colegio", ['colegio_id' => $destino->id])
            ->assertOk()
            ->assertJsonPath('usuario.colegio_id', $destino->id);

        $alumno->refresh();
        $this->assertSame($destino->id, $alumno->colegio_id);
        $this->assertCount(1, $alumno->clasesComoAlumno);
        $this->assertTrue($alumno->clasesComoAlumno->contains($claseDestino));
        $this->assertDatabaseHas('registros_administrativos', [
            'actor_usuario_id' => $master->id, 'objetivo_usuario_id' => $alumno->id,
            'accion' => 'usuario.trasladado', 'colegio_id' => $destino->id,
        ]);
    }

    public function test_deactivated_user_and_college_lose_access(): void
    {
        $colegio = Colegio::firstOrFail();
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Estado Usuarios');
        $alumno = $this->usuario(Usuario::ROL_ALUMNO, 'Alumno Desactivable', $colegio->id);

        $this->withSession(['usuario_id' => $master->id])
            ->patchJson("/api/usuarios/{$alumno->id}/estado", ['activo' => false])
            ->assertOk()
            ->assertJsonPath('usuario.activo', false);

        $this->flushSession();
        $this->withSession(['usuario_id' => $alumno->id])
            ->get('/inicio-alumno')
            ->assertRedirect('/login');

        $alumno->update(['activo' => true]);
        $colegio->update(['activo' => false]);
        $this->withSession(['usuario_id' => $alumno->id])
            ->get('/inicio-alumno')
            ->assertRedirect('/login');
    }

    public function test_sensitive_master_actions_are_available_in_the_audit_log(): void
    {
        $colegio = Colegio::firstOrFail();
        $master = $this->usuario(Usuario::ROL_MASTER, 'Master Auditoría');
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, 'Profesor Auditado', $colegio->id);

        $this->withSession(['usuario_id' => $master->id])
            ->postJson("/api/usuarios/{$profesor->id}/reset-password", ['nueva_clave' => 'Nueva1234'])
            ->assertOk();

        $this->getJson('/api/auditoria')
            ->assertOk()
            ->assertJsonPath('data.0.accion', 'usuario.clave_restaurada')
            ->assertJsonPath('data.0.actor.nombre', $master->nombre)
            ->assertJsonPath('data.0.objetivo_usuario.nombre', $profesor->nombre);

        $registro = DB::table('registros_administrativos')->first();
        $this->assertStringNotContainsString('Nueva1234', (string) $registro->detalles);
    }

    private function usuario(string $rol, string $nombre, ?int $colegioId = null): Usuario
    {
        return Usuario::create([
            'colegio_id' => $colegioId,
            'nombre' => $nombre,
            'email' => in_array($rol, [Usuario::ROL_PROFESOR, Usuario::ROL_ADMINISTRADOR_COLEGIO], true)
                ? strtolower(str_replace(' ', '.', $nombre)).'@example.test'
                : null,
            'clave' => Hash::make('Clave1234'),
            'rol' => $rol,
            'cambio_clave_requerido' => false,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ]);
    }
}
