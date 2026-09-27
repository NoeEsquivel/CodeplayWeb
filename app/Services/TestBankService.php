<?php

namespace App\Services;

use App\Models\TestBanco;
use App\Models\TestBancoPregunta;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestBankService
{
    public function crear(Usuario $actor, array $datos): TestBanco
    {
        $this->validarCreador($actor);

        return DB::transaction(function () use ($actor, $datos) {
            $banco = TestBanco::create([
                'colegio_id' => $actor->colegio_id,
                'creador_usuario_id' => $actor->id,
                'origen' => $actor->esAlumno() ? 'alumno' : 'profesor',
                'categoria' => 'personalizado',
                'materia' => trim((string) ($datos['materia'] ?? 'General')),
                'estado' => 'borrador',
                'nombre' => trim((string) ($datos['nombre'] ?? '')),
                'descripcion' => filled($datos['descripcion'] ?? null) ? trim((string) $datos['descripcion']) : null,
                'compartible' => false,
                'activo' => true,
            ]);
            $banco->usuariosBiblioteca()->syncWithoutDetaching([$actor->id => ['agregado_por_codigo' => false]]);
            return $banco;
        });
    }

    public function actualizar(Usuario $actor, TestBanco $banco, array $datos): TestBanco
    {
        $this->validarPropietario($actor, $banco);
        $banco->forceFill([
            'nombre' => trim((string) ($datos['nombre'] ?? $banco->nombre)),
            'descripcion' => filled($datos['descripcion'] ?? null) ? trim((string) $datos['descripcion']) : null,
            'materia' => trim((string) ($datos['materia'] ?? $banco->materia ?? 'General')),
        ]);
        $this->marcarBorrador($banco);
        $banco->save();
        return $banco;
    }

    public function agregarPregunta(Usuario $actor, TestBanco $banco, array $datos): TestBancoPregunta
    {
        $this->validarPropietario($actor, $banco);
        $atributos = $this->validarPregunta($datos);
        $atributos['orden'] = (int) ($datos['orden'] ?? (($banco->preguntas()->max('orden') ?? 0) + 1));
        $atributos['activa'] = true;
        $pregunta = $banco->preguntas()->create($atributos);
        $this->marcarBorrador($banco, true);
        return $pregunta;
    }

    public function actualizarPregunta(Usuario $actor, TestBanco $banco, TestBancoPregunta $pregunta, array $datos): TestBancoPregunta
    {
        $this->validarPropietario($actor, $banco);
        if ((int) $pregunta->banco_id !== (int) $banco->id) throw new \DomainException('La pregunta no pertenece a este banco.');
        $pregunta->update($this->validarPregunta($datos));
        $this->marcarBorrador($banco, true);
        return $pregunta;
    }

    public function eliminarPregunta(Usuario $actor, TestBanco $banco, TestBancoPregunta $pregunta): void
    {
        $this->validarPropietario($actor, $banco);
        if ((int) $pregunta->banco_id !== (int) $banco->id) throw new \DomainException('La pregunta no pertenece a este banco.');
        $pregunta->delete();
        $this->reordenar($actor, $banco, $banco->preguntas()->pluck('id')->all());
    }

    public function reordenar(Usuario $actor, TestBanco $banco, array $ids): void
    {
        $this->validarPropietario($actor, $banco);
        $actuales = $banco->preguntas()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $recibidos = array_values(array_unique(array_map('intval', $ids)));
        sort($actuales);
        $comparacion = $recibidos;
        sort($comparacion);
        if ($actuales !== $comparacion) throw new \InvalidArgumentException('El orden debe incluir todas las preguntas del banco una sola vez.');

        DB::transaction(function () use ($banco, $recibidos) {
            foreach ($recibidos as $indice => $id) {
                $banco->preguntas()->whereKey($id)->update(['orden' => $indice + 1]);
            }
            $this->marcarBorrador($banco, true);
        });
    }

    public function duplicar(Usuario $actor, TestBanco $banco): TestBanco
    {
        $this->validarPropietario($actor, $banco);

        return DB::transaction(function () use ($actor, $banco) {
            $copia = $this->crear($actor, [
                'nombre' => 'Copia de '.$banco->nombre,
                'descripcion' => $banco->descripcion,
                'materia' => $banco->materia,
            ]);
            foreach ($banco->preguntas()->orderBy('orden')->get() as $pregunta) {
                $copia->preguntas()->create([
                    'enunciado' => $pregunta->enunciado,
                    'tipo' => $pregunta->tipo,
                    'opciones' => $pregunta->opciones,
                    'respuestas_correctas' => $pregunta->respuestas_correctas,
                    'explicacion' => $pregunta->explicacion,
                    'tiempo_limite' => $pregunta->tiempo_limite,
                    'puntos' => $pregunta->puntos,
                    'orden' => $pregunta->orden,
                    'activa' => true,
                ]);
            }
            return $copia;
        });
    }

    public function publicar(Usuario $actor, TestBanco $banco): TestBanco
    {
        $this->validarPropietario($actor, $banco);
        if (!$banco->preguntas()->where('activa', true)->exists()) {
            throw new \DomainException('Añade al menos una pregunta antes de publicar el banco.');
        }
        $banco->forceFill(['estado' => 'publicado'])->save();
        return $banco;
    }

    public function eliminar(Usuario $actor, TestBanco $banco): void
    {
        $this->validarPropietario($actor, $banco);
        $banco->delete();
    }

    public function habilitarCodigo(Usuario $actor, TestBanco $banco): string
    {
        $this->validarPropietario($actor, $banco);
        if ($banco->estado !== 'publicado') throw new \DomainException('Publica el banco antes de generar su código.');
        if (!$banco->codigo_compartir) {
            do { $codigo = Str::upper(Str::random(8)); }
            while (TestBanco::withTrashed()->where('codigo_compartir', $codigo)->exists());
            $banco->forceFill(['codigo_compartir' => $codigo, 'compartible' => true])->save();
        } elseif (!$banco->compartible) {
            $banco->forceFill(['compartible' => true])->save();
        }
        return $banco->codigo_compartir;
    }

    public function deshabilitarCodigo(Usuario $actor, TestBanco $banco): void
    {
        $this->validarPropietario($actor, $banco);
        $banco->forceFill(['compartible' => false])->save();
    }

    public function buscarCompartible(Usuario $alumno, string $codigo): TestBanco
    {
        if (!$alumno->esAlumno()) throw new \DomainException('Solo los alumnos pueden usar códigos de compañeros.');
        $banco = TestBanco::with(['creador:id,nombre,colegio_id,rol', 'preguntas:id,banco_id'])
            ->where('codigo_compartir', Str::upper(trim($codigo)))
            ->where('compartible', true)->where('estado', 'publicado')->where('activo', true)->first();
        if (!$banco || !$banco->creador || !$banco->creador->esAlumno()) throw new \DomainException('El código no está disponible.');
        if ((int) $banco->creador_usuario_id === (int) $alumno->id) throw new \DomainException('Este código pertenece a tu propio banco.');
        if ((int) $banco->colegio_id !== (int) $alumno->colegio_id) throw new \DomainException('El código no está disponible para tu colegio.');

        $clasesPropias = $alumno->clasesComoAlumno()->where('clases.activa', true)->pluck('clases.id');
        $compartenClase = $banco->creador->clasesComoAlumno()->whereIn('clases.id', $clasesPropias)->where('clases.activa', true)->exists();
        if (!$compartenClase) throw new \DomainException('El Test solo puede compartirse con compañeros de clase.');
        return $banco;
    }

    public function agregarPorCodigo(Usuario $alumno, string $codigo): TestBanco
    {
        $banco = $this->buscarCompartible($alumno, $codigo);
        if ($banco->usuariosBiblioteca()->where('usuarios.id', $alumno->id)->exists()) {
            throw new \DomainException('Este banco ya está en tu biblioteca.');
        }
        $banco->usuariosBiblioteca()->attach($alumno->id, ['agregado_por_codigo' => true]);
        return $banco;
    }

    public function retirarDeBiblioteca(Usuario $alumno, TestBanco $banco): void
    {
        if ((int) $banco->creador_usuario_id === (int) $alumno->id) throw new \DomainException('El banco original se administra desde Mis bancos.');
        $relacion = $banco->usuariosBiblioteca()->where('usuarios.id', $alumno->id)->wherePivot('agregado_por_codigo', true)->exists();
        if (!$relacion) throw new \DomainException('El banco no pertenece a tu biblioteca compartida.');
        $banco->usuariosBiblioteca()->detach($alumno->id);
    }

    public function validarBancoBiblioteca(Usuario $alumno, TestBanco $banco): void
    {
        if ((int) $banco->colegio_id !== (int) $alumno->colegio_id || !$banco->activo || $banco->estado !== 'publicado') {
            throw new \DomainException('Este banco compartido no está disponible.');
        }
        if (!$banco->usuariosBiblioteca()->where('usuarios.id', $alumno->id)->wherePivot('agregado_por_codigo', true)->exists()) {
            throw new \DomainException('Este banco no pertenece a tu biblioteca compartida.');
        }
    }

    public function copiarDesdeBiblioteca(Usuario $alumno, TestBanco $original): TestBanco
    {
        $this->validarBancoBiblioteca($alumno, $original);
        return DB::transaction(function () use ($alumno, $original) {
            $copia = $this->crear($alumno, [
                'nombre' => 'Copia de '.$original->nombre, 'descripcion' => $original->descripcion, 'materia' => $original->materia,
            ]);
            foreach ($original->preguntas()->where('activa', true)->orderBy('orden')->get() as $pregunta) {
                $copia->preguntas()->create([
                    'enunciado' => $pregunta->enunciado, 'tipo' => $pregunta->tipo,
                    'opciones' => $pregunta->opciones, 'respuestas_correctas' => $pregunta->respuestas_correctas,
                    'explicacion' => $pregunta->explicacion, 'tiempo_limite' => $pregunta->tiempo_limite,
                    'puntos' => $pregunta->puntos,
                    'orden' => $pregunta->orden, 'activa' => true,
                ]);
            }
            return $copia;
        });
    }
    public function validarPropietario(Usuario $actor, TestBanco $banco): void
    {
        if ((int) $banco->creador_usuario_id !== (int) $actor->id || (int) $banco->colegio_id !== (int) $actor->colegio_id || $banco->esSistema()) {
            throw new \DomainException('No puedes modificar este banco de preguntas.');
        }
    }

    private function validarPregunta(array $datos): array
    {
        $tipo = (string) ($datos['tipo'] ?? '');
        if (!in_array($tipo, InteractiveTestRules::TIPOS_PREGUNTA, true)) throw new \InvalidArgumentException('El tipo de pregunta no es válido.');

        $enunciado = trim((string) ($datos['enunciado'] ?? ''));
        $respuestas = array_values(array_unique(array_map(fn ($v) => is_string($v) ? trim($v) : $v, array_filter((array) ($datos['respuestas_correctas'] ?? []), fn ($v) => $v !== null && $v !== ''))));
        $opciones = isset($datos['opciones']) ? array_filter((array) $datos['opciones'], fn ($v) => trim((string) $v) !== '') : null;
        if ($enunciado === '' || mb_strlen($enunciado) > 2000 || $respuestas === []) throw new \InvalidArgumentException('La pregunta y su respuesta correcta son obligatorias.');
        if (in_array($tipo, ['opcion_unica', 'seleccion_multiple'], true)) {
            if (count($opciones ?? []) < 2) throw new \InvalidArgumentException('Las preguntas de selección necesitan al menos dos opciones.');
            foreach ($respuestas as $respuesta) if (!array_key_exists((string) $respuesta, $opciones)) throw new \InvalidArgumentException('Cada respuesta correcta debe corresponder a una opción.');
        }
        if (in_array($tipo, ['opcion_unica', 'verdadero_falso'], true) && count($respuestas) !== 1) throw new \InvalidArgumentException('Este tipo necesita exactamente una respuesta correcta.');
        if ($tipo === 'verdadero_falso' && !in_array((string) $respuestas[0], ['1', '0', 'true', 'false'], true)) throw new \InvalidArgumentException('La respuesta debe ser verdadero o falso.');
        if ($tipo === 'respuesta_directa' && count($respuestas) > 20) throw new \InvalidArgumentException('Puedes configurar hasta veinte respuestas alternativas.');

        return [
            'enunciado' => $enunciado,
            'tipo' => $tipo,
            'opciones' => $opciones,
            'respuestas_correctas' => $respuestas,
            'explicacion' => filled($datos['explicacion'] ?? null) ? trim((string) $datos['explicacion']) : null,
            'tiempo_limite' => max(5, min(300, (int) ($datos['tiempo_limite'] ?? 20))),
            'puntos' => max(1, min(1000, (int) ($datos['puntos'] ?? 1))),
        ];
    }

    private function validarCreador(Usuario $actor): void
    {
        if ((!$actor->esAlumno() && !$actor->esProfesor()) || !$actor->colegio_id) throw new \DomainException('Este usuario no puede crear bancos de preguntas.');
    }

    private function marcarBorrador(TestBanco $banco, bool $guardar = false): void
    {
        $banco->forceFill(['estado' => 'borrador', 'compartible' => false]);
        if ($guardar) $banco->save();
    }
}


