<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AventuraController;
use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\ColegioController;
use App\Http\Controllers\DesafioController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\StudentProgressController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TeacherPanelController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\UserManagementController;
use App\Services\AventuraFinal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('auth.login'))->name('home');
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::post('/login/verify', [AuthController::class, 'verify'])
    ->middleware('throttle:10,1')
    ->name('login.verify');

Route::get('/recuperar-cuenta', [RecoveryController::class, 'showRequestForm'])->name('recovery.form');
Route::post('/recuperar-cuenta', [RecoveryController::class, 'requestRecovery'])
    ->middleware('throttle:account-recovery')->name('recovery.request');
Route::get('/restablecer-clave/{token}', [RecoveryController::class, 'showResetForm'])
    ->middleware('throttle:10,10')->name('recovery.reset.form');
Route::post('/restablecer-clave', [RecoveryController::class, 'resetPassword'])
    ->middleware('throttle:10,10')->name('recovery.reset');
Route::get('/verificar-correo/{token}', [SecurityController::class, 'verifyEmail'])
    ->middleware('throttle:10,10')->name('security.email.verify');

Route::middleware('auth.custom')->group(function () {
    Route::get('/cambio-clave-obligatorio', [AuthController::class, 'mostrarCambioClaveObligatorio'])
        ->name('cambio.clave.obligatorio');
    Route::post('/cambio-clave-procesar', [AuthController::class, 'procesarCambioClaveObligatorio'])
        ->middleware('throttle:10,1')
        ->name('cambio.clave.procesar');
    Route::get('/seguridad/correo', [SecurityController::class, 'showEmailForm'])->name('security.email.form');
    Route::post('/seguridad/correo', [SecurityController::class, 'saveEmail'])
        ->middleware('throttle:security-email-update')->name('security.email.save');
    Route::get('/seguridad/correo/pendiente', [SecurityController::class, 'pendingEmail'])->name('security.email.pending');
    Route::post('/seguridad/correo/reenviar', [SecurityController::class, 'resendVerification'])
        ->middleware('throttle:security-email-resend')->name('security.email.resend');
    Route::get('/seguridad/doble-factor/configurar', [SecurityController::class, 'showTwoFactorSetup'])->name('security.2fa.setup');
    Route::post('/seguridad/doble-factor/configurar', [SecurityController::class, 'confirmTwoFactor'])
        ->middleware('throttle:security-two-factor')->name('security.2fa.confirm');
    Route::get('/seguridad/doble-factor', [SecurityController::class, 'showTwoFactorChallenge'])->name('security.2fa.challenge');
    Route::post('/seguridad/doble-factor', [SecurityController::class, 'verifyTwoFactorChallenge'])
        ->middleware('throttle:security-two-factor')->name('security.2fa.challenge.verify');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth.custom', 'role:Alumno'])->group(function () {
    Route::get('/inicio-alumno', function () {
        $trofeoAventura = AventuraFinal::victoria((int) session('usuario_id'));

        return view('inicio-alumno', compact('trofeoAventura'));
    })->name('inicio.alumno');

    Route::get('/test/{modulo}', [TestController::class, 'show'])->name('test.show');
    Route::post('/test/{modulo}/evaluar', [TestController::class, 'evaluar'])
        ->middleware('throttle:30,1')
        ->name('test.evaluar');
    Route::get('/test/{modulo}/check', [TestController::class, 'checkAvailability'])
        ->name('test.check');

    Route::prefix('alumno')->group(function () {
        Route::get('/documentacion', [DocumentacionController::class, 'index'])->name('alumno.documentacion');
        Route::get('/documentacion/{documento}', [DocumentacionController::class, 'show'])->name('alumno.documentacion.show');
        Route::get('/tutoriales', [TutorialController::class, 'index'])->name('alumno.tutoriales');
        Route::get('/desafios', [DesafioController::class, 'index'])->name('alumno.desafios');
        Route::get('/desafios/programacion', [DesafioController::class, 'programacion'])->name('alumno.desafios.programacion');

        foreach (range(1, 20) as $nivel) {
            Route::get("/desafios/nivel{$nivel}", [DesafioController::class, "nivel{$nivel}"])
                ->name("alumno.desafios.nivel{$nivel}");
        }

        Route::get('/aventura', [AventuraController::class, 'index'])->name('alumno.aventura');
        Route::get('/aventura/final/{expedicion}', [AventuraController::class, 'finalAventura'])
            ->whereNumber('expedicion')->name('alumno.aventura.final');
        Route::post('/aventura/completar', [AventuraController::class, 'completar'])
            ->middleware('throttle:10,1')->name('alumno.aventura.completar');
        Route::get('/aventura/mapa', [AventuraController::class, 'mapa'])->name('alumno.aventura.mapa');
        Route::post('/aventura/combate', [AventuraController::class, 'combate'])
            ->middleware('throttle:60,1')->name('alumno.aventura.combate');
        Route::post('/aventura/reiniciar', [AventuraController::class, 'reiniciar'])
            ->middleware('throttle:10,1')->name('alumno.aventura.reiniciar');
        Route::post('/aventura/evento', [AventuraController::class, 'evento'])
            ->middleware('throttle:60,1')->name('alumno.aventura.evento');
        Route::post('/aventura/gremio', [AventuraController::class, 'gremio'])
            ->middleware('throttle:60,1')->name('alumno.aventura.gremio');
        Route::get('/aventura/derrota/{expedicion}', [AventuraController::class, 'derrota'])
            ->whereNumber('expedicion')->name('alumno.aventura.derrota');
        Route::post('/aventura/iniciar', [AventuraController::class, 'iniciar'])
            ->middleware('throttle:30,1')->name('alumno.aventura.iniciar');
        Route::post('/aventura/camino', [AventuraController::class, 'camino'])
            ->middleware('throttle:30,1')->name('alumno.aventura.camino');
        Route::post('/aventura/equipamiento', [AventuraController::class, 'guardar'])
            ->middleware('throttle:30,1')->name('alumno.aventura.equipamiento');

        Route::get('/tests', fn () => view('alumno.tests'))->name('alumno.tests');
        Route::get('/progreso', StudentProgressController::class)->name('alumno.progreso');
    });

    Route::get('/usuario/puntos', function () {
        $usuario = DB::table('usuarios')->where('id', session('usuario_id'))->first();

        abort_unless($usuario, 404, 'Usuario no encontrado');

        $puntosDisponibles = ($usuario->puntos_totales ?? 0) - ($usuario->puntos_usados ?? 0);
        session(['puntos_disponibles' => $puntosDisponibles]);

        return response()->json([
            'success' => true,
            'puntos' => $puntosDisponibles,
            'puntos_totales' => $usuario->puntos_totales ?? 0,
            'puntos_usados' => $usuario->puntos_usados ?? 0,
        ]);
    })->name('usuario.puntos');

    Route::get('/tutoriales/progreso', [TutorialController::class, 'getProgreso'])
        ->name('tutoriales.progreso');
    Route::get('/tutorial/{slug}', [TutorialController::class, 'show'])->name('tutorial.show');
    Route::post('/tutorial/{slug}/completar', [TutorialController::class, 'completar'])
        ->middleware('throttle:30,1')
        ->name('tutorial.completar');

    Route::post('/desafios/procesar-recompensa', [DesafioController::class, 'procesarRecompensa'])
        ->middleware('throttle:30,1')
        ->name('desafios.procesar-recompensa');
    Route::post('/desafios/validar-proyecto-final', [DesafioController::class, 'validarProyectoFinal'])
        ->middleware('throttle:30,1')
        ->name('desafios.validar-proyecto-final');
});

Route::prefix('api/colegio')
    ->middleware(['auth.custom', 'role:AdministradorColegio', 'throttle:60,1'])
    ->group(function () {
        Route::get('/resumen', [SchoolAdminController::class, 'summary']);
        Route::get('/usuarios-por-rol/{rol}', [UserManagementController::class, 'indexApi'])
            ->whereIn('rol', ['Alumno', 'Profesor']);
        Route::post('/usuarios', [UserManagementController::class, 'storeApi']);
        Route::post('/usuarios/{id}/reset-password', [UserManagementController::class, 'resetPasswordApi'])
            ->whereNumber('id');
        Route::get('/clases', [SchoolAdminController::class, 'classes']);
        Route::post('/clases', [SchoolAdminController::class, 'storeClass']);
        Route::put('/clases/{clase}', [SchoolAdminController::class, 'updateClass'])->whereNumber('clase');
        Route::post('/clases/{clase}/profesores/{usuario}', [SchoolAdminController::class, 'assignProfessor'])
            ->whereNumber(['clase', 'usuario']);
        Route::delete('/clases/{clase}/profesores/{usuario}', [SchoolAdminController::class, 'removeProfessor'])
            ->whereNumber(['clase', 'usuario']);
        Route::post('/clases/{clase}/alumnos/{usuario}', [SchoolAdminController::class, 'assignStudent'])
            ->whereNumber(['clase', 'usuario']);
        Route::delete('/clases/{clase}/alumnos/{usuario}', [SchoolAdminController::class, 'removeStudent'])
            ->whereNumber(['clase', 'usuario']);
    });

Route::prefix('api/profesor')
    ->middleware(['auth.custom', 'role:Profesor', 'throttle:60,1'])
    ->group(function () {
        Route::get('/clases', [TeacherPanelController::class, 'classes']);
        Route::get('/progreso', [TeacherPanelController::class, 'report']);
        Route::get('/progreso/exportar', [TeacherPanelController::class, 'export']);
        Route::post('/clases/{clase}/alumnos/{usuario}', [TeacherPanelController::class, 'assignStudent'])
            ->whereNumber(['clase', 'usuario']);
        Route::delete('/clases/{clase}/alumnos/{usuario}', [TeacherPanelController::class, 'removeStudent'])
            ->whereNumber(['clase', 'usuario']);
    });

Route::get('/inicio-profesor', fn () => view('inicio-administrador'))
    ->middleware(['auth.custom', 'role:Profesor'])
    ->name('inicio.profesor');

Route::get('/inicio-administrador', fn () => view('inicio-administrador'))
    ->middleware(['auth.custom', 'role:Profesor'])
    ->name('inicio.administrador');

Route::get('/inicio-administrador-colegio', fn () => view('inicio-administrador-colegio'))
    ->middleware(['auth.custom', 'role:AdministradorColegio'])
    ->name('inicio.administrador-colegio');

Route::get('/inicio-master', fn () => view('inicio-master'))
    ->middleware(['auth.custom', 'role:Master'])
    ->name('inicio.master');

Route::prefix('api')
    ->middleware(['auth.custom', 'role:Profesor,Master', 'throttle:60,1'])
    ->group(function () {
        Route::get('/usuarios-por-rol/{rol}', [UserManagementController::class, 'indexApi'])
            ->whereIn('rol', ['Alumno', 'Profesor', 'AdministradorColegio']);
        Route::post('/usuarios', [UserManagementController::class, 'storeApi']);
        Route::post('/usuarios/{id}/reset-password', [UserManagementController::class, 'resetPasswordApi'])
            ->whereNumber('id');
        Route::delete('/usuarios/{id}', [UserManagementController::class, 'destroyApi'])
            ->whereNumber('id');

        Route::middleware('role:Master')->group(function () {
            Route::get('/colegios', [ColegioController::class, 'index']);
            Route::post('/colegios', [ColegioController::class, 'store']);
            Route::get('/colegios/{colegio}/resumen', [ColegioController::class, 'show'])
                ->whereNumber('colegio');
            Route::put('/colegios/{colegio}', [ColegioController::class, 'update'])
                ->whereNumber('colegio');
            Route::delete('/colegios/{colegio}', [ColegioController::class, 'destroy'])
                ->whereNumber('colegio');
            Route::patch('/usuarios/{id}/estado', [UserManagementController::class, 'updateStatusApi'])
                ->whereNumber('id');
            Route::patch('/usuarios/{id}/colegio', [UserManagementController::class, 'moveCollegeApi'])
                ->whereNumber('id');
            Route::get('/auditoria', [AdminAuditController::class, 'index']);
        });
    });
