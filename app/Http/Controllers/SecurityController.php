<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationToken;
use App\Models\TwoFactorAuthentication;
use App\Models\Usuario;
use App\Services\SecurityMailService;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SecurityController extends Controller
{
    public function __construct(
        private readonly SecurityMailService $mail,
        private readonly TotpService $totp,
    ) {
    }

    public function showEmailForm(Request $request)
    {
        return view('security.email', ['usuario' => $this->usuario($request)]);
    }

    public function saveEmail(Request $request)
    {
        $usuario = $this->usuario($request);
        abort_unless($usuario->requiereCorreoVerificado(), 403);

        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')), 'UTF-8')]);
        $datos = $request->validate([
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
        ]);

        $anterior = $usuario->email;
        $anteriorVerificado = (bool) $usuario->email_verified_at;
        $cambio = mb_strtolower((string) $anterior) !== $datos['email'];

        if ($cambio) {
            $usuario->forceFill([
                'email' => $datos['email'],
                'email_verified_at' => null,
                'session_version' => ((int) $usuario->session_version) + 1,
            ])->save();
            $request->session()->put('usuario_session_version', $usuario->session_version);
            $request->session()->forget('two_factor_verified_user_id');

            if ($anterior && $anteriorVerificado) {
                $this->mail->send($anterior, 'Tu correo de CodePlay fue cambiado', 'El correo asociado a tu cuenta fue modificado.');
            }
        }

        $enviado = $this->sendVerification($usuario->fresh());

        return redirect()->route('security.email.pending')
            ->with($enviado ? 'success' : 'error', $enviado
                ? 'Enviamos un enlace de verificación a tu correo.'
                : 'No fue posible enviar el correo. Revisa la configuración de correo e inténtalo de nuevo.');
    }

    public function pendingEmail(Request $request)
    {
        $usuario = $this->usuario($request);

        if ($usuario->email_verified_at) {
            return $this->afterSecurityStep($request, $usuario);
        }

        return view('security.email-pending', compact('usuario'));
    }

    public function resendVerification(Request $request)
    {
        $usuario = $this->usuario($request);
        abort_unless($usuario->email && !$usuario->email_verified_at, 422);

        $enviado = $this->sendVerification($usuario);

        return back()->with($enviado ? 'success' : 'error', $enviado
            ? 'Enviamos un nuevo enlace de verificación.'
            : 'No fue posible enviar el correo en este momento.');
    }

    public function verifyEmail(string $token)
    {
        $registro = EmailVerificationToken::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$registro) {
            return view('security.verify-result', ['success' => false]);
        }

        DB::transaction(function () use ($registro) {
            $registro->forceFill(['used_at' => now()])->save();
            $registro->usuario->forceFill(['email_verified_at' => now()])->save();
            EmailVerificationToken::query()
                ->where('usuario_id', $registro->usuario_id)
                ->whereKeyNot($registro->id)
                ->delete();
        });

        return view('security.verify-result', ['success' => true]);
    }

    public function showTwoFactorSetup(Request $request)
    {
        $usuario = $this->masterVerificado($request);
        $registro = $usuario->twoFactorAuthentication;

        if ($registro?->confirmed_at) {
            return redirect()->route('security.2fa.challenge');
        }

        if (!$registro) {
            $registro = TwoFactorAuthentication::create([
                'usuario_id' => $usuario->id,
                'secret' => $this->totp->generateSecret(),
            ]);
        }

        return view('security.two-factor-setup', [
            'secret' => $registro->secret,
            'provisioningUri' => $this->totp->provisioningUri($registro->secret, $usuario->email ?: $usuario->nombre),
        ]);
    }

    public function confirmTwoFactor(Request $request)
    {
        $usuario = $this->masterVerificado($request);
        $datos = $request->validate(['codigo' => ['required', 'digits:6']]);
        $registro = $usuario->twoFactorAuthentication;

        if (!$registro || !$this->totp->verify($registro->secret, $datos['codigo'])) {
            throw ValidationException::withMessages(['codigo' => 'El código del autenticador no es válido.']);
        }

        $codigos = $this->generateRecoveryCodes();
        $registro->forceFill([
            'confirmed_at' => now(),
            'recovery_codes' => array_map(fn (string $code) => hash('sha256', $code), $codigos),
        ])->save();

        $request->session()->put('two_factor_verified_user_id', $usuario->id);
        $this->mail->send($usuario->email, 'Segundo factor activado en CodePlay', 'El segundo factor de tu cuenta Master fue activado.');

        return view('security.two-factor-codes', compact('codigos'));
    }

    public function showTwoFactorChallenge(Request $request)
    {
        $usuario = $this->usuario($request);
        abort_unless($usuario->esMaster() && $usuario->email_verified_at, 403);
        abort_unless($usuario->twoFactorAuthentication?->confirmed_at, 403);

        return view('security.two-factor-challenge');
    }

    public function verifyTwoFactorChallenge(Request $request)
    {
        $usuario = $this->usuario($request);
        abort_unless($usuario->esMaster() && $usuario->twoFactorAuthentication?->confirmed_at, 403);

        $datos = $request->validate(['codigo' => ['required', 'string', 'max:30']]);
        $registro = $usuario->twoFactorAuthentication;
        $codigo = strtoupper(trim($datos['codigo']));
        $valido = $this->totp->verify($registro->secret, $codigo);

        if (!$valido) {
            $hash = hash('sha256', $codigo);
            $codigos = $registro->recovery_codes ?? [];
            $indice = array_search($hash, $codigos, true);

            if ($indice !== false) {
                unset($codigos[$indice]);
                $registro->forceFill(['recovery_codes' => array_values($codigos)])->save();
                $valido = true;
            }
        }

        if (!$valido) {
            throw ValidationException::withMessages(['codigo' => 'El código ingresado no es válido.']);
        }

        $request->session()->put('two_factor_verified_user_id', $usuario->id);

        return redirect()->route('inicio.master');
    }

    private function sendVerification(Usuario $usuario): bool
    {
        EmailVerificationToken::query()->where('usuario_id', $usuario->id)->delete();
        $token = bin2hex(random_bytes(32));
        EmailVerificationToken::create([
            'usuario_id' => $usuario->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(60),
        ]);

        $verificationUrl = route('security.email.verify', ['token' => $token]);
        $enviado = $this->mail->send(
            $usuario->email,
            'Verifica tu correo de CodePlay',
            'Confirma que este correo te pertenece. El enlace vence en 60 minutos y solo puede utilizarse una vez.',
            $verificationUrl,
            'Verificar correo',
        );

        if (!$enviado && app()->environment('local')) {
            session()->flash('local_verification_url', $verificationUrl);
        }

        return $enviado;
    }

    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 10))
            ->map(fn () => strtoupper(substr(bin2hex(random_bytes(8)), 0, 4).'-'.substr(bin2hex(random_bytes(8)), 0, 4).'-'.substr(bin2hex(random_bytes(8)), 0, 4)))
            ->all();
    }

    private function masterVerificado(Request $request): Usuario
    {
        $usuario = $this->usuario($request);
        abort_unless($usuario->esMaster() && $usuario->email_verified_at, 403);
        return $usuario;
    }

    private function usuario(Request $request): Usuario
    {
        $usuario = $request->attributes->get('usuario_actual');
        abort_unless($usuario instanceof Usuario, 401);
        return $usuario;
    }

    private function afterSecurityStep(Request $request, Usuario $usuario)
    {
        if ($usuario->esMaster()) {
            return redirect()->route($usuario->twoFactorAuthentication?->confirmed_at
                ? 'security.2fa.challenge'
                : 'security.2fa.setup');
        }

        return redirect()->route($usuario->esProfesor() ? 'inicio.profesor' : 'inicio.administrador-colegio');
    }
}
