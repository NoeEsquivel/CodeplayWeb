<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyLoginTurnstile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('services.turnstile.enabled')) {
            return $next($request);
        }

        $siteKey = (string) config('services.turnstile.site_key');
        $secret = (string) config('services.turnstile.secret_key');
        $testing = (bool) config('services.turnstile.testing');
        $localTest = $testing && app()->environment(['local', 'testing'])
            && in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'], true)
            && $siteKey === '1x00000000000000000000AA'
            && $secret === '1x0000000000000000000000000000000AA';

        // Las claves públicas de prueba nunca deben proteger un servidor de producción.
        if ($siteKey === '' || $secret === '' || ($testing && ! $localTest)
            || (! $localTest && (preg_match('/^[123]x0{10,}/', $siteKey) || preg_match('/^[123]x0{10,}/', $secret)))) {
            return $this->reject($request, 'La verificación de seguridad no está configurada. Contacta al administrador.');
        }

        $token = $request->input('cf-turnstile-response');
        if (! is_string($token) || trim($token) === '' || strlen($token) > 2048) {
            return $this->reject($request, 'Completa la verificación de seguridad antes de ingresar.');
        }

        try {
            $response = Http::asForm()->connectTimeout(3)->timeout(8)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                ['secret' => $secret, 'response' => $token]
            );
            $result = $response->json();
        } catch (ConnectionException $exception) {
            return $this->reject($request, 'No se pudo contactar con la verificación de seguridad. Revisa tu conexión e intenta nuevamente.');
        }

        $hostnames = config('services.turnstile.hostnames', []);
        if ($hostnames === []) {
            $hostnames = [(string) parse_url(config('app.url'), PHP_URL_HOST)];
        }
        $valid = $response->successful() && ($result['success'] ?? false) === true;
        if (! $localTest) {
            $valid = $valid && ($result['action'] ?? null) === 'login'
                && in_array(strtolower((string) ($result['hostname'] ?? '')), array_map('strtolower', $hostnames), true);
        }

        if (! $valid) {
            return $this->reject($request, 'La verificación de seguridad falló o venció. Vuelve a intentarlo.');
        }

        return $next($request);
    }

    private function reject(Request $request, string $message): Response
    {
        return redirect()->route('login')->with('error', $message)->withInput($request->only('nombre'));
    }
}
