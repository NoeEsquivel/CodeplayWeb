<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enemigos', function (Blueprint $table) {
            $table->string('clave', 60)->nullable()->unique();
            $table->json('rasgos')->nullable();
        });
        Schema::table('combates', function (Blueprint $table) {
            $table->foreignId('expedicion_id')->nullable()->constrained('aventura_expediciones')->restrictOnDelete();
            $table->unsignedTinyInteger('escenario')->nullable();
            $table->json('resumen')->nullable();
            $table->unique(['expedicion_id', 'escenario']);
        });
        $enemigos = [
            ['lobo', 'Lobo del sendero', 28, 7, 1, 14, 'embestida', 'Cada tercer turno ataca con más fuerza. Defenderse reduce el golpe.'],
            ['slime', 'Slime de rocío', 32, 5, 0, 16, 'division', 'Si sobrevive por debajo de media vida, se divide una vez. Elimina primero a sus fragmentos.'],
            ['ladron', 'Duende saqueador', 30, 6, 1, 18, 'robo', 'En su primer turno esconde hasta 8 de tu oro. Al ganar recuperas todo lo robado.'],
            ['avispa', 'Avispa esmeralda', 24, 6, 0, 16, 'veneno', 'Cada tercer turno aplica veneno durante tres turnos. Acabar el combate elimina el veneno.'],
            ['hongo', 'Hongo acorazado', 38, 5, 4, 20, 'coraza', 'Su coraza reduce los ataques físicos. El fuego le causa un 50% más de daño.'],
            ['silvar', 'Silvar, Guardián de las Raíces', 210, 19, 5, 120, 'jefe', 'Bajo media vida se enfurece. Cada tercer turno prepara raíces rúnicas: responde una pregunta para interrumpirlas.'],
        ];
        foreach ($enemigos as [$clave,$nombre,$vida,$dano,$defensa,$xp,$habilidad,$descripcion]) {
            DB::table('enemigos')->insert(['clave' => $clave, 'nombre' => $nombre, 'vida' => $vida, 'daño' => $dano, 'defensa' => $defensa,
                'magia' => 0, 'velocidad' => 1, 'recompensa_puntos' => $clave === 'silvar' ? 60 : 8,
                'rasgos' => json_encode(['region' => 1, 'experiencia' => $xp, 'habilidad' => $habilidad, 'descripcion' => $descripcion]),
                'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (DB::table('combates')->whereNotNull('expedicion_id')->exists()) {
            throw new RuntimeException('Conserva el historial de combates antes de revertir.');
        }
        foreach (DB::table('aventura_expediciones')->get() as $run) {
            foreach (json_decode($run->exploracion ?? '[]', true) as $event) {
                if (isset($event['combate'])) {
                    throw new RuntimeException('Hay combates guardados en expediciones.');
                }
            }
        }
        DB::table('enemigos')->whereIn('clave', ['lobo', 'slime', 'ladron', 'avispa', 'hongo', 'silvar'])->whereNotIn('id', DB::table('combates')->select('enemigo_id'))->delete();
        Schema::table('combates', function (Blueprint $table) {
            $table->dropUnique(['expedicion_id', 'escenario']);
            if (DB::getDriverName() === 'sqlite') {
                $table->dropColumn('expedicion_id');
            } else {
                $table->dropConstrainedForeignId('expedicion_id');
            }
            $table->dropColumn(['escenario', 'resumen']);
        });
        Schema::table('enemigos', function (Blueprint $table) {
            $table->dropUnique(['clave']);
            $table->dropColumn(['clave', 'rasgos']);
        });
    }
};
