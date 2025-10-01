<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserManagementController;

// Rutas API para gestión de usuarios
Route::get('/usuarios-por-rol/{rol}', function ($rol) {
    $usuarios = \App\Models\Usuario::where('rol', $rol)->get();
    return response()->json($usuarios);
});

Route::post('/usuarios', [UserManagementController::class, 'storeApi']);
Route::post('/usuarios/{id}/reset-password', [UserManagementController::class, 'resetPasswordApi']);
Route::delete('/usuarios/{id}', [UserManagementController::class, 'destroyApi']);