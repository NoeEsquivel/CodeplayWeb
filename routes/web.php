<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AventuraController;
use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\ColegioController;
use App\Http\Controllers\ClassroomTestController;
use App\Http\Controllers\DesafioController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\FriendCodeController;
use App\Http\Controllers\FriendLiveTestController;
use App\Http\Controllers\LiveTestEngineController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\StudentProgressController;
use App\Http\Controllers\StudentBankController;
use App\Http\Controllers\StudentTestsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TeacherPanelController;
use App\Http\Controllers\TeacherGradebookController;
use App\Http\Controllers\TeacherTestController;
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

Route::prefix('api/tests/en-vivo')
    ->middleware(['auth.custom', 'role:Alumno,Profesor', 'throttle:live-tests'])
    ->group(function () {
        Route::post('/salas', [LiveTestEngineController::class, 'create'])->name('tests.live.create');
        Route::post('/salas/unirse', [LiveTestEngineController::class, 'join'])->name('tests.live.join');
        Route::post('/salas/{sesion}/abrir', [LiveTestEngineController::class, 'open'])->whereNumber('sesion')->name('tests.live.open');
        Route::post('/salas/{sesion}/reconectar', [LiveTestEngineController::class, 'reconnect'])->whereNumber('sesion')->name('tests.live.reconnect');
        Route::post('/salas/{sesion}/iniciar', [LiveTestEngineController::class, 'start'])->whereNumber('sesion')->name('tests.live.start');
        Route::get('/salas/{sesion}/estado', [LiveTestEngineController::class, 'state'])->whereNumber('sesion')->name('tests.live.state');
        Route::post('/salas/{sesion}/responder', [LiveTestEngineController::class, 'answer'])->whereNumber('sesion')->name('tests.live.answer');
        Route::post('/salas/{sesion}/cerrar', [LiveTestEngineController::class, 'close'])->whereNumber('sesion')->name('tests.live.close');
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

        Route::get('/tests', [StudentTestsController::class, 'index'])->name('alumno.tests');
        Route::get('/tests/clase', [ClassroomTestController::class, 'index'])->name('alumno.tests.clase');
        Route::post('/tests/clase/{sesion}/entrar', [ClassroomTestController::class, 'enter'])->whereNumber('sesion')->name('alumno.tests.clase.enter');
        Route::get('/tests/clase/{sesion}/trivia', [ClassroomTestController::class, 'trivia'])->whereNumber('sesion')->name('alumno.tests.clase.trivia');
        Route::get('/tests/clase/{sesion}/examen', [ClassroomTestController::class, 'exam'])->whereNumber('sesion')->name('alumno.tests.clase.examen');
        Route::post('/tests/clase/{sesion}/examen/{emision}/guardar', [ClassroomTestController::class, 'saveExam'])->whereNumber(['sesion','emision'])->name('alumno.tests.clase.examen.save');
        Route::post('/tests/clase/{sesion}/examen/entregar', [ClassroomTestController::class, 'submitExam'])->whereNumber('sesion')->name('alumno.tests.clase.examen.submit');
        Route::get('/tests/codeplay', [StudentTestsController::class, 'codeplay'])->name('alumno.tests.codeplay');
        Route::post('/tests/codeplay/iniciar', [StudentTestsController::class, 'iniciar'])
            ->middleware('throttle:10,1')->name('alumno.tests.codeplay.iniciar');
        Route::get('/tests/codeplay/{sesion}', [StudentTestsController::class, 'jugar'])
            ->whereNumber('sesion')->name('alumno.tests.codeplay.jugar');
        Route::post('/tests/codeplay/{sesion}/responder', [StudentTestsController::class, 'responder'])
            ->whereNumber('sesion')->middleware('throttle:60,1')->name('alumno.tests.codeplay.responder');
        Route::get('/tests/codeplay/{sesion}/resultado', [StudentTestsController::class, 'resultado'])
            ->whereNumber('sesion')->name('alumno.tests.codeplay.resultado');

        Route::get('/tests/mis-bancos', [StudentBankController::class, 'index'])->name('alumno.bancos.index');
        Route::get('/tests/mis-bancos/crear', [StudentBankController::class, 'create'])->name('alumno.bancos.create');
        Route::post('/tests/mis-bancos', [StudentBankController::class, 'store'])
            ->middleware('throttle:30,1')->name('alumno.bancos.store');
        Route::get('/tests/mis-bancos/{banco}/editar', [StudentBankController::class, 'edit'])
            ->whereNumber('banco')->name('alumno.bancos.edit');
        Route::put('/tests/mis-bancos/{banco}', [StudentBankController::class, 'update'])
            ->whereNumber('banco')->middleware('throttle:30,1')->name('alumno.bancos.update');
        Route::delete('/tests/mis-bancos/{banco}', [StudentBankController::class, 'destroy'])
            ->whereNumber('banco')->middleware('throttle:30,1')->name('alumno.bancos.destroy');
        Route::post('/tests/mis-bancos/{banco}/duplicar', [StudentBankController::class, 'duplicate'])
            ->whereNumber('banco')->middleware('throttle:20,1')->name('alumno.bancos.duplicate');
        Route::post('/tests/mis-bancos/{banco}/publicar', [StudentBankController::class, 'publish'])
            ->whereNumber('banco')->middleware('throttle:20,1')->name('alumno.bancos.publish');
        Route::post('/tests/mis-bancos/{banco}/compartir', [StudentBankController::class, 'share'])
            ->whereNumber('banco')->middleware('throttle:20,1')->name('alumno.bancos.share');
        Route::post('/tests/mis-bancos/{banco}/probar', [StudentBankController::class, 'preview'])
            ->whereNumber('banco')->middleware('throttle:10,1')->name('alumno.bancos.preview');
        Route::post('/tests/mis-bancos/{banco}/dejar-compartir', [StudentBankController::class, 'stopSharing'])
            ->whereNumber('banco')->middleware('throttle:20,1')->name('alumno.bancos.stop-sharing');

        Route::get('/tests/codigo-amigo', [FriendCodeController::class, 'index'])->name('alumno.codigo-amigo.index');
        Route::post('/tests/codigo-amigo/consultar', [FriendCodeController::class, 'lookup'])
            ->middleware('throttle:30,1')->name('alumno.codigo-amigo.lookup');
        Route::post('/tests/codigo-amigo/agregar', [FriendCodeController::class, 'add'])
            ->middleware('throttle:20,1')->name('alumno.codigo-amigo.add');
        Route::delete('/tests/codigo-amigo/{banco}', [FriendCodeController::class, 'remove'])
            ->whereNumber('banco')->middleware('throttle:20,1')->name('alumno.codigo-amigo.remove');
        Route::post('/tests/codigo-amigo/{banco}/copiar', [FriendCodeController::class, 'copy'])
            ->whereNumber('banco')->middleware('throttle:20,1')->name('alumno.codigo-amigo.copy');
        Route::post('/tests/codigo-amigo/{banco}/practicar', [FriendCodeController::class, 'practice'])
            ->whereNumber('banco')->middleware('throttle:10,1')->name('alumno.codigo-amigo.practice');

        Route::get('/tests/amigos', [FriendLiveTestController::class, 'index'])->name('alumno.tests.amigos.index');
        Route::post('/tests/amigos/crear', [FriendLiveTestController::class, 'store'])
            ->middleware('throttle:10,1')->name('alumno.tests.amigos.store');
        Route::post('/tests/amigos/unirse', [FriendLiveTestController::class, 'join'])
            ->middleware('throttle:20,1')->name('alumno.tests.amigos.join');
        Route::get('/tests/amigos/{sesion}/sala', [FriendLiveTestController::class, 'room'])
            ->whereNumber('sesion')->name('alumno.tests.amigos.room');
        Route::post('/tests/amigos/{sesion}/iniciar', [FriendLiveTestController::class, 'start'])
            ->whereNumber('sesion')->middleware('throttle:20,1')->name('alumno.tests.amigos.start');
        Route::post('/tests/amigos/{sesion}/pausar', [FriendLiveTestController::class, 'pause'])
            ->whereNumber('sesion')->middleware('throttle:20,1')->name('alumno.tests.amigos.pause');
        Route::post('/tests/amigos/{sesion}/reanudar', [FriendLiveTestController::class, 'resume'])
            ->whereNumber('sesion')->middleware('throttle:20,1')->name('alumno.tests.amigos.resume');
        Route::post('/tests/amigos/{sesion}/terminar', [FriendLiveTestController::class, 'finish'])
            ->whereNumber('sesion')->middleware('throttle:20,1')->name('alumno.tests.amigos.finish');
        Route::post('/tests/amigos/{sesion}/repetir', [FriendLiveTestController::class, 'repeat'])
            ->whereNumber('sesion')->middleware('throttle:10,1')->name('alumno.tests.amigos.repeat');
        Route::get('/tests/amigos/{sesion}/configurar', [FriendLiveTestController::class, 'configure'])
            ->whereNumber('sesion')->name('alumno.tests.amigos.configure');
        Route::put('/tests/amigos/{sesion}/configurar', [FriendLiveTestController::class, 'update'])
            ->whereNumber('sesion')->middleware('throttle:10,1')->name('alumno.tests.amigos.update');

        Route::post('/tests/mis-bancos/{banco}/preguntas', [StudentBankController::class, 'storeQuestion'])
            ->whereNumber('banco')->middleware('throttle:60,1')->name('alumno.bancos.preguntas.store');
        Route::put('/tests/mis-bancos/{banco}/preguntas/{pregunta}', [StudentBankController::class, 'updateQuestion'])
            ->whereNumber(['banco', 'pregunta'])->middleware('throttle:60,1')->name('alumno.bancos.preguntas.update');
        Route::delete('/tests/mis-bancos/{banco}/preguntas/{pregunta}', [StudentBankController::class, 'destroyQuestion'])
            ->whereNumber(['banco', 'pregunta'])->middleware('throttle:60,1')->name('alumno.bancos.preguntas.destroy');
        Route::post('/tests/mis-bancos/{banco}/preguntas/{pregunta}/mover', [StudentBankController::class, 'moveQuestion'])
            ->whereNumber(['banco', 'pregunta'])->middleware('throttle:60,1')->name('alumno.bancos.preguntas.move');
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

Route::prefix('profesor/tests')->middleware(['auth.custom', 'role:Profesor'])->name('profesor.tests.')->group(function () {
    Route::get('/', [TeacherTestController::class, 'index'])->name('index');
    Route::get('/bancos/crear', [TeacherTestController::class, 'create'])->name('banks.create');
    Route::post('/bancos', [TeacherTestController::class, 'store'])->name('banks.store');
    Route::get('/bancos/{banco}/editar', [TeacherTestController::class, 'edit'])->whereNumber('banco')->name('banks.edit');
    Route::put('/bancos/{banco}', [TeacherTestController::class, 'update'])->whereNumber('banco')->name('banks.update');
    Route::delete('/bancos/{banco}', [TeacherTestController::class, 'destroy'])->whereNumber('banco')->name('banks.destroy');
    Route::post('/bancos/{banco}/publicar', [TeacherTestController::class, 'publish'])->whereNumber('banco')->name('banks.publish');
    Route::post('/bancos/{banco}/asignar', [TeacherTestController::class, 'assign'])->whereNumber('banco')->name('banks.assign');
    Route::post('/bancos/{banco}/preguntas', [TeacherTestController::class, 'storeQuestion'])->whereNumber('banco')->name('questions.store');
    Route::post('/bancos/{banco}/preguntas/importar-sistema', [TeacherTestController::class, 'importSystemQuestions'])->whereNumber('banco')->name('questions.import-system');
    Route::put('/bancos/{banco}/preguntas/{pregunta}', [TeacherTestController::class, 'updateQuestion'])->whereNumber(['banco', 'pregunta'])->name('questions.update');
    Route::delete('/bancos/{banco}/preguntas/{pregunta}', [TeacherTestController::class, 'destroyQuestion'])->whereNumber(['banco', 'pregunta'])->name('questions.destroy');
    Route::post('/sesiones', [TeacherTestController::class, 'createSession'])->name('sessions.store');
    Route::get('/sesiones/{sesion}', [TeacherTestController::class, 'showSession'])->whereNumber('sesion')->name('sessions.show');
    Route::post('/sesiones/{sesion}/{action}', [TeacherTestController::class, 'sessionAction'])
        ->whereNumber('sesion')->whereIn('action', ['start', 'pause', 'resume', 'finish'])->name('sessions.action');
});

Route::prefix('profesor/calificaciones')->middleware(['auth.custom', 'role:Profesor'])->name('profesor.calificaciones.')->group(function () {
    Route::get('/', [TeacherGradebookController::class, 'index'])->name('index');
    Route::get('/exportar', [TeacherGradebookController::class, 'export'])->name('export');
    Route::get('/imprimir-clase', [TeacherGradebookController::class, 'printClass'])->name('print-class');
    Route::get('/resultado/{resultado}', [TeacherGradebookController::class, 'detail'])->whereNumber('resultado')->name('detail');
    Route::post('/sesiones/{sesion}/archivar', [TeacherGradebookController::class, 'archive'])->whereNumber('sesion')->name('archive');
    Route::post('/sesiones/{sesion}/restaurar', [TeacherGradebookController::class, 'restore'])->whereNumber('sesion')->name('restore');
    Route::delete('/sesiones/{sesion}', [TeacherGradebookController::class, 'destroy'])->whereNumber('sesion')->name('destroy');
});

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
