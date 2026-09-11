<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\AventuraCatalogo;
use App\Services\AventuraInventario;
use App\Models\AventuraExpedicion;

return new class extends Migration {
    public function up(): void
    {
        // Check ambiguity before MySQL DDL (which is not transactional).
        if (DB::table('aventura_expediciones')->whereIn('estado', ['preparacion', 'activa'])
            ->select('usuario_id')->groupBy('usuario_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Hay varias expediciones abiertas para un usuario. Conciliarlas antes de migrar.');
        }
        foreach (DB::table('aventura_expediciones')->whereIn('estado', ['preparacion', 'activa'])->get() as $run) {
            (new AventuraCatalogo())->cotizar(json_decode($run->equipo, true, 512, JSON_THROW_ON_ERROR), [5, 10, 15, 20]);
        }
        Schema::table('items', function (Blueprint $table) {
            $table->string('clave_catalogo', 80)->nullable()->unique();
            $table->string('categoria', 20)->nullable();
            $table->string('rareza', 20)->nullable();
            $table->json('bonus')->nullable();
            $table->unsignedInteger('mana')->default(0);
            $table->unsignedInteger('desafio')->nullable();
        });
        Schema::table('aventura_expediciones', function (Blueprint $table) {
            $table->unsignedTinyInteger('escenario')->default(1);
            $table->unsignedInteger('vida_actual')->nullable();
            $table->unsignedInteger('mana_actual')->nullable();
            $table->unsignedInteger('oro')->default(0);
            $table->json('efectos')->nullable();
            $table->json('mejoras')->nullable();
            $table->timestamp('iniciada_at')->nullable();
        });
        Schema::table('inventario', function (Blueprint $table) {
            $table->foreignId('expedicion_id')->nullable()->constrained('aventura_expediciones')->restrictOnDelete();
            $table->string('posicion', 20)->nullable();
        });
        DB::transaction(function () {
            foreach ((new AventuraCatalogo())->items() as $item) {
                DB::table('items')->insert([
                    'clave_catalogo' => $item['id'], 'nombre' => $item['nombre'],
                    'tipo' => $item['tipo'] === 'pocion' ? 'consumible' : 'equipamiento',
                    'categoria' => $item['tipo'], 'rareza' => $item['rareza'], 'precio' => $item['precio'],
                    'descripcion' => $item['descripcion'], 'bonus' => json_encode($item['bonus']),
                    'mana' => $item['mana'], 'desafio' => $item['desafio'], 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            foreach (DB::table('aventura_expediciones')->whereIn('estado', ['preparacion', 'activa'])->get() as $anterior) {
                $equipo = json_decode($anterior->equipo, true, 512, JSON_THROW_ON_ERROR);
                $run = AventuraExpedicion::findOrFail($anterior->id);
                AventuraInventario::guardar($run, $equipo);
                $bonus = (new AventuraCatalogo())->cotizar($equipo, [5, 10, 15, 20])['bonus'];
                $personaje = DB::table('aventura_personajes')->where('usuario_id', $anterior->usuario_id)->first();
                DB::table('aventura_expediciones')->where('id', $anterior->id)->update([
                    'vida_actual' => 100 + 5 * (($personaje->vitalidad ?? 1) + $bonus['vitalidad'] - 1),
                    'mana_actual' => 50 + 2 * (($personaje->inteligencia ?? 1) + $bonus['inteligencia'] - 1),
                    'efectos' => '[]', 'mejoras' => '[]',
                    'iniciada_at' => $anterior->estado === 'activa' ? $anterior->updated_at : null,
                ]);
            }
        });
        // The UI gets equipo through an accessor, never a second stored inventory.
        Schema::table('aventura_expediciones', fn (Blueprint $table) => $table->dropColumn('equipo'));
    }

    public function down(): void
    {
        // A rollback must not silently discard loot or a played expedition.
        if (DB::table('aventura_expediciones')->where('estado', 'activa')->exists()
            || DB::table('inventario')->whereNotNull('expedicion_id')->whereNull('posicion')->exists()) {
            throw new RuntimeException('Finaliza las partidas y conserva el botín antes de revertir esta integración.');
        }
        $equipos = AventuraExpedicion::all()->mapWithKeys(fn ($run) => [$run->id => $run->equipo]);
        Schema::table('aventura_expediciones', fn (Blueprint $table) => $table->json('equipo')->nullable());
        foreach ($equipos as $id => $equipo) {
            DB::table('aventura_expediciones')->where('id', $id)->update(['equipo' => json_encode($equipo)]);
        }
        DB::table('inventario')->whereNotNull('expedicion_id')->delete();
        DB::table('items')->whereNotNull('clave_catalogo')->whereNotIn('id', DB::table('inventario')->select('item_id'))->delete();
        Schema::table('inventario', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') $table->dropColumn('expedicion_id');
            else $table->dropConstrainedForeignId('expedicion_id');
            $table->dropColumn('posicion');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique(['clave_catalogo']);
            $table->dropColumn(['clave_catalogo', 'categoria', 'rareza', 'bonus', 'mana', 'desafio']);
        });
        Schema::table('aventura_expediciones', fn (Blueprint $table) => $table->dropColumn([
            'escenario', 'vida_actual', 'mana_actual', 'oro', 'efectos', 'mejoras', 'iniciada_at',
        ]));
    }
};
