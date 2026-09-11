<?php

namespace Tests\Feature;

use App\Models\{Usuario,AventuraExpedicion,AventuraPersonaje,Combate};
use App\Services\{AventuraService,AventuraCombate,AventuraCatalogo,AventuraDragon};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AventuraDragonTest extends TestCase
{
    private Usuario $user;
    private AventuraExpedicion $run;
    private AventuraCombate $engine;

    protected function setUp(): void
    {
        parent::setUp();
        if(!in_array('sqlite',\PDO::getAvailableDrivers())) $this->markTestSkipped('Se requiere pdo_sqlite.');
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        foreach(['2025_09_30_064434_create_usuarios_table.php','2025_09_30_064441_create_documentacion_table.php','2025_09_30_064446_create_tests_table.php','2025_09_30_064451_create_preguntas_test_table.php','2025_10_18_173127_add_test_module_to_tests_table.php','2025_09_30_064527_create_enemigos_table.php','2025_09_30_064532_create_combates_table.php','2025_09_30_064537_create_items_table.php','2025_09_30_064544_create_inventario_table.php','2025_10_21_082507_create_desafio_recompensas_table.php','2026_09_08_120000_create_aventura_preparacion_tables.php','2026_09_08_140000_integrate_aventura_inventory.php','2026_09_09_090000_add_aventura_exploracion.php','2026_09_09_120000_add_aventura_bosque_combate.php','2026_09_09_180000_add_aventura_regiones.php','2026_09_10_090000_add_aventura_dragon.php'] as $file) (require database_path('migrations/'.$file))->up();
        DB::table('documentacion')->insert(['id'=>1,'titulo'=>'Prueba','contenido'=>'Prueba']);
        DB::table('tests')->insert(['id'=>1,'documentacion_id'=>1,'titulo'=>'Prueba','modulo'=>'mundo1']);
        foreach(range(1,3) as $i) DB::table('preguntas_test')->insert(['test_id'=>1,'pregunta'=>'Runas '.$i,'opcion_a'=>'Correcta','opcion_b'=>'Incorrecta','opcion_c'=>'Otra','opcion_d'=>'Otra más','respuesta_correcta'=>'a']);
        $this->user=Usuario::create(['nombre'=>'Prueba','clave'=>'fixture','puntos_totales'=>8000]);
        $this->engine=new class extends AventuraCombate { protected function azar(int $min,int $max): int { return $max; } };
        $this->withSession(['usuario_id'=>$this->user->id]);
    }

    protected function tearDown(): void { DB::disconnect('sqlite'); parent::tearDown(); }

    private function preparar(bool $avanzado=true): void
    {
        $camp=new AventuraService(); $s=$camp->estado($this->user->id);
        $p=AventuraPersonaje::where('usuario_id',$this->user->id)->first(); $p->nivel=$avanzado?20:1;
        foreach(AventuraCatalogo::STATS as $stat) $p->$stat=$avanzado?20:1; $p->save();
        $equipo=AventuraCatalogo::vacio();
        if($avanzado) {
            $equipo['arma']='filo-fenix'; $equipo['armadura']='coraza-dragon';
            $equipo['hechizos']=['supernova','brasa']; $equipo['pociones']=[['id'=>'pocion-mayor','cantidad'=>8]];
        } else $equipo['arma']='espada-viajero';
        $camp->guardar($this->user->id,$s['expedicion']['id'],0,$equipo);
        $camp->iniciar($this->user->id,$s['expedicion']['id']);
        $this->run=AventuraExpedicion::findOrFail($s['expedicion']['id']);
        $this->run->escenario=50;
        if($avanzado) $this->run->mejoras=[['tipo'=>'dano','valor'=>20],['tipo'=>'resistencia','valor'=>10]];
        $this->run->exploracion=[50=>['tipo'=>'dragon','estado'=>'pendiente']]; $this->run->save();
        $this->accion(['accion'=>'abrir']);
    }

    private function accion(array $a): string { return $this->engine->actuar($this->user->id,$this->run->id,$this->run->fresh()->version,$a); }
    private function battle(): array { return $this->run->fresh()->exploracion[50]['combate']; }
    private function cambiar(callable $f): void { $r=$this->run->fresh(); $h=$r->exploracion; $f($h[50]['combate']); $r->exploracion=$h; $r->save(); }

    public function test_prepared_player_wins_three_phases_with_real_resources_and_reward_is_once(): void
    {
        $this->preparar(); $fases=[]; $questions=[]; $pociones=0;
        $beforePoints=$this->user->fresh()->puntos_usados;
        for($i=0;$i<80 && $this->battle()['estado']!=='victoria';$i++) {
            $b=$this->battle(); $fases[]=AventuraDragon::fase($b['enemigos'][0]);
            if($b['estado']==='pregunta') { $questions[]=$b['pregunta']; $a=['accion'=>'responder','respuesta'=>'a']; }
            elseif($this->run->fresh()->vida_actual<110) { $a=['accion'=>'pocion','objeto'=>'pocion-mayor']; $pociones++; }
            elseif(AventuraDragon::fase($b['enemigos'][0])===1 && $this->run->fresh()->mana_actual>=36) $a=['accion'=>'hechizo','objeto'=>'supernova','objetivo'=>0];
            else $a=['accion'=>'atacar','objetivo'=>0];
            $version=$this->run->fresh()->version; $this->accion($a);
            self::assertGreaterThan(0,$this->run->fresh()->vida_actual);
        }
        self::assertSame('victoria',$this->battle()['estado']); self::assertSame([1,2,3],array_values(array_unique($fases)));
        self::assertGreaterThan(0,$pociones); self::assertGreaterThanOrEqual(3,count(array_unique($questions)));
        self::assertSame(1,Combate::where('escenario',50)->count()); self::assertSame(300,$this->run->fresh()->oro);
        self::assertSame($beforePoints,$this->user->fresh()->puntos_usados);
        self::assertSame(600,AventuraPersonaje::where('usuario_id',$this->user->id)->value('experiencia'));
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>$version]+$a)->assertStatus(409);
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>$this->run->fresh()->version,'accion'=>'continuar'])->assertStatus(409);
        $this->get('/alumno/aventura/mapa')->assertRedirect(route('alumno.aventura.final',['expedicion'=>$this->run->id]));
        self::assertSame(50,$this->run->fresh()->escenario); self::assertSame(300,$this->run->fresh()->oro);
    }

    private function victoriaRapida(): void
    {
        $this->preparar(); $this->cambiar(fn(&$b)=>$b['enemigos'][0]['vida']=1);
        $this->accion(['accion'=>'atacar','objetivo'=>0]);
    }

    public function test_final_scene_trophy_and_replay_keep_progress_and_refund_only_once(): void
    {
        $this->victoriaRapida();
        $id=$this->run->id; $version=$this->run->fresh()->version;
        $personaje=AventuraPersonaje::where('usuario_id',$this->user->id)->first()->toArray();
        $this->get('/alumno/aventura/final/'.$id)->assertOk()->assertSee('Soy Noé Esquivel.')->assertSee('Conquistador de la Aventura');
        $this->get('/inicio-alumno')->assertOk()->assertSee('Conquistador de la Aventura');
        $this->post('/alumno/aventura/completar',['expedicion_id'=>$id,'version'=>$version])->assertRedirect(route('alumno.aventura'));
        self::assertSame('victoria',$this->run->fresh()->estado);
        self::assertSame(0,$this->user->fresh()->puntos_usados); self::assertSame(0,$this->run->inventario()->count());
        self::assertSame($personaje,AventuraPersonaje::where('usuario_id',$this->user->id)->first()->toArray());
        $new=(new AventuraService())->estado($this->user->id)['expedicion'];
        self::assertNotSame($id,$new['id']); self::assertSame('preparacion',$new['estado']); self::assertSame(1,$new['escenario']);
        // A retry of the old closing request must not refund another expedition's purchases.
        $equipo=AventuraCatalogo::vacio(); $equipo['arma']='espada-viajero';
        (new AventuraService())->guardar($this->user->id,$new['id'],$new['version'],$equipo);
        $spent=$this->user->fresh()->puntos_usados;
        $this->post('/alumno/aventura/completar',['expedicion_id'=>$id,'version'=>$version])->assertRedirect();
        self::assertSame($spent,$this->user->fresh()->puntos_usados);
        $this->flushSession(); $this->withSession(['usuario_id'=>$this->user->id]);
        $this->get('/inicio-alumno')->assertOk()->assertSee('Conquistador de la Aventura');
        $this->get('/alumno/aventura/final/'.$id)->assertOk()->assertSee('Preparar otra aventura')->assertDontSee('name="expedicion_id"',false);
        self::assertSame(1,Combate::where('escenario',50)->count());
    }

    public function test_final_cannot_be_claimed_without_victory_or_by_other_account(): void
    {
        $this->preparar(); $id=$this->run->id; $v=$this->run->fresh()->version;
        $this->get('/alumno/aventura/final/'.$id)->assertNotFound();
        $this->get('/inicio-alumno')->assertOk()->assertDontSee('Conquistador de la Aventura');
        $this->postJson('/alumno/aventura/completar',['expedicion_id'=>$id,'version'=>$v])->assertStatus(409);
        self::assertSame('activa',$this->run->fresh()->estado);
        $this->cambiar(fn(&$b)=>$b['enemigos'][0]['vida']=1); $this->accion(['accion'=>'atacar','objetivo'=>0]);
        $other=Usuario::create(['nombre'=>'Otra cuenta','clave'=>'fixture']);
        $this->withSession(['usuario_id'=>$other->id]);
        $this->get('/alumno/aventura/final/'.$id)->assertNotFound();
        $this->get('/inicio-alumno')->assertOk()->assertDontSee('Conquistador de la Aventura');
        $this->postJson('/alumno/aventura/completar',['expedicion_id'=>$id,'version'=>$this->run->fresh()->version])->assertNotFound();
    }

    public function test_unprepared_player_loses_and_investment_is_refunded_once(): void
    {
        $this->preparar(false); self::assertGreaterThan(0,$this->user->fresh()->puntos_usados);
        for($i=0;$i<20 && $this->run->fresh()->estado==='activa';$i++) $this->accion($this->battle()['estado']==='pregunta'?['accion'=>'responder','respuesta'=>'b']:['accion'=>'atacar','objetivo'=>0]);
        self::assertSame('derrota',$this->run->fresh()->estado); self::assertSame(0,$this->user->fresh()->puntos_usados);
        self::assertSame(0,$this->run->inventario()->count()); self::assertSame(1,AventuraPersonaje::where('usuario_id',$this->user->id)->value('nivel'));
        (new AventuraService())->registrarDerrota($this->user->id,$this->run->id);
        self::assertSame(0,$this->user->fresh()->puntos_usados);
    }

    public function test_questions_persist_correct_answer_opens_one_attack_and_wrong_answer_is_mitigated(): void
    {
        $this->preparar(); $this->cambiar(fn(&$b)=>$b['turno']=3);
        $this->accion(['accion'=>'defender']); $b=$this->battle();
        self::assertSame('pregunta',$b['estado']);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('Aliento del olvido')->assertDontSee('respuesta_correcta');
        self::assertSame($b['pregunta'],$this->battle()['pregunta']);
        $this->accion(['accion'=>'responder','respuesta'=>'a']); self::assertTrue($this->battle()['enemigos'][0]['expuesto']);
        $this->accion(['accion'=>'atacar','objetivo'=>0]); self::assertFalse($this->battle()['enemigos'][0]['expuesto']);
        foreach([900=>1,600=>2,300=>3] as $hp=>$fase) {
            $this->cambiar(function(&$b)use($hp){$b['enemigos'][0]['vida']=$hp;$b['turno']=6;});
            $this->run->refresh()->update(['vida_actual'=>245,'efectos'=>[['tipo'=>'barrera','nombre'=>'Prueba','valor'=>200]]]);
            $this->accion(['accion'=>'defender']); self::assertSame('pregunta',$this->battle()['estado']);
            self::assertSame(AventuraDragon::especial($this->battle()['enemigos'][0]),$this->battle()['enemigos'][0]['ataque_especial']);
            $this->accion(['accion'=>'responder','respuesta'=>'b']); self::assertSame(245,$this->run->fresh()->vida_actual);
            self::assertSame([],$this->run->fresh()->efectos);
        }
    }

    public function test_fire_resistance_phase_thresholds_and_missing_question_fallback(): void
    {
        $this->preparar();
        $this->accion(['accion'=>'hechizo','objeto'=>'brasa','objetivo'=>0]);
        // (8 + 20 magic) * 1.2 bonus = 34, minus 9 defense = 25, fire resistance => 13.
        self::assertSame(987,$this->battle()['enemigos'][0]['vida']);
        self::assertSame(1,AventuraDragon::fase(['vida'=>667,'max'=>1000]));
        self::assertSame(2,AventuraDragon::fase(['vida'=>666,'max'=>1000]));
        self::assertSame(3,AventuraDragon::fase(['vida'=>333,'max'=>1000]));
        $this->cambiar(fn(&$b)=>$b['turno']=3); $this->accion(['accion'=>'defender']);
        DB::table('preguntas_test')->delete(); $hp=$this->run->fresh()->vida_actual;
        $this->accion(['accion'=>'responder','respuesta'=>'a']); self::assertSame($hp,$this->run->fresh()->vida_actual);
        self::assertArrayNotHasKey('expuesto',$this->battle()['enemigos'][0]);
    }

    public function test_other_users_and_forged_encounters_cannot_control_the_dragon(): void
    {
        $this->preparar(); $v=$this->run->fresh()->version;
        $other=Usuario::create(['nombre'=>'Otro','clave'=>'fixture']); $this->withSession(['usuario_id'=>$other->id]);
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>$v,'accion'=>'atacar','objetivo'=>0])->assertNotFound();
        self::assertSame($v,$this->run->fresh()->version);
        $this->withSession(['usuario_id'=>$this->user->id]);
        $this->run->refresh()->update(['escenario'=>49,'exploracion'=>[49=>['tipo'=>'dragon','estado'=>'pendiente']]]);
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>$v,'accion'=>'abrir'])->assertStatus(409);
    }
}
