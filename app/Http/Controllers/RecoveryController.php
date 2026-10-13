<?php

namespace App\Http\Controllers;

use App\Models\AccountRecoveryToken;
use App\Models\Usuario;
use App\Services\SecurityMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RecoveryController extends Controller
{
    public function __construct(private readonly SecurityMailService $mail) {}

    public function showRequestForm()
    {
        return view('recovery.request');
    }

    public function requestRecovery(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')), 'UTF-8')]);
        $datos = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'tipo' => ['required', 'in:username,password'],
        ]);

        $usuario = Usuario::query()
            ->whereRaw('LOWER(email) = ?', [$datos['email']])
            ->whereNotNull('email_verified_at')
            ->whereIn('rol', [Usuario::ROL_PROFESOR, Usuario::ROL_ADMINISTRADOR_COLEGIO, Usuario::ROL_MASTER])
            ->first();

        if ($usuario) {
            if ($datos['tipo'] === 'username') {
                $this->mail->send(
                    $usuario->email,
                    'Recuperación de usuario de CodePlay',
                    'Tu nombre de usuario es: '.$usuario->nombre
                );
            } else {
                AccountRecoveryToken::query()
                    ->where('usuario_id', $usuario->id)
                    ->where('tipo', 'password')
                    ->whereNull('used_at')
                    ->update(['used_at' => now()]);

                $token = bin2hex(random_bytes(32));
                AccountRecoveryToken::create([
                    'usuario_id' => $usuario->id,
                    'tipo' => 'password',
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => now()->addMinutes(30),
                ]);

                $this->mail->send(
                    $usuario->email,
                    'Restablece tu contraseña de CodePlay',
                    'El enlace vence en 30 minutos, funciona una sola vez y cerrará tus sesiones anteriores.',
                    route('recovery.reset.form', ['token' => $token]),
                    'Restablecer contraseña',
                );
            }
        }

        return back()->with('success', 'Si el correo corresponde a una cuenta habilitada, recibirás las instrucciones de recuperación.');
    }

    public function showResetForm(string $token)
    {
        if (! $this->validToken($token)) {
            return view('recovery.invalid');
        }

        return view('recovery.reset', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $datos = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $registro = $this->validToken($datos['token']);
        if (! $registro) {
            return view('recovery.invalid');
        }

        DB::transaction(function () use ($registro, $datos) {
            $registro->forceFill(['used_at' => now(), 'attempts' => ((int) $registro->attempts) + 1])->save();
            $usuario = $registro->usuario;
            $usuario->forceFill([
                'clave' => Hash::make($datos['password']),
                'cambio_clave_requerido' => false,
                'session_version' => ((int) $usuario->session_version) + 1,
            ])->save();

            AccountRecoveryToken::query()
                ->where('usuario_id', $usuario->id)
                ->whereKeyNot($registro->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $this->mail->send($usuario->email, 'Contraseña de CodePlay actualizada', 'Tu contraseña fue restablecida y todas las sesiones anteriores fueron cerradas.');
        });

        return redirect()->route('login')->with('success', 'Contraseña restablecida. Ya puedes iniciar sesión.');
    }

    private function validToken(string $token): ?AccountRecoveryToken
    {
        return AccountRecoveryToken::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('tipo', 'password')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', 5)
            ->first();
    }
}
