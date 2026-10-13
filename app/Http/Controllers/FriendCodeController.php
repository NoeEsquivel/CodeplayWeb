<?php

namespace App\Http\Controllers;

use App\Models\TestBanco;
use App\Models\Usuario;
use App\Services\BankPracticeService;
use App\Services\TestBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FriendCodeController extends Controller
{
    public function index(): View
    {
        return $this->view();
    }

    public function lookup(Request $request, TestBankService $service): View
    {
        $codigo = Str::upper(trim((string) $request->validate(['codigo' => ['required', 'string', 'min:6', 'max:16']])['codigo']));
        try {
            $banco = $service->buscarCompartible($this->usuario(), $codigo);
            $consulta = [
                'id' => $banco->id,
                'codigo' => $banco->codigo_compartir,
                'autor' => $banco->creador->nombre,
                'nombre' => $banco->nombre,
                'descripcion' => $banco->descripcion,
                'materia' => $banco->materia,
                'preguntas' => $banco->preguntas->count(),
                'ya_agregado' => $banco->usuariosBiblioteca()->where('usuarios.id', $this->usuario()->id)->exists(),
            ];

            return $this->view($consulta);
        } catch (\DomainException $e) {
            return $this->view(null, $e->getMessage());
        }
    }

    public function add(Request $request, TestBankService $service): RedirectResponse
    {
        $codigo = (string) $request->validate(['codigo' => ['required', 'string', 'max:16']])['codigo'];
        try {
            $banco = $service->agregarPorCodigo($this->usuario(), $codigo);

            return redirect()->route('alumno.codigo-amigo.index')->with('success', "“{$banco->nombre}” se añadió a tu biblioteca.");
        } catch (\DomainException $e) {
            return redirect()->route('alumno.codigo-amigo.index')->with('error', $e->getMessage());
        }
    }

    public function remove(TestBanco $banco, TestBankService $service): RedirectResponse
    {
        try {
            $service->retirarDeBiblioteca($this->usuario(), $banco);

            return back()->with('success', 'Banco retirado de tu biblioteca. El original continúa con su creador.');
        } catch (\DomainException) {
            abort(404);
        }
    }

    public function copy(TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $this->autorizarBiblioteca($banco, $service);
        $copia = $service->copiarDesdeBiblioteca($this->usuario(), $banco);

        return redirect()->route('alumno.bancos.edit', $copia)
            ->with('success', 'Creaste una copia independiente. Puedes editarla sin cambiar el original.');
    }

    public function practice(TestBanco $banco, TestBankService $service, BankPracticeService $practicas): RedirectResponse
    {
        $this->autorizarBiblioteca($banco, $service);
        try {
            $sesion = $practicas->crear($this->usuario(), $banco);

            return redirect()->route('alumno.tests.codeplay.jugar', $sesion);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function view(?array $consulta = null, ?string $error = null): View
    {
        $usuario = $this->usuario();
        $biblioteca = $usuario->bancosTestBiblioteca()
            ->wherePivot('agregado_por_codigo', true)
            ->where('test_bancos.colegio_id', $usuario->colegio_id)
            ->where('test_bancos.activo', true)
            ->with(['creador:id,nombre', 'preguntas:id,banco_id'])
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('alumno.codigo-amigo', compact('biblioteca', 'consulta', 'error'));
    }

    private function autorizarBiblioteca(TestBanco $banco, TestBankService $service): void
    {
        try {
            $service->validarBancoBiblioteca($this->usuario(), $banco);
        } catch (\DomainException) {
            abort(404);
        }
    }

    private function usuario(): Usuario
    {
        return Usuario::findOrFail((int) session('usuario_id'));
    }
}
