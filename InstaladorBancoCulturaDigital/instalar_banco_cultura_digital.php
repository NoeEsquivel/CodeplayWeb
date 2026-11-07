<?php

declare(strict_types=1);

use App\Models\TestBanco;
use App\Models\Usuario;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

function terminar(string $mensaje, int $codigo = 1): never
{
    $salida = $codigo === 0 ? STDOUT : STDERR;
    fwrite($salida, $mensaje.PHP_EOL);
    exit($codigo);
}

function opcion(string $nombre): ?string
{
    $prefijo = '--'.$nombre.'=';
    foreach ($_SERVER['argv'] ?? [] as $argumento) {
        if (str_starts_with($argumento, $prefijo)) {
            return substr($argumento, strlen($prefijo));
        }
    }

    return null;
}

function nombreNormalizado(Usuario $usuario): string
{
    return Str::lower(Str::ascii(trim($usuario->nombre.' '.($usuario->nombre_completo ?? ''))));
}

if (PHP_SAPI !== 'cli') {
    terminar('Este instalador solo puede ejecutarse desde la consola.');
}

$directorioProyecto = opcion('project-dir') ?: getcwd();
$directorioProyecto = rtrim((string) realpath($directorioProyecto), DIRECTORY_SEPARATOR);
$autoload = $directorioProyecto.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
$bootstrap = $directorioProyecto.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'app.php';

if (! is_file($directorioProyecto.DIRECTORY_SEPARATOR.'artisan') || ! is_file($autoload) || ! is_file($bootstrap)) {
    terminar('No se encontró una instalación completa de CodePlay. Ejecute el instalador desde la carpeta que contiene artisan.');
}

require $autoload;
$app = require $bootstrap;
$app->make(Kernel::class)->bootstrap();

foreach (['usuarios', 'test_bancos', 'test_banco_preguntas', 'test_biblioteca_usuario'] as $tabla) {
    if (! Schema::hasTable($tabla)) {
        terminar("Falta la tabla {$tabla}. Ejecute primero: php artisan migrate --force");
    }
}

$columnasBanco = ['colegio_id', 'creador_usuario_id', 'origen', 'categoria', 'materia', 'estado', 'nombre', 'descripcion', 'activo'];
foreach ($columnasBanco as $columna) {
    if (! Schema::hasColumn('test_bancos', $columna)) {
        terminar("La instalación de CodePlay no está actualizada: falta test_bancos.{$columna}. Ejecute las migraciones pendientes.");
    }
}

$archivoDatos = __DIR__.DIRECTORY_SEPARATOR.'banco_cultura_digital_jeremias.json';
if (! is_file($archivoDatos)) {
    terminar('No se encontró banco_cultura_digital_jeremias.json junto al instalador.');
}

try {
    $datos = json_decode((string) file_get_contents($archivoDatos), true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    terminar('El archivo de preguntas no es JSON válido: '.$e->getMessage());
}

$preguntas = $datos['preguntas'] ?? [];
if (! is_array($preguntas) || count($preguntas) !== 250) {
    terminar('El archivo debe contener exactamente 250 preguntas.');
}

foreach ($preguntas as $indice => $pregunta) {
    $numero = $indice + 1;
    $opciones = $pregunta['opciones'] ?? [];
    $correctas = $pregunta['respuestas_correctas'] ?? [];
    if (($pregunta['tipo'] ?? null) !== 'opcion_unica'
        || trim((string) ($pregunta['enunciado'] ?? '')) === ''
        || array_keys($opciones) !== ['a', 'b', 'c', 'd']
        || count($correctas) !== 1
        || ! array_key_exists((string) $correctas[0], $opciones)) {
        terminar("La pregunta {$numero} no tiene el formato requerido por CodePlay.");
    }
}

$idSolicitado = opcion('user-id');
if ($idSolicitado !== null && ! ctype_digit($idSolicitado)) {
    terminar('El parámetro --user-id debe ser un número entero.');
}

if ($idSolicitado !== null) {
    $profesores = Usuario::query()
        ->whereKey((int) $idSolicitado)
        ->where('rol', Usuario::ROL_PROFESOR)
        ->get();
} else {
    $profesores = Usuario::query()
        ->where('rol', Usuario::ROL_PROFESOR)
        ->get()
        ->filter(fn (Usuario $usuario) => preg_match('/\bjeremias\b/u', nombreNormalizado($usuario)) === 1)
        ->values();
}

if ($profesores->isEmpty()) {
    terminar('No se encontró un usuario con rol Profesor cuyo nombre sea Jeremias. No se realizó ningún cambio.');
}

if ($profesores->count() > 1) {
    fwrite(STDOUT, "Se encontraron varios profesores llamados Jeremias:".PHP_EOL);
    foreach ($profesores as $profesor) {
        fwrite(STDOUT, sprintf(
            "  ID %d | usuario: %s | nombre: %s | colegio: %s | activo: %s%s",
            $profesor->id,
            $profesor->nombre,
            $profesor->nombre_completo ?: '-',
            $profesor->colegio_id ?: '-',
            $profesor->activo ? 'sí' : 'no',
            PHP_EOL
        ));
    }
    fwrite(STDOUT, 'Escriba el ID correcto y presione Enter: ');
    $seleccion = trim((string) fgets(STDIN));
    $profesor = $profesores->firstWhere('id', (int) $seleccion);
    if (! $profesor) {
        terminar('No se seleccionó un ID válido. No se realizó ningún cambio.');
    }
} else {
    $profesor = $profesores->first();
}

if (! $profesor->activo) {
    terminar("El profesor seleccionado (ID {$profesor->id}) está inactivo. No se realizó ningún cambio.");
}
if (! $profesor->colegio_id) {
    terminar("El profesor seleccionado (ID {$profesor->id}) no tiene colegio asignado. No se realizó ningún cambio.");
}

$nombreBanco = (string) $datos['nombre'];
$existente = TestBanco::withTrashed()
    ->where('creador_usuario_id', $profesor->id)
    ->where('colegio_id', $profesor->colegio_id)
    ->where('origen', 'profesor')
    ->where('nombre', $nombreBanco)
    ->first();

if ($existente) {
    if ($existente->trashed()) {
        terminar("Ya existe un banco eliminado con este nombre (ID {$existente->id}). No se restauró ni se sobrescribió.");
    }

    $guardadas = $existente->preguntas()->orderBy('orden')->get();
    $coincide = $guardadas->count() === count($preguntas);
    if ($coincide) {
        foreach ($guardadas as $indice => $guardada) {
            $esperada = $preguntas[$indice];
            if ($guardada->enunciado !== $esperada['enunciado']
                || $guardada->tipo !== $esperada['tipo']
                || $guardada->opciones !== $esperada['opciones']
                || $guardada->respuestas_correctas !== $esperada['respuestas_correctas']) {
                $coincide = false;
                break;
            }
        }
    }

    if ($coincide) {
        terminar("El banco ya estaba instalado para {$profesor->nombre} (ID del banco: {$existente->id}). No se duplicó.", 0);
    }

    terminar("Ya existe un banco llamado '{$nombreBanco}' para este profesor, pero su contenido es diferente. No se sobrescribió.");
}

try {
    $banco = DB::transaction(function () use ($datos, $preguntas, $profesor): TestBanco {
        $banco = TestBanco::create([
            'colegio_id' => $profesor->colegio_id,
            'creador_usuario_id' => $profesor->id,
            'origen' => 'profesor',
            'categoria' => 'personalizado',
            'materia' => (string) $datos['materia'],
            'estado' => 'publicado',
            'clave_sistema' => null,
            'nombre' => (string) $datos['nombre'],
            'descripcion' => (string) $datos['descripcion'],
            'codigo_compartir' => null,
            'compartible' => false,
            'activo' => true,
        ]);

        foreach ($preguntas as $indice => $pregunta) {
            $banco->preguntas()->create([
                'enunciado' => trim((string) $pregunta['enunciado']),
                'tipo' => 'opcion_unica',
                'opciones' => $pregunta['opciones'],
                'respuestas_correctas' => $pregunta['respuestas_correctas'],
                'explicacion' => trim((string) ($pregunta['explicacion'] ?? '')) ?: null,
                'tiempo_limite' => 20,
                'puntos' => 1,
                'orden' => $indice + 1,
                'activa' => true,
            ]);
        }

        $banco->usuariosBiblioteca()->syncWithoutDetaching([
            $profesor->id => ['agregado_por_codigo' => false],
        ]);

        return $banco;
    });
} catch (Throwable $e) {
    terminar('La base de datos rechazó la instalación. La transacción fue revertida: '.$e->getMessage());
}

terminar(
    "Instalación completada correctamente.".PHP_EOL.
    "Profesor: {$profesor->nombre} (ID {$profesor->id})".PHP_EOL.
    "Banco: {$banco->nombre} (ID {$banco->id})".PHP_EOL.
    "Preguntas: 250".PHP_EOL.
    "Estado: publicado y listo para seleccionarse al crear una Trivia.",
    0
);

