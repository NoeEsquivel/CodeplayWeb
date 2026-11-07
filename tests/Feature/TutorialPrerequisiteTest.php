<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\Usuario;
use Database\Seeders\TutorialesSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TutorialPrerequisiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Se requiere pdo_sqlite.');
        }

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        foreach ([
            '2025_09_30_064434_create_usuarios_table.php',
            '2025_10_01_063305_add_cambio_clave_requerido_to_usuarios_table.php',
            '2025_10_02_083703_add_ultimo_acceso_to_usuarios_table.php',
            '2026_09_08_120000_create_aventura_preparacion_tables.php',
            '2026_09_24_120000_create_colegios_and_expand_user_roles.php',
            '2026_09_25_100000_add_user_status_and_admin_audit.php',
            '2026_09_24_121000_add_email_to_usuarios_table.php',
            '2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php',
            '2026_09_24_123000_create_clases_and_memberships.php',
            '2026_09_25_091000_add_ciclo_escolar_to_clases.php',
            '2026_09_27_130000_add_full_names_and_adventure_alias.php',
            '2025_09_30_064441_create_documentacion_table.php',
            '2025_09_30_064446_create_tests_table.php',
            '2025_09_30_064451_create_preguntas_test_table.php',
            '2025_09_30_064457_create_resultados_test_table.php',
            '2025_10_18_173127_add_test_module_to_tests_table.php',
            '2026_09_26_100000_create_documentation_progress.php',
            '2025_09_30_064502_create_tutoriales_table.php',
            '2025_09_30_064507_create_progreso_tutorial_table.php',
            '2025_10_20_090049_add_slug_to_tutoriales_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        $this->seed(TutorialesSeeder::class);

        foreach (['html2', 'html3', 'html4', 'css1', 'css2', 'css3', 'css4', 'js1', 'js2', 'js3', 'mundo3'] as $module) {
            $docId = DB::table('documentacion')->insertGetId(['titulo' => $module, 'contenido' => 'Contenido']);
            $testId = DB::table('tests')->insertGetId(['documentacion_id' => $docId, 'modulo' => $module, 'titulo' => 'Test '.$module]);
            DB::table('preguntas_test')->insert([
                'test_id' => $testId, 'pregunta' => 'Pregunta de práctica',
                'opcion_a' => 'Correcta', 'opcion_b' => 'Incorrecta', 'opcion_c' => 'Otra', 'opcion_d' => 'Otra opción',
                'respuesta_correcta' => 'a',
            ]);
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_first_tutorial_and_its_video_remain_available_without_prerequisites(): void
    {
        $this->signIn($this->student());
        $this->get(route('tutorial.show', 'primer-pagina-web'))->assertOk()
            ->assertSee('id="tutorialVideo"', false)->assertDontSee('Video bloqueado');
        $this->withHeader('Range', 'bytes=0-15')->get(route('tutorial.video', 'Primera_pagina.mp4'))
            ->assertStatus(206)->assertHeader('content-length', '16');
        $this->postJson(route('tutorial.completar', 'primer-pagina-web'))->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('puntos_ganados', 25);
    }

    public function test_each_of_tutorials_two_to_ten_explains_the_required_topics_and_protects_its_video(): void
    {
        $student = $this->student();
        $this->signIn($student);
        $expected = [
            'estilos-css' => ['Estilos_css.mp4', ['css1', 'css2']],
            'botones-interactivos' => ['Botones_interactivos.mp4', ['js3']],
            'navegacion-paginas' => ['Navegacion_paginas.mp4', ['html3']],
            'introduccion-bootstrap' => ['Introduccion_bootstrap.mp4', ['html2', 'css1']],
            'formularios-web' => ['Formularios_web.mp4', ['html4']],
            'javascript-basico' => ['Javascript_basico.mp4', ['js1', 'js2']],
            'entorno-desarrollo' => ['Entorno_desarrollo.mp4', ['mundo3']],
            'diseno-responsivo' => ['Diseno_responsivo.mp4', ['css3', 'css4']],
            'proyecto-completo' => ['Proyecto_completo.mp4', ['html2', 'css4', 'js3']],
        ];

        foreach ($expected as $slug => [$video, $modules]) {
            $page = $this->get(route('tutorial.show', $slug))->assertOk()->assertSee('Video bloqueado')
                ->assertSee('Completar Tutorial')->assertDontSee('id="tutorialVideo"', false)
                ->assertDontSee(route('tutorial.video', $video), false);
            $this->assertCompletionButton($page, true);
            foreach ($modules as $module) {
                $page->assertSee(route('alumno.documentacion.show', $module), false)
                    ->assertSee(route('test.show', $module), false);
            }
            $this->getJson(route('tutorial.video', $video))->assertForbidden();
            $this->postJson(route('tutorial.completar', $slug))->assertForbidden()
                ->assertJsonPath('success', false)->assertJsonStructure(['error', 'requirements']);
        }
        $this->assertSame(0, DB::table('progreso_tutorial')->where('usuario_id', $student->id)->count());
        $this->assertSame(0, $student->fresh()->puntos_totales);
    }

    public function test_visiting_alone_does_not_unlock_and_a_completed_test_with_zero_points_does(): void
    {
        $student = $this->student();
        $this->signIn($student);
        $this->get(route('alumno.documentacion.show', 'html3'))->assertOk();
        $this->assertDatabaseHas('progreso_documentacion', ['usuario_id' => $student->id, 'documento' => 'html3']);
        $this->get(route('tutorial.show', 'navegacion-paginas'))->assertOk()
            ->assertSee('Lectura: visitada')->assertSee('Test: pendiente');
        $this->getJson(route('tutorial.video', 'Navegacion_paginas.mp4'))->assertForbidden();
        $this->postJson(route('tutorial.completar', 'navegacion-paginas'))->assertForbidden()
            ->assertJsonPath('requirements.0.visited', true)->assertJsonPath('requirements.0.attempted', false);

        $questionId = DB::table('preguntas_test')->where('test_id', $this->testId('html3'))->value('id');
        $this->post(route('test.evaluar', 'html3'), ['respuestas' => [$questionId => 'b']])->assertOk();
        $this->assertDatabaseHas('resultados_test', ['usuario_id' => $student->id, 'test_id' => $this->testId('html3'), 'puntaje' => 0]);
        $page = $this->get(route('tutorial.show', 'navegacion-paginas'))->assertOk()
            ->assertDontSee('Video bloqueado')->assertSee('id="tutorialVideo"', false);
        $this->assertCompletionButton($page, false);
        $this->withHeader('Range', 'bytes=0-15')->get(route('tutorial.video', 'Navegacion_paginas.mp4'))
            ->assertStatus(206)->assertHeader('content-length', '16')
            ->assertHeader('accept-ranges', 'bytes')->assertHeader('cache-control', 'no-store, private');
        $this->postJson(route('tutorial.completar', 'navegacion-paginas'))->assertOk()
            ->assertJsonPath('success', true)->assertJsonPath('puntos_ganados', 25);
        $this->assertDatabaseHas('progreso_tutorial', [
            'usuario_id' => $student->id,
            'tutorial_id' => DB::table('tutoriales')->where('slug', 'navegacion-paginas')->value('id'),
            'estado' => 'completado',
        ]);
        $this->postJson(route('tutorial.completar', 'navegacion-paginas'))->assertOk()->assertJsonPath('success', false);
        $this->assertSame(25, $student->fresh()->puntos_totales);
    }

    public function test_a_test_attempt_without_a_document_visit_stays_locked(): void
    {
        $student = $this->student();
        $this->attempt($student, 'html3');
        $this->signIn($student);
        $this->get(route('tutorial.show', 'navegacion-paginas'))->assertOk()
            ->assertSee('Lectura: pendiente')->assertSee('Test: realizado');
        $this->getJson(route('tutorial.video', 'Navegacion_paginas.mp4'))->assertForbidden();
        $this->postJson(route('tutorial.completar', 'navegacion-paginas'))->assertForbidden()
            ->assertJsonPath('requirements.0.visited', false)->assertJsonPath('requirements.0.attempted', true);
        $this->assertSame(0, $student->fresh()->puntos_totales);
    }

    public function test_all_selected_topics_are_required_and_old_progress_is_recognized(): void
    {
        $student = $this->student();
        $this->visit($student, 'css1');
        $this->attempt($student, 'css1');
        $this->visit($student, 'css2');
        $this->signIn($student);
        $this->getJson(route('tutorial.video', 'Estilos_css.mp4'))->assertForbidden()
            ->assertSee('Colores, Fuentes y Fondos');
        $this->postJson(route('tutorial.completar', 'estilos-css'))->assertForbidden()
            ->assertJsonPath('requirements.0.complete', true)->assertJsonPath('requirements.1.complete', false);

        $this->attempt($student, 'css2');
        $this->get(route('tutorial.show', 'estilos-css'))->assertOk()->assertDontSee('Video bloqueado');
        $this->withHeader('Range', 'bytes=0-15')->get(route('tutorial.video', 'Estilos_css.mp4'))->assertStatus(206);
        $this->withHeader('Range', 'bytes=0-1,4-5')->get(route('tutorial.video', 'Estilos_css.mp4'))->assertStatus(416);
    }

    public function test_another_students_progress_and_unrelated_tests_do_not_unlock_the_video(): void
    {
        $student = $this->student();
        $other = $this->student();
        $this->visit($student, 'html3');
        $this->attempt($student, 'html4');
        $this->visit($other, 'html3');
        $this->attempt($other, 'html3');
        $this->signIn($other);
        $this->withHeader('Range', 'bytes=0-15')->get(route('tutorial.video', 'Navegacion_paginas.mp4'))->assertStatus(206);
        $this->signIn($student);
        $this->getJson(route('tutorial.video', 'Navegacion_paginas.mp4'))->assertForbidden();
        $this->postJson(route('tutorial.completar', 'navegacion-paginas'))->assertForbidden();
        $this->assertSame(0, $student->fresh()->puntos_totales);
    }

    public function test_completing_the_written_tutorial_does_not_bypass_the_video_requirements(): void
    {
        $student = $this->student();
        DB::table('progreso_tutorial')->insert([
            'usuario_id' => $student->id,
            'tutorial_id' => DB::table('tutoriales')->where('slug', 'estilos-css')->value('id'),
            'estado' => 'completado',
        ]);
        $this->signIn($student);
        $this->get(route('tutorial.show', 'estilos-css'))->assertOk()
            ->assertSee('¡Tutorial Completado!')->assertSee('Video bloqueado');
        $this->getJson(route('tutorial.video', 'Estilos_css.mp4'))->assertForbidden();
    }

    public function test_a_qualified_student_can_stream_and_seek_all_nine_protected_videos(): void
    {
        $student = $this->student();
        foreach (DB::table('tests')->pluck('modulo') as $module) {
            $this->visit($student, $module);
            $this->attempt($student, $module);
        }
        $this->signIn($student);

        foreach (DB::table('tutoriales')->where('slug', '!=', 'primer-pagina-web')->pluck('slug') as $slug) {
            $page = $this->get(route('tutorial.show', $slug))->assertOk()->assertDontSee('Video bloqueado')
                ->assertSee('id="tutorialVideo"', false);
            $this->assertCompletionButton($page, false);
            $this->postJson(route('tutorial.completar', $slug))->assertOk()->assertJsonPath('success', true);
        }
        $this->assertSame(9, DB::table('progreso_tutorial')->where('usuario_id', $student->id)->where('estado', 'completado')->count());
        $this->assertSame(455, $student->fresh()->puntos_totales);
        foreach (['Estilos_css', 'Botones_interactivos', 'Navegacion_paginas', 'Introduccion_bootstrap',
            'Formularios_web', 'Javascript_basico', 'Entorno_desarrollo', 'Diseno_responsivo', 'Proyecto_completo'] as $file) {
            $expected = file_get_contents(resource_path('videos/tutoriales/'.$file.'.mp4'), false, null, 0, 16);
            $this->withHeader('Range', 'bytes=0-15')->get(route('tutorial.video', $file.'.mp4'))
                ->assertStatus(206)->assertHeader('content-length', '16')->assertStreamedContent($expected);
        }
    }

    public function test_protected_files_have_no_public_copy_and_video_routes_require_a_valid_student_session(): void
    {
        foreach (['Estilos_css', 'Botones_interactivos', 'Navegacion_paginas', 'Introduccion_bootstrap',
            'Formularios_web', 'Javascript_basico', 'Entorno_desarrollo', 'Diseno_responsivo', 'Proyecto_completo'] as $file) {
            $this->assertFileDoesNotExist(public_path('videos/'.$file.'.mp4'));
        }
        $this->get(route('tutorial.video', 'Estilos_css.mp4'))->assertRedirect(route('login'));
        $this->signIn($this->student());
        $this->getJson(route('tutorial.video', 'archivo-no-permitido.mp4'))->assertNotFound();
    }

    public function test_missing_prerequisites_leave_an_in_progress_tutorial_and_points_unchanged(): void
    {
        $student = $this->student();
        $tutorialId = DB::table('tutoriales')->where('slug', 'estilos-css')->value('id');
        DB::table('progreso_tutorial')->insert([
            'usuario_id' => $student->id, 'tutorial_id' => $tutorialId, 'estado' => 'en_progreso',
        ]);
        $this->visit($student, 'css1');
        $this->attempt($student, 'css1');
        $this->signIn($student);
        $this->postJson(route('tutorial.completar', 'estilos-css'))->assertForbidden()->assertJsonPath('success', false);
        $this->assertDatabaseHas('progreso_tutorial', [
            'usuario_id' => $student->id, 'tutorial_id' => $tutorialId, 'estado' => 'en_progreso',
        ]);
        $this->assertSame(0, $student->fresh()->puntos_totales);
    }

    private function assertCompletionButton(TestResponse $page, bool $disabled): void
    {
        $document = new \DOMDocument;
        $document->loadHTML($page->getContent(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $button = (new \DOMXPath($document))->query('//button[@id="completeTutorialButton"]');
        $this->assertSame(1, $button->length);
        $this->assertSame($disabled, $button->item(0)->hasAttribute('disabled'));
        $this->assertSame(! $disabled, $button->item(0)->hasAttribute('onclick'));
        if ($disabled) {
            $page->assertSee('Completa las lecturas y los tests indicados arriba para habilitar este botón.');
        }
    }

    private function student(): Usuario
    {
        return Usuario::create([
            'colegio_id' => Colegio::firstOrFail()->id, 'nombre' => 'Alumno tutorial '.uniqid(),
            'clave' => Hash::make('Clave1234'), 'rol' => Usuario::ROL_ALUMNO,
            'activo' => true, 'cambio_clave_requerido' => false,
        ]);
    }

    private function signIn(Usuario $student): void
    {
        $this->flushSession();
        $this->withSession(['usuario_id' => $student->id, 'usuario_session_version' => $student->fresh()->session_version]);
    }

    private function visit(Usuario $student, string $module): void
    {
        DB::table('progreso_documentacion')->insert([
            'usuario_id' => $student->id, 'documento' => $module,
            'visto_at' => now()->subYear(), 'created_at' => now()->subYear(), 'updated_at' => now()->subYear(),
        ]);
    }

    private function attempt(Usuario $student, string $module): void
    {
        DB::table('resultados_test')->insert([
            'usuario_id' => $student->id, 'test_id' => $this->testId($module), 'puntaje' => 0,
            'created_at' => now()->subYear(), 'updated_at' => now()->subYear(),
        ]);
    }

    private function testId(string $module): int
    {
        return (int) DB::table('tests')->where('modulo', $module)->value('id');
    }
}
