<?php

namespace Tests\Feature;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\Usuario;
use App\Services\LiveTestSessionService;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InteractiveTestDatabaseRulesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_share_codes_never_cross_class_or_school(): void
    {
        [$collegeA, $collegeB] = $this->colleges();
        $classA = $this->class($collegeA, 'Clase A', 'A');
        $classB = $this->class($collegeA, 'Clase B', 'B');
        $owner = $this->user(Usuario::ROL_ALUMNO, 'Dueño', $collegeA->id);
        $classmate = $this->user(Usuario::ROL_ALUMNO, 'Compañero', $collegeA->id);
        $otherClass = $this->user(Usuario::ROL_ALUMNO, 'Otra clase', $collegeA->id);
        $foreign = $this->user(Usuario::ROL_ALUMNO, 'Externo', $collegeB->id);
        $classA->agregarAlumno($owner);
        $classA->agregarAlumno($classmate);
        $classB->agregarAlumno($otherClass);

        $service = app(TestBankService::class);
        $bank = $service->crear($owner, ['nombre' => 'Repaso']);
        $service->agregarPregunta($owner, $bank, [
            'tipo' => 'respuesta_directa', 'enunciado' => '¿Qué significa HTML?',
            'respuestas_correctas' => ['HyperText Markup Language'],
        ]);
        $service->publicar($owner, $bank);
        $code = $service->habilitarCodigo($owner, $bank);
        $service->agregarPorCodigo($classmate, $code);

        $this->assertDatabaseHas('test_biblioteca_usuario', ['banco_id' => $bank->id, 'usuario_id' => $classmate->id]);
        foreach ([$otherClass, $foreign] as $blocked) {
            try { $service->agregarPorCodigo($blocked, $code); $this->fail('Se cruzó el límite de clase o colegio.'); }
            catch (\DomainException) { $this->assertDatabaseMissing('test_biblioteca_usuario', ['banco_id' => $bank->id, 'usuario_id' => $blocked->id]); }
        }
    }

    public function test_only_assigned_teacher_can_create_class_session_and_secret_is_hashed(): void
    {
        [$college] = $this->colleges();
        $teacher = $this->user(Usuario::ROL_PROFESOR, 'Profesor asignado', $college->id);
        $other = $this->user(Usuario::ROL_PROFESOR, 'Profesor no asignado', $college->id);
        $class = $this->class($college, 'Clase prueba', 'TEST');
        $class->agregarProfesor($teacher);

        $session = app(LiveTestSessionService::class)->crear($teacher, [
            'tipo' => 'clase', 'modalidad' => 'trivia', 'clase_id' => $class->id,
            'palabra_clave' => 'clave-local', 'max_participantes' => 30,
        ]);

        $this->assertSame($college->id, $session->colegio_id);
        $this->assertTrue(Hash::check('clave-local', $session->palabra_clave_hash));
        $this->assertSame(0, $teacher->fresh()->puntos_totales);

        $this->expectException(\DomainException::class);
        app(LiveTestSessionService::class)->crear($other, ['tipo' => 'clase', 'modalidad' => 'trivia', 'clase_id' => $class->id]);
    }

    private function colleges(): array
    {
        $suffix = str_replace('.', '', uniqid('', true));
        return [
            Colegio::create(['nombre' => 'Colegio A '.$suffix, 'codigo' => 'TA-'.$suffix, 'activo' => true]),
            Colegio::create(['nombre' => 'Colegio B '.$suffix, 'codigo' => 'TB-'.$suffix, 'activo' => true]),
        ];
    }

    private function class(Colegio $college, string $name, string $code): Clase
    {
        return Clase::create(['colegio_id' => $college->id, 'nombre' => $name.uniqid(), 'codigo' => $code.uniqid(), 'ciclo_escolar' => '2026', 'activa' => true]);
    }

    private function user(string $role, string $name, int $collegeId): Usuario
    {
        return Usuario::create([
            'colegio_id' => $collegeId, 'nombre' => $name.uniqid(),
            'email' => $role === Usuario::ROL_ALUMNO ? null : uniqid().'@example.test',
            'email_verified_at' => $role === Usuario::ROL_ALUMNO ? null : now(),
            'clave' => Hash::make('Clave1234'), 'rol' => $role, 'activo' => true,
            'cambio_clave_requerido' => false, 'puntos_totales' => 0, 'puntos_usados' => 0,
        ]);
    }
}


