<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentCodePlayTestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_menu_displays_five_options_and_opens_codeplay_categories(): void
    {
        $student = $this->student('Alumno menú');
        $this->withSession($this->sessionFor($student))
            ->get('/alumno/tests')
            ->assertOk()
            ->assertSee('Test CodePlay')
            ->assertSee('Estudia con ayuda de CodePlay')
            ->assertSee('Tengo un Código Amigo')
            ->assertSee('Test con amigos')
            ->assertSee('CodePlay en clase');

        $this->get('/alumno/tests/codeplay')
            ->assertOk()
            ->assertSee('Fundamentos HTML')
            ->assertSee('Diseño visual con CSS')
            ->assertSee('JavaScript básico')
            ->assertSee('Mundos de programación')
            ->assertSee('Test CodePlay completo');
    }

    public function test_codeplay_practice_uses_ten_questions_records_result_and_never_changes_codeplay_points(): void
    {
        $student = $this->student('Alumno práctica', 275);
        $this->systemBankWithTenQuestions();

        $this->withSession($this->sessionFor($student))
            ->post('/alumno/tests/codeplay/iniciar', ['categoria' => 'html'])
            ->assertRedirect();

        $session = TestSesion::where('anfitrion_usuario_id', $student->id)->latest('id')->firstOrFail();
        $this->assertSame(10, $session->preguntas()->count());
        $this->assertSame('activa', $session->estado);
        $this->assertFalse((bool) ($session->configuracion['otorga_puntos_codeplay'] ?? true));

        for ($i = 0; $i < 10; $i++) {
            $emission = $session->preguntas()
                ->whereDoesntHave('respuestas', fn ($query) => $query->whereHas('participante', fn ($p) => $p->where('usuario_id', $student->id)))
                ->with('pregunta')->orderBy('orden')->firstOrFail();
            $answer = $emission->pregunta->respuestas_correctas[0];

            $this->post(route('alumno.tests.codeplay.responder', $session), ['respuesta' => $answer])
                ->assertRedirect(route('alumno.tests.codeplay.jugar', $session));
        }

        $this->get(route('alumno.tests.codeplay.jugar', $session))
            ->assertRedirect(route('alumno.tests.codeplay.resultado', $session));
        $this->get(route('alumno.tests.codeplay.resultado', $session))
            ->assertOk()
            ->assertSee('10')
            ->assertSee('Aciertos')
            ->assertSee('no modificó tus puntos CodePlay');

        $this->assertDatabaseHas('test_resultados_sesion', [
            'sesion_id' => $session->id, 'usuario_id' => $student->id,
            'total_preguntas' => 10, 'correctas' => 10, 'incorrectas' => 0, 'puntos_test' => 0,
        ]);
        $this->assertSame(275, $student->fresh()->puntos_totales);
    }

    public function test_complete_practice_explicitly_excludes_integrated_projects(): void
    {
        $student = $this->student('Alumno completo');
        $this->systemBankWithTenQuestions('html');
        $projectBank = $this->systemBankWithTenQuestions('proyectos');

        $this->withSession($this->sessionFor($student))
            ->post('/alumno/tests/codeplay/iniciar', ['categoria' => 'completo'])
            ->assertRedirect();

        $session = TestSesion::where('anfitrion_usuario_id', $student->id)->latest('id')->firstOrFail();
        $this->assertFalse($session->bancos()->where('test_bancos.id', $projectBank->id)->exists());
        $this->assertSame(10, $session->preguntas()->count());
    }

    public function test_student_cannot_open_another_students_practice(): void
    {
        $owner = $this->student('Alumno propietario');
        $intruder = $this->student('Alumno intruso');
        $this->systemBankWithTenQuestions();

        $this->withSession($this->sessionFor($owner))
            ->post('/alumno/tests/codeplay/iniciar', ['categoria' => 'html'])
            ->assertRedirect();
        $session = TestSesion::where('anfitrion_usuario_id', $owner->id)->latest('id')->firstOrFail();

        $this->flushSession();
        $this->withSession($this->sessionFor($intruder))
            ->get(route('alumno.tests.codeplay.jugar', $session))
            ->assertNotFound();
    }

    private function systemBankWithTenQuestions(string $category = 'html'): TestBanco
    {
        $bank = TestBanco::create([
            'origen' => 'sistema', 'categoria' => $category, 'clave_sistema' => 'block102-'.uniqid(),
            'nombre' => 'Banco automático Bloque 10.2', 'compartible' => false, 'activo' => true,
        ]);
        foreach (range(1, 10) as $position) {
            $bank->preguntas()->create([
                'enunciado' => 'Pregunta automática '.$position,
                'tipo' => 'opcion_unica',
                'opciones' => ['a' => 'Correcta', 'b' => 'Incorrecta'],
                'respuestas_correctas' => ['a'],
                'tiempo_limite' => 20, 'orden' => $position, 'activa' => true,
            ]);
        }
        return $bank;
    }

    private function student(string $name, int $points = 0): Usuario
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $college = Colegio::create(['nombre' => 'Colegio '.$suffix, 'codigo' => 'CP-'.$suffix, 'activo' => true]);
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


