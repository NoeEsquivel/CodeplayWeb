<?php

namespace Tests\Unit;

use App\Http\Controllers\DesafioController;
use App\Services\MiniArcadeValidator;
use PHPUnit\Framework\TestCase;

class MiniArcadeValidatorTest extends TestCase
{
    private function pages(): array
    {
        $open = '<!DOCTYPE html><html lang="es"><head><title>Arcade</title></head><body>';
        $close = '</body></html>';
        return [
            'menu' => $open . '<h1>Arcade espacial</h1><p id="descripcion">Elige un juego y consigue puntos resolviendo ambos retos.</p><button data-vista="culebrita">Jugar</button><button data-vista="memoria">Memoria</button>' . $close,
            'culebrita' => $open . '<canvas id="tableroSerpiente"></canvas><span id="puntos">0</span><button data-vista="menu">Volver</button><script>' . <<<'JS'
function siguienteCabeza(cabeza, direccion) { return { x: cabeza.x + direccion.x, y: cabeza.y + direccion.y }; }
function chocaConPared(cabeza, limite) { return cabeza.x < 0 || cabeza.y < 0 || cabeza.x >= limite || cabeza.y >= limite; }
function puntosPorComida(puntos) { return puntos + 10; }
JS
                . '</script>' . $close,
            'memoria' => $open . '<div id="tableroMemoria"></div><span id="puntos">0</span><button data-vista="menu">Volver</button><script>' . <<<'JS'
function sonPareja(primera, segunda) { return primera === segunda; }
function puntosPorPareja(puntos) { return puntos + 20; }
function partidaTerminada(parejas) { return parejas === 6; }
JS
                . '</script>' . $close,
        ];
    }

    private function json(array $pages): string
    {
        return json_encode($pages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function test_complete_guided_solution_passes_and_reward_is_recomputed_for_level_20(): void
    {
        $code = $this->json($this->pages());
        self::assertTrue((new MiniArcadeValidator())->passes($code));
        $controller = new DesafioController();
        $validate = new \ReflectionMethod($controller, 'codigoCumpleRequisitos');
        $validate->setAccessible(true);
        self::assertTrue($validate->invoke($controller, 20, $code));
        $points = new \ReflectionMethod($controller, 'obtenerPuntosPorNivel');
        $points->setAccessible(true);
        self::assertSame(1000, $points->invoke($controller, 20));
    }

    public function test_each_unfinished_or_incorrect_function_prevents_completion(): void
    {
        $replacements = [
            ['culebrita', 'return { x: cabeza.x + direccion.x, y: cabeza.y + direccion.y };', 'return null;', 'snakeMove'],
            ['culebrita', 'return cabeza.x < 0 || cabeza.y < 0 || cabeza.x >= limite || cabeza.y >= limite;', 'return false;', 'snakeWall'],
            ['culebrita', 'return puntos + 10;', 'return 10;', 'snakeScore'],
            ['memoria', 'return primera === segunda;', 'return true;', 'memoryMatch'],
            ['memoria', 'return puntos + 20;', 'return puntos + 10;', 'memoryScore'],
            ['memoria', 'return parejas === 6;', 'return parejas >= 5;', 'memoryWin'],
        ];
        foreach ($replacements as [$page, $from, $to, $check]) {
            $pages = $this->pages();
            $pages[$page] = str_replace($from, $to, $pages[$page]);
            $checks = (new MiniArcadeValidator())->checks($this->json($pages));
            self::assertFalse($checks[$check], $check);
            self::assertFalse((new MiniArcadeValidator())->passes($this->json($pages)));
        }
    }

    public function test_comments_strings_and_duplicate_functions_do_not_supply_a_solution(): void
    {
        foreach ([
            '/* function puntosPorComida(puntos) { return puntos + 10; } */',
            'const pista = "function puntosPorComida(puntos) { return puntos + 10; }";',
            'function puntosPorComida(puntos) { return puntos + 10; } function puntosPorComida(puntos) { return 10; }',
            "function puntosPorComida(puntos) { return\npuntos + 10; }",
            'function puntosPorComida(puntos) { return fetch("https://example.com"); }',
            'function puntosPorComida(puntos) { return process.exit(); }',
            'function puntosPorComida(puntos) { return puntos.__proto__; }',
        ] as $replacement) {
            $pages = $this->pages();
            $pages['culebrita'] = str_replace('function puntosPorComida(puntos) { return puntos + 10; }', $replacement, $pages['culebrita']);
            self::assertFalse((new MiniArcadeValidator())->checks($this->json($pages))['snakeScore'], $replacement);
        }
    }

    public function test_equivalent_parenthesized_expressions_are_accepted(): void
    {
        $pages = $this->pages();
        $pages['culebrita'] = str_replace('return puntos + 10;', 'return (10 + puntos);', $pages['culebrita']);
        $pages['memoria'] = str_replace('return primera === segunda;', 'return !(primera !== segunda);', $pages['memoria']);
        self::assertTrue((new MiniArcadeValidator())->passes($this->json($pages)));
    }

    public function test_broken_navigation_and_unchanged_instructions_are_rejected(): void
    {
        $pages = $this->pages();
        $pages['menu'] = str_replace(['Arcade espacial', 'Elige un juego y consigue puntos resolviendo ambos retos.', 'data-vista="memoria"'],
            ['Mi Mini Arcade', 'Escribe aquí cómo jugar', 'data-vista="otra"'], $pages['menu']);
        $checks = (new MiniArcadeValidator())->checks($this->json($pages));
        self::assertFalse($checks['menuTitle']);
        self::assertFalse($checks['menuDescription']);
        self::assertFalse($checks['structure']);
    }

    public function test_invalid_json_sql_and_external_entities_are_rejected(): void
    {
        $validator = new MiniArcadeValidator();
        foreach (['', '{}', '[]', 'null', "' OR 1=1; DROP TABLE usuarios; --", str_repeat('x', 60001)] as $code) {
            self::assertFalse($validator->passes($code));
        }
        $pages = $this->pages();
        $pages['menu'] = '<!DOCTYPE html SYSTEM "file:///etc/passwd">' . $pages['menu'];
        self::assertFalse($validator->passes($this->json($pages)));
    }

    public function test_real_starters_cannot_be_submitted_and_complete_fixture_can(): void
    {
        $root = dirname(__DIR__, 2);
        $pages = [];
        foreach (['menu', 'culebrita', 'memoria'] as $page) {
            $pages[$page] = file_get_contents($root . '/public/desafios/mini-arcade/' . $page . '.html');
        }
        $validator = new MiniArcadeValidator();
        $checks = $validator->checks($this->json($pages));
        self::assertFalse($validator->passes($this->json($pages)));
        self::assertFalse($checks['snakeMove']);
        self::assertFalse($checks['memoryMatch']);
        self::assertTrue($validator->passes(file_get_contents($root . '/tests/Fixtures/mini-arcade-solution.json')));
    }
}
