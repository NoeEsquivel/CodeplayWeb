<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

// Ruta para cambio de contraseña obligatorio
Route::get('/cambio-clave-obligatorio', [AuthController::class, 'mostrarCambioClaveObligatorio'])
    ->name('cambio.clave.obligatorio');

Route::post('/cambio-clave-procesar', [AuthController::class, 'procesarCambioClaveObligatorio'])
    ->name('cambio.clave.procesar');

// Ruta de logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');