<?php

namespace App\Http\Controllers;

use App\Models\TestBanco;
use App\Models\TestBancoPregunta;
use App\Models\Usuario;
use App\Services\BankPracticeService;
use App\Services\TestBankService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentBankController extends Controller
{
    public function index(): View
    {
        $usuario = $this->usuario();
        $bancos = TestBanco::where('creador_usuario_id', $usuario->id)
            ->where('colegio_id', $usuario->colegio_id)
            ->where('origen', 'alumno')
            ->withCount('preguntas')
            ->latest('updated_at')
            ->get();

        return view('alumno.bancos.index', compact('bancos'));
    }

    public function create(): View
    {
        return view('alumno.bancos.create');
    }

    public function store(Request $request, TestBankService $service): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'materia' => ['required', 'string', 'max:100'],
        ]);
        $banco = $service->crear($this->usuario(), $datos);

        return redirect()->route('alumno.bancos.edit', $banco)->with('success', 'Banco guardado como borrador.');
    }

    public function edit(TestBanco $banco, TestBankService $service): View
    {
        $this->autorizarBanco($banco);
        $service->validarPropietario($this->usuario(), $banco);
        $banco->load(['preguntas' => fn ($q) => $q->orderBy('orden')]);

        return view('alumno.bancos.edit', compact('banco'));
    }

    public function update(Request $request, TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'materia' => ['required', 'string', 'max:100'],
        ]);
        $service->actualizar($this->usuario(), $banco, $datos);

        return back()->with('success', 'Cambios guardados. El banco quedó como borrador hasta volver a publicarlo.');
    }

    public function duplicate(TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        $copia = $service->duplicar($this->usuario(), $banco);

        return redirect()->route('alumno.bancos.edit', $copia)->with('success', 'Copia creada como borrador.');
    }

    public function destroy(TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        $service->eliminar($this->usuario(), $banco);

        return redirect()->route('alumno.bancos.index')->with('success', 'Banco eliminado.');
    }

    public function publish(TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        try {
            $service->publicar($this->usuario(), $banco);

            return back()->with('success', 'Banco publicado. Ya puedes generar su código.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function share(TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        try {
            $codigo = $service->habilitarCodigo($this->usuario(), $banco);

            return back()->with('success', "Código para compartir: {$codigo}");
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function stopSharing(TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        $service->deshabilitarCodigo($this->usuario(), $banco);

        return back()->with('success', 'El código quedó desactivado. Ya no admite nuevas incorporaciones.');
    }

    public function storeQuestion(Request $request, TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        try {
            $service->agregarPregunta($this->usuario(), $banco, $this->questionData($request));

            return back()->with('success', 'Pregunta añadida. El banco quedó como borrador.');
        } catch (\InvalidArgumentException|\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateQuestion(Request $request, TestBanco $banco, TestBancoPregunta $pregunta, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        try {
            $service->actualizarPregunta($this->usuario(), $banco, $pregunta, $this->questionData($request));

            return back()->with('success', 'Pregunta actualizada. Publica nuevamente cuando termines.');
        } catch (\InvalidArgumentException|\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyQuestion(TestBanco $banco, TestBancoPregunta $pregunta, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        $service->eliminarPregunta($this->usuario(), $banco, $pregunta);

        return back()->with('success', 'Pregunta eliminada.');
    }

    public function moveQuestion(Request $request, TestBanco $banco, TestBancoPregunta $pregunta, TestBankService $service): RedirectResponse
    {
        $this->autorizarBanco($banco);
        $service->validarPropietario($this->usuario(), $banco);
        if ((int) $pregunta->banco_id !== (int) $banco->id) {
            abort(404);
        }
        $direction = $request->validate(['direccion' => ['required', 'in:subir,bajar']])['direccion'];
        $ids = $banco->preguntas()->orderBy('orden')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $position = array_search((int) $pregunta->id, $ids, true);
        $target = $direction === 'subir' ? $position - 1 : $position + 1;
        if ($position !== false && isset($ids[$target])) {
            [$ids[$position], $ids[$target]] = [$ids[$target], $ids[$position]];
            $service->reordenar($this->usuario(), $banco, $ids);
        }

        return back();
    }

    public function preview(TestBanco $banco, BankPracticeService $practicas): RedirectResponse
    {
        $this->autorizarBanco($banco);
        try {
            $sesion = $practicas->crear($this->usuario(), $banco);

            return redirect()->route('alumno.tests.codeplay.jugar', $sesion);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function questionData(Request $request): array
    {
        $request->validate([
            'tipo' => ['required', 'in:opcion_unica,seleccion_multiple,verdadero_falso,respuesta_directa'],
            'enunciado' => ['required', 'string', 'max:2000'],
            'explicacion' => ['nullable', 'string', 'max:3000'],
            'tiempo_limite' => ['required', 'integer', 'min:5', 'max:300'],
        ]);

        $tipo = (string) $request->input('tipo');
        $opciones = null;
        $respuestas = [];
        if (in_array($tipo, ['opcion_unica', 'seleccion_multiple'], true)) {
            $opciones = collect((array) $request->input('opciones', []))
                ->map(fn ($value) => trim((string) $value))->filter()->all();
            $respuestas = $tipo === 'opcion_unica'
                ? [(string) $request->input('respuesta_unica')]
                : array_values((array) $request->input('respuestas_multiples', []));
        } elseif ($tipo === 'verdadero_falso') {
            $respuestas = [(string) $request->input('respuesta_booleana')];
        } else {
            $respuestas = preg_split('/\R/u', (string) $request->input('respuestas_escritas', ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return [
            'tipo' => $tipo, 'enunciado' => $request->input('enunciado'), 'opciones' => $opciones,
            'respuestas_correctas' => $respuestas, 'explicacion' => $request->input('explicacion'),
            'tiempo_limite' => (int) $request->input('tiempo_limite', 20),
        ];
    }

    private function autorizarBanco(TestBanco $banco): void
    {
        $usuario = $this->usuario();
        abort_unless(
            ! $banco->esSistema()
            && $banco->origen === 'alumno'
            && (int) $banco->creador_usuario_id === (int) $usuario->id
            && (int) $banco->colegio_id === (int) $usuario->colegio_id,
            404
        );
    }

    private function usuario(): Usuario
    {
        return Usuario::findOrFail((int) session('usuario_id'));
    }
}
