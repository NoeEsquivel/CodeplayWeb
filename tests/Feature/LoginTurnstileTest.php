<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyLoginTurnstile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginTurnstileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.turnstile' => [
            'enabled' => true, 'testing' => false, 'site_key' => 'real-site-key',
            'secret_key' => 'real-secret-key', 'hostnames' => ['codeplay.lat'],
        ]]);
        Http::preventStrayRequests();
    }

    public function test_disabled_installation_does_not_call_cloudflare(): void
    {
        config(['services.turnstile.enabled' => false]);
        $this->fake();
        $this->assertSame(200, $this->verify()->getStatusCode());
        Http::assertNothingSent();
        $this->get('/login')->assertOk()->assertDontSee('challenges.cloudflare.com');
    }

    public function test_missing_token_and_invalid_tokens_are_rejected_before_authentication(): void
    {
        $this->fake();
        foreach ([null, '', ['invalid'], str_repeat('x', 2049)] as $token) {
            $this->assertSame(302, $this->verify($token)->getStatusCode());
        }
        Http::assertNothingSent();
        $this->fake(['challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['timeout-or-duplicate']])]);
        $this->post(route('login.verify'), ['nombre' => 'Prueba', 'clave' => 'NuncaGuardar', 'cf-turnstile-response' => 'expired'])
            ->assertRedirect(route('login'))->assertSessionHas('error')->assertSessionMissing('usuario_id');
        $this->assertArrayNotHasKey('clave', session()->getOldInput());
    }

    public function test_production_verifies_success_action_and_hostname(): void
    {
        foreach ([
            ['success' => false, 'hostname' => 'codeplay.lat', 'action' => 'login'],
            ['success' => true, 'hostname' => 'otro.test', 'action' => 'login'],
            ['success' => true, 'hostname' => 'codeplay.lat', 'action' => 'register'],
            ['success' => true],
        ] as $data) {
            $this->fake(['challenges.cloudflare.com/*' => Http::response($data)]);
            $this->assertSame(302, $this->verify('token')->getStatusCode());
        }
        $this->fake(['challenges.cloudflare.com/*' => Http::response(['success' => true, 'hostname' => 'codeplay.lat', 'action' => 'login'])]);
        $this->assertSame(200, $this->verify('token')->getStatusCode());
        Http::assertSent(fn ($request) => $request['secret'] === 'real-secret-key' && $request['response'] === 'token');
    }

    public function test_configuration_errors_network_errors_and_invalid_responses_fail_closed(): void
    {
        $this->fake();
        config(['services.turnstile.secret_key' => '']);
        $this->assertSame(302, $this->verify('token')->getStatusCode());
        Http::assertNothingSent();
        config(['services.turnstile.secret_key' => 'real-secret-key']);
        $this->fake(fn () => throw new ConnectionException('Sin conexión'));
        $this->assertSame(302, $this->verify('token')->getStatusCode());
        $this->fake(['challenges.cloudflare.com/*' => Http::response('not json', 500)]);
        $this->assertSame(302, $this->verify('token')->getStatusCode());
    }

    public function test_official_test_keys_only_work_in_explicit_local_mode(): void
    {
        config(['services.turnstile.testing' => true,
            'services.turnstile.site_key' => '1x00000000000000000000AA',
            'services.turnstile.secret_key' => '1x0000000000000000000000000000000AA']);
        $this->fake(['challenges.cloudflare.com/*' => Http::response(['success' => true, 'hostname' => 'example.com'])]);
        $this->assertSame(200, $this->verify('XXXX.DUMMY.TOKEN.XXXX')->getStatusCode());
        $this->app->instance('env', 'production');
        $this->assertSame(302, $this->verify('XXXX.DUMMY.TOKEN.XXXX')->getStatusCode());
        $this->app->instance('env', 'testing');
        $this->assertSame(302, $this->verify('token', 'https://codeplay.lat')->getStatusCode());
        config(['services.turnstile.testing' => false]);
        $this->assertSame(302, $this->verify('token')->getStatusCode());
    }

    public function test_login_shows_only_the_public_key(): void
    {
        $this->get('/login')->assertOk()->assertSee('challenges.cloudflare.com/turnstile/v0/api.js', false)
            ->assertSee('data-sitekey="real-site-key"', false)->assertSee('data-action="login"', false)
            ->assertDontSee('real-secret-key');
    }

    private function fake($callback = null): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake($callback);
    }

    private function verify(mixed $token = null, string $url = 'http://localhost/login/verify')
    {
        $request = Request::create($url, 'POST', ['nombre' => 'Prueba', 'clave' => 'NuncaGuardar', 'cf-turnstile-response' => $token]);
        $request->setLaravelSession(session()->driver());
        return (new VerifyLoginTurnstile)->handle($request, fn () => response('Autorizado'));
    }
}
