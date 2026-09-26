<?php

namespace Tests\Feature;

use App\Models\{Usuario,AventuraExpedicion,AventuraPersonaje,PreguntaTest};
use App\Services\{AventuraService,AventuraEventos,AventuraInventario,AventuraMejoras,AventuraPreguntas,AventuraRelatos,AventuraCombate,AventuraCatalogo};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AventuraEventosTest extends TestCase
{
    private Usuario $user;
    private AventuraExpedicion $run;
    private AventuraService $camp;
    private AventuraEventos $engine;

    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite',\PDO::getAvailableDrivers())) $this->markTestSkipped('Se requiere pdo_sqlite.');
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        foreach (['2025_09_30_064434_create_usuarios_table.php','2025_09_30_064441_create_documentacion_table.php','2025_09_30_064446_create_tests_table.php','2025_09_30_064451_create_preguntas_test_table.php','2025_10_18_173127_add_test_module_to_tests_table.php','2025_09_30_064527_create_enemigos_table.php','2025_09_30_064532_create_combates_table.php','2025_09_30_064537_create_items_table.php','2025_09_30_064544_create_inventario_table.php','2025_10_21_082507_create_desafio_recompensas_table.php','2026_09_08_120000_create_aventura_preparacion_tables.php','2026_09_08_140000_integrate_aventura_inventory.php','2026_09_09_090000_add_aventura_exploracion.php','2026_09_09_120000_add_aventura_bosque_combate.php'] as $file) (require database_path('migrations/'.$file))->up();
        DB::table('documentacion')->insert(['id'=>1,'titulo'=>'HTML','contenido'=>'Prueba']);
        DB::table('tests')->insert(['id'=>1,'documentacion_id'=>1,'titulo'=>'HTML','modulo'=>'html1']);
        DB::table('preguntas_test')->insert(['test_id'=>1,'pregunta'=>'¿Qué crea un párrafo?','opcion_a'=>'<p>','opcion_b'=>'<img>','opcion_c'=>'<a>','opcion_d'=>'<br>','respuesta_correcta'=>'a']);
        $this->user = Usuario::create(['nombre'=>'Explorador','clave'=>'fixture','rol'=>'Alumno','puntos_totales'=>1200,'puntos_usados'=>100]);
        $this->camp = new AventuraService(); $s=$this->camp->estado($this->user->id);
        $this->camp->iniciar($this->user->id,$s['expedicion']['id']);
        $this->run = AventuraExpedicion::findOrFail($s['expedicion']['id']);
        $this->engine = new class extends AventuraEventos {
            public array $tiradas = [];
            protected function azar(int $min,int $max): int { $valor=array_shift($this->tiradas) ?? $min; assert($valor >= $min && $valor <= $max); return $valor; }
        };
        $this->app->instance(AventuraEventos::class,$this->engine);
        $this->withSession(['usuario_id'=>$this->user->id]);
        $this->encuentro();
    }
    protected function tearDown(): void { DB::disconnect('sqlite'); parent::tearDown(); }
    private function encuentro(string $tipo='narrativa',?array $n=null): void
    {
        $this->run->refresh();
        $this->run->exploracion=[$this->run->escenario=>['tipo'=>$tipo,'estado'=>'pendiente','narrativa'=>$n ?? ['id'=>$tipo==='tesoro'?'cofre':'vagabundo','etapa'=>'decision']]];
        $this->run->save();
    }
    private function eventPost(string $accion,array $extras=[],?int $version=null)
    {
        return $this->withSession(['usuario_id'=>$this->user->id])->postJson('/alumno/aventura/evento',[
            'expedicion_id'=>$this->run->id,'version'=>$version ?? $this->run->fresh()->version,'accion'=>$accion]+$extras);
    }
    private function narrativa(): array { $r=$this->run->fresh(); return $r->exploracion[$r->escenario]['narrativa']; }

    public function test_question_persists_is_private_and_rewards_once(): void
    {
        $this->eventPost('continuar')->assertStatus(409);
        $this->eventPost('preguntar')->assertOk(); $this->eventPost('preguntar')->assertStatus(409);
        $this->eventPost('ayudar')->assertOk();
        $n=$this->narrativa(); self::assertSame(1,$n['pregunta']);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('¿Qué crea un párrafo?')->assertDontSee('respuesta_correcta')->assertDontSee('La respuesta correcta era');
        self::assertSame($n,$this->narrativa());
        $this->eventPost('ignorar')->assertStatus(409);
        $v=$this->run->fresh()->version;
        $this->eventPost('responder',['respuesta'=>'a','oro'=>99999,'usuario_id'=>999,'pregunta'=>999])->assertOk();
        self::assertSame(13,$this->run->fresh()->oro);
        $this->eventPost('responder',['respuesta'=>'a'],$v)->assertStatus(409);
        $this->eventPost('responder',['respuesta'=>'a'])->assertStatus(409);
        self::assertSame(13,$this->run->fresh()->oro);
        self::assertSame(1200,$this->user->fresh()->puntos_totales);
        self::assertSame(100,$this->user->fresh()->puntos_usados);
        $this->eventPost('continuar')->assertOk(); self::assertSame(2,$this->run->fresh()->escenario);
        $this->eventPost('continuar')->assertStatus(409);
    }

    public function test_legacy_encounter_and_all_stories_can_be_resolved_without_reward(): void
    {
        self::assertGreaterThanOrEqual(10,count(AventuraRelatos::todos()));
        foreach (array_keys(AventuraRelatos::todos()) as $id) {
            $this->encuentro('narrativa',['id'=>$id,'etapa'=>'decision']);
            $this->get('/alumno/aventura/mapa')->assertOk()->assertSee(AventuraRelatos::todos()[$id][0]);
            $this->eventPost('ignorar')->assertOk();
        }
        $this->run->refresh(); $this->run->exploracion=[1=>['tipo'=>'narrativa','estado'=>'pendiente']]; $this->run->save();
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('Acercarme');
        $this->eventPost('descubrir')->assertOk(); $this->eventPost('descubrir')->assertStatus(409);
        self::assertSame(0,$this->run->fresh()->oro);
    }

    public function test_incorrect_answer_shows_feedback_and_can_cause_defeat_with_exact_refund(): void
    {
        $this->engine->tiradas=[2];
        $this->run->vida_actual=4; $this->run->save();
        $this->eventPost('ayudar')->assertOk();
        $this->eventPost('responder',['respuesta'=>'b'])->assertOk()->assertJsonPath('url',route('alumno.aventura.derrota',['expedicion'=>$this->run->id]));
        self::assertSame('derrota',$this->run->fresh()->estado);
        self::assertStringContainsString('<p>',implode(' ',$this->narrativa()['detalle']));
        self::assertSame(100,$this->user->fresh()->puntos_usados);
        $this->eventPost('responder',['respuesta'=>'a'])->assertStatus(409);
    }

    public function test_wrong_answers_can_leave_gold_intact_take_gold_or_start_combat(): void
    {
        foreach ([1,3,4] as $consecuencia) {
            $this->encuentro(); $this->run->refresh(); $this->run->oro=6; $this->run->save();
            $this->engine->tiradas=[$consecuencia];
            $this->eventPost('ayudar')->assertOk(); $this->eventPost('responder',['respuesta'=>'d'])->assertOk();
            self::assertSame($consecuencia===3?0:6,$this->run->fresh()->oro);
            if ($consecuencia===4) {
                self::assertSame('enemigo',$this->run->fresh()->exploracion[1]['tipo']);
                $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('El ruido atrajo');
                $this->eventPost('continuar')->assertStatus(409);
            }
        }
    }

    public function test_chest_mimic_and_rune_chest_use_existing_combat_and_bank(): void
    {
        $this->encuentro('tesoro'); $this->engine->tiradas=[1];
        $this->eventPost('abrir')->assertOk();
        $run=$this->run->fresh(); self::assertSame('mimic',$run->exploracion[1]['variante']);
        $engine=new AventuraCombate(); $engine->actuar($this->user->id,$run->id,$run->version,['accion'=>'abrir']);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('Slime Mimic');
        self::assertSame('mimic',$this->run->fresh()->exploracion[1]['combate']['enemigos'][0]['clave']);
        $this->encuentro('tesoro'); $this->engine->tiradas=[20];
        $this->eventPost('abrir')->assertOk(); self::assertSame('pregunta',$this->narrativa()['etapa']);
        $this->engine->tiradas=[2]; $this->eventPost('responder',['respuesta'=>'a'])->assertOk();
        self::assertSame(17,AventuraPersonaje::where('usuario_id',$this->user->id)->value('experiencia'));
    }

    public function test_loot_is_in_existing_inventory_and_equips_without_charging(): void
    {
        $this->encuentro('tesoro');
        self::assertTrue(AventuraInventario::recibir($this->run,'filo-fenix'));
        $this->run->refresh();
        $this->run->exploracion=[1=>['tipo'=>'tesoro','estado'=>'resuelto','narrativa'=>[
            'id'=>'cofre','etapa'=>'resultado','resultado'=>'Botín','detalle'=>[],'botin'=>'filo-fenix',
        ]]];
        $this->run->save();
        self::assertSame('filo-fenix',$this->narrativa()['botin']);
        $this->eventPost('equipar')->assertOk();
        self::assertSame('filo-fenix',$this->run->fresh()->equipo['arma']);
        self::assertSame(0,$this->run->fresh()->inversion);
        self::assertSame(100,$this->user->fresh()->puntos_usados);
        $this->eventPost('equipar')->assertStatus(409);
        $this->get('/alumno/aventura/mapa')->assertOk()->assertSee('Ya está equipado');
        $this->camp->reiniciar($this->user->id,$this->run->id,$this->run->fresh()->version);
        self::assertSame(0,$this->run->inventario()->count());
        self::assertSame(100,$this->user->fresh()->puntos_usados);
    }

    public function test_potion_cap_duplicates_and_foreign_inventory_are_not_overwritten(): void
    {
        self::assertTrue(AventuraInventario::recibir($this->run,'pocion-vida'));
        $this->run->inventario()->update(['cantidad'=>20]);
        self::assertFalse(AventuraInventario::recibir($this->run,'pocion-mana'));
        self::assertTrue(AventuraInventario::recibir($this->run,'cuero'));
        self::assertFalse(AventuraInventario::recibir($this->run,'cuero'));
        $row=$this->run->inventario()->whereHas('item',fn($q)=>$q->where('clave_catalogo','cuero'))->first();
        $row->expedicion_id=null; $row->save();
        self::assertFalse(AventuraInventario::recibir($this->run,'cuero'));
        self::assertNull($row->fresh()->expedicion_id);
    }

    public function test_new_spell_requires_replacement_and_keeps_previous_spell(): void
    {
        foreach (['brasa','alivio'] as $clave) { AventuraInventario::recibir($this->run,$clave); AventuraInventario::equiparBotin($this->run,$clave,null); }
        $this->encuentro('tesoro');
        AventuraInventario::recibir($this->run,'escarcha');
        $this->run->refresh(); $this->run->exploracion=[1=>['tipo'=>'tesoro','estado'=>'resuelto','narrativa'=>['id'=>'cofre','etapa'=>'resultado','resultado'=>'Botín','detalle'=>[],'botin'=>'escarcha']]]; $this->run->save();
        $this->eventPost('equipar')->assertStatus(422);
        $this->eventPost('equipar',['reemplazar'=>'brasa'])->assertOk();
        self::assertSame(['alivio','escarcha'],$this->run->fresh()->equipo['hechizos']);
        self::assertSame(3,$this->run->inventario()->count());
    }

    public function test_improvements_are_capped_and_health_xp_and_temporary_effect_work(): void
    {
        $this->engine->tiradas=[90,5,1]; $this->encuentro('tesoro');
        $this->eventPost('abrir')->assertOk();
        self::assertSame(110,$this->camp->estado($this->user->id)['personaje']['vida']);
        self::assertSame(110,$this->run->fresh()->vida_actual);
        $this->run->refresh();
        foreach (array_keys(AventuraMejoras::TIPOS) as $tipo) for($i=0;$i<10;$i++) AventuraMejoras::agregar($this->run,$tipo);
        $this->run->save();
        self::assertSame(130,$this->camp->estado($this->user->id)['personaje']['vida']);
        self::assertSame(130,AventuraMejoras::experiencia($this->run,100));
        self::assertSame(15,AventuraMejoras::valor($this->run,'robo_vida'));
        $oroAntes=$this->run->fresh()->oro;
        $this->encuentro('tesoro'); $this->engine->tiradas=[90,6];
        $this->eventPost('abrir')->assertOk();
        self::assertSame($oroAntes+15,$this->run->fresh()->oro); // La mejora crítica ya estaba al máximo.
        $this->encuentro('enemigo'); $this->run->refresh(); $this->run->vida_actual=50; $this->run->save();
        $combate=new class extends AventuraCombate { protected function azar(int $min,int $max): int { return $min; } };
        $combate->actuar($this->user->id,$this->run->id,$this->run->fresh()->version,['accion'=>'abrir']);
        for($i=0;$i<2;$i++) $combate->actuar($this->user->id,$this->run->id,$this->run->fresh()->version,['accion'=>'atacar','objetivo'=>0]);
        self::assertSame('victoria',$this->run->fresh()->exploracion[1]['combate']['estado']);
        self::assertGreaterThan(50,$this->run->fresh()->vida_actual);
        self::assertSame([],$this->run->fresh()->efectos);
        self::assertSame(19,$this->run->fresh()->exploracion[1]['combate']['recompensa']['xp']);
    }

    public function test_bank_progression_missing_questions_and_user_boundaries(): void
    {
        DB::table('tests')->insert(['id'=>2,'documentacion_id'=>1,'titulo'=>'CSS','modulo'=>'css1']);
        $q=PreguntaTest::first()->replicate(); $q->test_id=2; $q->save();
        self::assertSame($q->id,(new AventuraPreguntas())->elegir(11)->id);
        $this->eventPost('ayudar')->assertOk(); PreguntaTest::whereKey(1)->delete();
        $this->eventPost('responder',['respuesta'=>'a'])->assertOk(); self::assertSame(0,$this->run->fresh()->oro);
        $this->encuentro(); $this->eventPost('ayudar')->assertOk(); self::assertSame('resultado',$this->narrativa()['etapa']);
        $other=Usuario::create(['nombre'=>'Otro','clave'=>'fixture','rol'=>'Alumno']);
        $this->withSession(['usuario_id'=>$other->id])->postJson('/alumno/aventura/evento',['expedicion_id'=>$this->run->id,'version'=>$this->run->fresh()->version,'accion'=>'ignorar'])->assertNotFound();
        $this->eventPost('responder',['respuesta'=>"' OR 1=1 --"])->assertStatus(422);
        $this->encuentro('gremio'); $this->eventPost('ignorar')->assertStatus(409);
    }
}
