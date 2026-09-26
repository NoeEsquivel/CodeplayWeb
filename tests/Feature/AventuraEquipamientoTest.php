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
        foreach (['2025_09_30_064434_create_usuarios_table.php', '2025_09_30_064537_create_items_table.php', '2025_09_30_064544_create_inventario_table.php', '2025_10_21_082507_create_desafio_recompensas_table.php', '2026_09_08_120000_create_aventura_preparacion_tables.php', '2026_09_08_140000_integrate_aventura_inventory.php'] as $migration) (require database_path('migrations/' . $migration))->up();
        $this->player = Usuario::create(['nombre' => 'Explorador de prueba', 'clave' => 'not-a-real-account', 'rol' => 'Alumno', 'puntos_totales' => 1200, 'puntos_usados' => 100]);
        (require database_path('migrations/2026_09_09_090000_add_aventura_exploracion.php'))->up();
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

    public function test_restart_refunds_once_and_preserves_permanent_progress(): void
    {
        $equipment = AventuraCatalogo::vacio();
        $equipment['arma'] = 'espada-viajero';
        $p = $this->payload($equipment);
        $this->service->guardar($this->player->id, $p['expedicion_id'], $p['version'], $equipment);
        $this->service->iniciar($this->player->id, $p['expedicion_id']);
        $this->service->otorgarExperiencia($this->player->id, 'restart-test', 120);
        $before = $this->service->estado($this->player->id);
        $run = AventuraExpedicion::findOrFail($p['expedicion_id']);
        $run->update(['escenario'=>4, 'oro'=>30, 'mejoras'=>[['tipo'=>'dano','valor'=>10]]]);
        $request = ['expedicion_id'=>$run->id, 'version'=>$run->version];
        $this->postJson('/alumno/aventura/reiniciar', $request)->assertUnauthorized();
        $this->withSession(['usuario_id'=>$this->player->id]);
        $this->postJson('/alumno/aventura/reiniciar', ['expedicion_id'=>$run->id,'version'=>$run->version-1])->assertStatus(409);
        $this->postJson('/alumno/aventura/reiniciar', $request)->assertOk();
        $after = $this->service->estado($this->player->id);
        self::assertSame($before['personaje']['base'], $after['personaje']['base']);
        self::assertSame(2, $after['personaje']['nivel']);
        self::assertSame(20, $after['personaje']['experiencia']);
        self::assertSame(1, $after['expedicion']['escenario']);
        self::assertSame('preparacion', $after['expedicion']['estado']);
        self::assertSame([], $after['inventario']);
        self::assertSame([], $run->fresh()->mejoras);
        self::assertSame('reiniciada', $run->fresh()->estado);
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        $this->postJson('/alumno/aventura/reiniciar', $request)->assertOk();
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        self::assertSame($after['expedicion']['id'], $this->service->estado($this->player->id)['expedicion']['id']);
        $other = Usuario::create(['nombre'=>'Otro', 'clave'=>'test', 'rol'=>'Alumno']);
        $this->withSession(['usuario_id'=>$other->id])->postJson('/alumno/aventura/reiniciar', $request)->assertNotFound();
    }

    public function test_menu_requires_session_and_initializes_permanent_stats_without_spending(): void
    {
        $this->get('/alumno/aventura')->assertRedirect('/login');
        $this->postJson('/alumno/aventura/equipamiento', [])->assertUnauthorized();
        $this->withSession(['usuario_id' => $this->player->id])->get('/alumno/aventura')->assertOk()->assertSee('Antes del primer paso.');
        $state = $this->service->estado($this->player->id);
        self::assertSame(array_fill_keys(AventuraCatalogo::STATS, 1), $state['personaje']['base']);
        self::assertSame(100, $state['personaje']['vida']);
        self::assertSame(50, $state['personaje']['mana']);
        self::assertSame(100, $state['personaje']['siguiente']);
        self::assertSame(0, $state['personaje']['experiencia']);
        self::assertCount(45, $state['catalogo']);
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
            protected function incremento(int $nivel): int { return [0, 1, 2, 0, 1][$this->index++ % 5]; }
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

    public function test_level_milestones_expand_attribute_growth_and_spell_capacity(): void
    {
        self::assertSame(2, AventuraCatalogo::limiteHechizos(10));
        self::assertSame(3, AventuraCatalogo::limiteHechizos(11));
        self::assertSame(4, AventuraCatalogo::limiteHechizos(21));

        $equipo = AventuraCatalogo::vacio();
        $equipo['hechizos'] = ['brasa', 'alivio', 'escarcha'];
        $catalogo = new AventuraCatalogo();
        try {
            $catalogo->cotizar($equipo, [], 10);
            self::fail('Un personaje de nivel 10 no debe equipar tres hechizos.');
        } catch (\Illuminate\Validation\ValidationException) {
            // El tercer espacio se abre exactamente al llegar al nivel 11.
        }
        self::assertSame(145, $catalogo->cotizar($equipo, [], 11)['costo']);

        $this->service->estado($this->player->id);
        $personaje = AventuraPersonaje::where('usuario_id', $this->player->id)->firstOrFail();
        $personaje->update(['nivel' => 10, 'experiencia' => 0]);
        $service = new class extends AventuraService {
            protected function incremento(int $nivel): int { return $nivel === 11 ? 3 : 0; }
        };
        $service->otorgarExperiencia($this->player->id, 'milestone-level-11', 550);
        self::assertSame(11, $service->estado($this->player->id)['personaje']['nivel']);
        self::assertSame(['dano' => 4, 'magia' => 4, 'armadura' => 4, 'inteligencia' => 4, 'vitalidad' => 4], $service->estado($this->player->id)['personaje']['base']);
    }

    public function test_other_users_cannot_modify_a_preparation_and_no_xp_endpoint_exists(): void
    {
        $payload = $this->payload(AventuraCatalogo::vacio());
        $other = Usuario::create(['nombre' => 'Otro explorador', 'clave' => 'test','rol'=>'Alumno', 'puntos_totales' => 500]);
        $this->withSession(['usuario_id' => $other->id])->postJson('/alumno/aventura/equipamiento', $payload)->assertStatus(409);
        $this->postJson('/alumno/aventura/experiencia', ['cantidad' => 900])->assertNotFound();
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        self::assertSame(0, $other->fresh()->puntos_usados);
    }

    public function test_existing_purchase_is_migrated_without_charging_and_rollback_preserves_selection(): void
    {
        $equipment = AventuraCatalogo::vacio();
        $equipment['arma'] = 'espada-viajero';
        $equipment['armadura'] = 'tunica';
        $equipment['hechizos'] = ['brasa', 'alivio'];
        $equipment['pociones'] = [['id' => 'pocion-vida', 'cantidad' => 4]];
        $state = $this->service->estado($this->player->id);
        $state = $this->service->guardar($this->player->id, $state['expedicion']['id'], 0, $equipment);
        $balance = $this->player->fresh()->puntos_usados;
        $migration = require database_path('migrations/2026_09_08_140000_integrate_aventura_inventory.php');
        $migration->down();
        self::assertSame($equipment, json_decode(DB::table('aventura_expediciones')->value('equipo'), true));
        // The live camp must remain usable even if migration approval is pending.
        $legacy = $this->service->estado($this->player->id);
        self::assertSame($equipment, $legacy['expedicion']['equipo']);
        $this->withSession(['usuario_id' => $this->player->id])->get('/alumno/aventura')->assertOk();
        $legacy = $this->service->guardar($this->player->id, $legacy['expedicion']['id'], $legacy['expedicion']['version'], $equipment);
        self::assertSame($equipment, $legacy['expedicion']['equipo']);
        $migration->up();
        self::assertSame($balance, $this->player->fresh()->puntos_usados);
        $after = $this->service->estado($this->player->id);
        self::assertSame($equipment, $after['expedicion']['equipo']);
        self::assertSame($state['puntos'], $after['puntos']);
        self::assertCount(5, $after['inventario']);
        self::assertSame(54, $after['expedicion']['mana_actual']);
        self::assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('aventura_expediciones', 'equipo'));
        $this->service->estado($this->player->id);
        self::assertSame(5, DB::table('inventario')->count());
        self::assertSame(45, DB::table('items')->count());
    }

    public function test_active_run_resumes_resources_and_consumed_inventory_without_reset_or_duplicate_start(): void
    {
        $equipment = AventuraCatalogo::vacio();
        $equipment['pociones'] = [['id' => 'pocion-vida', 'cantidad' => 3]];
        $payload = $this->payload($equipment);
        $this->service->guardar($this->player->id, $payload['expedicion_id'], 0, $equipment);
        $this->service->iniciar($this->player->id, $payload['expedicion_id']);
        $run = AventuraExpedicion::findOrFail($payload['expedicion_id']);
        $started = $run->iniciada_at;
        $version = $run->version;
        $run->update(['escenario' => 7, 'vida_actual' => 42, 'mana_actual' => 12, 'oro' => 80,
            'efectos' => [['tipo' => 'veneno', 'turnos' => 2]], 'mejoras' => [['tipo' => 'dano', 'valor' => 10]]]);
        $run->inventario()->update(['cantidad' => 1]);
        $this->service->iniciar($this->player->id, $run->id);
        $state = (new AventuraService())->estado($this->player->id);
        self::assertSame(7, $state['expedicion']['escenario']);
        self::assertSame(42, $state['expedicion']['vida_actual']);
        self::assertSame(12, $state['expedicion']['mana_actual']);
        self::assertSame(80, $state['expedicion']['oro']);
        self::assertSame(1, $state['expedicion']['equipo']['pociones'][0]['cantidad']);
        self::assertSame($version, $state['expedicion']['version']);
        self::assertTrue($started->equalTo($run->fresh()->iniciada_at));
        self::assertSame(145, $this->player->fresh()->puntos_usados);
        $this->service->registrarDerrota($this->player->id, $run->id);
        $this->service->registrarDerrota($this->player->id, $run->id);
        self::assertSame(100, $this->player->fresh()->puntos_usados); // Includes the two consumed potions.
        self::assertSame(0, $run->inventario()->count());
        self::assertSame([], $run->fresh()->efectos);
        self::assertSame([], $run->fresh()->mejoras);
        self::assertSame(0, $run->fresh()->oro);
        $next = $this->service->estado($this->player->id);
        self::assertNotSame($run->id, $next['expedicion']['id']);
        self::assertSame(1, $next['expedicion']['escenario']);
        self::assertSame(100, $next['expedicion']['vida_actual']);
    }

    public function test_inventory_replacement_and_defeat_preserve_unrelated_items_and_other_users(): void
    {
        $item = DB::table('items')->insertGetId(['nombre' => 'Recuerdo antiguo', 'tipo' => 'especial', 'descripcion' => 'Anterior', 'precio' => 0]);
        DB::table('inventario')->insert(['usuario_id' => $this->player->id, 'item_id' => $item, 'cantidad' => 7]);
        $other = Usuario::create(['nombre' => 'Otra cuenta', 'clave' => 'test','rol'=>'Alumno', 'puntos_totales' => 1000]);
        $equipment = AventuraCatalogo::vacio(); $equipment['arma'] = 'espada-viajero';
        foreach ([$this->player->id, $other->id] as $id) {
            $state = $this->service->estado($id);
            $this->service->guardar($id, $state['expedicion']['id'], 0, $equipment);
        }
        $run = $this->service->estado($this->player->id)['expedicion']['id'];
        $this->service->iniciar($this->player->id, $run);
        $this->service->registrarDerrota($this->player->id, $run);
        self::assertSame(7, DB::table('inventario')->where('item_id', $item)->value('cantidad'));
        self::assertSame($equipment, $this->service->estado($other->id)['expedicion']['equipo']);
        self::assertSame(30, $other->fresh()->puntos_usados);
    }

    public function test_world_has_exact_probabilities_and_obligatory_bosses(): void
    {
        $counts = [];
        foreach (range(1, 100) as $roll) {
            $type = \App\Services\AventuraMundo::tipo(1, $roll);
            $counts[$type] = ($counts[$type] ?? 0) + 1;
            foreach ([10, 20, 30, 40, 50] as $level) self::assertSame($level === 50 ? 'dragon' : 'jefe', \App\Services\AventuraMundo::tipo($level, $roll));
        }
        self::assertSame(['tesoro' => 3, 'narrativa' => 27, 'gremio' => 20, 'enemigo' => 50], $counts);
        foreach (range(1, 50) as $level) self::assertSame(intdiv($level - 1, 10) + 1, \App\Services\AventuraMundo::region($level)['id']);
    }

    public function test_map_preview_start_choice_and_reload_preserve_the_actual_run(): void
    {
        $this->get('/alumno/aventura/mapa')->assertRedirect('/login');
        $this->postJson('/alumno/aventura/iniciar', [])->assertUnauthorized();
        $this->postJson('/alumno/aventura/camino', [])->assertUnauthorized();
        $this->withSession(['usuario_id' => $this->player->id]);
        foreach (range(1, 5) as $region) $this->get('/alumno/aventura/mapa?region=' . $region)->assertOk()->assertSee('El conocimiento abre caminos.');
        $state = $this->service->estado($this->player->id);
        self::assertSame('preparacion', $state['expedicion']['estado']);
        $payload = ['expedicion_id' => $state['expedicion']['id'], 'version' => $state['expedicion']['version']];
        $this->postJson('/alumno/aventura/camino', $payload + ['camino' => 0])->assertStatus(409);
        $this->postJson('/alumno/aventura/iniciar', $payload)->assertOk();
        $this->postJson('/alumno/aventura/iniciar', $payload)->assertOk();
        $run = AventuraExpedicion::findOrFail($payload['expedicion_id']);
        self::assertSame(1, $run->version);
        $this->get('/alumno/aventura/mapa')->assertSee('El campamento te recibe');
        $this->postJson('/alumno/aventura/camino', $payload + ['camino' => 0])->assertStatus(409);
        $payload['version'] = $run->version;
        $this->postJson('/alumno/aventura/camino', $payload + ['camino' => 7])->assertStatus(422);
        $this->postJson('/alumno/aventura/camino', $payload + ['camino' => 1, 'tipo' => 'tesoro', 'oro' => 9999, 'escenario' => 50])->assertOk();
        $saved = $run->fresh()->exploracion;
        self::assertSame(1, $saved[1]['camino']);
        self::assertSame('pendiente', $saved[1]['estado']);
        $this->postJson('/alumno/aventura/camino', $payload + ['camino' => 2])->assertStatus(409);
        $payload['version'] = $run->fresh()->version;
        $this->postJson('/alumno/aventura/camino', $payload + ['camino' => 2])->assertStatus(409);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('event-stage')->assertDontSee('data-path-choice', false);
        $this->get('/alumno/aventura/mapa?region=5')->assertOk()->assertSee('Volver a mi región');
        self::assertSame($saved, $run->fresh()->exploracion);
        self::assertSame(1, $run->fresh()->escenario);
        self::assertSame(0, $run->fresh()->oro);
        self::assertSame(100, $this->player->fresh()->puntos_usados);
        $other = Usuario::create(['nombre' => 'Otra sesión', 'clave' => 'test','rol'=>'Alumno']);
        $this->withSession(['usuario_id' => $other->id])->postJson('/alumno/aventura/iniciar', $payload)->assertNotFound();
        $this->postJson('/alumno/aventura/camino', $payload + ['camino' => 0])->assertNotFound();
    }
}
