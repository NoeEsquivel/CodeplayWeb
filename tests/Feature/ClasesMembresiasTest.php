<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClasesMembresiasTest extends TestCase
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

        $this->insertarUsuario('Alumno Uno', 'Alumno');
        $this->insertarUsuario('Alumno Dos', 'Alumno');
        $this->insertarUsuario('Profesor Uno', 'Administrador');
        $this->insertarUsuario('Profesor Dos', 'Administrador');
        $this->insertarUsuario('Administrador Escolar', 'Master');
        $this->insertarUsuario('Master Global', 'Master');

        foreach ([
            '2026_09_24_120000_create_colegios_and_expand_user_roles.php',
            '2026_09_24_121000_add_email_to_usuarios_table.php',
            '2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php',
            '2026_09_24_123000_create_clases_and_memberships.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_default_class_contains_existing_students_and_professors(): void
    {
        $colegio = Colegio::where('codigo', 'INED-MARIA-LUISA')->firstOrFail();
        $clase = Clase::where('colegio_id', $colegio->id)
            ->where('codigo', 'GENERAL')
            ->firstOrFail();

        $this->assertSame('Clase General', $clase->nombre);
        $this->assertTrue($clase->activa);
        $this->assertTrue($clase->colegio->is($colegio));
        $this->assertTrue($colegio->clases->contains($clase));
        $this->assertCount(2, $clase->profesores);
        $this->assertCount(2, $clase->alumnos);
        $this->assertSame(0, DB::table('clase_profesor')->whereIn('usuario_id', function ($query) {
            $query->select('id')->from('usuarios')->where('rol', Usuario::ROL_MASTER);
        })->count());
    }

    public function test_students_and_professors_can_belong_to_multiple_classes_without_duplicates(): void
    {
        $colegio = Colegio::where('codigo', 'INED-MARIA-LUISA')->firstOrFail();
        $claseAdicional = Clase::create([
            'colegio_id' => $colegio->id,
            'nombre' => 'Programación Avanzada',
            'codigo' => 'PROG-AVANZADA',
            'activa' => true,
        ]);
        $alumno = Usuario::where('nombre', 'Alumno Uno')->firstOrFail();
        $profesor = Usuario::where('nombre', 'Profesor Uno')->firstOrFail();

        $claseAdicional->agregarAlumno($alumno);
        $claseAdicional->agregarAlumno($alumno);
        $claseAdicional->agregarProfesor($profesor);
        $claseAdicional->agregarProfesor($profesor);

        $this->assertCount(2, $alumno->fresh()->clasesComoAlumno);
        $this->assertCount(2, $profesor->fresh()->clasesComoProfesor);
        $this->assertSame(1, DB::table('clase_alumno')
            ->where('clase_id', $claseAdicional->id)
            ->where('usuario_id', $alumno->id)
            ->count());
        $this->assertSame(1, DB::table('clase_profesor')
            ->where('clase_id', $claseAdicional->id)
            ->where('usuario_id', $profesor->id)
            ->count());
    }

    public function test_membership_rejects_an_incorrect_role(): void
    {
        $clase = Clase::where('codigo', 'GENERAL')->firstOrFail();
        $alumno = Usuario::where('nombre', 'Alumno Uno')->firstOrFail();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Solo un usuario Profesor');

        $clase->agregarProfesor($alumno);
    }

    public function test_membership_rejects_a_user_from_another_college(): void
    {
        $clase = Clase::where('codigo', 'GENERAL')->firstOrFail();
        $otroColegio = Colegio::create([
            'nombre' => 'Colegio Aislado',
            'codigo' => 'COLEGIO-AISLADO',
            'activo' => true,
        ]);
        $alumnoExterno = Usuario::create([
            'colegio_id' => $otroColegio->id,
            'nombre' => 'Alumno Externo',
            'clave' => Hash::make('Clave1234'),
            'rol' => Usuario::ROL_ALUMNO,
            'cambio_clave_requerido' => false,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('deben pertenecer al mismo colegio');

        $clase->agregarAlumno($alumnoExterno);
    }
    public function test_class_codes_are_unique_inside_each_college(): void
    {
        $otroColegio = Colegio::create([
            'nombre' => 'Otro Colegio',
            'codigo' => 'OTRO-COLEGIO',
            'activo' => true,
        ]);

        $clase = Clase::create([
            'colegio_id' => $otroColegio->id,
            'nombre' => 'Clase General',
            'codigo' => 'GENERAL',
            'activa' => true,
        ]);

        $this->assertDatabaseHas('clases', [
            'id' => $clase->id,
            'colegio_id' => $otroColegio->id,
            'codigo' => 'GENERAL',
        ]);
        $this->assertSame(2, Clase::where('codigo', 'GENERAL')->count());
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