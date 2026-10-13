<?php

use App\Services\AventuraCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NUEVOS_HECHIZOS = [
        'quebrarrocas', 'raiz-vital', 'ritual-doble', 'tormenta-cadena',
        'furia-runica', 'imbuicion-ignea', 'terremoto',
    ];

    public function up(): void
    {
        $catalogo = (new AventuraCatalogo)->items();
        foreach (array_merge(self::NUEVOS_HECHIZOS, ['supernova']) as $clave) {
            $item = $catalogo[$clave];
            DB::table('items')->updateOrInsert(
                ['clave_catalogo' => $clave],
                [
                    'nombre' => $item['nombre'],
                    'tipo' => 'equipamiento',
                    'categoria' => $item['tipo'],
                    'rareza' => $item['rareza'],
                    'precio' => $item['precio'],
                    'descripcion' => $item['descripcion'],
                    'bonus' => json_encode($item['bonus']),
                    'mana' => $item['mana'],
                    'desafio' => $item['desafio'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('items')->whereIn('clave_catalogo', self::NUEVOS_HECHIZOS)
            ->whereNotIn('id', DB::table('inventario')->select('item_id'))
            ->delete();
        DB::table('items')->where('clave_catalogo', 'supernova')->update([
            'precio' => 850,
            'descripcion' => 'Ataque estelar: 70 + tu magia de daño.',
            'mana' => 36,
            'updated_at' => now(),
        ]);
    }
};
