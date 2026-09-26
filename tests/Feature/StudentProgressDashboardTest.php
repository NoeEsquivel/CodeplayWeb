<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentProgressDashboardTest extends TestCase
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
        Carbon::setTestNow();
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_dashboard_displays_only_own_progress_and_links_available_tests(): void
    {
        Carbon::setTestNow('2026-09-26 12:00:00');
        $student = $this->student('Alumno Dashboard', 500, 120);
        $other = $this->student('Alumno Externo Dashboard', 9876, 0);
        [$blocked, $ready, $new] = $this->testsCatalog();

        DB::table('progreso_documentacion')->insert([
            'usuario_id' => $student->id, 'documento' => 'html1', 'visto_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('resultados_test')->insert([
            ['usuario_id' => $student->id, 'test_id' => $blocked, 'puntaje' => 80, 'fecha' => now(), 'created_at' => now()->subHours(2), 'updated_at' => now()],
            ['usuario_id' => $student->id, 'test_id' => $ready, 'puntaje' => 60, 'fecha' => now(), 'created_at' => now()->subHours(25), 'updated_at' => now()],
            ['usuario_id' => $other->id, 'test_id' => $blocked, 'puntaje' => 100, 'fecha' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withSession($this->sessionFor($student))
            ->get('/alumno/progreso')
            ->assertOk()
            ->assertSee('Tu recorrido en CODEPLAY')
            ->assertSee('Documentación revisada')
            ->assertSee('Documentación pendiente de revisar')
            ->assertSee('Podrás ganar puntos en', false)
            ->assertSee(route('test.show', 'css1'), false)
            ->assertSee(route('test.show', 'js1'), false)
            ->assertSee('380')
            ->assertDontSee('9876');

        $html = $response->getContent();
        $this->assertStringNotContainsString('href="'.route('test.show', 'html1').'"', $html);
    }

    public function test_test_entry_and_availability_use_the_same_24_hour_rule(): void
    {
        Carbon::setTestNow('2026-09-26 12:00:00');
        $student = $this->student('Alumno Espera', 0, 0);
        [$testId] = $this->testsCatalog();
        DB::table('resultados_test')->insert([
            'usuario_id' => $student->id, 'test_id' => $testId, 'puntaje' => 50,
            'fecha' => now(), 'created_at' => now()->subHours(23), 'updated_at' => now(),
        ]);

        $this->withSession($this->sessionFor($student))
            ->getJson('/test/html1/check')
            ->assertOk()
            ->assertJsonPath('canTakeTest', false);
        $this->get('/test/html1')->assertRedirect(route('alumno.documentacion'));

        DB::table('resultados_test')->where('usuario_id', $student->id)->update(['created_at' => now()->subHours(25)]);

        $this->getJson('/test/html1/check')
            ->assertOk()
            ->assertJsonPath('canTakeTest', true)
            ->assertJsonPath('nextAvailableTime', null);
        $this->get('/test/html1')->assertOk();
    }

    public function test_main_student_menu_opens_the_progress_dashboard(): void
    {
        $student = $this->student('Alumno Menú Progreso', 0, 0);

        $this->withSession($this->sessionFor($student))
            ->get('/inicio-alumno')
            ->assertOk()
            ->assertSee(route('alumno.progreso'), false)
            ->assertDontSee('Próximamente: Revisa tus estadísticas y logros');
    }

    private function testsCatalog(): array
    {
        $ids = [];
        foreach ([
            ['html1', 'Introducción a HTML'],
            ['css1', 'Introducción a CSS'],
            ['js1', 'Introducción a JavaScript'],
        ] as [$module, $title]) {
            $documentId = DB::table('documentacion')->insertGetId([
                'titulo' => $title, 'contenido' => 'Contenido de prueba', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $ids[] = DB::table('tests')->insertGetId([
                'documentacion_id' => $documentId, 'modulo' => $module, 'titulo' => 'Test de '.$title,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        return $ids;
    }

    private function student(string $name, int $total, int $used): Usuario
    {
        return Usuario::create([
            'colegio_id' => Colegio::firstOrFail()->id,
            'nombre' => $name,
            'clave' => Hash::make('Clave1234'),
            'rol' => Usuario::ROL_ALUMNO,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => $total,
            'puntos_usados' => $used,
        ]);
    }

    private function sessionFor(Usuario $student): array
    {
        return [
            'usuario_id' => $student->id,
            'usuario_session_version' => $student->fresh()->session_version,
        ];
    }
}
