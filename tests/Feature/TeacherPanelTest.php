<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherPanelTest extends TestCase
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
            '2025_09_30_064502_create_tutoriales_table.php',
            '2025_09_30_064507_create_progreso_tutorial_table.php',
            '2025_09_30_064514_create_niveles_desafio_table.php',
            '2025_10_01_063305_add_cambio_clave_requerido_to_usuarios_table.php',
            '2025_10_02_083703_add_ultimo_acceso_to_usuarios_table.php',
            '2025_10_21_082507_create_desafio_recompensas_table.php',
            '2026_09_08_120000_create_aventura_preparacion_tables.php',
            '2026_09_24_120000_create_colegios_and_expand_user_roles.php',
            '2026_09_24_121000_add_email_to_usuarios_table.php',
            '2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php',
            '2026_09_24_123000_create_clases_and_memberships.php',
            '2026_09_25_091000_add_ciclo_escolar_to_clases.php',
            '2026_09_25_100000_add_user_status_and_admin_audit.php',
            '2026_09_26_100000_create_documentation_progress.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        Schema::table('aventura_expediciones', function (Blueprint $table) {
            $table->unsignedTinyInteger('escenario')->default(1);
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_teacher_only_lists_students_linked_to_own_classes(): void
    {
        [$teacher, $ownClass] = $this->teacherWithClass('Profesor Uno', 'Clase Uno');
        [$otherTeacher, $otherClass] = $this->teacherWithClass('Profesor Dos', 'Clase Dos', $teacher->colegio_id);
        $visible = $this->student('Alumno Visible', $teacher->colegio_id);
        $hidden = $this->student('Alumno Oculto', $teacher->colegio_id);
        $ownClass->agregarAlumno($visible);
        $otherClass->agregarAlumno($hidden);

        $this->withSession(['usuario_id' => $teacher->id])
            ->getJson('/api/usuarios-por-rol/Alumno')
            ->assertOk()
            ->assertJsonFragment(['nombre' => $visible->nombre])
            ->assertJsonMissing(['nombre' => $hidden->nombre]);

        $this->getJson('/api/profesor/clases')
            ->assertOk()
            ->assertJsonFragment(['nombre' => $ownClass->nombre])
            ->assertJsonMissing(['nombre' => $otherClass->nombre]);
    }

    public function test_teacher_creates_student_only_inside_an_assigned_class(): void
    {
        [$teacher, $ownClass] = $this->teacherWithClass('Profesor Creador', 'Clase Propia');
        [, $foreignClass] = $this->teacherWithClass('Profesor Ajeno', 'Clase Ajena', $teacher->colegio_id);

        $this->withSession(['usuario_id' => $teacher->id])
            ->postJson('/api/usuarios', [
                'nombre' => 'Alumno Sin Clase', 'rol' => Usuario::ROL_ALUMNO, 'clave' => 'Temporal123',
            ])->assertUnprocessable()->assertJsonValidationErrors('clase_id');

        $this->postJson('/api/usuarios', [
            'nombre' => 'Alumno Clase Ajena', 'rol' => Usuario::ROL_ALUMNO,
            'clase_id' => $foreignClass->id, 'clave' => 'Temporal123',
        ])->assertUnprocessable()->assertJsonPath('code', 'CLASS_NOT_ALLOWED');

        $response = $this->postJson('/api/usuarios', [
            'nombre' => 'Alumno Clase Propia', 'rol' => Usuario::ROL_ALUMNO,
            'clase_id' => $ownClass->id, 'clave' => 'Temporal123',
        ])->assertCreated();

        $studentId = $response->json('usuario.id');
        $this->assertDatabaseHas('clase_alumno', ['clase_id' => $ownClass->id, 'usuario_id' => $studentId]);
        $this->assertDatabaseMissing('clase_alumno', ['clase_id' => $foreignClass->id, 'usuario_id' => $studentId]);
    }

    public function test_teacher_can_reset_only_linked_students(): void
    {
        [$teacher, $class] = $this->teacherWithClass('Profesor Claves', 'Clase Claves');
        $linked = $this->student('Alumno Vinculado', $teacher->colegio_id);
        $unlinked = $this->student('Alumno No Vinculado', $teacher->colegio_id);
        $class->agregarAlumno($linked);

        $this->withSession(['usuario_id' => $teacher->id])
            ->postJson("/api/usuarios/{$linked->id}/reset-password", ['nueva_clave' => 'Nueva1234'])
            ->assertOk();

        $this->postJson("/api/usuarios/{$unlinked->id}/reset-password", ['nueva_clave' => 'Nueva1234'])
            ->assertForbidden();
    }

    public function test_progress_filters_never_expose_other_classes_or_students(): void
    {
        [$teacher, $ownClass] = $this->teacherWithClass('Profesor Reporte', 'Clase Reporte');
        [, $foreignClass] = $this->teacherWithClass('Profesor Otro', 'Clase Privada', $teacher->colegio_id);
        $ownStudent = $this->student('Alumno Reporte', $teacher->colegio_id);
        $foreignStudent = $this->student('Alumno Privado', $teacher->colegio_id);
        $ownClass->agregarAlumno($ownStudent);
        $foreignClass->agregarAlumno($foreignStudent);

        $this->withSession(['usuario_id' => $teacher->id])
            ->getJson('/api/profesor/progreso')
            ->assertOk()
            ->assertJsonFragment(['nombre' => $ownStudent->nombre])
            ->assertJsonMissing(['nombre' => $foreignStudent->nombre]);

        $this->getJson('/api/profesor/progreso?clase_id='.$foreignClass->id)->assertNotFound();
        $this->getJson('/api/profesor/progreso?alumno_id='.$foreignStudent->id)->assertForbidden();
    }

    public function test_progress_can_be_searched_by_full_name_or_username(): void
    {
        [$teacher, $class] = $this->teacherWithClass('Profesor Búsqueda', 'Clase Búsqueda');
        $first = $this->student('lgarcia', $teacher->colegio_id);
        $second = $this->student('mlopez', $teacher->colegio_id);
        $first->update(['nombre_completo' => 'Lucía García Méndez']);
        $second->update(['nombre_completo' => 'Mario López Pérez']);
        $class->agregarAlumno($first);
        $class->agregarAlumno($second);

        $this->withSession(['usuario_id' => $teacher->id])
            ->getJson('/api/profesor/progreso?buscar='.urlencode('Lucía García'))
            ->assertOk()
            ->assertJsonCount(1, 'alumnos')
            ->assertJsonPath('alumnos.0.usuario', 'lgarcia')
            ->assertJsonPath('resumen.alumnos', 1);

        $this->getJson('/api/profesor/progreso?buscar=mlopez')
            ->assertOk()
            ->assertJsonCount(1, 'alumnos')
            ->assertJsonPath('alumnos.0.nombre', 'Mario López Pérez');
    }

    public function test_module_progress_matches_current_data(): void
    {
        [$teacher, $class] = $this->teacherWithClass('Profesor Cálculos', 'Clase Cálculos');
        $student = $this->student('Alumno Cálculos', $teacher->colegio_id, 250);
        $class->agregarAlumno($student);

        DB::table('progreso_documentacion')->insert([
            ['usuario_id' => $student->id, 'documento' => 'html1', 'visto_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['usuario_id' => $student->id, 'documento' => 'css1', 'visto_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);
        $tutorial1 = DB::table('tutoriales')->insertGetId(['titulo' => 'Uno', 'dificultad' => 'facil', 'puntos_otorgados' => 10, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('tutoriales')->insert(['titulo' => 'Dos', 'dificultad' => 'medio', 'puntos_otorgados' => 10, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('progreso_tutorial')->insert(['usuario_id' => $student->id, 'tutorial_id' => $tutorial1, 'estado' => 'completado', 'fecha' => now(), 'created_at' => now(), 'updated_at' => now()]);
        foreach (range(1, 4) as $number) {
            DB::table('niveles_desafio')->insert(['numero' => $number, 'descripcion' => 'Nivel '.$number, 'dificultad' => 'facil', 'puntos_otorgados' => 10, 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach ([1, 2] as $number) {
            DB::table('desafio_recompensas')->insert(['usuario_id' => $student->id, 'nivel_desafio' => $number, 'completado_primera_vez' => true, 'puntos_obtenidos' => 10, 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('aventura_personajes')->insert(['usuario_id' => $student->id, 'nivel' => 7, 'experiencia' => 20, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('aventura_expediciones')->insert(['usuario_id' => $student->id, 'estado' => 'activa', 'equipo' => '[]', 'escenario' => 25, 'created_at' => now(), 'updated_at' => now()]);

        $this->withSession(['usuario_id' => $teacher->id])
            ->getJson('/api/profesor/progreso?alumno_id='.$student->id)
            ->assertOk()
            ->assertJsonPath('alumnos.0.modulos.documentacion.completados', 2)
            ->assertJsonPath('alumnos.0.modulos.tutoriales.porcentaje', 50)
            ->assertJsonPath('alumnos.0.modulos.desafios.porcentaje', 50)
            ->assertJsonPath('alumnos.0.modulos.aventura.porcentaje', 50)
            ->assertJsonPath('alumnos.0.puntos_totales', 250);

        $this->getJson('/api/profesor/progreso?alumno_id='.$student->id.'&modulo=tutoriales')
            ->assertOk()
            ->assertJsonPath('alumnos.0.modulos.tutoriales.completados', 1)
            ->assertJsonMissingPath('alumnos.0.modulos.aventura');
    }

    public function test_export_contains_only_authorized_filtered_summary(): void
    {
        [$teacher, $class] = $this->teacherWithClass('Profesor Exporta', 'Clase Exporta');
        $student = $this->student('Alumno Exportable', $teacher->colegio_id);
        $class->agregarAlumno($student);

        $response = $this->withSession(['usuario_id' => $teacher->id])
            ->get('/api/profesor/progreso/exportar?modulo=documentacion')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $content = (string) $response->getContent();
        $this->assertStringStartsWith('PK', $content);
    }

    public function test_viewing_documentation_records_progress_without_changing_student_page(): void
    {
        $college = Colegio::firstOrFail();
        $student = $this->student('Alumno Documentación', $college->id);

        $this->withSession(['usuario_id' => $student->id])
            ->get('/alumno/documentacion/html1')
            ->assertOk();

        $this->assertDatabaseHas('progreso_documentacion', [
            'usuario_id' => $student->id, 'documento' => 'html1',
        ]);
    }

    private function teacherWithClass(string $teacherName, string $className, ?int $collegeId = null): array
    {
        $collegeId ??= Colegio::firstOrFail()->id;
        $teacher = $this->user(Usuario::ROL_PROFESOR, $teacherName, $collegeId);
        $class = Clase::create([
            'colegio_id' => $collegeId,
            'nombre' => $className,
            'codigo' => strtoupper(str_replace(' ', '-', $className)),
            'ciclo_escolar' => '2026',
            'activa' => true,
        ]);
        $class->agregarProfesor($teacher);
        return [$teacher, $class];
    }

    private function student(string $name, int $collegeId, int $points = 0): Usuario
    {
        return $this->user(Usuario::ROL_ALUMNO, $name, $collegeId, $points);
    }

    private function user(string $role, string $name, int $collegeId, int $points = 0): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId,
            'nombre' => $name,
            'email' => $role === Usuario::ROL_PROFESOR ? strtolower(str_replace(' ', '.', $name)).'@example.test' : null,
            'clave' => Hash::make('Clave1234'),
            'rol' => $role,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => $points,
            'puntos_usados' => 0,
        ]);
    }
}
