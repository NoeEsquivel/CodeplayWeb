<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\DesafioController;
use App\Http\Controllers\AventuraController;
// Ruta raíz (página principal) que redirige al login
Route::get('/', function () {
    return view('auth.login');
})->name('home');

// Ruta para mostrar el login (alternativa)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Ruta para procesar el login
Route::post('/login/verify', [AuthController::class, 'verify'])->name('login.verify');

// Rutas protegidas (solo para usuarios autenticados)
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/inicio-alumno', function () {
        return view('inicio-alumno');
    })->name('inicio.alumno');
    
    Route::get('/inicio-administrador', function () {
        return view('inicio-administrador');
    })->name('inicio.administrador');
    
    Route::get('/inicio-master', function () {
        return view('inicio-master');
    })->name('inicio.master');
});


// Rutas para tests
Route::get('/test/{modulo}', [TestController::class, 'show'])->name('test.show');
Route::post('/test/{modulo}/evaluar', [TestController::class, 'evaluar'])->name('test.evaluar');
// Ruta para verificar disponibilidad del test
Route::get('/test/{modulo}/check', [TestController::class, 'checkAvailability'])->name('test.check');

// Ruta para cambio de contraseña obligatorio
Route::get('/cambio-clave-obligatorio', [AuthController::class, 'mostrarCambioClaveObligatorio'])
    ->name('cambio.clave.obligatorio');

Route::post('/cambio-clave-procesar', [AuthController::class, 'procesarCambioClaveObligatorio'])
    ->name('cambio.clave.procesar');

// Rutas para alumnos - MODIFICA SOLO DOCUMENTACIÓN
Route::prefix('alumno')->group(function () {
    // Cambia esta ruta para usar el controlador
    Route::get('/documentacion', [DocumentacionController::class, 'index'])->name('alumno.documentacion');
    Route::get('/tutoriales', [TutorialController::class, 'index'])->name('alumno.tutoriales');
    Route::get('/desafios', [DesafioController::class, 'index'])->name('alumno.desafios');
    Route::get('/desafios/programacion', [DesafioController::class, 'programacion'])->name('alumno.desafios.programacion');
    Route::get('/desafios/nivel1', [DesafioController::class, 'nivel1'])->name('alumno.desafios.nivel1');
    Route::get('/desafios/nivel2', [DesafioController::class, 'nivel2'])->name('alumno.desafios.nivel2');
    Route::get('/desafios/nivel3', [DesafioController::class, 'nivel3'])->name('alumno.desafios.nivel3');
    Route::get('/desafios/nivel4', [DesafioController::class, 'nivel4'])->name('alumno.desafios.nivel4');
    Route::get('/desafios/nivel5', [DesafioController::class, 'nivel5'])->name('alumno.desafios.nivel5');
    Route::get('/aventura', [AventuraController::class, 'index'])->name('alumno.aventura');
    // Mantén las demás rutas igual
    
    Route::get('/tests', function () {
        return view('alumno.tests');
    })->name('alumno.tests');
    
    Route::get('/progreso', function () {
        return view('alumno.progreso');
    })->name('alumno.progreso');
});

// Añade esta ruta para los documentos individuales
Route::get('/alumno/documentacion/{documento}', [DocumentacionController::class, 'show'])->name('alumno.documentacion.show');
// Ruta de logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta para obtener los puntos del usuario (ACTUALIZADA)
Route::get('/usuario/puntos', function() {
    $usuarioId = session('usuario_id');
    
    if (!$usuarioId) {
        return response()->json([
            'success' => false,
            'error' => 'Usuario no autenticado'
        ]);
    }
    
    $usuario = DB::table('usuarios')->where('id', $usuarioId)->first();
    
    if (!$usuario) {
        return response()->json([
            'success' => false,
            'error' => 'Usuario no encontrado'
        ]);
    }
    
    $puntosDisponibles = ($usuario->puntos_totales ?? 0) - ($usuario->puntos_usados ?? 0);
    
    // Actualizar la sesión
    session(['puntos_disponibles' => $puntosDisponibles]);
    
    return response()->json([
        'success' => true,
        'puntos' => $puntosDisponibles,
        'puntos_totales' => $usuario->puntos_totales ?? 0,
        'puntos_usados' => $usuario->puntos_usados ?? 0
    ]);
})->name('usuario.puntos');

// Rutas para tutoriales individuales
Route::get('/tutorial/{slug}', [TutorialController::class, 'show'])->name('tutorial.show');

// Rutas para tutoriales
Route::get('/tutorial/{slug}', [TutorialController::class, 'show'])->name('tutorial.show');
Route::post('/tutorial/{slug}/completar', [TutorialController::class, 'completar'])->name('tutorial.completar');
// Ruta para obtener el progreso de tutoriales
Route::get('/tutoriales/progreso', [TutorialController::class, 'getProgreso'])->name('tutoriales.progreso');
// Ruta para procesar recompensas de desafíos
Route::post('/desafios/procesar-recompensa', [DesafioController::class, 'procesarRecompensa'])->name('desafios.procesar-recompensa');