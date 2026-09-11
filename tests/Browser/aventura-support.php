<?php
// Browser fixtures run against an explicitly named disposable SQLite file, never the project's database.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$mode = $argv[1] ?? '';
$database = $argv[2] ?? '';
if (!preg_match('/^codeplay-aventura-test-[a-f0-9]+\.sqlite$/', basename($database)) || realpath(dirname($database)) !== realpath(sys_get_temp_dir())) throw new RuntimeException('Only a temporary fixture database is allowed.');
if ($mode === 'init') touch($database);
if (!is_file($database)) throw new RuntimeException('Fixture not initialized.');
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $database]);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\URL::forceRootUrl($argv[3] ?? 'http://127.0.0.1:18721');
if ($mode === 'init') {
    foreach (['2025_09_30_064434_create_usuarios_table.php', '2025_09_30_064537_create_items_table.php', '2025_09_30_064544_create_inventario_table.php', '2025_10_21_082507_create_desafio_recompensas_table.php', '2026_09_08_120000_create_aventura_preparacion_tables.php', '2026_09_08_140000_integrate_aventura_inventory.php'] as $migration) (require database_path('migrations/' . $migration))->up();
    App\Models\Usuario::create(['nombre' => 'Exploradora', 'clave' => 'fixture-only', 'puntos_totales' => 1400, 'puntos_usados' => 0]);
    (require database_path('migrations/2026_09_09_090000_add_aventura_exploracion.php'))->up();
    foreach (['2025_09_30_064441_create_documentacion_table.php','2025_09_30_064446_create_tests_table.php','2025_09_30_064451_create_preguntas_test_table.php','2025_10_18_173127_add_test_module_to_tests_table.php','2025_09_30_064527_create_enemigos_table.php','2025_09_30_064532_create_combates_table.php','2026_09_09_120000_add_aventura_bosque_combate.php'] as $migration) (require database_path('migrations/'.$migration))->up();
    Illuminate\Support\Facades\DB::table('documentacion')->insert(['id'=>1,'titulo'=>'Fixture','contenido'=>'Fixture']);
    Illuminate\Support\Facades\DB::table('tests')->insert(['id'=>1,'documentacion_id'=>1,'modulo'=>'html1','titulo'=>'Fixture']);
    Illuminate\Support\Facades\DB::table('preguntas_test')->insert(['test_id'=>1,'pregunta'=>'¿Qué etiqueta crea un párrafo?','opcion_a'=>'<p>','opcion_b'=>'<img>','opcion_c'=>'<a>','opcion_d'=>'<br>','respuesta_correcta'=>'a']);
    Illuminate\Support\Facades\DB::table('desafio_recompensas')->insert(['usuario_id' => 1, 'nivel_desafio' => 5, 'completado_primera_vez' => true]);
    exit;
}
$service = new App\Services\AventuraService();
if ($mode === 'home') {
    $trofeoAventura=App\Services\AventuraFinal::victoria(1);
    echo view('inicio-alumno',compact('trofeoAventura'))->render(); exit;
}
if ($mode === 'ending') {
    $partida=App\Models\AventuraExpedicion::where('usuario_id',1)->findOrFail((int)$argv[4]);
    $victoria=App\Services\AventuraFinal::victoria(1,$partida->id);
    if(!$victoria) throw new RuntimeException('No victory.');
    echo view('aventura.final',compact('partida','victoria'))->render(); exit;
}
if ($mode === 'complete') {
    parse_str(stream_get_contents(STDIN),$payload);
    $service->completar(1,(int)$payload['expedicion_id'],(int)$payload['version']);
    echo json_encode(['url'=>($argv[3] ?? '').'/alumno/aventura']); exit;
}
if ($mode === 'region-fixture') {
    if (!App\Models\Enemigo::where('clave','archivista')->exists()) (require database_path('migrations/2026_09_09_180000_add_aventura_regiones.php'))->up();
    if (!App\Models\Enemigo::where('clave','dragon')->exists()) (require database_path('migrations/2026_09_10_090000_add_aventura_dragon.php'))->up();
    $estado=$service->estado(1); $service->iniciar(1,$estado['expedicion']['id']);
    $run=App\Models\AventuraExpedicion::findOrFail($estado['expedicion']['id']);
    $personaje=App\Models\AventuraPersonaje::where('usuario_id',1)->firstOrFail();
    $personaje->nivel=15; foreach(App\Services\AventuraCatalogo::STATS as $stat) $personaje->$stat=15; $personaje->save();
    $stats=$service->estado(1)['personaje'];
    $run->escenario=(int)($argv[4] ?? 20); $run->vida_actual=$stats['vida']; $run->mana_actual=$stats['mana'];
    $run->exploracion=[$run->escenario=>['tipo'=>$run->escenario===50?'dragon':($run->escenario%10===0?'jefe':'enemigo'),'estado'=>'pendiente']]; $run->version++; $run->save();
    (new App\Services\AventuraCombate())->actuar(1,$run->id,$run->version,['accion'=>'abrir']);
    echo 'Fixture ready'; exit;
}
if ($mode === 'arena-group-fixture') {
    $state=$service->estado(1); $run=App\Models\AventuraExpedicion::findOrFail($state['expedicion']['id']);
    $run->escenario=5; $run->vida_actual=$state['personaje']['vida']; $run->efectos=[];
    $run->exploracion=[5=>['tipo'=>'enemigo','estado'=>'pendiente']];$run->version++;$run->save();
    $engine=new class extends App\Services\AventuraCombate {protected function azar(int $min,int $max): int{return $min;}};
    $engine->actuar(1,$run->id,$run->version,['accion'=>'abrir']);echo 'Ready';exit;
}
if ($mode === 'dragon-final-fixture') {
    $state=$service->estado(1); $run=App\Models\AventuraExpedicion::findOrFail($state['expedicion']['id']);
    if ($run->escenario!==50) throw new RuntimeException('Expected dragon fixture.');
    $h=$run->exploracion; $h[50]['combate']['enemigos'][0]['vida']=1;
    $h[50]['combate']['estado']='jugador'; $h[50]['combate']['pregunta']=null;
    $run->exploracion=$h; $run->save(); echo 'Fixture ready'; exit;
}
if ($mode === 'guild-fixture') {
    $estado=$service->estado(1); $service->iniciar(1,$estado['expedicion']['id']);
    $run=App\Models\AventuraExpedicion::findOrFail($estado['expedicion']['id']);
    App\Services\AventuraInventario::recibir($run,'pocion-vida');
    $run->escenario=2;
    $run->exploracion=[2=>['tipo'=>'gremio','estado'=>'pendiente','gremio'=>['ofertas'=>['runas','botiquin','explorador','patrulla'],'encargos'=>[]]]];
    $run->version++; $run->save(); echo 'Fixture ready'; exit;
}
if ($mode === 'guild') {
    $payload=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
    (new App\Services\AventuraGremio())->actuar(1,$payload['expedicion_id'],$payload['version'],$payload);
    echo json_encode(['url'=>($argv[3] ?? '').'/alumno/aventura/mapa#story-title']); exit;
}
if ($mode === 'event-fixture') {
    $estado=$service->estado(1); $service->iniciar(1,$estado['expedicion']['id']);
    $run=App\Models\AventuraExpedicion::findOrFail($estado['expedicion']['id']);
    $tipo=($argv[4] ?? '') === 'chest' ? 'tesoro' : 'narrativa';
    $evento=['tipo'=>$tipo,'estado'=>'pendiente'];
    if (($argv[4] ?? '') !== 'legacy') $evento['narrativa']=['id'=>$tipo === 'tesoro'?'cofre':'vagabundo','etapa'=>'decision'];
    $run->escenario=2; $run->exploracion=[2=>$evento]; $run->version++; $run->save(); echo 'Fixture ready'; exit;
}
if ($mode === 'event') {
    $payload=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
    $engine=new class extends App\Services\AventuraEventos {
        public array $tiradas=[];
        protected function azar(int $min,int $max): int { return array_shift($this->tiradas) ?? $min; }
    };
    if ($payload['accion']==='abrir') $engine->tiradas=[90,4,100,0];
    $result=$engine->actuar(1,$payload['expedicion_id'],$payload['version'],$payload);
    echo json_encode(['url'=>($argv[3] ?? '').($result==='derrota'?'/alumno/aventura/derrota/'.$payload['expedicion_id']:'/alumno/aventura/mapa#story-title')]); exit;
}
if ($mode === 'restart') {
    $payload=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
    $service->reiniciar(1,$payload['expedicion_id'],$payload['version']);
    echo json_encode(['url'=>($argv[3] ?? '').'/alumno/aventura']); exit;
}
if ($mode === 'combat-fixture') {
    $state = $service->estado(1);
    $run = App\Models\AventuraExpedicion::findOrFail($state['expedicion']['id']);
    $run->estado='preparacion'; $run->save();
    $equipo=App\Services\AventuraCatalogo::vacio(); $equipo['arma']='espada-viajero'; $equipo['armadura']='cuero';
    $equipo['hechizos']=['brasa','barrera']; $equipo['pociones']=[['id'=>'pocion-vida','cantidad'=>3]];
    $service->guardar(1,$run->id,$run->version,$equipo); $service->iniciar(1,$run->id);
    $run->refresh(); $boss=($argv[4] ?? '') === 'boss'; $run->escenario=$boss ? 10 : 1;
    $run->exploracion=[$run->escenario=>['tipo'=>$boss?'jefe':'enemigo','estado'=>'pendiente']]; $run->save();
    $engine=new class extends App\Services\AventuraCombate { protected function azar(int $min,int $max): int { return $min; } };
    $engine->actuar(1,$run->id,$run->version,['accion'=>'abrir']);
    if ($boss) { $run->refresh(); $h=$run->exploracion; $h[10]['combate']['turno']=3; $run->exploracion=$h; $run->save(); }
    echo 'Fixture ready'; exit;
}
if ($mode === 'combat') {
    $payload=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
    $engine=new App\Services\AventuraCombate();
    $outcome=$engine->actuar(1,$payload['expedicion_id'],$payload['version'],$payload);
    if(App\Services\AventuraFinal::victoria(1,$payload['expedicion_id'])) { echo json_encode(['secuencia'=>$engine->secuencia(),'url'=>($argv[3] ?? '').'/alumno/aventura/final/'.$payload['expedicion_id']]); exit; }
    echo json_encode(['secuencia'=>$engine->secuencia(),'url'=>($argv[3] ?? '').($outcome==='derrota'?'/alumno/aventura/derrota/'.$payload['expedicion_id']:'/alumno/aventura/mapa#story-title')]); exit;
}
if ($mode === 'defeat') {
    $partida=App\Models\AventuraExpedicion::where('usuario_id',1)->where('estado','derrota')->findOrFail((int)($argv[4] ?? 0));
    $personaje=App\Models\AventuraPersonaje::where('usuario_id',1)->firstOrFail();
    echo view('aventura.derrota',compact('partida','personaje'))->render(); exit;
}
if (in_array($mode, ['start', 'path'], true)) {
    $payload = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    $exploration = new App\Services\AventuraExploracion();
    if ($mode === 'start') $exploration->iniciar(1, $payload['expedicion_id'], $payload['version']);
    else $exploration->elegir(1, $payload['expedicion_id'], $payload['version'], $payload['camino']);
    echo json_encode(['url' => ($argv[3] ?? '') . '/alumno/aventura/mapa']);
    exit;
}
if ($mode === 'save') {
    try {
        $payload = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
        echo json_encode(['success' => true, 'estado' => $service->guardar(1, $payload['expedicion_id'], $payload['version'], $payload['equipo'])]);
    } catch (Throwable $error) {
        echo json_encode(['success' => false, 'message' => $error->getMessage(), 'status' => 422]);
    }
    exit;
}
$estado = $service->estado(1);
session(['usuario_nombre' => 'Exploradora']);
if ($mode === 'map') {
    $mundo = (new App\Services\AventuraExploracion())->vista($estado, isset($argv[4]) && $argv[4] !== '' ? (int) $argv[4] : null);
    echo view('aventura.mapa', compact('estado', 'mundo'))->render();
    exit;
}
echo view('aventura.Index', compact('estado'))->render();
