<?php

namespace Tests\Unit;

use App\Http\Middleware\InjectStudentLux;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class InjectStudentLuxTest extends TestCase
{
    public function test_lux_is_inserted_in_the_page_without_modifying_embedded_documents(): void
    {
        $examples = [
            '<textarea id="editor"><html><head><title>Ejemplo</title></head><body>Hola</body></html></textarea>',
            '<script>const example = "<html><head></head><body>Hola</body></html>";</script>',
            '<!-- Ejemplo: </head></body> --><p>Desafío</p>',
            '<div data-example="<head></head><body></body>">Desafío</div>',
            '<textarea data-title="a > b"><head></head><body>Ejemplo</body></textarea>',
            '<textarea><head></head><body>Primer editor</body></textarea><textarea><head></head><body>Segundo editor</body></textarea>',
        ];

        foreach ($examples as $example) {
            $original = '<!DOCTYPE html><html><head><title>CodePlay</title>'
                .'<script>const closingTags = "</head></body>";</script>'
                .'<!-- </head> --></head><body>'.$example.'</body></html>';
            $html = $this->inject($original);

            $this->assertStringContainsString($example, $html);
            $this->assertStringContainsString('const closingTags = "</head></body>";', $html);
            $this->assertPlacement($html);
            $this->assertSame($original, str_replace([
                '<link rel="stylesheet" href="'.e(asset('css/student-lux.css')).'">',
                view('partials.student-lux', ['routeName' => 'alumno.desafios.nivel1', 'welcomePending' => false])->render(),
            ], '', $html));
        }
    }

    public function test_uppercase_closing_tags_with_whitespace_are_supported(): void
    {
        $html = $this->inject('<!DOCTYPE html><HTML><HEAD><TITLE>CodePlay</TITLE></HEAD ><BODY>Desafío</BODY ></HTML>');
        $this->assertPlacement($html);
    }

    public function test_a_page_without_an_explicit_head_still_loads_the_widget_styles(): void
    {
        $html = $this->inject('<html><body><textarea><head></head><body>Ejemplo</body></textarea></body></html>');
        $this->assertStringContainsString('<textarea><head></head><body>Ejemplo</body></textarea>', $html);
        $this->assertStringContainsString('css/student-lux.css', $html);
        $this->assertStringContainsString('id="codeplay-lux"', $html);
    }

    public function test_repeated_injection_does_not_duplicate_lux(): void
    {
        $html = $this->inject('<html><head></head><body>Desafío</body></html>');
        $this->assertSame($html, $this->inject($html));
    }

    private function inject(string $html): string
    {
        $request = Request::create('/alumno/desafios/nivel1');
        $request->attributes->set('usuario_actual', new Usuario(['rol' => Usuario::ROL_ALUMNO]));
        $route = (new Route('GET', '/alumno/desafios/nivel1', fn () => ''))->name('alumno.desafios.nivel1');
        $request->setRouteResolver(fn () => $route);

        return (new InjectStudentLux)->handle($request,
            fn () => new Response($html, 200, ['Content-Type' => 'text/html']))->getContent();
    }

    private function assertPlacement(string $html): void
    {
        $document = new \DOMDocument;
        // El contenido original se comprueba por separado; libxml usa reglas de HTML4 para script.
        $markup = preg_replace('~(<script\b[^>]*>).*?(</script\s*>)~is', '$1$2', $html);
        $document->loadHTML($markup, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('/html/head/link[contains(@href, "/css/student-lux.css")]')->length);
        $this->assertSame(1, $xpath->query('/html/body/aside[@id="codeplay-lux"]')->length);
        $this->assertSame(1, $xpath->query('//script[contains(@src, "/js/student-lux.js")]')->length);
    }
}
