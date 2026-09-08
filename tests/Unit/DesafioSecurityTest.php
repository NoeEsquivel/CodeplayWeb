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

    public function test_flexbox_and_grid_accept_formatted_css_without_skipping_requirements(): void
    {
        $controller = new DesafioController();
        $method = (new \ReflectionClass($controller))->getMethod('codigoCumpleRequisitos');
        $method->setAccessible(true);
        $flex = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Diseño con Flexbox</title>
    <style>
        .fila-tarjetas {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .tarjeta {
            padding: 20px 30px;
            border: 2px solid #4CAF50;
            background-color: #f0f8f0;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <h1>Mis poderes CSS</h1>
    <div class="fila-tarjetas">
        <div class="tarjeta">HTML</div>
        <div class="tarjeta">CSS</div>
        <div class="tarjeta">JavaScript</div>
    </div>
</body>
</html>
HTML;
        $grid = '<style>.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 20px; background-color: white; border-radius: 10px; }</style>';
        foreach ([6 => [$flex, 'flex'], 7 => [$grid, 'grid']] as $level => [$code, $layout]) {
            foreach (["display:{$layout}", "display: {$layout}", "display \t:\n {$layout}"] as $declaration) {
                self::assertTrue($method->invoke($controller, $level, str_replace("display: {$layout}", $declaration, $code)));
            }
            self::assertFalse($method->invoke($controller, $level, str_replace("display: {$layout}", 'display: block', $code)));
            self::assertFalse($method->invoke($controller, $level, str_replace('gap: 20px;', '', $code)));
            self::assertFalse($method->invoke($controller, $level, '<style>body { display: ' . $layout . '; }</style>'));
        }
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

    public function test_server_reproduces_the_requirements_for_all_nineteen_levels(): void
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
            17 => '<form id="registro"><label for="correo">Correo</label><input name="correo" id="correo" type="email" required><select><option>Uno</option></select><textarea></textarea><button>Enviar</button></form><style>.form{background:linear-gradient(#fff,#eee);border-radius:8px;display:flex}</style><script>registro.addEventListener("submit",event=>{event.preventDefault();const valor=correo.value;mensaje.textContent=valor;});</script>',
            18 => '<aside><nav>Menu</nav></aside><main><header>Panel</header><div class="card">Uno</div><div class="stat">Dos</div><div class="metric">Tres</div><button id="modo">Cambiar</button></main><style>main{display:grid;grid-template-columns:1fr;background:linear-gradient(#fff,#eee);border-radius:12px}@media(max-width:600px){main{display:flex;flex-wrap:wrap}}</style><script>document.getElementById("modo").addEventListener("click",()=>{document.querySelector("header").textContent="Actualizado";});</script>',
            19 => '<section id="productos" class="catalogo"><div class="producto card">Uno</div><div class="producto card">Dos</div><button id="carrito">Carrito</button></section><section id="checkout"><form><button>Confirmar</button></form><p id="mensaje"></p></section><style>.catalogo{display:grid;background-color:#eef;border-radius:12px}</style><script>sessionStorage.setItem("carrito",JSON.stringify([]));const items=sessionStorage.getItem("carrito");document.getElementById("carrito").addEventListener("click",()=>{location.hash="checkout";});document.getElementById("mensaje").textContent=items;</script>',
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

        $nivel17 = file_get_contents($this->projectPath('resources/views/desafios/fundamentos_web/desafiolvl17.blade.php'));
        self::assertStringContainsString('sandbox="allow-scripts allow-modals"', $nivel17);
        self::assertStringContainsString('desafio17_formulario_interactivo_referencia.svg', $nivel17);
        self::assertStringNotContainsString('iframeDoc.write', $nivel17);

        $nivel18 = file_get_contents($this->projectPath('resources/views/desafios/fundamentos_web/desafiolvl18.blade.php'));
        self::assertStringContainsString('sandbox="allow-scripts allow-modals"', $nivel18);
        self::assertStringNotContainsString('iframeDoc.write', $nivel18);

        $nivel19 = file_get_contents($this->projectPath('resources/views/desafios/fundamentos_web/desafiolvl19.blade.php'));
        self::assertStringContainsString('sandbox="allow-scripts allow-forms allow-modals"', $nivel19);
        self::assertStringNotContainsString('iframeDoc.write', $nivel19);
    }

    public function test_reward_controller_requires_code_and_a_valid_level(): void
    {
        $contenido = file_get_contents($this->projectPath('app/Http/Controllers/DesafioController.php'));

        self::assertStringContainsString("'codigo' => ['required', 'string', 'max:60000']", $contenido);
        self::assertStringContainsString("'nivel' => ['required', 'integer', 'between:1,20']", $contenido);
        self::assertStringContainsString('$this->codigoCumpleRequisitos($nivel, $codigo)', $contenido);
    }
}
