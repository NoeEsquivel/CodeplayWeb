<?php

namespace Tests\Feature;

use App\Models\{Usuario,AventuraExpedicion,Combate,AventuraPersonaje};
use App\Services\{AventuraService,AventuraCombate,AventuraCatalogo};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AventuraCombateTest extends TestCase
{
    private Usuario $user;
    private AventuraExpedicion $run;
    private AventuraCombate $engine;
    private AventuraService $camp;

    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite',\PDO::getAvailableDrivers())) $this->markTestSkipped('Se requiere pdo_sqlite.');
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        foreach (['2025_09_30_064434_create_usuarios_table.php','2025_09_30_064441_create_documentacion_table.php','2025_09_30_064446_create_tests_table.php','2025_09_30_064451_create_preguntas_test_table.php','2025_10_18_173127_add_test_module_to_tests_table.php','2025_09_30_064527_create_enemigos_table.php','2025_09_30_064532_create_combates_table.php','2025_09_30_064537_create_items_table.php','2025_09_30_064544_create_inventario_table.php','2025_10_21_082507_create_desafio_recompensas_table.php','2026_09_08_120000_create_aventura_preparacion_tables.php','2026_09_08_140000_integrate_aventura_inventory.php','2026_09_09_090000_add_aventura_exploracion.php','2026_09_09_120000_add_aventura_bosque_combate.php'] as $file) (require database_path('migrations/'.$file))->up();
        DB::table('documentacion')->insert(['id'=>1,'titulo'=>'Fixture HTML','contenido'=>'Fixture']);
        DB::table('tests')->insert(['id'=>1,'documentacion_id'=>1,'titulo'=>'HTML','modulo'=>'html1']);
        DB::table('preguntas_test')->insert(['test_id'=>1,'pregunta'=>'¿Qué etiqueta crea un párrafo?','opcion_a'=>'<p>','opcion_b'=>'<img>','opcion_c'=>'<a>','opcion_d'=>'<br>','respuesta_correcta'=>'a']);
        $this->user=Usuario::create(['nombre'=>'Combatiente','clave'=>'fixture','puntos_totales'=>3000,'puntos_usados'=>100]);
        $this->camp=new AventuraService();
        $state=$this->camp->estado($this->user->id);
        $equipment=AventuraCatalogo::vacio(); $equipment['arma']='espada-viajero'; $equipment['armadura']='cuero';
        $equipment['hechizos']=['brasa','barrera']; $equipment['pociones']=[['id'=>'pocion-vida','cantidad'=>3],['id'=>'pocion-mana','cantidad'=>2]];
        $this->camp->guardar($this->user->id,$state['expedicion']['id'],0,$equipment);
        $this->camp->iniciar($this->user->id,$state['expedicion']['id']);
        $this->run=AventuraExpedicion::findOrFail($state['expedicion']['id']);
        $this->run->exploracion=[1=>['camino'=>0,'tipo'=>'enemigo','estado'=>'pendiente']]; $this->run->save();
        $this->engine=new class extends AventuraCombate { protected function azar(int $min,int $max): int { return $min; } };
    }

    protected function tearDown(): void { DB::disconnect('sqlite'); parent::tearDown(); }
    private function action(array $a): string
    {
        return $this->engine->actuar($this->user->id,$this->run->id,$this->run->fresh()->version,$a);
    }
    private function battle(): array { $run=$this->run->fresh(); return $run->exploracion[$run->escenario]['combate']; }
    private function editBattle(callable $change): void
    {
        $run=$this->run->fresh(); $history=$run->exploracion; $battle=$history[$run->escenario]['combate']; $change($battle);
        $history[$run->escenario]['combate']=$battle; $run->exploracion=$history; $run->save();
    }
    private function combatPost(array $a, ?int $version=null)
    {
        return $this->withSession(['usuario_id'=>$this->user->id])->postJson('/alumno/aventura/combate',
            ['expedicion_id'=>$this->run->id,'version'=>$version ?? $this->run->fresh()->version]+$a);
    }

    public function test_turns_reload_and_retry_do_not_duplicate_damage_or_rewards(): void
    {
        $this->action(['accion'=>'abrir']); $b=$this->battle();
        self::assertCount(1,$b['enemigos']); self::assertSame('lobo',$b['enemigos'][0]['clave']);
        $version=$this->run->fresh()->version;
        $this->combatPost(['accion'=>'atacar','objetivo'=>0,'dano'=>9999,'oro'=>9999],$version)->assertOk();
        self::assertSame(1,$this->battle()['enemigos'][0]['nivel']);
        self::assertSame(15,$this->battle()['enemigos'][0]['vida']);
        self::assertSame(96,$this->run->fresh()->vida_actual);
        $this->combatPost(['accion'=>'atacar','objetivo'=>0],$version)->assertStatus(409);
        self::assertSame(96,$this->run->fresh()->vida_actual);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('Lobo del sendero');
        $this->combatPost(['accion'=>'abrir'])->assertStatus(409);
        $this->combatPost(['accion'=>'continuar'])->assertStatus(409);
        $this->combatPost(['accion'=>'atacar','objetivo'=>0])->assertOk();
        $version=$this->run->fresh()->version;
        $this->combatPost(['accion'=>'atacar','objetivo'=>0])->assertOk();
        self::assertSame('victoria',$this->battle()['estado']);
        self::assertSame(14,AventuraPersonaje::where('usuario_id',$this->user->id)->value('experiencia'));
        self::assertSame(8,$this->run->fresh()->oro); self::assertSame(1,Combate::count());
        $this->combatPost(['accion'=>'atacar','objetivo'=>0],$version)->assertStatus(409);
        $this->combatPost(['accion'=>'atacar','objetivo'=>0])->assertStatus(409);
        self::assertSame(1,DB::table('aventura_experiencia_eventos')->count());
        $this->combatPost(['accion'=>'continuar'])->assertOk(); self::assertSame(2,$this->run->fresh()->escenario);
        $this->combatPost(['accion'=>'continuar'])->assertStatus(409);
    }

    public function test_visual_sequence_preserves_player_then_enemy_order_and_does_not_leak_questions(): void
    {
        $this->action(['accion'=>'abrir']);
        $response=$this->combatPost(['accion'=>'atacar','objetivo'=>0])->assertOk();
        $sequence=$response->json('secuencia');
        self::assertSame('jugador',$sequence[0]['actor']); self::assertSame('atacar',$sequence[0]['tipo']);
        self::assertSame('enemigo-0',$sequence[1]['actor']);
        self::assertSame(15,$sequence[0]['enemigos'][0]['vida']);
        self::assertGreaterThan($sequence[1]['vida'],$sequence[0]['vida']);
        self::assertArrayNotHasKey('pregunta',$sequence[0]);
        self::assertArrayNotHasKey('respuesta_correcta',$sequence[0]);
        self::assertSame($this->run->fresh()->vida_actual,$sequence[count($sequence)-1]['vida']);
        $this->action(['accion'=>'defender']);
        self::assertSame('defender',$this->engine->secuencia()[0]['tipo']);
        self::assertStringNotContainsString('Golpeas',$this->engine->secuencia()[0]['texto']);
    }

    public function test_defense_barrier_potion_and_invalid_actions_are_atomic(): void
    {
        $this->action(['accion'=>'abrir']);
        $this->run->refresh()->update(['mana_actual'=>20]);
        $this->action(['accion'=>'defender']);
        self::assertSame(98,$this->run->fresh()->vida_actual); self::assertSame(24,$this->run->fresh()->mana_actual);
        $this->action(['accion'=>'hechizo','objeto'=>'barrera']);
        self::assertSame(98,$this->run->fresh()->vida_actual); self::assertSame(14,$this->run->fresh()->mana_actual);
        self::assertSame([],$this->run->fresh()->efectos);
        $this->combatPost(['accion'=>'hechizo','objeto'=>'brasa','objetivo'=>9])->assertStatus(422);
        self::assertSame(14,$this->run->fresh()->mana_actual);
        $this->combatPost(['accion'=>'hechizo','objeto'=>'supernova','objetivo'=>0])->assertStatus(422);
        $investment=$this->run->fresh()->inversion;
        $this->run->refresh()->update(['vida_actual'=>40]);
        $this->action(['accion'=>'pocion','objeto'=>'pocion-vida']);
        self::assertSame(59,$this->run->fresh()->vida_actual); // Turn 3: 14 attack minus 3 armor.
        self::assertSame(2,$this->run->fresh()->equipo['pociones'][0]['cantidad']);
        self::assertSame($investment,$this->run->fresh()->inversion);
        $this->run->refresh()->update(['mana_actual'=>0]);
        $this->combatPost(['accion'=>'hechizo','objeto'=>'brasa','objetivo'=>0])->assertStatus(422);
        self::assertSame(0,$this->run->fresh()->mana_actual);
        $this->combatPost(['accion'=>'pocion','objeto'=>"' OR 1=1 --"])->assertStatus(422);
    }

    public function test_split_poison_steal_and_fire_weakness_have_distinct_effects(): void
    {
        $this->action(['accion'=>'abrir']);
        $this->editBattle(function (&$b) { $b['enemigos'][0]['habilidad']='division'; $b['enemigos'][0]['vida']=24; $b['enemigos'][0]['max']=40; });
        $this->action(['accion'=>'atacar','objetivo'=>0]);
        self::assertCount(2,$this->battle()['enemigos']);
        self::assertSame(11,array_sum(array_column($this->battle()['enemigos'],'vida')));
        self::assertSame(14,array_sum(array_column($this->battle()['enemigos'],'xp')));
        $this->editBattle(function (&$b) { $b['enemigos']=[$b['enemigos'][0]]; $b['enemigos'][0]['vida']=100; $b['enemigos'][0]['max']=100; $b['enemigos'][0]['habilidad']='veneno'; $b['turno']=3; });
        $this->action(['accion'=>'defender']);
        self::assertSame('veneno',$this->run->fresh()->efectos[0]['tipo']);
        self::assertSame(2,$this->run->fresh()->efectos[0]['turnos']);
        $this->run->refresh()->update(['oro'=>20]);
        $this->editBattle(function (&$b) { $b['enemigos'][0]['habilidad']='robo'; });
        $this->action(['accion'=>'defender']);
        self::assertSame(12,$this->run->fresh()->oro); self::assertSame(8,$this->battle()['robado']);
        $this->editBattle(function (&$b) { $b['enemigos'][0]['vida']=10; $b['enemigos'][0]['habilidad']='coraza'; $b['enemigos'][0]['defensa']=4; });
        $this->action(['accion'=>'hechizo','objeto'=>'brasa','objetivo'=>0]);
        self::assertSame('victoria',$this->battle()['estado']); // (8+1-2) * 1.5 = 11.
        self::assertSame(28,$this->run->fresh()->oro);
        self::assertSame([],$this->run->fresh()->efectos);
    }

    public function test_boss_question_uses_existing_bank_and_never_exposes_answer_before_submission(): void
    {
        $this->run->update(['escenario'=>10,'exploracion'=>[10=>['tipo'=>'jefe','estado'=>'pendiente']]]);
        $this->action(['accion'=>'abrir']);
        self::assertSame('silvar',$this->battle()['enemigos'][0]['clave']);
        $this->editBattle(fn(&$b)=>$b['turno']=3);
        $this->action(['accion'=>'defender']);
        self::assertSame('pregunta',$this->battle()['estado']); self::assertSame(100,$this->run->fresh()->vida_actual);
        $page=$this->withSession(['usuario_id'=>$this->user->id])->get('/alumno/aventura/mapa')->assertOk()->assertSee('¿Qué etiqueta crea un párrafo?')->assertDontSee('respuesta_correcta');
        preg_match_all('/data-combat-action="responder" data-answer="([a-d])"><b>([A-D])<\/b> (.*?)<\/button>/s', $page->getContent(), $options, PREG_SET_ORDER);
        self::assertCount(4, $options);
        $source=['a'=>'<p>', 'b'=>'<img>', 'c'=>'<a>', 'd'=>'<br>'];
        foreach ($options as $position=>$option) {
            self::assertSame(['A','B','C','D'][$position], $option[2]);
            self::assertSame($source[$option[1]], html_entity_decode($option[3], ENT_QUOTES, 'UTF-8'));
        }
        $again=$this->get('/alumno/aventura/mapa')->assertOk();
        preg_match_all('/data-combat-action="responder" data-answer="([a-d])"><b>([A-D])<\/b> (.*?)<\/button>/s', $again->getContent(), $sameOptions, PREG_SET_ORDER);
        self::assertSame($options, $sameOptions);
        $correctButton=array_values(array_filter($options, fn($option)=>html_entity_decode($option[3], ENT_QUOTES, 'UTF-8')==='<p>'))[0];
        self::assertArrayNotHasKey('respuesta_correcta',$this->engine->preguntaPublica($this->battle()));
        $this->combatPost(['accion'=>'atacar','objetivo'=>0])->assertStatus(409);
        $this->action(['accion'=>'responder','respuesta'=>$correctButton[1]]);
        self::assertSame(100,$this->run->fresh()->vida_actual); self::assertSame(4,$this->battle()['turno']);
        $this->editBattle(function (&$b) { $b['turno']=6; $b['enemigos'][0]['vida']=100; });
        $this->action(['accion'=>'defender']);
        $this->action(['accion'=>'responder','respuesta'=>'b']);
        self::assertSame(76,$this->run->fresh()->vida_actual); // ceil((ceil(19*2.6)-3)/2).
        $this->editBattle(fn(&$b)=>$b['enemigos'][0]['vida']=1);
        $this->action(['accion'=>'atacar','objetivo'=>0]);
        self::assertSame('victoria',$this->battle()['estado']);
        self::assertSame(2,AventuraPersonaje::where('usuario_id',$this->user->id)->value('nivel'));
        $this->action(['accion'=>'continuar']); self::assertSame(11,$this->run->fresh()->escenario);
    }

    public function test_defeat_refunds_consumed_items_once_and_is_private(): void
    {
        $this->camp->otorgarExperiencia($this->user->id,'fixture:old',120);
        $this->action(['accion'=>'abrir']);
        $this->run->inventario()->whereHas('item',fn($q)=>$q->where('clave_catalogo','pocion-vida'))->update(['cantidad'=>0]);
        $this->run->update(['vida_actual'=>1]);
        $version=$this->run->fresh()->version;
        $this->combatPost(['accion'=>'defender'],$version)->assertOk()->assertJsonPath('url',route('alumno.aventura.derrota',['expedicion'=>$this->run->id]));
        self::assertSame('derrota',$this->run->fresh()->estado);
        self::assertSame(0,$this->run->inventario()->count());
        self::assertSame(100,$this->user->fresh()->puntos_usados);
        self::assertSame(2,AventuraPersonaje::where('usuario_id',$this->user->id)->value('nivel'));
        $this->combatPost(['accion'=>'defender'],$version)->assertStatus(409);
        $this->get('/alumno/aventura/derrota/'.$this->run->id)->assertOk()->assertSee('Caer también es parte del camino.');
        $other=Usuario::create(['nombre'=>'Otra cuenta','clave'=>'fixture']);
        $this->withSession(['usuario_id'=>$other->id])->get('/alumno/aventura/derrota/'.$this->run->id)->assertNotFound();
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>$version,'accion'=>'abrir'])->assertNotFound();
        self::assertSame(100,$this->user->fresh()->puntos_usados); self::assertSame(1,Combate::count());
    }

    public function test_multiple_enemies_and_missing_question_bank_are_supported(): void
    {
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>0,'accion'=>'abrir'])->assertRedirect('/login');
        $this->run->update(['escenario'=>5,'exploracion'=>[5=>['tipo'=>'enemigo','estado'=>'pendiente']]]);
        $this->action(['accion'=>'abrir']); self::assertCount(2,$this->battle()['enemigos']);
        $this->action(['accion'=>'atacar','objetivo'=>1]);
        self::assertSame(40,$this->battle()['enemigos'][0]['vida']);
        self::assertSame(27,$this->battle()['enemigos'][1]['vida']);
        self::assertSame(88,$this->run->fresh()->vida_actual);
        $this->run->refresh()->update(['escenario'=>10,'exploracion'=>[10=>['tipo'=>'jefe','estado'=>'pendiente']]]);
        $this->action(['accion'=>'abrir']);
        DB::table('preguntas_test')->delete();
        $this->editBattle(fn(&$b)=>$b['turno']=3);
        $this->action(['accion'=>'defender']);
        self::assertSame('jugador',$this->battle()['estado']); self::assertSame(4,$this->battle()['turno']);
        self::assertSame(80,$this->run->fresh()->vida_actual);
    }

    public function test_every_catalogue_spell_uses_its_existing_mana_cost(): void
    {
        foreach ((new AventuraCatalogo())->items() as $id=>$item) {
            if ($item['tipo'] !== 'hechizo') continue;
            $this->run->refresh(); $equipment=$this->run->equipo; $equipment['hechizos']=[$id];
            $this->run->estado='preparacion'; $this->run->exploracion=[1=>['tipo'=>'enemigo','estado'=>'pendiente']]; $this->run->save();
            $this->camp->guardar($this->user->id,$this->run->id,$this->run->version,$equipment);
            $this->camp->iniciar($this->user->id,$this->run->id);
            $this->action(['accion'=>'abrir']);
            $this->editBattle(function (&$b) { $b['enemigos'][0]['vida']=500; $b['enemigos'][0]['max']=500; });
            $this->run->refresh()->update(['vida_actual'=>50]);
            $before=$this->run->fresh()->mana_actual;
            $this->action(['accion'=>'hechizo','objeto'=>$id,'objetivo'=>0]);
            self::assertSame($before-$item['mana'],$this->run->fresh()->mana_actual,$id);
            self::assertSame(2,$this->battle()['turno']);
            self::assertGreaterThan(0,$this->run->fresh()->vida_actual);
        }
    }
}
