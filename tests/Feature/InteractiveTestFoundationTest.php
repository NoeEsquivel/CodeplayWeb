<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\TestBancoPregunta;
use App\Models\Usuario;
use App\Services\InteractiveTestRules;
use App\Services\LegacyTestBankImporter;
use App\Services\LiveTestScoringService;
use App\Services\LiveTestSessionService;
use App\Services\TestAnswerEvaluator;
use App\Services\TestBankService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InteractiveTestFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) $this->markTestSkipped('Se requiere pdo_sqlite.');
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_legacy_bank_is_imported_without_altering_original_tables(): void
    {
        $documentId = DB::table('documentacion')->insertGetId(['titulo' => 'Fundamentos HTML', 'contenido' => 'Contenido heredado', 'created_at' => now(), 'updated_at' => now()]);
        $testId = DB::table('tests')->insertGetId(['documentacion_id' => $documentId, 'titulo' => 'Fundamentos HTML', 'modulo' => 'html1', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('preguntas_test')->insert([
            'test_id' => $testId, 'pregunta' => '¿Qué etiqueta crea un enlace?', 'opcion_a' => '<a>', 'opcion_b' => '<p>',
            'opcion_c' => '<div>', 'opcion_d' => '<img>', 'respuesta_correcta' => 'a', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $primera = app(LegacyTestBankImporter::class)->importar();
        $segunda = app(LegacyTestBankImporter::class)->importar();

        $this->assertSame(['bancos' => 1, 'preguntas' => 1], $primera);
        $this->assertSame(['bancos' => 0, 'preguntas' => 0], $segunda);
        $this->assertDatabaseHas('test_bancos', ['clave_sistema' => 'html1', 'origen' => 'sistema', 'categoria' => 'html']);
        $this->assertDatabaseHas('preguntas_test', ['id' => 1, 'respuesta_correcta' => 'a']);
        $this->assertSame(['a'], DB::table('test_banco_preguntas')->first() ? json_decode(DB::table('test_banco_preguntas')->first()->respuestas_correctas, true) : []);
    }

    public function test_question_types_are_evaluated_with_normalized_answers(): void
    {
        $evaluador = app(TestAnswerEvaluator::class);
        $directa = new TestBancoPregunta(['tipo' => 'respuesta_directa', 'respuestas_correctas' => ['Cascading Style Sheets', 'CSS']]);
        $multiple = new TestBancoPregunta(['tipo' => 'seleccion_multiple', 'respuestas_correctas' => ['a', 'c']]);
        $booleana = new TestBancoPregunta(['tipo' => 'verdadero_falso', 'respuestas_correctas' => [true]]);

        $this->assertTrue($evaluador->esCorrecta($directa, '  cascading  style sheets '));
        $this->assertTrue($evaluador->esCorrecta($multiple, ['c', 'a']));
        $this->assertFalse($evaluador->esCorrecta($multiple, ['a']));
        $this->assertTrue($evaluador->esCorrecta($booleana, 'verdadero'));
    }

    public function test_shared_student_banks_are_limited_to_same_class_and_school(): void
    {
        [$collegeA, $collegeB] = $this->colleges();
        $classA = $this->class($collegeA, 'Primero A', '1A');
        $classOther = $this->class($collegeA, 'Primero B', '1B');
        $owner = $this->user(Usuario::ROL_ALUMNO, 'Propietario', $collegeA->id);
        $classmate = $this->user(Usuario::ROL_ALUMNO, 'Compañero', $collegeA->id);
        $sameSchoolOtherClass = $this->user(Usuario::ROL_ALUMNO, 'Otra Clase', $collegeA->id);
        $foreign = $this->user(Usuario::ROL_ALUMNO, 'Otro Colegio', $collegeB->id);
        $classA->agregarAlumno($owner);
        $classA->agregarAlumno($classmate);
        $classOther->agregarAlumno($sameSchoolOtherClass);

        $service = app(TestBankService::class);
        $bank = $service->crear($owner, ['nombre' => 'Mi repaso']);
        $service->agregarPregunta($owner, $bank, ['tipo' => 'respuesta_directa', 'enunciado' => '¿Qué significa HTML?', 'respuestas_correctas' => ['HyperText Markup Language']]);
        $service->publicar($owner, $bank);
        $code = $service->habilitarCodigo($owner, $bank);

        $service->agregarPorCodigo($classmate, $code);
        $this->assertDatabaseHas('test_biblioteca_usuario', ['banco_id' => $bank->id, 'usuario_id' => $classmate->id, 'agregado_por_codigo' => true]);

        foreach ([$sameSchoolOtherClass, $foreign] as $blocked) {
            try {
                $service->agregarPorCodigo($blocked, $code);
                $this->fail('Se permitió importar un banco sin compartir clase/colegio.');
            } catch (\DomainException) {
                $this->assertDatabaseMissing('test_biblioteca_usuario', ['banco_id' => $bank->id, 'usuario_id' => $blocked->id]);
            }
        }
    }

    public function test_class_sessions_validate_role_membership_school_limits_and_secret_hash(): void
    {
        [$collegeA, $collegeB] = $this->colleges();
        $teacher = $this->user(Usuario::ROL_PROFESOR, 'Profesor Uno', $collegeA->id);
        $otherTeacher = $this->user(Usuario::ROL_PROFESOR, 'Profesor Dos', $collegeA->id);
        $class = $this->class($collegeA, 'Clase Docente', 'DOC');
        $foreignClass = $this->class($collegeB, 'Clase Externa', 'EXT');
        $class->agregarProfesor($teacher);
        $session = app(LiveTestSessionService::class)->crear($teacher, [
            'tipo' => 'clase', 'modalidad' => 'trivia', 'clase_id' => $class->id,
            'palabra_clave' => 'secreto', 'max_participantes' => 30,
        ]);

        $this->assertSame($collegeA->id, $session->colegio_id);
        $this->assertSame($class->id, $session->clase_id);
        $this->assertNotSame('secreto', $session->palabra_clave_hash);
        $this->assertTrue(Hash::check('secreto', $session->palabra_clave_hash));

        foreach ([[$otherTeacher, $class->id], [$teacher, $foreignClass->id]] as [$actor, $classId]) {
            try {
                app(LiveTestSessionService::class)->crear($actor, ['tipo' => 'clase', 'modalidad' => 'trivia', 'clase_id' => $classId]);
                $this->fail('Se permitió crear una sesión de una clase no autorizada.');
            } catch (\Throwable) {
                $this->assertTrue(true);
            }
        }

        $this->expectException(\InvalidArgumentException::class);
        app(LiveTestSessionService::class)->crear($teacher, ['tipo' => 'clase', 'modalidad' => 'trivia', 'clase_id' => $class->id, 'max_participantes' => 31]);
    }

    public function test_test_points_are_separate_and_state_transitions_are_safe(): void
    {
        [$college] = $this->colleges();
        $student = $this->user(Usuario::ROL_ALUMNO, 'Anfitrión', $college->id);
        $session = app(LiveTestSessionService::class)->crear($student, ['tipo' => 'amigos', 'modalidad' => 'trivia']);
        app(InteractiveTestRules::class)->transicionar($session, 'espera');
        app(InteractiveTestRules::class)->transicionar($session, 'activa');

        $this->assertSame('activa', $session->fresh()->estado);
        $this->assertSame(2, $session->fresh()->version);
        $this->assertSame(100, app(LiveTestScoringService::class)->puntosPorOrden(true, 1, 2));
        $this->assertSame(50, app(LiveTestScoringService::class)->puntosPorOrden(true, 2, 2));
        $this->assertSame(0, app(LiveTestScoringService::class)->puntosPorOrden(false, 1, 2));
        $this->assertSame(0, $student->fresh()->puntos_totales);

        $this->expectException(\DomainException::class);
        app(InteractiveTestRules::class)->transicionar($session->fresh(), 'configuracion');
    }

    private function colleges(): array
    {
        $suffix = str_replace('.', '', uniqid('', true));
        return [
            Colegio::create(['nombre' => 'Colegio A '.$suffix, 'codigo' => 'A-'.$suffix, 'activo' => true]),
            Colegio::create(['nombre' => 'Colegio B '.$suffix, 'codigo' => 'B-'.$suffix, 'activo' => true]),
        ];
    }

    private function class(Colegio $college, string $name, string $code): Clase
    {
        return Clase::create(['colegio_id' => $college->id, 'nombre' => $name, 'codigo' => $code, 'ciclo_escolar' => '2026', 'activa' => true]);
    }

    private function user(string $role, string $name, ?int $collegeId): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId, 'nombre' => $name,
            'email' => $role === Usuario::ROL_ALUMNO ? null : strtolower(str_replace(' ', '.', $name)).uniqid().'@example.test',
            'email_verified_at' => $role === Usuario::ROL_ALUMNO ? null : now(),
            'clave' => Hash::make('Clave1234'), 'rol' => $role, 'activo' => true,
            'cambio_clave_requerido' => false, 'puntos_totales' => 0, 'puntos_usados' => 0,
        ]);
    }
}


