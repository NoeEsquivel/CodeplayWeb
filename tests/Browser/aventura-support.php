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
    foreach (['2025_09_30_064434_create_usuarios_table.php', '2025_10_21_082507_create_desafio_recompensas_table.php', '2026_09_08_120000_create_aventura_preparacion_tables.php'] as $migration) (require database_path('migrations/' . $migration))->up();
    App\Models\Usuario::create(['nombre' => 'Exploradora', 'clave' => 'fixture-only', 'puntos_totales' => 1400, 'puntos_usados' => 0]);
    Illuminate\Support\Facades\DB::table('desafio_recompensas')->insert(['usuario_id' => 1, 'nivel_desafio' => 5, 'completado_primera_vez' => true]);
    exit;
}
$service = new App\Services\AventuraService();
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
echo view('aventura.Index', compact('estado'))->render();
