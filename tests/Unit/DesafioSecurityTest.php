<?php

namespace Tests\Unit;

use App\Http\Controllers\DesafioController;
use PHPUnit\Framework\TestCase;

class DesafioSecurityTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    public function test_code_is_validated_as_text_and_sql_payload_is_not_accepted_as_a_solution(): void
    {
        $controller = new DesafioController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('codigoCumpleRequisitos');
        $method->setAccessible(true);

        $sqlPayload = "' OR 1=1; DROP TABLE usuarios; --";

        self::assertFalse($method->invoke($controller, 1, $sqlPayload));
        self::assertTrue($method->invoke(
            $controller,
            1,
            "<h1>Hola</h1><p>Texto</p><strong>Importante</strong><em>Énfasis</em> {$sqlPayload}"
        ));
    }

    public function test_server_reproduces_the_requirements_for_all_sixteen_levels(): void
    {
        $controller = new DesafioController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('codigoCumpleRequisitos');
        $method->setAccessible(true);

        $soluciones = [
            1 => '<h1>Título</h1><p>Texto</p><strong>Fuerte</strong><em>Énfasis</em>',
            2 => '<h1>Título</h1><h2>Sección</h2><p>Texto</p><ul><li>Uno</li></ul>',
            3 => '<h1>Enlaces</h1><a href="#inicio">Ir</a><img src="foto.jpg" alt="Foto">',
            4 => '<style>h1{color:red;background-color:white}</style><h1>Título</h1><p>Texto</p>',
            5 => '<style>.c{width:100px;padding:10px;border:1px solid;margin:5px}</style><div class="c">Caja</div>',
            6 => '<style>.fila{display:flex;gap:10px;justify-content:center;padding:10px;border:1px solid;background-color:white}</style>',
            7 => '<style>.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:10px;background-color:white;border-radius:8px}</style>',
            8 => '<script>const boton=document.getElementById("b");if (boton) { boton.textContent="ok"; boton.addEventListener("click",()=>{}); }</script>',
            9 => '<script>const boton=document.getElementById("b");boton.addEventListener("click",()=>{boton.classList.add("activo");boton.textContent="ok";});</script>',
            10 => '<form><input id="edad"><button type="submit">Validar</button></form><script>form.addEventListener("submit",(event)=>{event.preventDefault();const edad=input.value;if(edad){mensaje.textContent="ok";}});</script>',
            11 => '<link rel="stylesheet" href="bootstrap stylesheet"><div class="container"><div class="row"><div class="col-4 card"></div><div class="col-4 card"></div><div class="col-4 card"></div></div></div>',
            12 => '<link rel="stylesheet" href="bootstrap stylesheet"><div class="card"><button class="btn">Abrir</button><span class="badge">Nuevo</span><div class="alert">Aviso</div></div>',
            13 => <<<'PHP'
<?php
$nombre = "Ana";
echo $nombre;
?>
PHP,
            14 => <<<'PHP'
<form method="post"><input name="nombre"></form>
<?php if (isset($_POST["nombre"])) echo "Hola"; ?>
PHP,
            15 => <<<'PHP'
<?php
session_start();
$_SESSION["usuario"] = "Ana";
setcookie("tema", "oscuro");
echo "ok";
?>
PHP,
            16 => '<nav><a href="#inicio">Inicio</a></nav><header id="inicio">Hola</header><img src="foto.jpg" alt="Foto"><section>Sobre mí</section><ul><li>HTML</li><li>CSS</li><li>JS</li></ul><article class="project card"></article><article class="project card"></article><form><input><textarea></textarea><button>Enviar</button></form><style>body{background-color:#000;color:#fff;padding:20px;border-radius:8px;display:flex;gap:10px}@media(max-width:600px){body{flex-wrap:wrap}}</style><script>document.body.addEventListener("click",()=>{});</script>',
        ];

        foreach ($soluciones as $nivel => $codigo) {
            self::assertTrue($method->invoke($controller, $nivel, $codigo), "La validación del nivel {$nivel} rechazó una solución válida");
        }
    }

    public function test_all_html_preview_challenges_use_a_sandbox(): void
    {
        for ($nivel = 1; $nivel <= 12; $nivel++) {
            $ruta = $this->projectPath("resources/views/desafios/fundamentos_web/desafiolvl{$nivel}.blade.php");
            $contenido = file_get_contents($ruta);

            self::assertStringContainsString("sandbox', 'allow-scripts allow-modals", $contenido, "Nivel {$nivel} sin sandbox");
            self::assertStringNotContainsString('iframeDoc.write', $contenido, "Nivel {$nivel} todavía usa document.write");
        }
    }

    public function test_reward_controller_requires_code_and_a_valid_level(): void
    {
        $contenido = file_get_contents($this->projectPath('app/Http/Controllers/DesafioController.php'));

        self::assertStringContainsString("'codigo' => ['required', 'string', 'max:60000']", $contenido);
        self::assertStringContainsString("'nivel' => ['required', 'integer', 'between:1,16']", $contenido);
        self::assertStringContainsString('$this->codigoCumpleRequisitos($nivel, $codigo)', $contenido);
    }
}
