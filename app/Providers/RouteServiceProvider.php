<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('security-email-update', function (Request $request) {
            return $this->accountLimit($request, 'security-email-update', 5, 10);
        });

        RateLimiter::for('security-email-resend', function (Request $request) {
            return $this->accountLimit($request, 'security-email-resend', 3, 10);
        });

        RateLimiter::for('security-two-factor', function (Request $request) {
            return $this->accountLimit($request, 'security-two-factor', 10, 5);
        });

        RateLimiter::for('account-recovery', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email')), 'UTF-8');
            $response = fn (Request $request, array $headers) => redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', 'token']))
                ->with('error', 'Has realizado demasiados intentos. Espera unos minutos antes de volver a intentarlo.')
                ->withHeaders($headers);

            return [
                Limit::perMinutes(10, 20)
                    ->by('recovery-ip:'.$request->ip())
                    ->response($response),
                Limit::perMinutes(10, 5)
                    ->by('recovery-email:'.hash('sha256', $email))
                    ->response($response),
            ];
        });

        RateLimiter::for('live-tests', function (Request $request) {
            $usuario = $request->attributes->get('usuario_actual');
            $usuarioId = $usuario?->id ?: $request->session()->get('usuario_id') ?: $request->ip();
            $routeSession = $request->route('sesion');
            $sesionId = is_object($routeSession) ? ($routeSession->id ?? 'general') : ($routeSession ?: 'general');
            $routeName = (string) optional($request->route())->getName();
            $isPolling = $routeName === 'tests.live.state';

            return Limit::perMinute($isPolling ? 240 : 90)
                ->by("live-tests:{$routeName}:{$usuarioId}:{$sesionId}")
                ->response(fn (Request $request, array $headers) => response()->json([
                    'success' => false,
                    'message' => 'La sala recibió demasiadas solicitudes seguidas. Intenta nuevamente en unos segundos.',
                ], 429, $headers));
        });

        $this->routes(function () {
            Route::middleware('api')->prefix('api')->group(base_path('routes/api.php'));
            Route::middleware('web')->group(base_path('routes/web.php'));
        });
    }

    private function accountLimit(Request $request, string $prefix, int $attempts, int $minutes): Limit
    {
        $identity = $request->session()->get('usuario_id') ?: $request->ip();

        return Limit::perMinutes($minutes, $attempts)
            ->by($prefix.':'.$identity)
            ->response(fn (Request $request, array $headers) => redirect()->back()
                ->with('error', 'Has realizado demasiados intentos. Espera unos minutos antes de volver a intentarlo.')
                ->withHeaders($headers));
    }
}
