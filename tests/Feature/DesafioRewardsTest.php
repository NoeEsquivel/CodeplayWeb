<?php

namespace Tests\Feature;

use App\Models\Colegio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DesafioRewardsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reward_is_granted_once_then_respects_cooldown_and_allows_half_reward_next_day(): void
    {
        $college = Colegio::create([
            'nombre' => 'Colegio desafío '.uniqid(),
            'codigo' => 'DES-'.uniqid(),
            'activo' => true,
        ]);
        $student = Usuario::create([
            'colegio_id' => $college->id,
            'nombre' => 'Alumno desafío '.uniqid(),
            'clave' => Hash::make('Clave1234'),
            'rol' => Usuario::ROL_ALUMNO,
            'activo' => true,
            'cambio_clave_requerido' => false,
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ]);
        $session = [
            'usuario_id' => $student->id,
            'usuario_session_version' => $student->fresh()->session_version,
        ];
        $validCode = '<h1>Título</h1><p><strong>Texto</strong> <em>destacado</em></p>';

        $this->withSession($session)->postJson(route('desafios.procesar-recompensa'), [
            'nivel' => 1,
            'codigo' => $validCode,
        ])->assertOk()->assertJson([
            'success' => true,
            'puntos_otorgados' => 50,
            'completado_primera_vez' => true,
            'puntos_totales' => 50,
            'puntos_disponibles' => 50,
        ]);

        $this->postJson(route('desafios.procesar-recompensa'), [
            'nivel' => 1,
            'codigo' => $validCode,
        ])->assertOk()->assertJsonPath('success', false)->assertJsonPath('error', 'recompensa_cooldown');

        DB::table('desafio_recompensas')
            ->where('usuario_id', $student->id)
            ->where('nivel_desafio', 1)
            ->update(['ultima_recompensa' => now()->subHours(25)]);

        $this->postJson(route('desafios.procesar-recompensa'), [
            'nivel' => 1,
            'codigo' => $validCode,
        ])->assertOk()->assertJson([
            'success' => true,
            'puntos_otorgados' => 25,
            'completado_primera_vez' => true,
            'puntos_totales' => 75,
            'puntos_disponibles' => 75,
        ]);

        $this->assertDatabaseHas('desafio_recompensas', [
            'usuario_id' => $student->id,
            'nivel_desafio' => 1,
            'puntos_obtenidos' => 75,
        ]);
    }
}