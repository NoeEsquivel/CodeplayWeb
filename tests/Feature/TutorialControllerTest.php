<?php

namespace Tests\Feature;

use App\Http\Controllers\TutorialController;
use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TutorialControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_tutorial_pages_completion_and_progress_cover_the_student_flow(): void
    {
        $student = $this->student();
        $slug = 'primer-pagina-web';
        $tutorial = DB::table('tutoriales')->where('slug', $slug)->first();
        if ($tutorial) {
            $tutorialId = $tutorial->id;
            DB::table('tutoriales')->where('id', $tutorialId)->update(['puntos_otorgados' => 25]);
        } else {
            $tutorialId = DB::table('tutoriales')->insertGetId([
                'titulo' => 'Primera página '.uniqid(), 'slug' => $slug, 'dificultad' => 'facil',
                'puntos_otorgados' => 25, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('tutoriales')->insert([
            'titulo' => 'Tutorial sin plantilla', 'slug' => 'sin-plantilla-'.uniqid(), 'dificultad' => 'medio',
            'puntos_otorgados' => 10, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->withSession($this->sessionFor($student));
        $this->get(route('tutorial.show', $slug))->assertOk()->assertSee('Tu Primera Página Web');
        $missingView = DB::table('tutoriales')->where('titulo', 'Tutorial sin plantilla')->value('slug');
        $this->get(route('tutorial.show', $missingView))->assertOk()->assertSee('Tutorial en Desarrollo');
        $this->get(route('tutorial.show', 'inexistente'))->assertNotFound();

        $this->postJson(route('tutorial.completar', $slug))->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('puntos_ganados', 25);
        $this->assertDatabaseHas('progreso_tutorial', [
            'usuario_id' => $student->id, 'tutorial_id' => $tutorialId, 'estado' => 'completado',
        ]);
        $this->postJson(route('tutorial.completar', $slug))->assertOk()
            ->assertJsonPath('success', false)->assertJsonPath('error', 'Ya completaste este tutorial');
        $this->getJson(route('tutoriales.progreso'))->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('tutorialesCompletados', 1)
            ->assertJsonPath('progreso', 10);

        $controller = app(TutorialController::class);
        session()->flush();
        $this->assertFalse($controller->getProgreso()->getData(true)['success']);
        $this->assertSame('Usuario no autenticado', $controller->completar(Request::create('/'), $slug)->getData(true)['error']);
        session(['usuario_id' => $student->id]);
        $this->assertSame('Tutorial no encontrado', $controller->completar(Request::create('/'), 'no-existe')->getData(true)['error']);
    }

    public function test_tutorial_video_supports_ranges_and_rejects_invalid_requests(): void
    {
        $student = $this->student();
        $session = $this->sessionFor($student);

        $this->withSession($session)->get(route('tutorial.video', 'archivo-no-permitido.mp4'))->assertNotFound();
        $this->withSession($session)->withHeader('Range', 'bytes=0-15')
            ->get(route('tutorial.video', 'Primera_pagina.mp4'))
            ->assertStatus(206)->assertHeader('accept-ranges', 'bytes')
            ->assertHeader('content-length', '16');
        $this->withSession($session)->withHeader('Range', 'bytes=0-1,4-5')
            ->get(route('tutorial.video', 'Primera_pagina.mp4'))->assertStatus(416);
        $this->withSession($session)->withHeader('Range', 'bytes=999999999-')
            ->get(route('tutorial.video', 'Primera_pagina.mp4'))->assertStatus(416);
    }

    private function student(): Usuario
    {
        return Usuario::create([
            'colegio_id' => Colegio::firstOrFail()->id,
            'nombre' => 'Alumno tutorial '.uniqid(),
            'clave' => Hash::make('Clave1234'),
            'rol' => Usuario::ROL_ALUMNO,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ]);
    }

    private function sessionFor(Usuario $user): array
    {
        return ['usuario_id' => $user->id, 'usuario_session_version' => $user->fresh()->session_version];
    }
}
