<?php

namespace Tests\Feature;

use App\Models\{Usuario,AventuraExpedicion,AventuraPersonaje,Enemigo};
use App\Services\{AventuraService,AventuraCombate,AventuraCatalogo,AventuraInventario};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AventuraRegionesTest extends TestCase
{
    private Usuario $user;
    private AventuraExpedicion $run;
    private AventuraCombate $engine;
    protected function setUp(): void
    {
        parent::setUp();
        if(!in_array('sqlite',\PDO::getAvailableDrivers())) $this->markTestSkipped('Se requiere pdo_sqlite.');
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        foreach(['2025_09_30_064434_create_usuarios_table.php','2025_09_30_064441_create_documentacion_table.php','2025_09_30_064446_create_tests_table.php','2025_09_30_064451_create_preguntas_test_table.php','2025_10_18_173127_add_test_module_to_tests_table.php','2025_09_30_064527_create_enemigos_table.php','2025_09_30_064532_create_combates_table.php','2025_09_30_064537_create_items_table.php','2025_09_30_064544_create_inventario_table.php','2025_10_21_082507_create_desafio_recompensas_table.php','2026_09_08_120000_create_aventura_preparacion_tables.php','2026_09_08_140000_integrate_aventura_inventory.php','2026_09_09_090000_add_aventura_exploracion.php','2026_09_09_120000_add_aventura_bosque_combate.php','2026_09_09_180000_add_aventura_regiones.php'] as $file) (require database_path('migrations/'.$file))->up();
        DB::table('documentacion')->insert(['id'=>1,'titulo'=>'Prueba','contenido'=>'Prueba']);
        foreach(['html1','css1','js1','mundo1'] as $i=>$modulo) {
            DB::table('tests')->insert(['id'=>$i+1,'documentacion_id'=>1,'titulo'=>'Prueba','modulo'=>$modulo]);
            DB::table('preguntas_test')->insert(['test_id'=>$i+1,'pregunta'=>'Pregunta '.$modulo,'opcion_a'=>'Correcta','opcion_b'=>'Incorrecta','opcion_c'=>'Otra','opcion_d'=>'Otra más','respuesta_correcta'=>'a']);
        }
        $this->user=Usuario::create(['nombre'=>'Prueba','clave'=>'fixture','rol'=>'Alumno','puntos_totales'=>5000]);
        $camp=new AventuraService(); $s=$camp->estado($this->user->id);
        $equipo=AventuraCatalogo::vacio(); $equipo['arma']='filo-fenix'; $equipo['armadura']='coraza-dragon'; $equipo['hechizos']=['brasa','barrera']; $equipo['pociones']=[['id'=>'pocion-vida','cantidad'=>3]];
        $camp->guardar($this->user->id,$s['expedicion']['id'],0,$equipo); $camp->iniciar($this->user->id,$s['expedicion']['id']);
        $this->run=AventuraExpedicion::findOrFail($s['expedicion']['id']);
        $this->engine=new class extends AventuraCombate {
            public array $tiradas=[];
            protected function azar(int $min,int $max): int { return array_shift($this->tiradas) ?? $min; }
        };
        $this->withSession(['usuario_id'=>$this->user->id]);
    }
    protected function tearDown(): void { DB::disconnect('sqlite'); parent::tearDown(); }
    private function abrir(int $escenario,?string $clave=null): void
    {
        $this->run->refresh(); $this->run->estado='activa'; $this->run->vida_actual=500; $this->run->mana_actual=100;
        $this->run->escenario=$escenario; $this->run->efectos=[]; $this->run->exploracion=[$escenario=>['tipo'=>$escenario%10===0?'jefe':'enemigo','estado'=>'pendiente']]; $this->run->save();
        if($clave) {
            $region=intdiv($escenario-1,10)+1;
            $keys=Enemigo::whereIn('clave',AventuraCombate::REGIONES[$region])->orderBy('id')->pluck('clave')->all();
            $this->engine->tiradas=[$escenario>=5?100:array_search($clave,$keys),array_search($clave,$keys)];
        }
        $this->accion(['accion'=>'abrir']);
    }
    private function accion(array $a): string { return $this->engine->actuar($this->user->id,$this->run->id,$this->run->fresh()->version,$a); }
    private function battle(): array { $r=$this->run->fresh(); return $r->exploracion[$r->escenario]['combate']; }
    private function cambiar(callable $f): void { $r=$this->run->fresh(); $h=$r->exploracion; $f($h[$r->escenario]['combate']); $r->exploracion=$h; $r->save(); }

    public function test_all_regions_have_five_enemies_and_each_scenario_uses_its_region(): void
    {
        self::assertSame(29,Enemigo::count());
        foreach(AventuraCombate::REGIONES as $region=>$keys) self::assertSame(5,Enemigo::whereIn('clave',$keys)->count());
        foreach(range(11,49) as $nivel) {
            $this->abrir($nivel);
            $b=$this->battle(); $region=intdiv($nivel-1,10)+1;
            foreach($b['enemigos'] as $enemy) self::assertSame($region,$enemy['region']);
            if($nivel%10===0) { self::assertCount(1,$b['enemigos']); self::assertSame(AventuraCombate::JEFES[$nivel],$b['enemigos'][0]['clave']); }
            $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('combat-stage');
        }
        $this->run->refresh()->update(['escenario'=>50,'exploracion'=>[50=>['tipo'=>'dragon','estado'=>'pendiente']]]);
        // This fixture intentionally omits the next migration: fail safely until its bestiary exists.
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>$this->run->fresh()->version,'accion'=>'abrir'])->assertStatus(503);
    }

    public function test_vampire_does_not_heal_from_damage_absorbed_by_barrier_and_mage_is_countered_by_defense(): void
    {
        $this->abrir(11,'vampiro');
        $this->cambiar(function(&$b){$b['enemigos'][0]['vida']=20;});
        $this->run->refresh()->update(['efectos'=>[['tipo'=>'barrera','valor'=>100,'nombre'=>'Prueba']]]);
        $this->accion(['accion'=>'defender']); self::assertSame(20,$this->battle()['enemigos'][0]['vida']);
        $this->abrir(21,'bruja'); $this->cambiar(fn(&$b)=>$b['turno']=2);
        $max=(new AventuraService())->estado($this->user->id)['personaje']['mana'];
        $this->run->refresh()->update(['mana_actual'=>$max]);
        $this->accion(['accion'=>'defender']); self::assertSame($max,$this->run->fresh()->mana_actual);
    }

    public function test_summon_occurs_once_without_reward_and_does_not_attack_immediately(): void
    {
        $this->abrir(11,'nigromante'); $this->cambiar(fn(&$b)=>$b['turno']=3);
        $this->accion(['accion'=>'defender']); $b=$this->battle();
        self::assertCount(2,$b['enemigos']); self::assertSame(0,$b['enemigos'][1]['xp']); self::assertSame(0,$b['enemigos'][1]['oro']);
        self::assertSame(499,$this->run->fresh()->vida_actual);
        $this->cambiar(fn(&$b)=>$b['turno']=6); $this->accion(['accion'=>'defender']); self::assertCount(2,$this->battle()['enemigos']);
    }

    public function test_stolen_potion_is_restored_exactly_once_after_victory(): void
    {
        $this->abrir(21,'saqueador'); $this->accion(['accion'=>'defender']);
        self::assertSame(2,(int)$this->run->inventario()->where('posicion','pocion')->sum('cantidad'));
        $this->cambiar(fn(&$b)=>$b['enemigos'][0]['vida']=1);
        $v=$this->run->fresh()->version; $this->accion(['accion'=>'atacar','objetivo'=>0]);
        self::assertSame(3,(int)$this->run->inventario()->where('posicion','pocion')->sum('cantidad'));
        $this->postJson('/alumno/aventura/combate',['expedicion_id'=>$this->run->id,'version'=>$v,'accion'=>'atacar','objetivo'=>0])->assertStatus(409);
        self::assertSame(3,(int)$this->run->inventario()->where('posicion','pocion')->sum('cantidad'));
    }

    public function test_boss_questions_match_region_and_prepared_character_can_win_all_four(): void
    {
        $p=AventuraPersonaje::where('usuario_id',$this->user->id)->first(); $p->nivel=10;
        foreach(AventuraCatalogo::STATS as $stat) $p->$stat=10; $p->save();
        foreach([10,20,30,40] as $nivel) {
            $this->abrir($nivel);
            $max=(new AventuraService())->estado($this->user->id)['personaje'];
            $this->run->refresh()->update(['vida_actual'=>$max['vida'],'mana_actual'=>$max['mana']]);
            $preguntas=0;
            for($turno=0;$turno<60 && $this->battle()['estado']!=='victoria';$turno++) {
                if($this->battle()['estado']==='pregunta') {
                    $esperada=[10=>'html1',20=>'css1',30=>'js1',40=>'mundo1'][$nivel];
                    self::assertSame('Pregunta '.$esperada,$this->engine->preguntaPublica($this->battle())['pregunta']);
                    $this->accion(['accion'=>'responder','respuesta'=>'a']); $preguntas++;
                } else $this->accion(['accion'=>'atacar','objetivo'=>0]);
                self::assertGreaterThan(0,$this->run->fresh()->vida_actual,'Debe poder ganar con preparación y respuestas correctas.');
            }
            self::assertSame('victoria',$this->battle()['estado']); self::assertGreaterThan(0,$preguntas);
            $this->accion(['accion'=>'continuar']); self::assertSame($nivel+1,$this->run->fresh()->escenario);
        }
    }

    public function test_boss_failed_specials_and_second_phase_are_distinct(): void
    {
        foreach([20,30,40] as $nivel) {
            $this->abrir($nivel); $this->cambiar(function(&$b){$b['turno']=3;$b['enemigos'][0]['vida']=(int)floor($b['enemigos'][0]['max']/2);});
            $this->accion(['accion'=>'defender']); self::assertSame('pregunta',$this->battle()['estado']);
            $mana=$this->run->fresh()->mana_actual;
            $this->accion(['accion'=>'responder','respuesta'=>'b']);
            self::assertLessThan(500,$this->run->fresh()->vida_actual);
            if($nivel===20) self::assertSame($mana-8,$this->run->fresh()->mana_actual);
            if($nivel===30) self::assertSame('veneno',$this->run->fresh()->efectos[0]['tipo']);
        }
    }
}
