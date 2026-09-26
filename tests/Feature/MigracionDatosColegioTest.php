<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MigracionDatosColegioTest extends TestCase
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
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }

        $this->insertarUsuario('Alumno Actual', 'Alumno');
        $this->insertarUsuario('Profesor Actual', 'Administrador');
        $this->insertarUsuario('Master Actual', 'Master');

        foreach ([
            '2026_09_24_120000_create_colegios_and_expand_user_roles.php',
            '2026_09_24_121000_add_email_to_usuarios_table.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_existing_students_and_professors_are_assigned_to_ined_but_master_remains_global(): void
    {
        $migration = require database_path(
            'migrations/2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php'
        );

        $migration->up();
        $migration->up();

        $colegio = DB::table('colegios')->first();

        $this->assertNotNull($colegio);
        $this->assertSame('INED MARIA LUISA', $colegio->nombre);
        $this->assertSame('INED-MARIA-LUISA', $colegio->codigo);
        $this->assertSame(1, (int) $colegio->activo);
        $this->assertSame(1, DB::table('colegios')->count());

        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Alumno Actual',
            'rol' => Usuario::ROL_ALUMNO,
            'colegio_id' => $colegio->id,
        ]);
        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Profesor Actual',
            'rol' => Usuario::ROL_PROFESOR,
            'colegio_id' => $colegio->id,
        ]);
        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Master Actual',
            'rol' => Usuario::ROL_MASTER,
            'colegio_id' => null,
        ]);
    }

    public function test_assigned_college_is_available_in_the_professor_session(): void
    {
        $migration = require database_path(
            'migrations/2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php'
        );
        $migration->up();

        $profesor = Usuario::where('nombre', 'Profesor Actual')->firstOrFail();

        $this->withSession(['usuario_id' => $profesor->id])
            ->get('/inicio-profesor')
            ->assertOk()
            ->assertSessionHas('usuario_colegio_id', $profesor->colegio_id)
            ->assertSessionHas('usuario_colegio_nombre', 'INED MARIA LUISA');
    }

    private function insertarUsuario(string $nombre, string $rol): void
    {
        DB::table('usuarios')->insert([
            'nombre' => $nombre,
            'clave' => Hash::make('Clave1234'),
            'rol' => $rol,
            'cambio_clave_requerido' => false,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}