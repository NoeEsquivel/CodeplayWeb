<?php

namespace Tests\Feature;

use App\Http\Middleware\CustomAuthMiddleware;
use App\Http\Middleware\RequireRole;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MiniArcadeEndpointTest extends TestCase
{
    public function test_preview_checks_require_authentication(): void
    {
        $this->postJson('/desafios/validar-proyecto-final', ['codigo' => '{}'])->assertUnauthorized();
    }

    public function test_invalid_requests_never_reach_the_database(): void
    {
        $this->withoutMiddleware([CustomAuthMiddleware::class, RequireRole::class]);
        $this->withSession(['usuario_id' => 999999]);
        DB::shouldReceive('beginTransaction')->never();
        DB::shouldReceive('table')->never();

        foreach ([[], ['codigo' => '{}'], ['codigo' => ['menu' => 'x']], ['codigo' => str_repeat('x', 60001)]] as $request) {
            $this->postJson('/desafios/validar-proyecto-final', $request)->assertStatus(422);
        }

        $this->postJson('/desafios/procesar-recompensa', [
            'nivel' => 20,
            'codigo' => "' OR 1=1; DROP TABLE usuarios; --",
            'puntos' => 999999,
            'checks' => ['snakeMove' => true, 'memoryMatch' => true],
        ])->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_starter_checks_report_pending_goals_without_awarding_points(): void
    {
        $this->withoutMiddleware([CustomAuthMiddleware::class, RequireRole::class]);
        $this->withSession(['usuario_id' => 999999]);
        DB::shouldReceive('beginTransaction')->never();
        DB::shouldReceive('table')->never();

        $pages = [];
        foreach (['menu', 'culebrita', 'memoria'] as $page) {
            $pages[$page] = file_get_contents(public_path("desafios/mini-arcade/{$page}.html"));
        }

        $code = json_encode($pages);
        $this->postJson('/desafios/validar-proyecto-final', ['codigo' => $code])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'checks' => ['snakeMove' => false, 'memoryMatch' => false],
            ]);

        $this->postJson('/desafios/procesar-recompensa', [
            'nivel' => 20,
            'codigo' => $code,
        ])->assertStatus(422)->assertJson(['success' => false]);
    }
}
