<?php

namespace App\Services;

use App\Models\Clase;
use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LiveTestSessionService
{
    public function crear(Usuario $actor, array $datos, array $bancos = []): TestSesion
    {
        $tipo = (string) ($datos['tipo'] ?? '');
        $modalidad = (string) ($datos['modalidad'] ?? '');
        if (! in_array($tipo, InteractiveTestRules::TIPOS_SESION, true) || ! in_array($modalidad, InteractiveTestRules::MODALIDADES, true)) {
            throw new \InvalidArgumentException('El tipo o modalidad de sesión no es válido.');
        }
        if (! $actor->colegio_id || (! $actor->esAlumno() && ! $actor->esProfesor())) {
            throw new \DomainException('Este usuario no puede crear sesiones.');
        }
        if (in_array($tipo, ['practica', 'amigos'], true) && ! $actor->esAlumno()) {
            throw new \DomainException('Esta sesión debe ser creada por un alumno.');
        }
        if ($tipo === 'clase' && ! $actor->esProfesor()) {
            throw new \DomainException('La sesión de clase debe ser creada por un profesor.');
        }

        $clase = null;
        if ($tipo === 'clase') {
            $clase = Clase::whereKey($datos['clase_id'] ?? 0)->where('colegio_id', $actor->colegio_id)->where('activa', true)->firstOrFail();
            if (! $actor->clasesComoProfesor()->whereKey($clase->id)->exists()) {
                throw new \DomainException('El profesor no está asignado a esta clase.');
            }
        }

        $maximo = (int) ($datos['max_participantes'] ?? InteractiveTestRules::MAX_PARTICIPANTES);
        if ($maximo < 1 || $maximo > InteractiveTestRules::MAX_PARTICIPANTES) {
            throw new \InvalidArgumentException('La sala admite entre 1 y 30 participantes.');
        }
        foreach ($bancos as $banco) {
            $this->validarBancoAccesible($actor, $banco, $tipo);
        }

        return DB::transaction(function () use ($actor, $datos, $tipo, $modalidad, $clase, $maximo, $bancos) {
            do {
                $codigo = Str::upper(Str::random(6));
            } while (TestSesion::where('codigo_sala', $codigo)->exists());
            $sesion = TestSesion::create([
                'colegio_id' => $actor->colegio_id,
                'clase_id' => $clase?->id,
                'anfitrion_usuario_id' => $actor->id,
                'codigo_sala' => $codigo,
                'palabra_clave_hash' => filled($datos['palabra_clave'] ?? null) ? Hash::make($datos['palabra_clave']) : null,
                'tipo' => $tipo,
                'modalidad' => $modalidad,
                'estado' => 'configuracion',
                'tiempo_pregunta' => $modalidad === 'examen' ? null : max(5, min(300, (int) ($datos['tiempo_pregunta'] ?? 20))),
                'puntaje_objetivo' => $datos['puntaje_objetivo'] ?? null,
                'max_participantes' => $maximo,
                'configuracion' => $datos['configuracion'] ?? null,
                'disponible_desde' => $datos['disponible_desde'] ?? null,
                'disponible_hasta' => $datos['disponible_hasta'] ?? null,
            ]);
            foreach (array_values($bancos) as $orden => $banco) {
                $sesion->bancos()->attach($banco->id, ['orden' => $orden + 1]);
            }

            return $sesion;
        });
    }

    public function validarBancoAccesible(Usuario $actor, TestBanco $banco, string $tipo): void
    {
        if (! $banco->activo) {
            throw new \DomainException('El banco seleccionado no está activo.');
        }
        if ($banco->esSistema()) {
            return;
        }
        if ((int) $banco->colegio_id !== (int) $actor->colegio_id) {
            throw new \DomainException('No puedes usar un banco de otro colegio.');
        }
        $esPropio = (int) $banco->creador_usuario_id === (int) $actor->id;
        $estaEnBiblioteca = $banco->usuariosBiblioteca()->where('usuarios.id', $actor->id)->exists();
        if (! $esPropio && ! $estaEnBiblioteca) {
            throw new \DomainException('El banco no pertenece a la biblioteca del anfitrión.');
        }
        if ($tipo !== 'practica' && $banco->estado !== 'publicado') {
            throw new \DomainException('El banco debe estar publicado antes de usarlo en una sesión en vivo.');
        }
    }
}
