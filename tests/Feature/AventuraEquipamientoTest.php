<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\AventuraPersonaje;
use App\Models\AventuraExpedicion;
use App\Services\AventuraCatalogo;
use App\Services\AventuraService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AventuraEquipamientoTest extends TestCase
{
    private Usuario $player;
    private AventuraService $service;

    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite', \PDO::getAvailableDrivers())) $this->markTestSkipped('Ejecutar PHP con pdo_sqlite para las pruebas aisladas de Aventura.');
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['2025_09_30_064434_create_usuarios_table.php', '2025_10_21_082507_create_desafio_recompensas_table.php', '2026_09_08_120000_create_aventura_preparacion_tables.php'] as $migration) (require database_path('migrations/' . $migration))->up();
        $this->player = Usuario::create(['nombre' => 'Explorador de prueba', 'clave' => 'not-a-real-account', 'rol' => 'Alumno', 'puntos_totales' => 1200, 'puntos_usados' => 100]);
        $this->service = new AventuraService();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function payload(array $equipment): array
    {
        $state = $this->service->estado($this->player->id);
        return ['expedicion_id' => $state['expedicion']['id'], 'version' => $state['expedicion']['version'], 'equipo' => $equipment];
    }

    public function test_menu_requires_session_and_initializes_permanent_stats_without_spending(): void
    {
        $this->get('/alumno/aventura')->assertRedirect('/login');
        $this->postJson('/alumno/aventura/equipamiento', [])->assertRedirect('/login');
        $this->withSession(['usuario_id' => $this->player->id])->get('/alumno/aventura')->assertOk()->assertSee('Antes del primer paso.');
        $state = $this->service->estado($this->player->id);
        self::assertSame(array_fill_keys(AventuraCatalogo::STATS, 1), $state['personaje']['base']);
        self::assertSame(100, $state['personaje']['vida']);
        self::assertSame(50, $state['personaje']['mana']);
        self::assertSame(100, $state['personaje']['siguiente']);
        self::assertSame(0, $state['personaje']['experiencia']);
        self::assertCount(38, $state['catalogo']);
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        self::assertSame(1, AventuraPersonaje::count());
        self::assertSame(1, AventuraExpedicion::count());
    }

    public function test_purchase_replacement_and_refund_use_server_prices_and_keep_learning_points(): void
    {
        $equipment = AventuraCatalogo::vacio();
        $equipment['arma'] = 'espada-viajero';
        $equipment['pociones'] = [['id' => 'pocion-vida', 'cantidad' => 3]];
        $payload = $this->payload($equipment) + ['precio' => 0, 'usuario_id' => 8888, 'xp' => 99999];
        $this->withSession(['usuario_id' => $this->player->id])->postJson('/alumno/aventura/equipamiento', $payload)
            ->assertOk()->assertJsonPath('estado.puntos.invertidos', 75)->assertJsonPath('estado.personaje.total.dano', 3);
        self::assertSame(175, $this->player->fresh()->puntos_usados);
        self::assertSame(0, $this->service->estado($this->player->id)['personaje']['experiencia']);
        // Rewards earned elsewhere during preparation must survive refunds.
        DB::table('usuarios')->where('id', $this->player->id)->increment('puntos_totales', 50);
        $equipment['arma'] = 'hacha-roble';
        $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertOk()->assertJsonPath('estado.puntos.invertidos', 145);
        self::assertSame(245, $this->player->fresh()->puntos_usados);
        $this->postJson('/alumno/aventura/equipamiento', $this->payload(AventuraCatalogo::vacio()))->assertOk()->assertJsonPath('estado.puntos.disponibles', 1150);
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        self::assertSame(1250, $this->player->fresh()->puntos_totales);
    }

    public function test_invalid_catalogue_locked_rewards_budget_and_stale_tab_are_rejected(): void
    {
        $this->withSession(['usuario_id' => $this->player->id]);
        foreach (["' OR 1=1;--", 'coraza-caja', 'pocion-vida'] as $id) {
            $equipment = AventuraCatalogo::vacio(); $equipment['arma'] = $id;
            $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
        }
        $equipment = AventuraCatalogo::vacio(); $equipment['arma'] = 'compilador-origen';
        $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
        $equipment['arma'] = 'filo-fenix'; $equipment['armadura'] = 'coraza-dragon';
        $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
        $equipment = AventuraCatalogo::vacio(); $equipment['hechizos'] = ['brasa', 'brasa'];
        $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
        $equipment['hechizos'] = []; $equipment['pociones'] = [['id' => 'pocion-vida', 'cantidad' => 20], ['id' => 'pocion-mana', 'cantidad' => 1]];
        $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
        $equipment['pociones'] = [['id' => 'pocion-vida', 'cantidad' => -1]];
        $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        $equipment = AventuraCatalogo::vacio(); $equipment['arma'] = 'espada-viajero';
        $payload = $this->payload($equipment);
        $this->postJson('/alumno/aventura/equipamiento', $payload)->assertOk();
        $this->postJson('/alumno/aventura/equipamiento', $payload)->assertStatus(409);
        self::assertSame(130, $this->player->fresh()->puntos_usados);
    }

    public function test_special_items_require_the_exact_completed_challenge(): void
    {
        $this->withSession(['usuario_id' => $this->player->id]);
        foreach ([5 => ['armadura', 'coraza-caja'], 10 => ['arma', 'filo-validacion'], 15 => ['armadura', 'manto-memoria'], 20 => ['arma', 'compilador-origen']] as $level => [$slot, $id]) {
            $equipment = AventuraCatalogo::vacio(); $equipment[$slot] = $id;
            $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
            DB::table('desafio_recompensas')->insert(['usuario_id' => $this->player->id, 'nivel_desafio' => $level, 'completado_primera_vez' => false]);
            $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertStatus(422);
            DB::table('desafio_recompensas')->where('usuario_id', $this->player->id)->where('nivel_desafio', $level)->update(['completado_primera_vez' => true]);
            $this->postJson('/alumno/aventura/equipamiento', $this->payload($equipment))->assertOk();
        }
    }

    public function test_defeat_refunds_once_and_preserves_level_and_other_spending(): void
    {
        $equipment = AventuraCatalogo::vacio(); $equipment['arma'] = 'espada-viajero'; $equipment['pociones'] = [['id' => 'pocion-vida', 'cantidad' => 2]];
        $payload = $this->payload($equipment);
        $this->service->guardar($this->player->id, $payload['expedicion_id'], 0, $equipment);
        $this->service->otorgarExperiencia($this->player->id, 'encuentro-fixture-1', 100);
        $stats = $this->service->estado($this->player->id)['personaje'];
        $this->service->iniciar($this->player->id, $payload['expedicion_id']);
        $this->withSession(['usuario_id' => $this->player->id])->postJson('/alumno/aventura/equipamiento', $this->payload(AventuraCatalogo::vacio()))->assertStatus(409);
        $this->service->registrarDerrota($this->player->id, $payload['expedicion_id']);
        $this->service->registrarDerrota($this->player->id, $payload['expedicion_id']);
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        self::assertSame(1200, $this->player->fresh()->puntos_totales);
        $state = $this->service->estado($this->player->id);
        self::assertSame(2, $state['personaje']['nivel']);
        self::assertSame($stats['base'], $state['personaje']['base']);
        self::assertSame(AventuraCatalogo::vacio(), $state['expedicion']['equipo']);
        self::assertSame(AventuraCatalogo::vacio(), AventuraExpedicion::find($payload['expedicion_id'])->equipo);
        self::assertSame(0, $state['puntos']['invertidos']);
    }

    public function test_xp_events_are_idempotent_and_multiple_level_gains_are_persistent(): void
    {
        $service = new class extends AventuraService {
            private int $index = 0;
            protected function incremento(): int { return [0, 1, 2, 0, 1][$this->index++ % 5]; }
        };
        $service->otorgarExperiencia($this->player->id, 'test:encuentro:1', 260);
        $state = $service->estado($this->player->id);
        self::assertSame(3, $state['personaje']['nivel']);
        self::assertSame(10, $state['personaje']['experiencia']);
        self::assertSame(200, $state['personaje']['siguiente']);
        self::assertSame(['dano' => 1, 'magia' => 3, 'armadura' => 5, 'inteligencia' => 1, 'vitalidad' => 3], $state['personaje']['base']);
        self::assertSame(110, $state['personaje']['vida']);
        self::assertSame(50, $state['personaje']['mana']);
        $service->otorgarExperiencia($this->player->id, 'test:encuentro:1', 260);
        self::assertSame($state['personaje'], $service->estado($this->player->id)['personaje']);
        $equipment = AventuraCatalogo::vacio(); $equipment['armadura'] = 'tunica';
        $after = $service->guardar($this->player->id, $state['expedicion']['id'], 0, $equipment);
        self::assertSame(54, $after['personaje']['mana']);
        self::assertSame(1, $after['personaje']['base']['inteligencia']);
    }

    public function test_other_users_cannot_modify_a_preparation_and_no_xp_endpoint_exists(): void
    {
        $payload = $this->payload(AventuraCatalogo::vacio());
        $other = Usuario::create(['nombre' => 'Otro explorador', 'clave' => 'test', 'puntos_totales' => 500]);
        $this->withSession(['usuario_id' => $other->id])->postJson('/alumno/aventura/equipamiento', $payload)->assertStatus(409);
        $this->postJson('/alumno/aventura/experiencia', ['cantidad' => 900])->assertNotFound();
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        self::assertSame(0, $other->fresh()->puntos_usados);
    }
}
