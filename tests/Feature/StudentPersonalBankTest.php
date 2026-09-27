<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\TestBankService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentPersonalBankTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_can_manage_all_question_types_drafts_order_publish_share_duplicate_and_delete(): void
    {
        $student = $this->student('Creador');
        $service = app(TestBankService::class);
        $bank = $service->crear($student, ['nombre' => 'Repaso personal', 'descripcion' => 'Mi material', 'materia' => 'Desarrollo web']);

        $this->assertSame('borrador', $bank->estado);
        $this->assertSame('Desarrollo web', $bank->materia);

        $single = $service->agregarPregunta($student, $bank, [
            'tipo' => 'opcion_unica', 'enunciado' => 'Selecciona HTML', 'opciones' => ['a' => 'HTML', 'b' => 'CSS'],
            'respuestas_correctas' => ['a'], 'explicacion' => 'HTML estructura el contenido.', 'tiempo_limite' => 25,
        ]);
        $multiple = $service->agregarPregunta($student, $bank, [
            'tipo' => 'seleccion_multiple', 'enunciado' => 'Selecciona dos', 'opciones' => ['a' => 'Uno', 'b' => 'Dos', 'c' => 'Tres'],
            'respuestas_correctas' => ['a', 'c'], 'tiempo_limite' => 30,
        ]);
        $boolean = $service->agregarPregunta($student, $bank, [
            'tipo' => 'verdadero_falso', 'enunciado' => 'CSS aplica estilos', 'respuestas_correctas' => ['1'], 'tiempo_limite' => 20,
        ]);
        $written = $service->agregarPregunta($student, $bank, [
            'tipo' => 'respuesta_directa', 'enunciado' => 'Escribe las siglas', 'respuestas_correctas' => ['CSS', 'Cascading Style Sheets'],
            'explicacion' => 'Se aceptan ambas formas.', 'tiempo_limite' => 40,
        ]);

        $service->reordenar($student, $bank, [$written->id, $boolean->id, $multiple->id, $single->id]);
        $this->assertSame([$written->id, $boolean->id, $multiple->id, $single->id], $bank->fresh()->preguntas->pluck('id')->all());
        $this->assertSame(['CSS', 'Cascading Style Sheets'], $written->fresh()->respuestas_correctas);

        $service->publicar($student, $bank);
        $code = $service->habilitarCodigo($student, $bank->fresh());
        $this->assertSame(8, strlen($code));
        $this->assertTrue($bank->fresh()->compartible);

        $copy = $service->duplicar($student, $bank->fresh());
        $this->assertSame('borrador', $copy->estado);
        $this->assertSame(4, $copy->preguntas()->count());
        $this->assertNull($copy->codigo_compartir);

        $service->actualizar($student, $bank->fresh(), ['nombre' => 'Versión editada', 'materia' => 'Web', 'descripcion' => 'Nueva']);
        $this->assertSame('borrador', $bank->fresh()->estado);
        $this->assertFalse($bank->fresh()->compartible);

        $service->eliminar($student, $copy);
        $this->assertSoftDeleted('test_bancos', ['id' => $copy->id]);
    }

    public function test_another_student_cannot_view_or_modify_the_bank(): void
    {
        $owner = $this->student('Propietario');
        $intruder = $this->student('Intruso', 0, $owner->colegio_id);
        $bank = app(TestBankService::class)->crear($owner, ['nombre' => 'Privado', 'materia' => 'HTML']);

        $this->withSession($this->sessionFor($intruder))
            ->get(route('alumno.bancos.edit', $bank))
            ->assertNotFound();
        $this->put(route('alumno.bancos.update', $bank), ['nombre' => 'Robado', 'materia' => 'Otro'])
            ->assertNotFound();
        $this->delete(route('alumno.bancos.destroy', $bank))->assertNotFound();

        $this->assertDatabaseHas('test_bancos', ['id' => $bank->id, 'nombre' => 'Privado', 'deleted_at' => null]);
    }

    public function test_preview_is_random_one_by_one_uses_configured_timer_and_does_not_add_codeplay_points(): void
    {
        $student = $this->student('Práctica', 430);
        $service = app(TestBankService::class);
        $bank = $service->crear($student, ['nombre' => 'Banco con tiempo', 'materia' => 'CSS']);
        $question = $service->agregarPregunta($student, $bank, [
            'tipo' => 'respuesta_directa', 'enunciado' => '¿Qué lenguaje da estilos?',
            'respuestas_correctas' => ['CSS'], 'explicacion' => 'CSS define la presentación.', 'tiempo_limite' => 5,
        ]);

        $this->withSession($this->sessionFor($student))
            ->post(route('alumno.bancos.preview', $bank))
            ->assertRedirect();

        $session = TestSesion::where('anfitrion_usuario_id', $student->id)->latest('id')->firstOrFail();
        $this->assertTrue((bool) $session->configuracion['con_tiempo']);
        $this->assertSame($bank->id, (int) $session->configuracion['banco_id']);

        $this->get(route('alumno.tests.codeplay.jugar', $session))
            ->assertOk()
            ->assertSee('Banco con tiempo')
            ->assertSee('id="timer"', false);

        $emission = $session->preguntas()->firstOrFail();
        $emission->forceFill(['abierta_at' => now()->subSeconds(10)])->save();
        $this->post(route('alumno.tests.codeplay.responder', $session), ['respuesta' => 'CSS'])->assertRedirect();
        $this->assertDatabaseHas('test_sesion_respuestas', [
            'sesion_pregunta_id' => $emission->id, 'correcta' => false, 'puntos_otorgados' => 0,
        ]);

        $this->get(route('alumno.tests.codeplay.jugar', $session))->assertRedirect();
        $this->get(route('alumno.tests.codeplay.resultado', $session))
            ->assertOk()->assertSee('CSS define la presentación.')->assertSee('Volver a editar el banco');
        $this->assertSame(430, $student->fresh()->puntos_totales);
    }

    private function student(string $name, int $points = 0, ?int $collegeId = null): Usuario
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $college = $collegeId ? Colegio::findOrFail($collegeId) : Colegio::create(['nombre' => 'Colegio '.$suffix, 'codigo' => 'PB-'.$suffix, 'activo' => true]);
        return Usuario::create([
            'colegio_id' => $college->id, 'nombre' => $name.' '.$suffix, 'clave' => Hash::make('Clave1234'),
            'rol' => Usuario::ROL_ALUMNO, 'activo' => true, 'cambio_clave_requerido' => false,
            'puntos_totales' => $points, 'puntos_usados' => 0,
        ]);
    }

    private function sessionFor(Usuario $student): array
    {
        return ['usuario_id' => $student->id, 'usuario_session_version' => $student->fresh()->session_version];
    }
}


