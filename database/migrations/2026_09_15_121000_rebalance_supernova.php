<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('items')->where('clave_catalogo', 'supernova')->update([
            'precio' => 1500,
            'descripcion' => 'Explosión cósmica: 150 + tu magia y 50% de tu vida faltante. Cuesta 75 de maná y 15% de tu vida máxima. Ignora 75% de la defensa y resistencia mágica.',
            'mana' => 75,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('items')->where('clave_catalogo', 'supernova')->update([
            'precio' => 1800,
            'descripcion' => 'Explosión cósmica: 150 + tu magia de daño. Ignora 75% de la defensa y resistencia mágica.',
            'mana' => 70,
            'updated_at' => now(),
        ]);
    }
};
