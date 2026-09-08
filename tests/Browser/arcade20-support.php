<?php

// Local browser-test support. It renders a fixture and runs the pure validator;
// it never authenticates a real user, executes student JavaScript or awards points.
require dirname(__DIR__, 2) . '/vendor/autoload.php';

if (($argv[1] ?? '') === 'checks') {
    $validator = new App\Services\MiniArcadeValidator();
    echo json_encode(['success' => true, 'checks' => $validator->checks(stream_get_contents(STDIN))]);
    exit;
}

$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\URL::forceRootUrl($argv[2] ?? 'http://127.0.0.1:18720');
$plantillas = [];
foreach (['menu', 'culebrita', 'memoria'] as $view) {
    $plantillas[$view] = file_get_contents(public_path("desafios/mini-arcade/{$view}.html"));
}
$infoRecompensa = ['puntos_base' => 1000, 'puede_reclamar' => true, 'tiempo_restante' => null];
echo view('desafios.fundamentos_web.desafiolvl20', compact('plantillas', 'infoRecompensa'))->render();
