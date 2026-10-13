<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $regiones = [
            2 => [
                ['momia', 'Momia del archivo', 'veneno', 'Cada tercer turno aplica veneno. Terminar el combate elimina sus efectos.'],
                ['vampiro', 'Vampiro de las ruinas', 'vampiro', 'Recupera 35% del daño que realmente te causa. Defenderte y usar barreras reduce su curación.'],
                ['espectro', 'Espectro de tinta', 'resiste_magia', 'Recibe 40% menos daño mágico. Los ataques físicos conservan su fuerza.'],
                ['centinela', 'Centinela de piedra', 'coraza', 'Defensa física elevada; el fuego le causa 50% más daño.'],
                ['nigromante', 'Nigromante del archivo', 'invocar', 'En el tercer turno invoca una sola vez un eco sin recompensa. Derrotarlo antes evita la invocación.'],
            ],
            3 => [
                ['sanguijuela', 'Sanguijuela gigante', 'vampiro', 'Absorbe vida según el daño real de sus ataques. Una barrera impide esa curación.'],
                ['bruja', 'Bruja del pantano', 'drenar', 'Cada segundo turno roba hasta 4 de maná. Defenderse evita el drenaje.'],
                ['cienago', 'Slime de cieno', 'division', 'Se divide una sola vez bajo media vida, repartiendo la vida que le queda. Sus fragmentos no entregan premios.'],
                ['saqueador', 'Saqueador del fango', 'robo_item', 'Esconde una poción de tu mochila una sola vez. La recuperas al vencer; tu inversión no cambia.'],
                ['basilisco', 'Basilisco de musgo', 'veneno', 'Cada tercer turno envenena durante tres turnos. El veneno no se detiene al defenderte.'],
            ],
            4 => [
                ['yeti', 'Yeti de la ventisca', 'embestida', 'Cada tercer turno duplica la fuerza del golpe. Prepárate con defensa o barrera.'],
                ['hechicero', 'Hechicero de escarcha', 'drenar', 'Cada segundo turno roba hasta 5 de maná si no te defiendes.'],
                ['golem_hielo', 'Gólem de hielo', 'coraza', 'Resiste golpes físicos, pero el fuego le causa 50% más daño.'],
                ['lince', 'Lince de cristal', 'robo', 'Esconde oro una sola vez. Puedes recuperarlo al ganar el combate.'],
                ['alma_nieve', 'Alma de la nieve', 'resiste_magia', 'Recibe 40% menos daño de hechizos ofensivos. Prueba un ataque físico.'],
            ],
            5 => [
                ['salamandra', 'Salamandra de ceniza', 'veneno', 'Sus cenizas aplican veneno cada tercer turno; desaparece al terminar el combate.'],
                ['caballero', 'Caballero de obsidiana', 'resiste_fisico', 'Su armadura reduce 35% del daño físico. Los hechizos evitan esa resistencia.'],
                ['cultista', 'Cultista del volcán', 'invocar', 'Invoca una sola vez un eco en el tercer turno. El eco no genera recompensas ni progreso extra.'],
                ['devorador', 'Devorador de brasas', 'vampiro', 'Recupera vida al herirte. Un golpe absorbido no lo cura.'],
                ['acechador', 'Acechador de lava', 'robo_item', 'Esconde una poción una sola vez. Vencer permite recuperarla.'],
            ],
        ];
        DB::transaction(function () use ($regiones) {
            foreach ($regiones as $region => $filas) {
                foreach ($filas as [$clave,$nombre,$habilidad,$descripcion]) {
                    $this->insertar($clave, $nombre, $region, 48 + ($region - 2) * 24, 12 + ($region - 2) * 6, 4 + ($region - 2) * 2 + ($habilidad === 'coraza' ? 4 : 0), 28 + ($region - 2) * 24, 12 + ($region - 2) * 6, $habilidad, $descripcion);
                }
            }
            $this->insertar('archivista', 'Aster, Archivista de las Ruinas', 2, 300, 25, 8, 180, 90, 'jefe', 'En turnos pares su sello reduce 25% del daño físico. Bajo media vida se enfurece. Su ataque rúnico también drena maná.', 'Sello del olvido');
            $this->insertar('matriarca', 'Náyade, Matriarca del Pantano', 3, 420, 33, 10, 260, 120, 'jefe', 'Recupera 25% del daño real de sus golpes normales. Bajo media vida se enfurece. Su ataque rúnico aplica veneno.', 'Marea tóxica');
            $this->insertar('titan', 'Bóreas, Titán de la Escarcha', 4, 560, 41, 12, 360, 160, 'jefe', 'El fuego causa 50% más daño. Sus golpes normales drenan maná si no te defiendes. Bajo media vida se enfurece.', 'Tormenta de cristal');
        });
    }

    private function insertar($clave, $nombre, $region, $vida, $dano, $defensa, $xp, $oro, $habilidad, $descripcion, $ataque = null): void
    {
        DB::table('enemigos')->insert(['clave' => $clave, 'nombre' => $nombre, 'vida' => $vida, 'daño' => $dano, 'defensa' => $defensa, 'magia' => 0, 'velocidad' => 1, 'recompensa_puntos' => $oro,
            'rasgos' => json_encode(['region' => $region, 'experiencia' => $xp, 'habilidad' => $habilidad, 'descripcion' => $descripcion, 'ataque_especial' => $ataque]), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        $claves = ['momia', 'vampiro', 'espectro', 'centinela', 'nigromante', 'sanguijuela', 'bruja', 'cienago', 'saqueador', 'basilisco', 'yeti', 'hechicero', 'golem_hielo', 'lince', 'alma_nieve', 'salamandra', 'caballero', 'cultista', 'devorador', 'acechador', 'archivista', 'matriarca', 'titan'];
        $ids = DB::table('enemigos')->whereIn('clave', $claves)->pluck('id');
        if (DB::table('combates')->whereIn('enemigo_id', $ids)->exists()) {
            throw new RuntimeException('Hay historial asociado al bestiario. No se puede eliminar.');
        }
        foreach (DB::table('aventura_expediciones')->get() as $run) {
            foreach (json_decode($run->exploracion ?? '[]', true) as $e) {
                foreach ($e['combate']['enemigos'] ?? [] as $enemigo) {
                    if (in_array($enemigo['id'], $ids->all(), true)) {
                        throw new RuntimeException('Hay enemigos guardados en una expedición.');
                    }
                }
            }
        }
        DB::table('enemigos')->whereIn('id', $ids)->delete();
    }
};
