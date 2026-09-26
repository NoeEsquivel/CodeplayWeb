<?php

namespace Tests\Feature;

use App\Mail\SecurityNotificationMail;
use App\Models\AccountRecoveryToken;
use App\Models\Colegio;
use App\Models\EmailVerificationToken;
use App\Models\TwoFactorAuthentication;
use App\Models\Usuario;
use App\Services\SecurityMailService;
use App\Services\TotpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Se requiere pdo_sqlite.');
        }

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        foreach ([
            '2025_09_30_064434_create_usuarios_table.php',
            '2025_10_01_063305_add_cambio_clave_requerido_to_usuarios_table.php',
            '2025_10_02_083703_add_ultimo_acceso_to_usuarios_table.php',
            '2026_09_24_120000_create_colegios_and_expand_user_roles.php',
            '2026_09_24_121000_add_email_to_usuarios_table.php',
            '2026_09_24_122000_assign_existing_users_to_ined_maria_luisa.php',
            '2026_09_24_123000_create_clases_and_memberships.php',
            '2026_09_25_090000_add_account_security.php',
            '2026_09_25_091000_add_ciclo_escolar_to_clases.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        Mail::fake();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_existing_professor_must_add_and_verify_email_before_entering(): void
    {
        $profesor = $this->usuario(Usuario::ROL_PROFESOR);

        $this->withSession(['usuario_id' => $profesor->id, 'usuario_session_version' => 1])
            ->get('/inicio-profesor')
            ->assertRedirect(route('security.email.form'));

        $this->post('/seguridad/correo', ['email' => 'profesor@example.test'])
            ->assertRedirect(route('security.email.pending'));

        $profesor->refresh();
        $this->assertSame('profesor@example.test', $profesor->email);
        $this->assertNull($profesor->email_verified_at);
        $this->assertDatabaseCount('email_verification_tokens', 1);
        Mail::assertSent(SecurityNotificationMail::class);

        $token = 'token-verificacion-seguro';
        EmailVerificationToken::query()->delete();
        EmailVerificationToken::create([
            'usuario_id' => $profesor->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
        ]);

        $this->get('/verificar-correo/'.$token)->assertOk()->assertSee('Correo verificado');
        $this->get('/verificar-correo/'.$token)->assertOk()->assertSee('Enlace no válido');
        $this->assertNotNull($profesor->fresh()->email_verified_at);

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_session_version' => $profesor->fresh()->session_version,
        ])->get('/inicio-profesor')->assertOk();
    }

    public function test_recovery_is_generic_rejects_expired_and_reused_links_and_closes_sessions(): void
    {
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, [
            'email' => 'recuperacion@example.test',
            'email_verified_at' => now(),
        ]);

        $message = 'Si el correo corresponde a una cuenta habilitada, recibirás las instrucciones de recuperación.';

        $this->post('/recuperar-cuenta', ['email' => 'inexistente@example.test', 'tipo' => 'password'])
            ->assertSessionHas('success', $message);
        $this->assertDatabaseCount('account_recovery_tokens', 0);

        $actionUrl = null;
        $this->post('/recuperar-cuenta', ['email' => $profesor->email, 'tipo' => 'password'])
            ->assertSessionHas('success', $message);
        Mail::assertSent(SecurityNotificationMail::class, function (SecurityNotificationMail $mail) use (&$actionUrl) {
            if ($mail->actionLabel === 'Restablecer contraseña') {
                $actionUrl = $mail->actionUrl;
                return true;
            }
            return false;
        });

        $this->assertNotNull($actionUrl);
        $token = basename((string) parse_url($actionUrl, PHP_URL_PATH));
        $this->get('/restablecer-clave/'.$token)->assertOk();

        $this->post('/restablecer-clave', [
            'token' => $token,
            'password' => 'NuevaClave9',
            'password_confirmation' => 'NuevaClave9',
        ])->assertRedirect(route('login'));

        $profesor->refresh();
        $this->assertTrue(Hash::check('NuevaClave9', $profesor->clave));
        $this->assertSame(2, $profesor->session_version);
        $this->get('/restablecer-clave/'.$token)->assertOk()->assertSee('Enlace no válido');

        $this->withSession(['usuario_id' => $profesor->id, 'usuario_session_version' => 1])
            ->getJson('/api/usuarios-por-rol/Alumno')
            ->assertUnauthorized();

        $expired = 'token-vencido-seguro';
        AccountRecoveryToken::create([
            'usuario_id' => $profesor->id,
            'tipo' => 'password',
            'token_hash' => hash('sha256', $expired),
            'expires_at' => now()->subMinute(),
        ]);
        $this->get('/restablecer-clave/'.$expired)->assertOk()->assertSee('Enlace no válido');
    }

    public function test_local_environment_offers_a_safe_link_when_smtp_is_unavailable(): void
    {
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, [
            'email' => 'local@example.test',
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->app['env'] = 'local';
        $this->mock(SecurityMailService::class, function ($mock) {
            $mock->shouldReceive('send')->once()->andReturnFalse();
        });

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_session_version' => 1,
        ])->post('/seguridad/correo/reenviar')
            ->assertRedirect()
            ->assertSessionHas('local_verification_url');

        $this->get('/seguridad/correo/pendiente')
            ->assertOk()
            ->assertSee('Verificar mi correo ahora');
    }

    public function test_students_do_not_receive_self_service_recovery(): void
    {
        $alumno = $this->usuario(Usuario::ROL_ALUMNO, [
            'email' => 'alumno@example.test',
            'email_verified_at' => now(),
        ]);

        $this->post('/recuperar-cuenta', ['email' => $alumno->email, 'tipo' => 'password'])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('account_recovery_tokens', 0);
        Mail::assertNothingSent();
    }

    public function test_master_requires_totp_and_recovery_codes_are_one_use(): void
    {
        $master = $this->usuario(Usuario::ROL_MASTER, [
            'colegio_id' => null,
            'email' => 'master@example.test',
            'email_verified_at' => now(),
        ]);

        $session = ['usuario_id' => $master->id, 'usuario_session_version' => 1];

        $this->withSession($session)->get('/inicio-master')
            ->assertRedirect(route('security.2fa.setup'));

        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $recovery = 'ABCD-EFGH-IJKL';
        TwoFactorAuthentication::create([
            'usuario_id' => $master->id,
            'secret' => $secret,
            'confirmed_at' => now(),
            'recovery_codes' => [hash('sha256', $recovery)],
        ]);

        $this->withSession($session)->get('/inicio-master')
            ->assertRedirect(route('security.2fa.challenge'));

        $this->post('/seguridad/doble-factor', ['codigo' => $recovery])
            ->assertRedirect(route('inicio.master'))
            ->assertSessionHas('two_factor_verified_user_id', $master->id);

        $this->get('/inicio-master')->assertOk();
        $this->post('/logout');

        $this->withSession($session)->post('/seguridad/doble-factor', ['codigo' => $recovery])
            ->assertSessionHasErrors('codigo');

        $validCode = $totp->code($secret, intdiv(time(), 30));
        $this->post('/seguridad/doble-factor', ['codigo' => $validCode])
            ->assertRedirect(route('inicio.master'));
    }

    public function test_email_resend_limit_is_independent_for_each_account(): void
    {
        $profesor = $this->usuario(Usuario::ROL_PROFESOR, ['email' => 'profesor-limite@example.test']);
        $administrador = $this->usuario(Usuario::ROL_ADMINISTRADOR_COLEGIO, ['email' => 'admin-limite@example.test']);

        RateLimiter::clear('security-email-resend:'.$profesor->id);
        RateLimiter::clear('security-email-resend:'.$administrador->id);

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->withSession([
                'usuario_id' => $profesor->id,
                'usuario_session_version' => 1,
            ])->post('/seguridad/correo/reenviar')->assertRedirect();
        }

        $this->withSession([
            'usuario_id' => $administrador->id,
            'usuario_session_version' => 1,
        ])->post('/seguridad/correo/reenviar')
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->withSession([
            'usuario_id' => $profesor->id,
            'usuario_session_version' => 1,
        ])->post('/seguridad/correo/reenviar')
            ->assertRedirect()
            ->assertSessionHas('error', 'Has realizado demasiados intentos. Espera unos minutos antes de volver a intentarlo.');
    }

    public function test_classes_have_school_year_and_keep_database_uniqueness(): void
    {
        $colegio = Colegio::firstOrFail();
        $clase = $colegio->clases()->firstOrFail();

        $this->assertSame((string) now()->year, $clase->ciclo_escolar);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('clase_alumno')->insert([
            ['clase_id' => $clase->id, 'usuario_id' => $this->usuario(Usuario::ROL_ALUMNO)->id, 'created_at' => now(), 'updated_at' => now()],
            ['clase_id' => $clase->id, 'usuario_id' => Usuario::latest('id')->value('id'), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function usuario(string $rol, array $attributes = []): Usuario
    {
        $colegioId = Colegio::first()?->id;

        return Usuario::create(array_merge([
            'colegio_id' => $rol === Usuario::ROL_MASTER ? null : $colegioId,
            'nombre' => $rol.' '.bin2hex(random_bytes(3)),
            'email' => null,
            'clave' => Hash::make('Clave1234'),
            'rol' => $rol,
            'cambio_clave_requerido' => false,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ], $attributes));
    }
}
