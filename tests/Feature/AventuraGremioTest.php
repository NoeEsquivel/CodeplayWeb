<?php

namespace Tests\Feature;

use App\Models\{Usuario,AventuraExpedicion,AventuraPersonaje,PreguntaTest};
use App\Services\{AventuraService,AventuraGremio,AventuraInventario,AventuraExploracion};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AventuraGremioTest extends TestCase
{
    private Usuario $user;
    private AventuraExpedicion $run;
    private AventuraService $camp;

    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite',\PDO::getAvailableDrivers())) $this->markTestSkipped('Se requiere pdo_sqlite.');
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        foreach (['2025_09_30_064434_create_usuarios_table.php','2025_09_30_064441_create_documentacion_table.php','2025_09_30_064446_create_tests_table.php','2025_09_30_064451_create_preguntas_test_table.php','2025_10_18_173127_add_test_module_to_tests_table.php','2025_09_30_064527_create_enemigos_table.php','2025_09_30_064532_create_combates_table.php','2025_09_30_064537_create_items_table.php','2025_09_30_064544_create_inventario_table.php','2025_10_21_082507_create_desafio_recompensas_table.php','2026_09_08_120000_create_aventura_preparacion_tables.php','2026_09_08_140000_integrate_aventura_inventory.php','2026_09_09_090000_add_aventura_exploracion.php','2026_09_09_120000_add_aventura_bosque_combate.php'] as $file) (require database_path('migrations/'.$file))->up();
        DB::table('documentacion')->insert(['id'=>1,'titulo'=>'HTML','contenido'=>'Prueba']);
        DB::table('tests')->insert(['id'=>1,'documentacion_id'=>1,'titulo'=>'HTML','modulo'=>'html1']);
        DB::table('preguntas_test')->insert(['test_id'=>1,'pregunta'=>'¿Qué crea un párrafo?','opcion_a'=>'<p>','opcion_b'=>'<img>','opcion_c'=>'<a>','opcion_d'=>'<br>','respuesta_correcta'=>'a']);
        $this->user=Usuario::create(['nombre'=>'Explorador','clave'=>'fixture','rol'=>'Alumno','puntos_totales'=>1200,'puntos_usados'=>100]);
        $this->camp=new AventuraService(); $s=$this->camp->estado($this->user->id);
        $this->camp->iniciar($this->user->id,$s['expedicion']['id']);
        $this->run=AventuraExpedicion::findOrFail($s['expedicion']['id']);
        $this->run->exploracion=[1=>['tipo'=>'gremio','estado'=>'pendiente','gremio'=>['ofertas'=>array_keys(AventuraGremio::catalogo()),'encargos'=>[]]]]; $this->run->save();
        $this->withSession(['usuario_id'=>$this->user->id]);
    }
    protected function tearDown(): void { DB::disconnect('sqlite'); parent::tearDown(); }
    private function guild(string $accion,?string $clave=null,array $extras=[],?int $version=null)
    {
        return $this->postJson('/alumno/aventura/gremio',array_merge(['expedicion_id'=>$this->run->id,'version'=>$version ?? $this->run->fresh()->version,'accion'=>$accion,'encargo'=>$clave,'origen'=>1],$extras));
    }
    private function quest(string $clave): array { return $this->run->fresh()->exploracion[1]['gremio']['encargos'][$clave]; }
    private function history(int $escenario,array $evento): void
    {
        $this->run->refresh(); $h=$this->run->exploracion; $h[$escenario]=$evento; $this->run->exploracion=$h; $this->run->escenario=$escenario; $this->run->save();
    }

    public function test_board_persists_legacy_board_opens_and_player_can_leave_without_accepting(): void
    {
        $this->run->exploracion=[]; $this->run->save();
        $exploration=new class extends AventuraExploracion { protected function tirar(): int { return 40; } };
        $exploration->elegir($this->user->id,$this->run->id,$this->run->version,0);
        $saved=$this->run->fresh()->exploracion;
        self::assertCount(4,$saved[1]['gremio']['ofertas']);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('Un encargo. Una nueva historia.');
        self::assertSame($saved,$this->run->fresh()->exploracion);
        $v=$this->run->fresh()->version; $this->guild('salir')->assertOk();
        $this->guild('salir',null,[],$v)->assertStatus(409);
        self::assertSame(2,$this->run->fresh()->escenario);
        self::assertSame(0,$this->run->fresh()->oro);
        $this->history(3,['tipo'=>'gremio','estado'=>'pendiente']);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('Consultar el tablón');
        $this->guild('abrir')->assertOk(); $this->guild('abrir')->assertStatus(409);
    }

    public function test_delivery_consumes_one_item_and_awards_once_without_changing_learning_currency(): void
    {
        $this->guild('aceptar','botiquin')->assertOk();
        $this->guild('cobrar','botiquin')->assertStatus(422);
        AventuraInventario::recibir($this->run,'pocion-vida');
        AventuraInventario::recibir($this->run,'pocion-vida');
        $v=$this->run->fresh()->version;
        $this->guild('cobrar','botiquin',['oro'=>9999,'xp'=>9999,'cantidad'=>0])->assertOk();
        self::assertSame(1,(int)$this->run->inventario()->sum('cantidad'));
        self::assertSame(15,$this->run->fresh()->oro);
        self::assertSame(20,AventuraPersonaje::where('usuario_id',$this->user->id)->value('experiencia'));
        $this->guild('cobrar','botiquin',[],$v)->assertStatus(409);
        $this->guild('cobrar','botiquin')->assertStatus(409);
        self::assertSame(1200,$this->user->fresh()->puntos_totales);
        self::assertSame(100,$this->user->fresh()->puntos_usados);
    }

    public function test_progress_counts_new_enemies_excludes_fragments_and_survives_scenarios(): void
    {
        $this->guild('aceptar','patrulla')->assertOk(); $this->guild('aceptar','lobo')->assertOk();
        $this->guild('salir')->assertOk();
        $this->history(2,['tipo'=>'enemigo','estado'=>'resuelto','combate'=>['estado'=>'victoria','enemigos'=>[['clave'=>'lobo','xp'=>14],['clave'=>'slime','xp'=>16],['clave'=>'slime','xp'=>0]]]]);
        $g=new AventuraGremio();
        self::assertSame(2,$g->progreso($this->run,'patrulla',$this->quest('patrulla')));
        $this->guild('cobrar','patrulla')->assertStatus(422);
        $this->guild('cobrar','lobo')->assertOk();
        $this->history(3,['tipo'=>'enemigo','estado'=>'resuelto','combate'=>['estado'=>'victoria','enemigos'=>[['clave'=>'hongo','xp'=>20]]]]);
        $this->guild('cobrar','patrulla')->assertOk();
        self::assertSame('cobrado',$this->quest('patrulla')['estado']);
    }

    public function test_quiz_uses_existing_bank_and_counts_for_mentor_without_retries(): void
    {
        $this->guild('aceptar','mentor')->assertOk(); $this->guild('aceptar','runas')->assertOk();
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('¿Qué crea un párrafo?')->assertDontSee('respuesta_correcta')->assertDontSee('La respuesta correcta era:');
        $question=$this->quest('runas')['pregunta']; $this->get('/alumno/aventura/mapa')->assertOk(); self::assertSame($question,$this->quest('runas')['pregunta']);
        $this->guild('responder','runas',['respuesta'=>'a'])->assertOk();
        $this->guild('responder','runas',['respuesta'=>'a'])->assertStatus(409);
        self::assertSame(1,(new AventuraGremio())->progreso($this->run->fresh(),'mentor',$this->quest('mentor')));
        $this->guild('cobrar','runas')->assertOk();
        $this->guild('salir')->assertOk();
        $this->history(2,['tipo'=>'narrativa','estado'=>'resuelto','narrativa'=>['correcta'=>true]]);
        $this->guild('cobrar','mentor')->assertOk();
        self::assertSame('experiencia',$this->run->fresh()->mejoras[0]['tipo']);
    }

    public function test_limit_abandonment_foreign_users_and_invalid_requests(): void
    {
        foreach(['patrulla','lobo','mentor'] as $q) $this->guild('aceptar',$q)->assertOk();
        $this->guild('aceptar','runas')->assertStatus(422);
        $this->guild('abandonar','lobo')->assertOk();
        $this->guild('aceptar','lobo')->assertStatus(409);
        $this->guild('aceptar','runas')->assertOk();
        $this->guild('cobrar',"' OR 1=1 --")->assertNotFound();
        $other=Usuario::create(['nombre'=>'Otro','clave'=>'fixture','rol'=>'Alumno']);
        $this->withSession(['usuario_id'=>$other->id]); $this->guild('salir')->assertNotFound();
        $this->flushSession(); $this->guild('salir')->assertUnauthorized();
    }

    public function test_wrong_or_deleted_question_releases_slot_without_reward(): void
    {
        $this->guild('aceptar','runas')->assertOk();
        $this->guild('responder','runas',['respuesta'=>'b'])->assertOk();
        self::assertSame('fallido',$this->quest('runas')['estado']);
        self::assertStringContainsString('<p>',$this->quest('runas')['mensaje']);
        $this->guild('cobrar','runas')->assertStatus(409);
        $this->history(2,['tipo'=>'gremio','estado'=>'pendiente','gremio'=>['ofertas'=>['runas'],'encargos'=>[]]]);
        $this->guild('aceptar','runas')->assertOk(); PreguntaTest::query()->delete();
        $this->guild('responder','runas',['origen'=>2,'respuesta'=>'a'])->assertOk();
        self::assertSame('cancelado',$this->run->fresh()->exploracion[2]['gremio']['encargos']['runas']['estado']);
        self::assertSame(0,$this->run->fresh()->oro);
    }

    public function test_discovery_travel_and_boss_rewards_use_real_history(): void
    {
        foreach(['hallazgo','explorador','guardian'] as $q) $this->guild('aceptar',$q)->assertOk();
        AventuraInventario::recibir($this->run,'pocion-vida');
        $this->guild('cobrar','hallazgo')->assertStatus(422);
        $this->guild('salir')->assertOk();
        $this->history(2,['tipo'=>'tesoro','estado'=>'resuelto','narrativa'=>['botin'=>'pocion-vida']]);
        $this->guild('cobrar','hallazgo')->assertOk();
        $this->guild('cobrar','explorador')->assertStatus(422);
        $this->history(3,['tipo'=>'gremio','estado'=>'pendiente']);
        $this->guild('cobrar','explorador')->assertOk();
        self::assertSame('inspiracion',$this->run->fresh()->efectos[0]['tipo']);
        $this->history(10,['tipo'=>'jefe','estado'=>'resuelto','combate'=>['estado'=>'victoria','enemigos'=>[['clave'=>'silvar','xp'=>120]]]]);
        $this->guild('cobrar','guardian')->assertOk(); $this->guild('equipar','guardian')->assertOk();
        self::assertSame('hierro',$this->run->fresh()->equipo['armadura']);
        $this->camp->reiniciar($this->user->id,$this->run->id,$this->run->fresh()->version);
        $next=$this->camp->estado($this->user->id); self::assertSame([], (new AventuraGremio())->diario(AventuraExpedicion::findOrFail($next['expedicion']['id'])));
        self::assertGreaterThan(1,$next['personaje']['nivel']);
    }

    public function test_claiming_or_answering_during_combat_is_rejected(): void
    {
        $this->guild('aceptar','runas')->assertOk();
        $this->history(2,['tipo'=>'enemigo','estado'=>'pendiente','combate'=>['estado'=>'jugador']]);
        $this->guild('responder','runas',['respuesta'=>'a'])->assertStatus(409);
        self::assertArrayNotHasKey('correcta',$this->quest('runas'));
    }
}
