<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiTenantIsolationRegressionTest extends TestCase
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

    public function test_the_four_roles_are_rejected_outside_their_own_panels(): void
    {
        [$college] = $this->colleges();
        $student = $this->user(Usuario::ROL_ALUMNO, 'Alumno Matriz', $college->id);
        $teacher = $this->user(Usuario::ROL_PROFESOR, 'Profesor Matriz', $college->id);
        $schoolAdmin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Administrador Matriz', $college->id);
        $master = $this->user(Usuario::ROL_MASTER, 'Master Matriz', null);

        $this->withSession($this->sessionFor($student));
        $this->getJson('/usuario/puntos')->assertOk();
        $this->getJson('/api/profesor/clases')->assertForbidden();
        $this->getJson('/api/colegio/resumen')->assertForbidden();
        $this->getJson('/api/colegios')->assertForbidden();

        $this->flushSession();
        $this->withSession($this->sessionFor($teacher));
        $this->getJson('/api/profesor/clases')->assertOk();
        $this->getJson('/api/colegio/resumen')->assertForbidden();
        $this->getJson('/api/colegios')->assertForbidden();
        $this->get('/alumno/documentacion')->assertForbidden();

        $this->flushSession();
        $this->withSession($this->sessionFor($schoolAdmin));
        $this->getJson('/api/colegio/resumen')->assertOk();
        $this->getJson('/api/profesor/clases')->assertForbidden();
        $this->getJson('/api/colegios')->assertForbidden();
        $this->get('/alumno/tutoriales')->assertForbidden();

        $this->flushSession();
        $this->withSession($this->sessionFor($master));
        $this->getJson('/api/colegios')->assertOk();
        $this->getJson('/api/profesor/clases')->assertForbidden();
        $this->getJson('/api/colegio/resumen')->assertForbidden();
        $this->get('/alumno/aventura')->assertForbidden();
    }

    public function test_school_administrator_cannot_read_modify_assign_or_reset_another_college(): void
    {
        [$ownCollege, $foreignCollege] = $this->colleges();
        $admin = $this->user(Usuario::ROL_ADMINISTRADOR_COLEGIO, 'Administrador Aislado', $ownCollege->id);
        $ownClass = $this->class($ownCollege, 'Clase Propia', 'PROPIA');
        $foreignClass = $this->class($foreignCollege, 'Clase Secreta', 'SECRETA');
        $ownStudent = $this->user(Usuario::ROL_ALUMNO, 'Alumno Propio Aislado', $ownCollege->id, 120);
        $foreignStudent = $this->user(Usuario::ROL_ALUMNO, 'Alumno Ajeno Aislado', $foreignCollege->id, 9999);
        $foreignClass->agregarAlumno($foreignStudent);

        $this->withSession($this->sessionFor($admin))
            ->getJson('/api/colegio/clases')
            ->assertOk()
            ->assertJsonFragment(['nombre' => $ownClass->nombre])
            ->assertJsonMissing(['nombre' => $foreignClass->nombre]);

        $this->getJson('/api/colegio/usuarios-por-rol/Alumno')
            ->assertOk()
            ->assertJsonFragment(['nombre' => $ownStudent->nombre])
            ->assertJsonMissing(['nombre' => $foreignStudent->nombre]);

        $this->getJson('/api/colegio/resumen')
            ->assertOk()
            ->assertJsonPath('progreso.puntos_totales', 120)
            ->assertJsonMissing(['nombre' => $foreignStudent->nombre]);

        $this->putJson("/api/colegio/clases/{$foreignClass->id}", ['nombre' => 'Alterada'])->assertNotFound();
        $this->postJson("/api/colegio/clases/{$ownClass->id}/alumnos/{$foreignStudent->id}")->assertUnprocessable();
        $this->postJson("/api/colegio/usuarios/{$foreignStudent->id}/reset-password", ['nueva_clave' => 'Nueva1234'])->assertForbidden();

        $this->assertDatabaseHas('clases', ['id' => $foreignClass->id, 'nombre' => 'Clase Secreta']);
        $this->assertDatabaseMissing('clase_alumno', ['clase_id' => $ownClass->id, 'usuario_id' => $foreignStudent->id]);
    }

    public function test_teacher_reports_and_class_operations_never_cross_college_or_unassigned_classes(): void
    {
        [$ownCollege, $foreignCollege] = $this->colleges();
        $teacher = $this->user(Usuario::ROL_PROFESOR, 'Profesor Aislado', $ownCollege->id);
        $ownClass = $this->class($ownCollege, 'Clase Profesor', 'PROFESOR');
        $ownClass->agregarProfesor($teacher);
        $ownStudent = $this->user(Usuario::ROL_ALUMNO, 'Alumno Visible Auditoría', $ownCollege->id);
        $ownClass->agregarAlumno($ownStudent);

        $foreignTeacher = $this->user(Usuario::ROL_PROFESOR, 'Profesor Extranjero', $foreignCollege->id);
        $foreignClass = $this->class($foreignCollege, 'Clase Extranjera', 'EXTRANJERA');
        $foreignClass->agregarProfesor($foreignTeacher);
        $foreignStudent = $this->user(Usuario::ROL_ALUMNO, 'Alumno Invisible Auditoría', $foreignCollege->id);
        $foreignClass->agregarAlumno($foreignStudent);

        $this->withSession($this->sessionFor($teacher))
            ->getJson('/api/profesor/progreso')
            ->assertOk()
            ->assertJsonFragment(['nombre' => $ownStudent->nombre])
            ->assertJsonMissing(['nombre' => $foreignStudent->nombre]);

        $csv = $this->get('/api/profesor/progreso/exportar')->assertOk()->streamedContent();
        $this->assertStringContainsString($ownStudent->nombre, $csv);
        $this->assertStringNotContainsString($foreignStudent->nombre, $csv);

        $this->getJson('/api/profesor/progreso?clase_id='.$foreignClass->id)->assertNotFound();
        $this->getJson('/api/profesor/progreso?alumno_id='.$foreignStudent->id)->assertForbidden();
        $this->postJson("/api/profesor/clases/{$ownClass->id}/alumnos/{$foreignStudent->id}")->assertUnprocessable();
        $this->deleteJson("/api/profesor/clases/{$foreignClass->id}/alumnos/{$foreignStudent->id}")->assertNotFound();
        $this->assertDatabaseMissing('clase_alumno', ['clase_id' => $ownClass->id, 'usuario_id' => $foreignStudent->id]);
    }

    public function test_student_modules_only_write_and_return_the_authenticated_students_data(): void
    {
        [$college] = $this->colleges();
        $student = $this->user(Usuario::ROL_ALUMNO, 'Alumno Regresión', $college->id, 321);
        $other = $this->user(Usuario::ROL_ALUMNO, 'Alumno Regresión Ajeno', $college->id, 9876);

        $this->withSession($this->sessionFor($student))
            ->getJson('/usuario/puntos')
            ->assertOk()
            ->assertJsonPath('puntos_totales', 321);

        $this->get('/alumno/documentacion')->assertOk();
        $this->get('/alumno/documentacion/html1')->assertOk();
        $this->get('/alumno/tutoriales')->assertOk();
        $this->get('/alumno/desafios')->assertOk();
        $this->get('/alumno/aventura')->assertOk();

        $this->assertDatabaseHas('progreso_documentacion', ['usuario_id' => $student->id, 'documento' => 'html1']);
        $this->assertDatabaseMissing('progreso_documentacion', ['usuario_id' => $other->id]);
    }

    public function test_multitenant_indexes_exist_for_the_queries_used_by_panels_and_statistics(): void
    {
        $expected = [
            'usuarios' => 'usuarios_colegio_rol_activo_idx',
            'clases' => 'clases_colegio_activa_idx',
            'clase_profesor' => 'clase_profesor_usuario_clase_idx',
            'clase_alumno' => 'clase_alumno_usuario_clase_idx',
            'progreso_tutorial' => 'progreso_tutorial_usuario_estado_idx',
            'progreso_niveles' => 'progreso_niveles_usuario_estado_idx',
            'desafio_recompensas' => 'desafio_usuario_completado_idx',
            'resultados_test' => 'resultados_usuario_fecha_idx',
        ];

        foreach ($expected as $table => $index) {
            $indexes = collect(DB::select("PRAGMA index_list('{$table}')"))->pluck('name');
            $this->assertTrue($indexes->contains($index), "Falta el índice {$index} en {$table}.");
        }
    }

    private function colleges(): array
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $first = Colegio::create(['nombre' => 'Colegio A '.$suffix, 'codigo' => 'A-'.$suffix, 'activo' => true]);
        $second = Colegio::create(['nombre' => 'Colegio B '.$suffix, 'codigo' => 'B-'.$suffix, 'activo' => true]);
        return [$first, $second];
    }

    private function class(Colegio $college, string $name, string $code): Clase
    {
        return Clase::create([
            'colegio_id' => $college->id,
            'nombre' => $name,
            'codigo' => $code,
            'ciclo_escolar' => '2026',
            'activa' => true,
        ]);
    }

    private function user(string $role, string $name, ?int $collegeId, int $points = 0): Usuario
    {
        $user = Usuario::create([
            'colegio_id' => $collegeId,
            'nombre' => $name,
            'email' => in_array($role, [Usuario::ROL_PROFESOR, Usuario::ROL_ADMINISTRADOR_COLEGIO, Usuario::ROL_MASTER], true)
                ? strtolower(str_replace(' ', '.', $name)).'@example.test' : null,
            'email_verified_at' => $role === Usuario::ROL_ALUMNO ? null : now(),
            'clave' => Hash::make('Clave1234'),
            'rol' => $role,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => $points,
            'puntos_usados' => 0,
        ]);

        if ($role === Usuario::ROL_MASTER) {
            $user->twoFactorAuthentication()->create([
                'secret' => 'JBSWY3DPEHPK3PXP',
                'recovery_codes' => ['codigo-prueba'],
                'confirmed_at' => now(),
            ]);
        }

        return $user;
    }

    private function sessionFor(Usuario $user): array
    {
        $session = [
            'usuario_id' => $user->id,
            'usuario_session_version' => $user->fresh()->session_version,
        ];
        if ($user->esMaster()) $session['two_factor_verified_user_id'] = $user->id;
        return $session;
    }
}
